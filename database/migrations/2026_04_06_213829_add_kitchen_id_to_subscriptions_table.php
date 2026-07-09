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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('KitchenOwnerID')->nullable()->after('CustomerID');
            $table->foreign('KitchenOwnerID')->references('KitchenOwnerID')->on('kitchen_owners')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['KitchenOwnerID']);
            $table->dropColumn('KitchenOwnerID');
        });
    }
};
