<?php
// app/Http/Controllers/Admin/PackageController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TourPackage;
use App\Models\Gallery;
use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class PackageController extends Controller
{
    // ... (method index, create, store, edit tidak berubah) ...
    public function index(Request $request)
    {
        $packages = TourPackage::query()
            ->when($request->input('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('destination_summary', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Packages/Index', [
            'packages' => $packages,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Packages/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'destination_summary' => 'required|string|max:255',
            'location_details' => 'nullable|string',
            'description' => 'required|string',
            'duration_days' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'small_car_price' => 'required|numeric|min:0',
            'large_car_price' => 'required|numeric|min:0',
            'group_tickets' => 'nullable|array',
            'group_tickets.*.name' => 'required|string|max:255',
            'group_tickets.*.price' => 'required|numeric|min:0',
            'group_tickets.*.max_persons' => 'required|integer|min:1',
            'discount_percent' => 'nullable|integer|min:0|max:100',
            'input_currency' => 'required|string|in:IDR,USD,MYR,SGD',
            'includes_hotel' => 'boolean',
            'includes_guide' => 'boolean',
            'includes_entrance_fee' => 'boolean',
            'includes_driver_vehicle' => 'boolean',
            'includes_about_this_trip' => 'boolean',
            'cover_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
            'status' => 'required|in:published,draft',
            'category' => 'nullable|string|max:255',
            'pickup_time' => 'nullable|string|max:10',
            'is_children_friendly' => 'boolean',
        ]);

        $currency = $validated['input_currency'] ?? 'IDR';

        $validated['original_price'] = $validated['price'];
        $validated['original_small_car_price'] = $validated['small_car_price'];
        $validated['original_large_car_price'] = $validated['large_car_price'];

        $currencyService = ($currency !== 'IDR') ? new CurrencyService() : null;

        if ($currency !== 'IDR' && !$currencyService->hasRate($currency)) {
            return back()->withErrors([
                'price' => 'Gagal mengambil kurs mata uang terbaru. Silakan coba lagi nanti atau masukkan harga dalam IDR.'
            ])->withInput();
        }

        if ($currency !== 'IDR') {
            $validated['price'] = $currencyService->convertToBase($validated['price'], $currency);
            $validated['small_car_price'] = $currencyService->convertToBase($validated['small_car_price'], $currency);
            $validated['large_car_price'] = $currencyService->convertToBase($validated['large_car_price'], $currency);
        }

        // Process group_tickets array
        $processedGroupTickets = [];
        if (!empty($validated['group_tickets'])) {
            foreach ($validated['group_tickets'] as $item) {
                $name = trim($item['name'] ?? '');
                $inputPrice = (float) ($item['price'] ?? 0);
                $maxPersons = (int) ($item['max_persons'] ?? 0);

                if (!empty($name) && $inputPrice > 0 && $maxPersons > 0) {
                    $convertedPrice = ($currency !== 'IDR') ? $currencyService->convertToBase($inputPrice, $currency) : $inputPrice;
                    $processedGroupTickets[] = [
                        'name' => $name,
                        'price' => $convertedPrice,
                        'max_persons' => $maxPersons,
                        'original_price' => $inputPrice,
                    ];
                }
            }
        }
        $validated['group_tickets'] = $processedGroupTickets;

        $validated['slug'] = Str::slug($validated['name']);

        if ($request->hasFile('cover_image')) {
            $manager = new ImageManager(new Driver());
            $imageFile = $request->file('cover_image');
            $image = $manager->read($imageFile);
            
            $imageName = Str::random(32) . '.webp';
            $encodedImage = $image->toWebp(75);
            $path = 'packages/' . $imageName; 

            Storage::disk('public')->put($path, (string) $encodedImage);
            
            $validated['cover_image_url'] = $path;
        }
        
        $validated['includes_hotel'] = $request->boolean('includes_hotel');
        $validated['includes_guide'] = $request->boolean('includes_guide');
        $validated['includes_entrance_fee'] = $request->boolean('includes_entrance_fee');
        $validated['includes_driver_vehicle'] = $request->boolean('includes_driver_vehicle');
        $validated['includes_about_this_trip'] = $request->boolean('includes_about_this_trip');
        $validated['is_children_friendly'] = $request->boolean('is_children_friendly');


        TourPackage::create($validated);
        return redirect()->route('admin.packages.index')->with('message', 'Paket berhasil ditambahkan.');
    }

    public function edit(TourPackage $package)
    {
        $package->load('galleries');
        return Inertia::render('Admin/Packages/Edit', [
            'package' => $package
        ]);
    }

    public function update(Request $request, TourPackage $package)
    {
          $validated = $request->validate([
            'name' => 'required|string|max:255',
            'destination_summary' => 'required|string|max:255',
            'location_details' => 'nullable|string',
            'description' => 'required|string',
            'duration_days' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'small_car_price' => 'required|numeric|min:0',
            'large_car_price' => 'required|numeric|min:0',
            'group_tickets' => 'nullable|array',
            'group_tickets.*.name' => 'required|string|max:255',
            'group_tickets.*.price' => 'required|numeric|min:0',
            'group_tickets.*.max_persons' => 'required|integer|min:1',
            'discount_percent' => 'nullable|integer|min:0|max:100',
            'input_currency' => 'required|string|in:IDR,USD,MYR,SGD',
            'includes_hotel' => 'boolean',
            'includes_guide' => 'boolean',
            'includes_entrance_fee' => 'boolean',
            'includes_driver_vehicle' => 'boolean',
            'includes_about_this_trip' => 'boolean',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
            'status' => 'required|in:published,draft',
            'category' => 'nullable|string|max:255',
            'pickup_time' => 'nullable|string|max:10',
            'is_children_friendly' => 'boolean',
        ]);

        $currency = $validated['input_currency'] ?? 'IDR';

        $validated['original_price'] = $validated['price'];
        $validated['original_small_car_price'] = $validated['small_car_price'];
        $validated['original_large_car_price'] = $validated['large_car_price'];

        $currencyService = ($currency !== 'IDR') ? new CurrencyService() : null;

        if ($currency !== 'IDR' && !$currencyService->hasRate($currency)) {
            return back()->withErrors([
                'price' => 'Gagal mengambil kurs mata uang terbaru. Silakan coba lagi nanti atau masukkan harga dalam IDR.'
            ])->withInput();
        }

        if ($currency !== 'IDR') {
            $validated['price'] = $currencyService->convertToBase($validated['price'], $currency);
            $validated['small_car_price'] = $currencyService->convertToBase($validated['small_car_price'], $currency);
            $validated['large_car_price'] = $currencyService->convertToBase($validated['large_car_price'], $currency);
        }

        // Process group_tickets array
        $processedGroupTickets = [];
        if (!empty($validated['group_tickets'])) {
            foreach ($validated['group_tickets'] as $item) {
                $name = trim($item['name'] ?? '');
                $inputPrice = (float) ($item['price'] ?? 0);
                $maxPersons = (int) ($item['max_persons'] ?? 0);

                if (!empty($name) && $inputPrice > 0 && $maxPersons > 0) {
                    $convertedPrice = ($currency !== 'IDR') ? $currencyService->convertToBase($inputPrice, $currency) : $inputPrice;
                    $processedGroupTickets[] = [
                        'name' => $name,
                        'price' => $convertedPrice,
                        'max_persons' => $maxPersons,
                        'original_price' => $inputPrice,
                    ];
                }
            }
        }
        $validated['group_tickets'] = $processedGroupTickets;

        if ($validated['name'] !== $package->name) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        if ($request->hasFile('cover_image')) {
            if ($package->cover_image_url) {
                Storage::disk('public')->delete($package->cover_image_url);
            }
            $manager = new ImageManager(new Driver());
            $imageFile = $request->file('cover_image');
            $image = $manager->read($imageFile);
            
            $imageName = Str::random(32) . '.webp';
            $encodedImage = $image->toWebp(75);
            $path = 'packages/' . $imageName;

            Storage::disk('public')->put($path, (string) $encodedImage);
            
            $validated['cover_image_url'] = $path;
        }

        $validated['includes_hotel'] = $request->boolean('includes_hotel');
        $validated['includes_guide'] = $request->boolean('includes_guide');
        $validated['includes_entrance_fee'] = $request->boolean('includes_entrance_fee');
        $validated['includes_driver_vehicle'] = $request->boolean('includes_driver_vehicle');
        $validated['includes_about_this_trip'] = $request->boolean('includes_about_this_trip');
        $validated['is_children_friendly'] = $request->boolean('is_children_friendly');

        $package->update($validated);
        return redirect()->route('admin.packages.index')->with('message', 'Paket berhasil diperbarui.');
    }

    public function destroy(TourPackage $package)
    {
        if ($package->cover_image_url) {
            Storage::disk('public')->delete($package->cover_image_url);
        }
        
        foreach ($package->galleries as $gallery) {
            Storage::disk('public')->delete($gallery->image_url);
            $gallery->delete();
        }

        $package->delete();
        return redirect()->route('admin.packages.index')->with('message', 'Paket berhasil dihapus.');
    }

    // =======================================================
    // PERBAIKI METHOD 'storeGallery'
    // =======================================================
    /**
     * Menyimpan gambar baru ke galeri paket (sudah .webp)
     */
    public function storeGallery(Request $request, TourPackage $package)
    {
        // 1. Validasi untuk menerima array gambar
        $validated = $request->validate([
            'images' => 'required|array', // Pastikan 'images' adalah array
            'images.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240', // Validasi setiap file di dalam array
        ]);

        $manager = new ImageManager(new Driver());

        // 2. Loop setiap file yang di-upload
        foreach ($validated['images'] as $imageFile) {
            // 3. Konversi ke .webp
            $image = $manager->read($imageFile);
            $imageName = Str::random(32) . '.webp';
            $encodedImage = $image->toWebp(75);
            $path = 'packages/gallery/' . $imageName;

            // 4. Simpan file
            Storage::disk('public')->put($path, (string) $encodedImage);

            // 5. Buat record di database
            $package->galleries()->create([
                'image_url' => $path,
            ]);
        }

        return redirect()->back()->with('message', count($validated['images']) . ' gambar berhasil ditambahkan.');
    }
    // =======================================================

    /**
     * Menghapus gambar dari galeri.
     */
    public function destroyGallery(Gallery $gallery)
    {
        if ($gallery->image_url) {
            Storage::disk('public')->delete($gallery->image_url);
        }
        $gallery->delete();

        return redirect()->back()->with('message', 'Gambar berhasil dihapus dari galeri.');
    }
}