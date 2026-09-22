<?php
// app/Models/TourPackage.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'destination_summary',
        'location_details',
        'description',
        'duration_days',
        'price',
        'small_car_price',
        'large_car_price',
        'group_tickets',
        'discount_percent',
        'input_currency',
        'original_price',
        'original_small_car_price',
        'original_large_car_price',
        'sold_count',
        'includes_hotel',
        'includes_guide',
        'includes_entrance_fee',
        'includes_driver_vehicle',
        'includes_about_this_trip',
        'cover_image_url',
        'status',
        'category',
        'pickup_time',
        'is_children_friendly',
    ];

    protected $casts = [
        'duration_days' => 'integer',
        'price' => 'decimal:2',
        'small_car_price' => 'decimal:2',
        'large_car_price' => 'decimal:2',
        'group_tickets' => 'array',
        'original_price' => 'decimal:2',
        'original_small_car_price' => 'decimal:2',
        'original_large_car_price' => 'decimal:2',
        'discount_percent' => 'integer',
        'sold_count' => 'integer',
        'includes_hotel' => 'boolean',
        'includes_guide' => 'boolean',
        'includes_entrance_fee' => 'boolean',
        'includes_driver_vehicle' => 'boolean',
        'includes_about_this_trip' => 'boolean',
        'is_children_friendly' => 'boolean',
    ];

    // Relasi: Satu paket bisa dibooking berkali-kali
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // Relasi: Satu paket bisa punya banyak review
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // =======================================================
    // PASTIKAN RELASI INI ADA
    // =======================================================
    /**
     * Relasi: Satu paket punya banyak gambar galeri.
     */
    public function galleries()
    {
        return $this->hasMany(Gallery::class, 'tour_package_id'); // atau 'package_id' sesuai skema
    }
    // =======================================================

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'slug'; // Agar route model binding menggunakan slug
    }
}