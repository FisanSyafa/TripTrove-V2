<?php
// app/Http/Controllers/Admin/VehicleTypeController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VehicleTypeController extends Controller
{
    public function index(Request $request)
    {
        $types = VehicleType::query()
            ->when($request->input('search'), fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/VehicleTypes/Index', [
            'types' => $types,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/VehicleTypes/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:vehicle_types',
            'additional_charge' => 'nullable|numeric|min:0',
        ]);

        VehicleType::create($validated);
        return redirect()->route('admin.vehicle-types.index')->with('message', 'Tipe Kendaraan berhasil ditambahkan.');
    }

    public function edit(VehicleType $vehicleType)
    {
        return Inertia::render('Admin/VehicleTypes/Edit', [
            'type' => $vehicleType
        ]);
    }

    public function update(Request $request, VehicleType $vehicleType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:vehicle_types,code,' . $vehicleType->id,
            'additional_charge' => 'nullable|numeric|min:0',
        ]);

        $vehicleType->update($validated);
        return redirect()->route('admin.vehicle-types.index')->with('message', 'Tipe Kendaraan berhasil diperbarui.');
    }

    public function destroy(VehicleType $vehicleType)
    {
        // Tambahkan logika cek jika tipe masih dipakai oleh kendaraan
        if ($vehicleType->vehicles()->count() > 0) {
            return redirect()->route('admin.vehicle-types.index')->with('error', 'Tipe ini tidak bisa dihapus karena masih digunakan oleh kendaraan.');
        }

        $vehicleType->delete();
        return redirect()->route('admin.vehicle-types.index')->with('message', 'Tipe Kendaraan berhasil dihapus.');
    }
}