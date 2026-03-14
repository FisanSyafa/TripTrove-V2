<?php
// app/Http/Controllers/Admin/BookingController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingConfirmedMail;

class BookingController extends Controller
{
    /**
     * Menampilkan daftar semua booking.
     */
    public function index(Request $request)
    {
        $bookings = Booking::query()
            ->with(['user', 'tourPackage']) // Eager load relasi
            ->when($request->input('search'), function($query, $search) {
                $query->where('booking_code', 'like', "%{$search}%")
                      ->orWhereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"))
                      ->orWhereHas('tourPackage', fn($q) => $q->where('name', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Bookings/Index', [
            'bookings' => $bookings,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Menampilkan detail booking dan dropdown yang sudah difilter.
     * (INI ADALAH METHOD YANG DIPERBAIKI)
     */
    public function show(Booking $booking)
    {
        $booking->load(['user', 'tourPackage', 'passengers', 'payments', 'driver', 'guide', 'vehicle']);

        // 1. Tentukan rentang tanggal booking yang sedang dilihat
        $bookingStartDate = Carbon::parse($booking->departure_date);
        $bookingEndDate = $booking->end_date 
            ? Carbon::parse($booking->end_date) 
            : $bookingStartDate->copy()->addDays($booking->tourPackage->duration_days - 1);

        // 2. Query dasar untuk booking yang konflik
        $conflictingBookingsQuery = fn($query) => 
            $query->whereIn('status', ['confirmed', 'waiting_confirmation', 'paid']) // <-- (Saya tambahkan 'paid' untuk keamanan)
                  ->where('id', '!=', $booking->id) // Abaikan booking ini sendiri
                  ->where(function ($q) use ($bookingStartDate, $bookingEndDate) {
                      $q->whereBetween('departure_date', [$bookingStartDate, $bookingEndDate])
                      ->orWhereBetween('end_date', [$bookingStartDate, $bookingEndDate])
                      ->orWhere(function ($sub) use ($bookingStartDate, $bookingEndDate) {
                          $sub->where('departure_date', '<', $bookingStartDate)
                              ->where('end_date', '>', $bookingEndDate);
                      });
                  });

        // =======================================================
        // PERBAIKAN LOGIKA ADA DI 3 BARIS DI BAWAH INI
        // =======================================================
        
        // 3. Ambil ID Driver, Guide, dan Vehicle yang SIBUK
        // (Kita jalankan query konflik di tabel 'bookings', LALU ambil ID-nya)
        
        // GANTI DARI: Booking::whereHas('driver', $conflictingBookingsQuery)...
        $unavailableDriverIds = Booking::where($conflictingBookingsQuery)
                                      ->whereNotNull('assigned_driver_id')
                                      ->pluck('assigned_driver_id');
                                        
        // GANTI DARI: Booking::whereHas('guide', $conflictingBookingsQuery)...
        $unavailableGuideIds = Booking::where($conflictingBookingsQuery)
                                     ->whereNotNull('assigned_guide_id')
                                     ->pluck('assigned_guide_id');

        // GANTI DARI: Booking::whereHas('vehicle', $conflictingBookingsQuery)...
        $unavailableVehicleIds = Booking::where($conflictingBookingsQuery)
                                       ->whereNotNull('assigned_vehicle_id')
                                       ->pluck('assigned_vehicle_id');

        // =======================================================
        // AKHIR DARI PERBAIKAN
        // =======================================================

        // 4. Ambil data dropdown
        // (Logika ini sudah benar: Ambil yg TIDAK SIBUK, ATAU yg sedang di-assign di booking INI)
        $drivers = User::where('role', 'driver')
                        ->where(function($query) use ($unavailableDriverIds, $booking) {
                            $query->whereNotIn('id', $unavailableDriverIds)
                                  ->orWhere('id', $booking->assigned_driver_id);
                        })
                        ->get(['id', 'name']);

        $guides = User::where('role', 'guide')
                       ->where(function($query) use ($unavailableGuideIds, $booking) {
                            $query->whereNotIn('id', $unavailableGuideIds)
                                  ->orWhere('id', $booking->assigned_guide_id);
                        })
                       ->get(['id', 'name']);
        
        $vehicles = Vehicle::where('is_available', true)
                           ->where(function($query) use ($unavailableVehicleIds, $booking) {
                                $query->whereNotIn('id', $unavailableVehicleIds)
                                      ->orWhere('id', $booking->assigned_vehicle_id);
                           })
                           ->get(['id', 'name', 'license_plate']);

        return Inertia::render('Admin/Bookings/Show', [
            'booking' => $booking,
            'drivers' => $drivers,
            'guides' => $guides,
            'vehicles' => $vehicles,
        ]);
    }

    /**
     * Update booking SETELAH validasi konflik.
     */
    public function update(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,waiting_confirmation,paid,confirmed,cancelled,completed',
            'assigned_driver_id' => 'nullable|exists:users,id',
            'assigned_guide_id' => 'nullable|exists:users,id',
            'assigned_vehicle_id' => 'nullable|exists:vehicles,id',
        ]);

        // [BARU] 1. Simpan status lama sebelum di-update
        // Ini penting untuk memastikan counter tidak bertambah berkali-kali jika admin menekan simpan ulang.
        $oldStatus = $booking->status;

        // =======================================================
        // VALIDASI KONFLIK JADWAL SAAT SUBMIT (BARU)
        // =======================================================

        // 1. Dapatkan rentang tanggal booking ini
        $bookingStartDate = Carbon::parse($booking->departure_date);
        $bookingEndDate = $booking->end_date 
            ? Carbon::parse($booking->end_date) 
            : $bookingStartDate->copy()->addDays($booking->tourPackage->duration_days - 1);

        // Fungsi helper untuk query konflik
        $checkConflict = function ($resourceId, $resourceColumn) use ($booking, $bookingStartDate, $bookingEndDate) {
            if (!$resourceId) {
                return false; // Lewati jika ID null (tidak di-assign)
            }

            return Booking::where('id', '!=', $booking->id) // Bukan booking ini
                ->where($resourceColumn, $resourceId)     // Dengan resource yg sama
                ->whereIn('status', ['confirmed', 'waiting_confirmation', 'paid']) // Yg aktif
                ->where(function ($q) use ($bookingStartDate, $bookingEndDate) {
                    $q->whereBetween('departure_date', [$bookingStartDate, $bookingEndDate])
                      ->orWhereBetween('end_date', [$bookingStartDate, $bookingEndDate])
                      ->orWhere(function ($sub) use ($bookingStartDate, $bookingEndDate) {
                          $sub->where('departure_date', '<', $bookingStartDate)
                              ->where('end_date', '>', $bookingEndDate);
                      });
                })
                ->exists();
        };

        // 2. Cek Driver
        if ($checkConflict($validated['assigned_driver_id'], 'assigned_driver_id')) {
            throw ValidationException::withMessages([
                'assigned_driver_id' => 'Driver ini sudah ditugaskan ke booking lain pada tanggal tersebut.',
            ]);
        }

        // 3. Cek Guide
        if ($checkConflict($validated['assigned_guide_id'], 'assigned_guide_id')) {
            throw ValidationException::withMessages([
                'assigned_guide_id' => 'Guide ini sudah ditugaskan ke booking lain pada tanggal tersebut.',
            ]);
        }
        
        // 4. Cek Vehicle
        if ($checkConflict($validated['assigned_vehicle_id'], 'assigned_vehicle_id')) {
            throw ValidationException::withMessages([
                'assigned_vehicle_id' => 'Kendaraan ini sudah ditugaskan ke booking lain pada tanggal tersebut.',
            ]);
        }
        // =======================================================
        // AKHIR VALIDASI KONFLIK
        // =======================================================

        // Update data booking
        $booking->update($validated);

        // [BARU] 2. Logika Update Popularitas (Sold Count)
        // Definisikan status yang dianggap "Deal / Terjual"
        $soldStatuses = ['confirmed', 'paid', 'completed'];

        // Skenario A: Booking baru saja disetujui/dibayar (tambah counter)
        // Cek: Status LAMA bukan 'sold', tapi status BARU adalah 'sold'
        if (!in_array($oldStatus, $soldStatuses) && in_array($booking->status, $soldStatuses)) {
            $booking->tourPackage->increment('sold_count');
        }

        // Skenario B: Booking dibatalkan setelah sebelumnya disetujui (kurangi counter)
        // Cek: Status LAMA adalah 'sold', tapi status BARU 'cancelled'
        if (in_array($oldStatus, $soldStatuses) && $booking->status === 'cancelled') {
            $booking->tourPackage->decrement('sold_count');
        }

        // [BARU] 3. Kirim email notifikasi saat status berubah ke 'confirmed'
        if ($oldStatus !== 'confirmed' && $booking->status === 'confirmed') {
            $booking->load(['user', 'tourPackage', 'driver', 'guide', 'vehicle']);
            Mail::to($booking->contact_email)->send(new BookingConfirmedMail($booking));
        }

        return redirect()->route('admin.bookings.show', $booking->id)
                         ->with('message', 'Booking berhasil diperbarui.');
    }
}