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
        Schema::table('tour_packages', function (Blueprint $table) {
            // Menambahkan kolom sold_count
            // default(0) penting agar paket lama otomatis bernilai 0
            // after('discount_percent') agar posisinya rapi setelah kolom diskon
            $table->integer('sold_count')->default(0)->after('discount_percent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tour_packages', function (Blueprint $table) {
            // Menghapus kolom jika migration di-rollback
            $table->dropColumn('sold_count');
        });
    }
};