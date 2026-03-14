<?php
// database/migrations/xxxx_create_passengers_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('passengers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->string('full_name');
            $table->date('date_of_birth');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('passengers');
    }
};