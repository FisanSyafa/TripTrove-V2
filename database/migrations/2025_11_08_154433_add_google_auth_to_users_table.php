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
        Schema::table('users', function (Blueprint $table) {
            // 1. Buat password menjadi nullable
            $table->string('password')->nullable()->change();

            // 2. Tambahkan kolom baru untuk Google Auth
            $table->string('google_id')->nullable()->after('email');
            $table->string('google_token')->nullable()->after('google_id');
            $table->string('avatar')->nullable()->after('phone_number'); // Asumsi 'avatar' setelah 'phone_number'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable(false)->change();
            $table->dropColumn(['google_id', 'google_token', 'avatar']);
        });
    }
};