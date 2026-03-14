<?php
// database/migrations/xxxx_create_reviews_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('tour_package_id')->constrained('tour_packages')->cascadeOnDelete();
            $table->tinyInteger('rating')->unsigned(); // 1-5
            $table->text('comment')->nullable();
            $table->timestamp('review_date')->useCurrent();
            $table->boolean('is_approved')->default(false);
            $table->timestamps();
            // Optional check constraint for MySQL 8.0.16+
            // $table->check('rating >= 1 AND rating <= 5');
        });
    }
    public function down(): void {
        Schema::dropIfExists('reviews');
    }
};