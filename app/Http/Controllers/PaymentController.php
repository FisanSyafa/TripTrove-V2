<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentSuccessMail;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function create(Booking $booking)
    {
        // Otorisasi: hanya pemilik booking atau guest yang membuat booking ini
        if (Auth::check() && $booking->user_id !== null && $booking->user_id !== auth()->id()) {
            abort(403);
        }

        // PERBAIKAN: Load relasi 'tourPackage' agar data paket tersedia di frontend
        $booking->load('tourPackage');

        // Jika sudah bayar, jangan bayar lagi
        if (in_array($booking->status, ['confirmed', 'paid', 'waiting_confirmation', 'completed'])) {
            if (Auth::check()) {
                return redirect()->route('dashboard')->with('message', 'Booking ini sudah dibayar.');
            }
            return redirect()->route('home')->with('message', 'Booking ini sudah dibayar.');
        }

        // Konfigurasi Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');

        // Jika snap_token belum ada, buat baru
        if (empty($booking->snap_token)) {
            $customerName = Auth::check() ? auth()->user()->name : ($booking->guest_name ?? 'Guest');
            $customerEmail = Auth::check() ? auth()->user()->email : $booking->contact_email;

            $params = [
                'transaction_details' => [
                    'order_id' => $booking->booking_code,
                    'gross_amount' => (int) $booking->total_price,
                ],
                'customer_details' => [
                    'first_name' => $customerName,
                    'email' => $customerEmail,
                    'phone' => $booking->contact_phone,
                ],
                'enabled_payments' => ['credit_card', 'gopay', 'qris', 'bank_transfer'],
            ];

            $snapToken = Snap::getSnapToken($params);
            
            $booking->update(['snap_token' => $snapToken]);
        }

        $snapScript = config('midtrans.is_production') 
            ? 'https://app.midtrans.com/snap/snap.js' 
            : 'https://app.sandbox.midtrans.com/snap/snap.js';

        return Inertia::render('Payment/Create', [
            'booking' => $booking,
            'clientKey' => config('midtrans.client_key'),
            'snapToken' => $booking->snap_token,
            'snapScript' => $snapScript,
            'adminWhatsappNumber' => config('app.admin_whatsapp_number'),
        ]);
    }

    /**
     * Handle payment success dari frontend
     */
    public function handleSuccess(Request $request, Booking $booking)
    {
        // Otorisasi
        if (Auth::check() && $booking->user_id !== null && $booking->user_id !== auth()->id()) {
            abort(403);
        }

        // Jika rute dipanggil sebagai GET (misal karena redirect HTTPS), 
        // dan booking sudah diproses, langsung ke halaman sukses.
        if ($request->isMethod('get')) {
            if (in_array($booking->status, ['waiting_confirmation', 'confirmed', 'paid', 'completed'])) {
                return redirect()->route('payment.success.page', $booking->id);
            }
            // Jika belum diproses tapi dipanggil GET, kemungkinan ada yang salah, 
            // tapi kita coba arahkan ke halaman payment lagi atau home.
            return redirect()->route('payment.create', $booking->id);
        }

        // Jika sudah diproses (POST), langsung redirect ke halaman sukses (jangan ke dashboard/home)
        if (in_array($booking->status, ['waiting_confirmation', 'confirmed', 'paid', 'completed'])) {
            return redirect()->route('payment.success.page', $booking->id);
        }

        // Update status booking
        $booking->update(['status' => 'waiting_confirmation']);

        // Tambah sold count
        $booking->tourPackage->increment('sold_count');

        // Buat record payment
        Payment::create([
            'booking_id' => $booking->id,
            'amount' => $booking->total_price,
            'payment_method' => $request->input('payment_type', 'midtrans'),
            'status' => 'verified',
            'paid_at' => now(),
            'payment_code' => $request->input('transaction_id', 'TXN-' . time())
        ]);

        // Kirim email notifikasi
        try {
            $booking->load(['user', 'tourPackage']);
            Mail::to($booking->contact_email)->send(new PaymentSuccessMail($booking));
        } catch (\Exception $mailError) {
            \Log::error('Failed to send payment success email: ' . $mailError->getMessage());
        }

        // SELALU arahkan ke halaman sukses, baik Guest maupun Logged-in User
        // agar mereka semua bisa melihat tombol WhatsApp & konfirmasi.
        return redirect()->route('payment.success.page', $booking->id);
    }

    /**
     * Show payment success page for guest users
     */
    public function success(Booking $booking)
    {
        try {
            // Only allow access if booking is paid/confirmed
            if (!in_array($booking->status, ['waiting_confirmation', 'confirmed', 'paid', 'completed'])) {
                return redirect()->route('home')->with('error', 'Booking not found or not paid.');
            }

            // Load necessary relations
            $booking->load('tourPackage');

            // Generate WhatsApp URL with confirmation message
            $adminPhone = config('app.admin_whatsapp_number');
            
            $participantLabel = __('Adult');
            $childrenLabel = __('Children');
            
            $participantText = $booking->num_adults . ' ' . $participantLabel;
            if ($booking->num_children > 0) {
                $participantText .= ' & ' . $booking->num_children . ' ' . $childrenLabel;
            }

            $pickupTime = $booking->tourPackage->pickup_time ?? '-';

            $message = "*" . __('Payment Confirmation - Paid') . "*\n\n";
            $message .= __('Halo TripTrove,') . "\n\n";
            $message .= __('I have successfully completed payment for my booking:') . "\n\n";
            $message .= "📋 *" . __('Booking Code') . ":* {$booking->booking_code}\n";
            $message .= "🎫 *" . __('Package') . ":* {$booking->tourPackage->name}\n";
            $message .= "📍 *" . __('Destination') . ":* {$booking->tourPackage->destination_summary}\n";
            $message .= "📅 *" . __('Departure Date') . ":* " . \Carbon\Carbon::parse($booking->departure_date)->format('d F Y') . "\n";
            $message .= "⏰ *" . __('Pickup Time') . ":* {$pickupTime}\n";
            $message .= "👥 *" . __('Participants') . ":* {$participantText}\n";
            $message .= "💰 *" . __('Total Paid') . ":* Rp " . number_format($booking->total_price, 0, ',', '.') . "\n\n";
            $message .= "✅ *" . __('Payment Status') . ":* " . __('Paid Successfully') . "\n\n";
            $message .= __('Please confirm my booking and provide trip details. Thank you!') . " 🙏";

            $waUrl = 'https://wa.me/' . $adminPhone . '?text=' . urlencode($message);

            return Inertia::render('Payment/Success', [
                'booking' => $booking,
                'whatsappUrl' => $waUrl,
            ]);
        } catch (\Exception $e) {
            \Log::error('Payment success page error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Fallback to home page with success message
            return redirect()->route('home')->with('message', 'Pembayaran berhasil! Terima kasih telah mempercayai TripTrove.');
        }
    }
}