<?php
// app/Models/Vehicle.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_type_id',
        'name',
        'license_plate',
        'color',
        'is_available',
    ];

    // Relasi: Kendaraan ini dimiliki oleh satu tipe
    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }

    // Relasi: Kendaraan ini bisa digunakan di banyak booking
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'assigned_vehicle_id');
    }
}