<?php
// app/Http/Controllers/BookingController.php
namespace App\Http\Controllers;

use App\Models\TourPackage;
use App\Models\Booking;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingCreatedMail;

class BookingController extends Controller
{
    /**
     * Menampilkan form booking JIKA user login.
     * Mengarahkan ke WhatsApp JIKA user adalah tamu.
     * (INI METHOD YANG KITA UBAH)
     */
    public function create(TourPackage $package)
    {
        if ($package->status !== 'published') {
            abort(404);
        }

        // Cek apakah pengguna sudah login
        if (Auth::check()) {
            return Inertia::render('Booking/Create', [
                'package' => $package
            ]);
        } else {
            
            $adminPhone = config('app.admin_whatsapp_number');

            $message = "Halo TripTrove,\n\n";
            $message .= "Saya tertarik untuk memesan paket tur:\n";
            $message .= "*{$package->name}*\n\n";
            $message .= "Mohon informasi lebih lanjut mengenai ketersediaan dan cara pemesanannya.\n\n";
            $message .= "Terima kasih.";

            $waUrl = 'https://wa.me/' . $adminPhone . '?text=' . urlencode($message);

            return Inertia::location($waUrl);
        }
    }

    /**
     * Simpan booking (HANYA UNTUK PENGGUNA LOGIN)
     * (Method ini TIDAK BERUBAH)
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'package_id' => 'required|exists:tour_packages,id',
            'departure_date' => 'required|date|after_or_equal:today',
            
            // Validasi baru
            'num_participants' => 'required|integer|min:1',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'required|string|max:20',

            // Validasi lama (dihapus)
            // 'passengers' => 'required|array|min:1',
            // 'passengers.*.full_name' => 'required|string|max:255',
            // 'passengers.*.date_of_birth' => 'required|date|before:today',
            
            'special_requests' => 'nullable|string',
        ]);

        $package = TourPackage::findOrFail($validatedData['package_id']);
        
        // Ambil jumlah peserta langsung dari form
        $numParticipants = $validatedData['num_participants'];

        $basePrice = $package->price;
        $discountPercent = $package->discount_percent;
        $pricePerPersonAfterDiscount = $basePrice - ($basePrice * $discountPercent / 100);
        $totalPrice = $pricePerPersonAfterDiscount * $numParticipants;

        $departureDate = Carbon::parse($validatedData['departure_date']);
        $endDate = $departureDate->copy()->addDays($package->duration_days - 1); 

        DB::beginTransaction();
        try {
            $booking = Booking::create([
                'booking_code' => 'TRV-' . strtoupper(Str::random(8)),
                'user_id' => auth()->id(),
                'tour_package_id' => $package->id,
                'departure_date' => $validatedData['departure_date'],
                'end_date' => $endDate,
                
                // Simpan data baru
                'num_participants' => $numParticipants,
                'contact_email' => $validatedData['contact_email'],
                'contact_phone' => $validatedData['contact_phone'],

                'package_price_at_booking' => $basePrice,
                'discount_at_booking' => $discountPercent,
                'total_price' => $totalPrice,
                'status' => 'pending',
                'special_requests' => $validatedData['special_requests'] ?? null,
            ]);
            
            // HAPUS LOOPING 'passengers'
            // foreach ($validatedData['passengers'] as $passengerData) {
            //     $booking->passengers()->create($passengerData);
            // }

            DB::commit();

            // Kirim email notifikasi booking created
            $booking->load(['user', 'tourPackage']);
            Mail::to($booking->contact_email)->send(new BookingCreatedMail($booking));

            return redirect()->route('dashboard')->with('message', 'Booking Anda berhasil! Menunggu pembayaran.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e->getMessage()); // Log error
            return back()->withInput()->with('error', 'Terjadi kesalahan saat membuat booking. Silakan coba lagi.');
        }
    }

    /**
     * Menampilkan invoice untuk booking yang sudah dibayar
     */
    public function invoice(Booking $booking)
    {
        // Pastikan user hanya bisa melihat invoice miliknya sendiri
        if ((int) $booking->user_id !== (int) auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        // Pastikan booking sudah dibayar (waiting_confirmation atau confirmed)
        if (!in_array($booking->status, ['waiting_confirmation', 'confirmed', 'completed'])) {
            return redirect()->route('dashboard')->with('error', 'Invoice hanya tersedia untuk booking yang sudah dibayar.');
        }

        // Load relasi yang diperlukan (tanpa payment karena tidak selalu ada)
        $booking->load(['user', 'tourPackage', 'driver', 'guide', 'vehicle']);

        return view('invoice.booking', compact('booking'));
    }

    /**
     * Handle Pay on Arrival - Update status dan return WhatsApp URL
     */
    public function payOnArrival(Booking $booking)
    {
        // Pastikan user hanya bisa akses booking miliknya sendiri
        if ((int) $booking->user_id !== (int) auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Pastikan booking masih pending
        if ($booking->status !== 'pending') {
            return response()->json(['error' => 'Booking sudah diproses'], 400);
        }

        // Update status ke waiting_confirmation
        $booking->update([
            'status' => 'waiting_confirmation'
        ]);

        // Increment sold_count
        $booking->tourPackage->increment('sold_count');

        // Kirim email notifikasi
        $booking->load(['user', 'tourPackage']);
        \Mail::to($booking->contact_email)->send(new \App\Mail\PaymentSuccessMail($booking));

        // Prepare WhatsApp message
        $adminPhone = config('app.admin_whatsapp_number');
        
        $message = "*Booking Confirmation - Pay on Arrival*\n\n";
        $message .= "Halo TripTrove,\n\n";
        $message .= "Saya ingin mengkonfirmasi booking dengan detail berikut:\n\n";
        $message .= "📋 *Booking Code:* {$booking->booking_code}\n";
        $message .= "🎫 *Package:* {$booking->tourPackage->name}\n";
        $message .= "📍 *Destination:* {$booking->tourPackage->destination_summary}\n";
        $message .= "📅 *Departure Date:* " . \Carbon\Carbon::parse($booking->departure_date)->format('d F Y') . "\n";
        $message .= "👥 *Participants:* {$booking->num_participants} orang\n";
        $message .= "💰 *Total Price:* Rp " . number_format($booking->total_price, 0, ',', '.') . "\n\n";
        $message .= "💳 *Payment Method:* Pay on Arrival (Bayar saat bertemu)\n\n";
        $message .= "Mohon konfirmasi booking saya. Terima kasih! 🙏";

        $waUrl = 'https://wa.me/' . $adminPhone . '?text=' . urlencode($message);

        // Return JSON dengan WhatsApp URL
        return response()->json([
            'success' => true,
            'whatsapp_url' => $waUrl
        ]);
    }
}