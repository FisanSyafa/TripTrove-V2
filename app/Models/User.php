<?php
// app/Models/User.php
namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail; // Uncomment jika Anda ingin verifikasi email
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable // implements MustVerifyEmail // Uncomment jika perlu
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // Tambahkan role
        'phone_number', // Tambahkan phone_number
        'address', // Tambahkan address
        'profile_picture_url', // Tambahkan profile_picture_url
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relasi: User bisa punya banyak booking
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // Relasi: User bisa punya banyak review
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // Helper untuk cek role
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // Tambahkan helper lain jika perlu (isGuide, isDriver, etc.)
}