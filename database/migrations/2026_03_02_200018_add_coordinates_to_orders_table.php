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
            $table->decimal('DriverLatitude', 10, 8)->nullable()->after('OrderStatus');
            $table->decimal('DriverLongitude', 11, 8)->nullable()->after('DriverLatitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['DriverLatitude', 'DriverLongitude']);
        });
    }
};
