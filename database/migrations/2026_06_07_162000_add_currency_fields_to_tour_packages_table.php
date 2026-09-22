<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tour_packages', function (Blueprint $table) {
            $table->string('input_currency')->default('IDR')->after('discount_percent');
            $table->decimal('original_price', 12, 2)->nullable()->after('input_currency');
            $table->decimal('original_small_car_price', 12, 2)->nullable()->after('original_price');
            $table->decimal('original_large_car_price', 12, 2)->nullable()->after('original_small_car_price');
        });
    }

    public function down(): void
    {
        Schema::table('tour_packages', function (Blueprint $table) {
            $table->dropColumn([
                'input_currency',
                'original_price',
                'original_small_car_price',
                'original_large_car_price'
            ]);
        });
    }
};
