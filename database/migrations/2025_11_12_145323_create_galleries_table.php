<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Perintah --create=galleries akan otomatis membuat ini
        Schema::create('galleries', function (Blueprint $table) {
            $table->id();
            
            // Kunci asing untuk relasi ke tabel tour_packages
            $table->foreignId('tour_package_id')
                  ->constrained('tour_packages')
                  ->onDelete('cascade'); // Jika paket dihapus, gambar galeri juga terhapus

            $table->string('image_url'); // Path ke file gambar (yang sudah di-webp)
            $table->string('caption')->nullable(); // Teks opsional untuk gambar
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('galleries');
    }
};