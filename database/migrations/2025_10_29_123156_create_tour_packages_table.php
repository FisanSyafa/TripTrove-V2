<?php
// database/migrations/xxxx_create_tour_packages_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tour_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('destination_summary');
            $table->text('location_details')->nullable();
            $table->text('description');
            $table->integer('duration_days');
            $table->decimal('price', 12, 2);
            $table->integer('discount_percent')->default(0);
            $table->boolean('includes_hotel')->default(false);
            $table->boolean('includes_guide')->default(false);
            $table->boolean('includes_entrance_fee')->default(false);
            $table->boolean('includes_driver_vehicle')->default(false);
            $table->string('cover_image_url')->nullable();
            $table->string('status')->default('draft'); // 'draft', 'published', 'archived'
            $table->string('category')->nullable(); // 'Short Trip', 'Long Trip', 'Half Day'
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('tour_packages');
    }
};