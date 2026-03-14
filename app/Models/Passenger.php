<?php
// app/Models/Passenger.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Passenger extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'full_name',
        'date_of_birth',
    ];

    // Relasi: Passenger ini milik satu Booking
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}