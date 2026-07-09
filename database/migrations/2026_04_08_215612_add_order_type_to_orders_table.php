<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('OrderType', ['Order', 'Meal Plan', 'Catering'])->default('Order')->after('OrderStatus');
        });

        // Back-fill: any order with a SubscriptionID is a Meal Plan
        \Illuminate\Support\Facades\DB::statement(
            "UPDATE orders SET OrderType = 'Meal Plan' WHERE SubscriptionID IS NOT NULL"
        );
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('OrderType');
        });
    }
};
