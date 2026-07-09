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
            $table->unsignedInteger('KitchenOrderNumber')->nullable()->after('OrderID');
            $table->unsignedBigInteger('KitchenOwnerID')->nullable()->after('CustomerID');
            $table->unsignedBigInteger('CatererID')->nullable()->after('KitchenOwnerID');
            
            // Add indices for performance
            $table->index(['KitchenOwnerID', 'KitchenOrderNumber']);
            $table->index(['CatererID', 'KitchenOrderNumber']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['KitchenOrderNumber', 'KitchenOwnerID', 'CatererID']);
        });
    }
};
