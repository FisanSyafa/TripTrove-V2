<?php
// app/Http/Controllers/Admin/ReviewController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReviewController extends Controller
{
    // Menampilkan daftar semua review
    public function index(Request $request)
    {
        $reviews = Review::with(['user', 'tourPackage'])
            ->when($request->input('status'), fn($q, $s) => 
                $q->where('is_approved', $s === 'approved')
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Reviews/Index', [
            'reviews' => $reviews,
            'filters' => $request->only(['status'])
        ]);
    }

    // Menyetujui/Membatalkan persetujuan review
    public function update(Request $request, Review $review)
    {
        $validated = $request->validate([
            'is_approved' => 'required|boolean',
        ]);

        $review->update($validated);

        $message = $validated['is_approved'] ? 'Review disetujui.' : 'Persetujuan review dibatalkan.';
        return redirect()->route('admin.reviews.index')->with('message', $message);
    }

    // Menghapus review
    public function destroy(Review $review)
    {
        $review->delete();
        return redirect()->route('admin.reviews.index')->with('message', 'Review berhasil dihapus.');
    }
}