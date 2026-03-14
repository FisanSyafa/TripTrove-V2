<?php
// app/Http/Controllers/Admin/VehicleController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $vehicles = Vehicle::with('vehicleType') // Eager load relasi
            ->when($request->input('search'), fn($q, $s) => 
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('license_plate', 'like', "%{$s}%")
            )
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Vehicles/Index', [
            'vehicles' => $vehicles,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Vehicles/Create', [
            'vehicleTypes' => VehicleType::all(['id', 'name']) // Kirim data untuk dropdown
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'license_plate' => 'required|string|max:255|unique:vehicles',
            'color' => 'nullable|string|max:255',
            'is_available' => 'boolean',
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
        ]);
        $validated['is_available'] = $request->boolean('is_available');

        Vehicle::create($validated);
        return redirect()->route('admin.vehicles.index')->with('message', 'Kendaraan berhasil ditambahkan.');
    }

    public function edit(Vehicle $vehicle)
    {
        return Inertia::render('Admin/Vehicles/Edit', [
            'vehicle' => $vehicle,
            'vehicleTypes' => VehicleType::all(['id', 'name']) // Kirim data untuk dropdown
        ]);
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'license_plate' => 'required|string|max:255|unique:vehicles,license_plate,' . $vehicle->id,
            'color' => 'nullable|string|max:255',
            'is_available' => 'boolean',
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
        ]);
        $validated['is_available'] = $request->boolean('is_available');

        $vehicle->update($validated);
        return redirect()->route('admin.vehicles.index')->with('message', 'Kendaraan berhasil diperbarui.');
    }

    public function destroy(Vehicle $vehicle)
    {
        // Tambahkan logika cek jika kendaraan masih dipakai di booking
        if ($vehicle->bookings()->count() > 0) {
             return redirect()->route('admin.vehicles.index')->with('error', 'Kendaraan ini tidak bisa dihapus karena terikat dengan booking.');
        }

        $vehicle->delete();
        return redirect()->route('admin.vehicles.index')->with('message', 'Kendaraan berhasil dihapus.');
    }
}