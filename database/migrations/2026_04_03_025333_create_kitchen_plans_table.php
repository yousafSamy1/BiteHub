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
        Schema::create('kitchen_plans', function (Blueprint $table) {
            $table->id('KitchenPlanID');
            $table->unsignedBigInteger('KitchenOwnerID');
            $table->string('Title', 255);
            $table->text('Description')->nullable();
            $table->decimal('Price', 10, 2);
            $table->enum('PlanTime', ['Daily', 'Weekly', 'Monthly']);
            $table->enum('Status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
            
            $table->foreign('KitchenOwnerID')
                  ->references('KitchenOwnerID')
                  ->on('kitchen_owners')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kitchen_plans');
    }
};
