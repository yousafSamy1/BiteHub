<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Tracks whether loyalty points have been awarded yet.
            // Points are only awarded when the order is marked "Delivered".
            $table->boolean('PointsAwarded')->default(false)->after('LoyaltyPoints');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('PointsAwarded');
        });
    }
};
