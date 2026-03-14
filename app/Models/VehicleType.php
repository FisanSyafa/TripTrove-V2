<?php
// app/Models/VehicleType.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'additional_charge',
    ];

    // Relasi: Satu tipe bisa dimiliki banyak kendaraan
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
}