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
        'discount_percent',
        'sold_count',
        'includes_hotel',
        'includes_guide',
        'includes_entrance_fee',
        'includes_driver_vehicle',
        'cover_image_url',
        'status',
        'category',
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