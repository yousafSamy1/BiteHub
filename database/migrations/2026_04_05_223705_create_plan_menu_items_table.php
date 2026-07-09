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
        Schema::create('plan_menu_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('KitchenPlanID');
            $table->unsignedBigInteger('MenuItemID');
            $table->timestamps();

            $table->foreign('KitchenPlanID')->references('KitchenPlanID')->on('kitchen_plans')->onDelete('cascade');
            $table->foreign('MenuItemID')->references('MenuItemID')->on('menu_items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_menu_items');
    }
};
