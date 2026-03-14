<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add guest_name to bookings for guest bookings
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('guest_name')->nullable()->after('user_id');
        });

        // Create dream_tour_requests table
        Schema::create('dream_tour_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('country_code', 10)->nullable();
            $table->date('departure_date')->nullable();
            $table->integer('num_adults')->default(1);
            $table->integer('num_children')->default(0);
            $table->json('destinations');
            $table->text('additional_info')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('guest_name');
        });

        Schema::dropIfExists('dream_tour_requests');
    }
};
