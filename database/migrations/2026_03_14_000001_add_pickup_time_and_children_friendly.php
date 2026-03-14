<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tour_packages', function (Blueprint $table) {
            $table->string('pickup_time')->nullable()->after('category');
            $table->boolean('is_children_friendly')->default(false)->after('pickup_time');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->integer('num_adults')->default(1)->after('num_participants');
            $table->integer('num_children')->default(0)->after('num_adults');
            $table->string('pickup_location')->nullable()->after('special_requests');
            $table->string('country_code', 10)->nullable()->after('contact_phone');
            $table->string('country')->nullable()->after('country_code');
        });
    }

    public function down(): void
    {
        Schema::table('tour_packages', function (Blueprint $table) {
            $table->dropColumn(['pickup_time', 'is_children_friendly']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['num_adults', 'num_children', 'pickup_location', 'country_code', 'country']);
        });
    }
};
