<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Gallery extends Model
{
    use HasFactory;

    // PILIH SALAH SATU dari dua opsi di bawah:

    // Opsi A (ketat & aman): sebutkan kolom yang boleh diisi mass assignment
    // -- Jika FK kamu bernama `tour_package_id`:
    protected $fillable = ['image_url', 'tour_package_id'];
    // -- Jika FK kamu bernama `package_id`, ganti baris di atas jadi:
    // protected $fillable = ['image_url', 'package_id'];

    // Opsi B (paling gampang): izinkan semua kolom (kurang ketat)
    // protected $guarded = [];

    // Relasi balik (sesuaikan nama FK)
    public function package()
    {
        return $this->belongsTo(TourPackage::class, 'tour_package_id'); // ganti ke 'package_id' kalau itu yang dipakai
    }
}
