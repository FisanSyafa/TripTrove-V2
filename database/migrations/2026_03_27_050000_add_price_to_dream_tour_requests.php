<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dream_tour_requests', function (Blueprint $table) {
            $table->decimal('admin_price', 15, 2)->nullable()->after('status');
            $table->string('price_currency', 10)->default('IDR')->after('admin_price');
        });
    }

    public function down(): void
    {
        Schema::table('dream_tour_requests', function (Blueprint $table) {
            $table->dropColumn(['admin_price', 'price_currency']);
        });
    }
};
