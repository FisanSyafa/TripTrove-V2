<?php
// app/Http/Controllers/DashboardController.php
namespace App\Http\Controllers; // Pastikan namespace benar

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Booking;

class DashboardController extends Controller
{
    public function index()
    {
        // Gunakan relasi dari user yang login
        // 'bookings()' adalah method relasi di App/Models/User.php
        $bookings = auth()->user()->bookings() 
            ->with(['tourPackage', 'review']) // Eager load (ini sudah benar)
            ->latest()
            ->paginate(10) // <-- GANTI .get() MENJADI .paginate()
            ->withQueryString(); // Agar filter/search tetap ada saat ganti halaman

        return Inertia::render('Dashboard', [
            'bookings' => $bookings // Kirim Paginator Object
        ]);
    }
}