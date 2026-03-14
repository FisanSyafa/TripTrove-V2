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

class PaymentController extends Controller
{
    public function create(Booking $booking)
    {
        // Otorisasi
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        // PERBAIKAN: Load relasi 'tourPackage' agar data paket tersedia di frontend
        $booking->load('tourPackage');

        // Jika sudah bayar, jangan bayar lagi
        if (in_array($booking->status, ['confirmed', 'paid', 'waiting_confirmation', 'completed'])) {
            return redirect()->route('dashboard')->with('message', 'Booking ini sudah dibayar.');
        }

        // Konfigurasi Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');

        // Jika snap_token belum ada, buat baru
        if (empty($booking->snap_token)) {
            $params = [
                'transaction_details' => [
                    'order_id' => $booking->booking_code, // Gunakan Booking Code yang unik
                    'gross_amount' => (int) $booking->total_price,
                ],
                'customer_details' => [
                    'first_name' => auth()->user()->name,
                    'email' => auth()->user()->email,
                    'phone' => $booking->contact_phone,
                ],
                // Mengaktifkan fitur Credit Card & QRIS
                'enabled_payments' => ['credit_card', 'gopay', 'qris', 'bank_transfer'],
            ];

            $snapToken = Snap::getSnapToken($params);
            
            // Simpan token ke database agar tidak generate ulang terus
            $booking->update(['snap_token' => $snapToken]);
        }

        return Inertia::render('Payment/Create', [
            'booking' => $booking,
            'clientKey' => config('midtrans.client_key'), // Kirim client key ke frontend
            'snapToken' => $booking->snap_token
        ]);
    }

    /**
     * Handle payment success dari frontend (untuk development tanpa webhook)
     */
    public function handleSuccess(Request $request, Booking $booking)
    {
        // Otorisasi
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        // Jika sudah diproses, skip
        if (in_array($booking->status, ['waiting_confirmation', 'confirmed', 'paid', 'completed'])) {
            return redirect()->route('dashboard')->with('message', 'Pembayaran sudah diproses sebelumnya.');
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
        $booking->load(['user', 'tourPackage']);
        Mail::to($booking->contact_email)->send(new PaymentSuccessMail($booking));

        return redirect()->route('dashboard')->with('message', 'Pembayaran berhasil! Booking Anda sedang diproses.');
    }
}