<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Review;
use App\Models\User;
use App\Models\TourPackage;
use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    /**
     * Menampilkan data statistik di dashboard admin.
     */
    public function index()
    {
        // 1. Stat Cards - Data Utama
        $pendingBookingsCount = Booking::where('status', 'waiting_confirmation')->count();
        $pendingReviewsCount = Review::where('is_approved', false)->count();
        $totalRevenue = Booking::whereIn('status', ['confirmed', 'completed', 'paid'])
                                ->sum('total_price');
        $totalCustomers = User::where('role', 'user')->count();
        
        // Stat Cards - Data Tambahan
        $totalBookings = Booking::count();
        $activePackages = TourPackage::where('status', 'published')->count();
        $avgBookingValue = Booking::whereIn('status', ['confirmed', 'completed', 'paid'])
                                   ->avg('total_price') ?? 0;
        
        // Calculate monthly growth
        $currentMonthRevenue = Booking::whereIn('status', ['confirmed', 'completed', 'paid'])
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('total_price');
        
        $lastMonthRevenue = Booking::whereIn('status', ['confirmed', 'completed', 'paid'])
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->sum('total_price');
        
        $monthlyGrowth = $lastMonthRevenue > 0 
            ? round((($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : 0;

        // 2. Chart Data - Monthly Revenue (Last 12 Months)
        $monthlyRevenue = collect();
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $revenue = Booking::whereIn('status', ['confirmed', 'completed', 'paid'])
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('total_price');
            
            $monthlyRevenue->push([
                'month' => $date->translatedFormat('M Y'),
                'revenue' => (int) $revenue,
            ]);
        }

        // 3. Chart Data - Booking Status Distribution
        $bookingStatusDistribution = Booking::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->status => $item->count];
            });

        // 4. Chart Data - Top 5 Packages by Revenue
        $topPackages = TourPackage::select('tour_packages.id', 'tour_packages.name')
            ->join('bookings', 'tour_packages.id', '=', 'bookings.tour_package_id')
            ->whereIn('bookings.status', ['confirmed', 'completed', 'paid'])
            ->groupBy('tour_packages.id', 'tour_packages.name')
            ->selectRaw('SUM(bookings.total_price) as total_revenue')
            ->selectRaw('COUNT(bookings.id) as total_bookings')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();

        // 5. Monthly Revenue Summary (for export table)
        $monthlyRevenueSummary = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $bookingsCount = Booking::whereIn('status', ['confirmed', 'completed', 'paid'])
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();
            $revenue = Booking::whereIn('status', ['confirmed', 'completed', 'paid'])
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('total_price');
            
            $monthlyRevenueSummary->push([
                'month' => $date->translatedFormat('F Y'),
                'bookings' => $bookingsCount,
                'revenue' => (int) $revenue,
            ]);
        }

        // 6. Ambil data untuk "Booking Terbaru (Perlu Aksi)"
        $recentBookings = Booking::query()
            ->with(['user:id,name', 'tourPackage:id,name'])
            ->whereIn('status', ['waiting_confirmation', 'confirmed'])
            ->orderByRaw("CASE WHEN status = 'waiting_confirmation' THEN 1 ELSE 2 END")
            ->orderBy('departure_date', 'asc')
            ->limit(5)
            ->get();
            
        // 7. Ambil data untuk "Review Terbaru (Perlu Aksi)"
        $recentReviews = Review::query()
            ->with(['user:id,name', 'tourPackage:id,name'])
            ->where('is_approved', false)
            ->latest('id')
            ->limit(5)
            ->get();


        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'pendingBookings' => $pendingBookingsCount,
                'pendingReviews' => $pendingReviewsCount,
                'totalRevenue' => $totalRevenue,
                'totalCustomers' => $totalCustomers,
                'totalBookings' => $totalBookings,
                'activePackages' => $activePackages,
                'avgBookingValue' => round($avgBookingValue),
                'monthlyGrowth' => $monthlyGrowth,
                'currentMonthRevenue' => $currentMonthRevenue,
            ],
            'chartData' => [
                'monthlyRevenue' => $monthlyRevenue,
                'bookingStatusDistribution' => $bookingStatusDistribution,
                'topPackages' => $topPackages,
            ],
            'monthlyRevenueSummary' => $monthlyRevenueSummary,
            'recentBookings' => $recentBookings,
            'recentReviews' => $recentReviews,
        ]);
    }

    /**
     * Export revenue data ke CSV
     */
    public function exportRevenueCsv()
    {
        $data = collect();
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $bookingsCount = Booking::whereIn('status', ['confirmed', 'completed', 'paid'])
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();
            $revenue = Booking::whereIn('status', ['confirmed', 'completed', 'paid'])
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('total_price');
            
            $data->push([
                'Bulan' => $date->translatedFormat('F Y'),
                'Jumlah Booking' => $bookingsCount,
                'Pendapatan (Rp)' => $revenue,
            ]);
        }

        $filename = 'revenue_report_' . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            // Add BOM for Excel UTF-8 compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header row
            fputcsv($file, array_keys($data->first()));
            
            // Data rows
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export top packages data ke CSV
     */
    public function exportPackagesCsv()
    {
        $topPackages = TourPackage::select('tour_packages.id', 'tour_packages.name', 'tour_packages.price', 'tour_packages.category')
            ->leftJoin('bookings', 'tour_packages.id', '=', 'bookings.tour_package_id')
            ->groupBy('tour_packages.id', 'tour_packages.name', 'tour_packages.price', 'tour_packages.category')
            ->selectRaw('COALESCE(SUM(CASE WHEN bookings.status IN ("confirmed", "completed", "paid") THEN bookings.total_price ELSE 0 END), 0) as total_revenue')
            ->selectRaw('COUNT(CASE WHEN bookings.status IN ("confirmed", "completed", "paid") THEN bookings.id END) as total_bookings')
            ->orderByDesc('total_revenue')
            ->get();

        $data = $topPackages->map(function ($pkg) {
            return [
                'Nama Paket' => $pkg->name,
                'Kategori' => $pkg->category ?? '-',
                'Harga (Rp)' => $pkg->price,
                'Total Booking' => $pkg->total_bookings,
                'Total Revenue (Rp)' => $pkg->total_revenue,
            ];
        });

        $filename = 'packages_performance_' . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            if ($data->count() > 0) {
                fputcsv($file, array_keys($data->first()));
                foreach ($data as $row) {
                    fputcsv($file, $row);
                }
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export bookings data ke CSV
     */
    public function exportBookingsCsv()
    {
        $bookings = Booking::with(['user:id,name', 'tourPackage:id,name'])
            ->orderByDesc('created_at')
            ->get();

        $data = $bookings->map(function ($booking) {
            return [
                'Kode Booking' => $booking->booking_code,
                'Pelanggan' => $booking->user->name ?? '-',
                'Paket' => $booking->tourPackage->name ?? '-',
                'Tanggal Keberangkatan' => $booking->departure_date,
                'Jumlah Peserta' => $booking->num_participants,
                'Total Harga (Rp)' => $booking->total_price,
                'Status' => $booking->status,
                'Tanggal Dibuat' => $booking->created_at->format('Y-m-d H:i'),
            ];
        });

        $filename = 'bookings_report_' . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            if ($data->count() > 0) {
                fputcsv($file, array_keys($data->first()));
                foreach ($data as $row) {
                    fputcsv($file, $row);
                }
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
