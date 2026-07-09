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
        Schema::table('kitchen_owners', function (Blueprint $table) {
            $table->time('OpeningTime')->nullable()->after('DeliveryTime');
            $table->time('ClosingTime')->nullable()->after('OpeningTime');
        });

        Schema::table('caterers', function (Blueprint $table) {
            $table->time('OpeningTime')->nullable()->after('BusinessName');
            $table->time('ClosingTime')->nullable()->after('OpeningTime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kitchen_owners', function (Blueprint $table) {
            $table->dropColumn(['OpeningTime', 'ClosingTime']);
        });

        Schema::table('caterers', function (Blueprint $table) {
            $table->dropColumn(['OpeningTime', 'ClosingTime']);
        });
    }
};
