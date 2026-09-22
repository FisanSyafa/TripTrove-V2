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
            if (Schema::hasColumn('tour_packages', 'group_ticket_price')) {
                $table->dropColumn(['group_ticket_price', 'group_ticket_max_persons', 'original_group_ticket_price']);
            }
            if (!Schema::hasColumn('tour_packages', 'group_tickets')) {
                $table->json('group_tickets')->nullable()->after('large_car_price');
            }
        });

        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'group_ticket_price')) {
                $table->dropColumn(['group_ticket_price', 'group_ticket_max_persons', 'group_ticket_count']);
            }
            if (!Schema::hasColumn('bookings', 'group_tickets')) {
                $table->json('group_tickets')->nullable()->after('car_price');
            }
            if (!Schema::hasColumn('bookings', 'group_ticket_total')) {
                $table->decimal('group_ticket_total', 12, 2)->default(0.00)->after('group_tickets');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tour_packages', function (Blueprint $table) {
            if (Schema::hasColumn('tour_packages', 'group_tickets')) {
                $table->dropColumn('group_tickets');
            }
        });

        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'group_tickets')) {
                $table->dropColumn('group_tickets');
            }
        });
    }
};
