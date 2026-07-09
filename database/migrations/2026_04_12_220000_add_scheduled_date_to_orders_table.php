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
        Schema::table('orders', function (Blueprint $table) {
            $table->date('ScheduledDate')->nullable()->after('DeliveryTime');
        });

        // Initialize existing orders with their CreatedAt date
        \Illuminate\Support\Facades\DB::update("UPDATE orders SET ScheduledDate = DATE(CreatedAt)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('ScheduledDate');
        });
    }
};
