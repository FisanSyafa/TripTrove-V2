<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use App\Models\Payment;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentSuccessMail;

class PaymentCallbackController extends Controller
{
    public function handle(Request $request)
    {
        $serverKey = config('midtrans.server_key');
        $hashed = hash("sha512", $request->order_id.$request->status_code.$request->gross_amount.$serverKey);

        // Validasi Signature Key dari Midtrans (Keamanan)
        if ($hashed == $request->signature_key) {
            
            $booking = Booking::where('booking_code', $request->order_id)->first();
            
            if (!$booking) return response()->json(['message' => 'Booking not found'], 404);

            if ($request->transaction_status == 'capture' || $request->transaction_status == 'settlement') {
                // PEMBAYARAN SUKSES
                
                // 1. Update status booking
                $booking->update(['status' => 'waiting_confirmation']);

                // 2. Tambah Popularitas (Sold Count)
                $booking->tourPackage->increment('sold_count');
                
                // 3. Buat record Payment
                Payment::create([
                    'booking_id' => $booking->id,
                    'amount' => $request->gross_amount,
                    'payment_method' => $request->payment_type,
                    'status' => 'verified',
                    'paid_at' => now(),
                    'payment_code' => $request->transaction_id
                ]);

                // 4. Kirim email notifikasi payment success
                $booking->load(['user', 'tourPackage']);
                Mail::to($booking->contact_email)->send(new PaymentSuccessMail($booking));

            } elseif ($request->transaction_status == 'expire' || $request->transaction_status == 'cancel' || $request->transaction_status == 'deny') {
                // PEMBAYARAN GAGAL
                $booking->update(['status' => 'cancelled']);
            }
        }

        return response()->json(['message' => 'Callback received']);
    }
}