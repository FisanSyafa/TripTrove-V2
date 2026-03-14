<?php
// app/Http/Controllers/UserReviewController.php
namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Review;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Gate; // Hapus atau komen baris ini
use Inertia\Inertia;

class UserReviewController extends Controller
{
    /**
     * Menampilkan form untuk membuat review.
     */
    public function create(Booking $booking)
    {
        // PERBAIKAN: Cek manual apakah user yang login adalah pemilik booking
        if ((int) $booking->user_id !== (int) auth()->id()) {
            abort(403, 'Anda tidak memiliki akses untuk mengulas booking ini.');
        }

        // Pastikan booking sudah selesai
        if ($booking->status !== 'completed') {
            return redirect()->route('dashboard')->with('error', 'Anda hanya bisa memberi ulasan untuk tur yang sudah selesai.');
        }

        // Pastikan booking belum di-review
        if ($booking->review) {
            return redirect()->route('dashboard')->with('error', 'Anda sudah memberi ulasan untuk booking ini.');
        }

        // Load data paket untuk ditampilkan di form
        $booking->load('tourPackage');

        return Inertia::render('Reviews/Create', [
            'booking' => $booking
        ]);
    }

    /**
     * Simpan review baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:5000',
        ]);

        $booking = Booking::findOrFail($validated['booking_id']);

        // PERBAIKAN: Cek manual authorisasi di sini juga
        if ((int) $booking->user_id !== (int) auth()->id()) {
            abort(403, 'Anda tidak memiliki akses untuk mengulas booking ini.');
        }

        // Pastikan booking sudah selesai
         if ($booking->status !== 'completed') {
            return redirect()->route('dashboard')->with('error', 'Anda hanya bisa memberi ulasan untuk tur yang sudah selesai.');
        }

        // Pastikan belum ada review (double check)
        if ($booking->review) {
             return redirect()->route('dashboard')->with('error', 'Anda sudah memberi ulasan untuk booking ini.');
        }

        // Buat review
        Review::create([
            'booking_id' => $booking->id,
            'user_id' => auth()->id(),
            'tour_package_id' => $booking->tour_package_id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'review_date' => now(),
            'is_approved' => false, // Default pending, menunggu moderasi
        ]);

        return redirect()->route('dashboard')->with('message', 'Terima kasih! Ulasan Anda telah terkirim dan menunggu moderasi.');
    }
}