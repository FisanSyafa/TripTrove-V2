<?php
// database/migrations/xxxx_create_bookings_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('tour_package_id')->constrained('tour_packages')->cascadeOnDelete();
            $table->date('departure_date');
            $table->integer('num_participants');
            $table->decimal('package_price_at_booking', 12, 2);
            $table->integer('discount_at_booking')->default(0);
            $table->decimal('total_price', 12, 2);
            $table->string('status')->default('pending');
            $table->text('special_requests')->nullable();
            $table->foreignId('assigned_driver_id')->nullable()->constrained('users');
            $table->foreignId('assigned_guide_id')->nullable()->constrained('users');
            $table->foreignId('assigned_vehicle_id')->nullable()->constrained('vehicles');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('bookings');
    }
};