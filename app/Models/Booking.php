<?php
// app/Models/Booking.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_code',
        'user_id',
        'tour_package_id',
        'departure_date',
        'num_participants',
        'contact_email',
        'contact_phone',
        'package_price_at_booking',
        'discount_at_booking',
        'total_price',
        'status',
        'special_requests',
        'assigned_driver_id',
        'assigned_guide_id',
        'assigned_vehicle_id',
        'snap_token',
        'end_date',
    ];

    // Relasi ke User (Pemesan)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke TourPackage
    public function tourPackage()
    {
        return $this->belongsTo(TourPackage::class);
    }

    // Relasi ke Passengers
    public function passengers()
    {
        return $this->hasMany(Passenger::class);
    }

    // Relasi ke Payments
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Relasi ke Payment terakhir (untuk invoice)
    public function payment()
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    // Relasi ke Review
    public function review()
    {
        return $this->hasOne(Review::class);
    }

    // Relasi ke User (Driver)
    public function driver()
    {
        return $this->belongsTo(User::class, 'assigned_driver_id');
    }

    // Relasi ke User (Guide)
    public function guide()
    {
        return $this->belongsTo(User::class, 'assigned_guide_id');
    }

     // Relasi ke Vehicle
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'assigned_vehicle_id');
    }
}