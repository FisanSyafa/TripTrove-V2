<?php
// app/Http/Controllers/PublicPackageController.php
namespace App\Http\Controllers;

use App\Models\TourPackage;
use App\Models\Announcement;
use App\Models\Review;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\CurrencyService;

class PublicPackageController extends Controller
{
    public function index()
    {
        // 1. Ambil Paket Populer
        $popularPackages = TourPackage::where('status', 'published')
            ->with(['reviews' => function ($query) {
                $query->where('is_approved', true); 
            }])
            ->orderByDesc('sold_count') // Sangat cepat!
            ->limit(8)
            ->get();

        // 2. Ambil Pengumuman Aktif
        $announcement = Announcement::where('is_active', true)
            ->latest()
            ->first();

        // 3. (BARU) Ambil Kategori untuk Filter di Home
        $categories = TourPackage::where('status', 'published')
            ->select('category')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        return Inertia::render('Home', [
            'popularPackages' => $popularPackages,
            'announcement' => $announcement,
            'filterOptions' => [
                'categories' => $categories // <--- Kirim ini
            ],
            'adminWhatsappNumber' => config('app.admin_whatsapp_number')
        ]);
    }

    // ... method allPackages dan show tetap sama ...
    public function allPackages(Request $request)
    {
        // ... (kode allPackages Anda sebelumnya) ...
        // Pastikan kode allPackages Anda tetap seperti revisi terakhir (paginate 8)
        $request->validate([
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|gte:min_price',
            'duration' => 'nullable|string|in:1-3,4-6,7+',
            'category' => 'nullable|string|max:255',
            'includes_hotel' => 'nullable|boolean',
            'includes_guide' => 'nullable|boolean',
        ]);

        $currency = session('currency', 'IDR');
        $currencyService = new CurrencyService();

        $minPriceInput = $request->input('min_price');
        $maxPriceInput = $request->input('max_price');

        // Konversi filter harga ke IDR (base currency) sebelum query
        $minPriceIdr = ($minPriceInput && $currency !== 'IDR') 
            ? $currencyService->convertToBase($minPriceInput, $currency) 
            : $minPriceInput;

        $maxPriceIdr = ($maxPriceInput && $currency !== 'IDR') 
            ? $currencyService->convertToBase($maxPriceInput, $currency) 
            : $maxPriceInput;

        $packages = TourPackage::query()
            ->where('status', 'published')
            ->with('reviews')
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('destination_summary', 'like', "%{$search}%")
                        ->orWhere('location_details', 'like', "%{$search}%");
                });
            })
            ->when($request->input('category'), function ($query, $category) {
                $query->where('category', $category);
            })
            ->when($minPriceIdr, function ($query, $minPrice) {
                $query->whereRaw('(price * (1 - COALESCE(discount_percent, 0) / 100)) >= ?', [$minPrice]);
            })
            ->when($maxPriceIdr, function ($query, $maxPrice) {
                $query->whereRaw('(price * (1 - COALESCE(discount_percent, 0) / 100)) <= ?', [$maxPrice]);
            })
            ->when($request->input('duration'), function ($query, $duration) {
                if ($duration === '1-3') {
                    $query->whereBetween('duration_days', [1, 3]);
                } elseif ($duration === '4-6') {
                    $query->whereBetween('duration_days', [4, 6]);
                } elseif ($duration === '7+') {
                    $query->where('duration_days', '>=', 7);
                }
            })
            ->when($request->boolean('includes_hotel'), fn($q) => $q->where('includes_hotel', true))
            ->when($request->boolean('includes_guide'), fn($q) => $q->where('includes_guide', true))
            ->when($request->boolean('includes_driver_vehicle'), fn($q) => $q->where('includes_driver_vehicle', true))
            ->when($request->input('sortBy'), function ($query, $sortBy) {
                if ($sortBy === 'price_asc') {
                    $query->orderByRaw('(price * (1 - COALESCE(discount_percent, 0) / 100)) asc');
                } elseif ($sortBy === 'price_desc') {
                    $query->orderByRaw('(price * (1 - COALESCE(discount_percent, 0) / 100)) desc');
                } elseif ($sortBy === 'duration_asc') {
                    $query->orderBy('duration_days', 'asc');
                }
            }, function ($query) {
                $query->latest('id');
            })
            ->paginate(8)
            ->withQueryString();

        $categories = TourPackage::where('status', 'published')
                                    ->select('category')
                                    ->whereNotNull('category')
                                    ->distinct()
                                    ->pluck('category');

        return Inertia::render('Package/Index', [
            'packages' => $packages,
            'filters' => $request->all(), 
            'filterOptions' => [
                'categories' => $categories
            ]
        ]);
    }

    public function show(TourPackage $package) 
    {
        if ($package->status !== 'published' && (!auth()->check() || !auth()->user()->isAdmin())) {
            abort(404);
        }

        $package->load([
            'reviews' => function ($query) {
                $query->where('is_approved', true)
                    ->with('user:id,name')
                    ->latest('review_date');
            },
            'galleries'
        ]);

        return Inertia::render('Package/Show', [
            'package' => $package,
            'adminWhatsappNumber' => config('app.admin_whatsapp_number')
        ]);
    }

    public function review(TourPackage $package)
    {
        if ($package->status !== 'published') {
            abort(404);
        }

        return Inertia::render('Package/Review', [
            'package' => $package
        ]);
    }

    public function storeReview(Request $request, TourPackage $package)
    {
        if ($package->status !== 'published') {
            abort(404);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:5000',
            'guest_name' => 'nullable|string|max:255',
            'guest_country' => 'nullable|string|max:255',
        ]);

        Review::create([
            'tour_package_id' => $package->id,
            'user_id' => auth()->id(), // null if guest
            'guest_name' => auth()->check() ? auth()->user()->name : ($validated['guest_name'] ?? 'Guest'),
            'guest_country' => $validated['guest_country'] ?? null,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'review_date' => now(),
            'is_approved' => false,
        ]);

        return redirect()->route('packages.show', $package->slug)
            ->with('message', 'Terima kasih! Ulasan Anda telah terkirim dan menunggu moderasi.');
    }
}