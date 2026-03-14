<?php
// app/Http/Controllers/BookingController.php
namespace App\Http\Controllers;

use App\Models\TourPackage;
use App\Models\Booking;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingCreatedMail;

class BookingController extends Controller
{
    /**
     * Menampilkan form booking untuk login dan guest user.
     */
    public function create(TourPackage $package)
    {
        if ($package->status !== 'published') {
            abort(404);
        }

        return Inertia::render('Booking/Create', [
            'package' => $package
        ]);
    }

    /**
     * Simpan booking (untuk login user dan guest)
     */
    public function store(Request $request)
    {
        $rules = [
            'package_id' => 'required|exists:tour_packages,id',
            'departure_date' => 'required|date|after_or_equal:today',
            'num_adults' => 'required|integer|min:1',
            'num_children' => 'nullable|integer|min:0',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'required|string|max:20',
            'country_code' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:100',
            'pickup_location' => 'nullable|string|max:500',
            'special_requests' => 'nullable|string',
        ];

        // Guest users need to provide their name
        if (!Auth::check()) {
            $rules['guest_name'] = 'required|string|max:255';
        }

        $validatedData = $request->validate($rules);

        $package = TourPackage::findOrFail($validatedData['package_id']);
        
        $numAdults = $validatedData['num_adults'];
        $numChildren = $validatedData['num_children'] ?? 0;
        $numParticipants = $numAdults + $numChildren;

        $basePrice = $package->price;
        $discountPercent = $package->discount_percent;
        $pricePerPersonAfterDiscount = $basePrice - ($basePrice * $discountPercent / 100);
        $totalPrice = $pricePerPersonAfterDiscount * $numParticipants;

        $departureDate = Carbon::parse($validatedData['departure_date']);
        $endDate = $departureDate->copy()->addDays($package->duration_days - 1); 

        DB::beginTransaction();
        try {
            $bookingData = [
                'booking_code' => 'TRV-' . strtoupper(Str::random(8)),
                'user_id' => Auth::check() ? auth()->id() : null,
                'guest_name' => $validatedData['guest_name'] ?? null,
                'tour_package_id' => $package->id,
                'departure_date' => $validatedData['departure_date'],
                'end_date' => $endDate,
                
                'num_participants' => $numParticipants,
                'num_adults' => $numAdults,
                'num_children' => $numChildren,
                'contact_email' => $validatedData['contact_email'],
                'contact_phone' => $validatedData['contact_phone'],
                'country_code' => $validatedData['country_code'] ?? null,
                'country' => $validatedData['country'] ?? null,
                'pickup_location' => $validatedData['pickup_location'] ?? null,

                'package_price_at_booking' => $basePrice,
                'discount_at_booking' => $discountPercent,
                'total_price' => $totalPrice,
                'status' => 'pending',
                'special_requests' => $validatedData['special_requests'] ?? null,
            ];

            // Add locale only if the column exists (after migration is run)
            if (\Schema::hasColumn('bookings', 'locale')) {
                $bookingData['locale'] = app()->getLocale();
            }

            $booking = Booking::create($bookingData);

            DB::commit();

            // Kirim email notifikasi booking created (jangan biarkan error email menghalangi redirect)
            try {
                $booking->load(['user', 'tourPackage']);
                Mail::to($booking->contact_email)->send(new BookingCreatedMail($booking));
            } catch (\Exception $mailError) {
                \Log::error('Failed to send booking email: ' . $mailError->getMessage());
                // Continue anyway, email is not critical
            }

            return redirect()->route('payment.create', $booking->id)->with('message', 'Booking Anda berhasil! Silakan lanjutkan pembayaran.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Booking creation failed: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->withInput()->withErrors(['error' => 'Terjadi kesalahan saat membuat booking: ' . $e->getMessage()]);
        }
    }

    /**
     * Menampilkan invoice untuk booking yang sudah dibayar
     */
    public function invoice(Booking $booking)
    {
        // Pastikan user hanya bisa melihat invoice miliknya sendiri (atau admin)
        if (Auth::check()) {
            if ($booking->user_id !== null && (int) $booking->user_id !== (int) auth()->id() && !auth()->user()->isAdmin()) {
                abort(403, 'Unauthorized action.');
            }
        } else {
            abort(403, 'Unauthorized action.');
        }

        // Pastikan booking sudah dibayar (waiting_confirmation atau confirmed)
        if (!in_array($booking->status, ['waiting_confirmation', 'confirmed', 'completed'])) {
            return redirect()->route('dashboard')->with('error', 'Invoice hanya tersedia untuk booking yang sudah dibayar.');
        }

        // Load relasi yang diperlukan
        $booking->load(['user', 'tourPackage', 'driver', 'guide', 'vehicle']);

        return view('invoice.booking', compact('booking'));
    }

    /**
     * Handle Pay on Arrival - Update status dan return WhatsApp URL
     */
    public function payOnArrival(Booking $booking)
    {
        // Pastikan user bisa akses booking miliknya sendiri (logged in or guest via session)
        if (Auth::check() && $booking->user_id !== null && (int) $booking->user_id !== (int) auth()->id()) {
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

        // Prepare WhatsApp message with detailed info
        $adminPhone = config('app.admin_whatsapp_number');
        
        $participantLabel = __('Adult');
        $childrenLabel = __('Children');
        
        $participantText = $booking->num_adults . ' ' . $participantLabel;
        if ($booking->num_children > 0) {
            $participantText .= ' & ' . $booking->num_children . ' ' . $childrenLabel;
        }

        $pickupTime = $booking->tourPackage->pickup_time ?? '-';

        $message = "*" . __('Booking Confirmation - Pay on Arrival') . "*\n\n";
        $message .= __('Halo TripTrove,') . "\n\n";
        $message .= __('I want to confirm a booking with the following details:') . "\n\n";
        $message .= "📋 *" . __('Booking Code') . ":* {$booking->booking_code}\n";
        $message .= "🎫 *" . __('Package') . ":* {$booking->tourPackage->name}\n";
        $message .= "📍 *" . __('Destination') . ":* {$booking->tourPackage->destination_summary}\n";
        $message .= "📅 *" . __('Departure Date') . ":* " . \Carbon\Carbon::parse($booking->departure_date)->format('d F Y') . "\n";
        $message .= "⏰ *" . __('Pickup Time') . ":* {$pickupTime}\n";
        $message .= "👥 *" . __('Participants') . ":* {$participantText}\n";
        $message .= "💰 *" . __('Total Price') . ":* Rp " . number_format($booking->total_price, 0, ',', '.') . "\n\n";
        $message .= "💳 *" . __('Payment Method') . ":* " . __('Pay on Arrival') . " (" . __('Pay when we meet') . ")\n\n";
        $message .= __('Please confirm my booking. Thank you!') . " 🙏";

        $waUrl = 'https://wa.me/' . $adminPhone . '?text=' . urlencode($message);

        // Return JSON dengan WhatsApp URL
        return response()->json([
            'success' => true,
            'whatsapp_url' => $waUrl
        ]);
    }
}