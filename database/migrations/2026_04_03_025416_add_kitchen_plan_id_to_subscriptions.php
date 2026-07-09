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
            $table->unsignedBigInteger('KitchenPlanID')->nullable()->after('CustomerID');
            $table->foreign('KitchenPlanID')
                  ->references('KitchenPlanID')
                  ->on('kitchen_plans')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['KitchenPlanID']);
            $table->dropColumn('KitchenPlanID');
        });
    }
};
