<?php
// app/Http/Controllers/Admin/PackageController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TourPackage;
use App\Models\Gallery;
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
            'discount_percent' => 'nullable|integer|min:0|max:100',
            'includes_hotel' => 'boolean',
            'includes_guide' => 'boolean',
            'includes_entrance_fee' => 'boolean',
            'includes_driver_vehicle' => 'boolean',
            'cover_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'status' => 'required|in:published,draft',
            'category' => 'nullable|string|max:255',
            'pickup_time' => 'nullable|string|max:10',
            'is_children_friendly' => 'boolean',
        ]);

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
            'discount_percent' => 'nullable|integer|min:0|max:100',
            'includes_hotel' => 'boolean',
            'includes_guide' => 'boolean',
            'includes_entrance_fee' => 'boolean',
            'includes_driver_vehicle' => 'boolean',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'status' => 'required|in:published,draft',
            'category' => 'nullable|string|max:255',
            'pickup_time' => 'nullable|string|max:10',
            'is_children_friendly' => 'boolean',
        ]);

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
            'images.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048', // Validasi setiap file di dalam array
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