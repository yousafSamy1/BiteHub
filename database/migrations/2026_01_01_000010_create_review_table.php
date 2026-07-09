<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id('ReviewID');
            $table->unsignedBigInteger('CustomerID')->nullable();
            $table->unsignedBigInteger('KitchenOwnerID')->nullable();
            $table->unsignedBigInteger('CatererID')->nullable();
            $table->unsignedBigInteger('OrderID')->nullable();
            $table->tinyInteger('Rating')->nullable();
            $table->text('Comment')->nullable();
            $table->timestamp('CreatedAt')->useCurrent();
            $table->foreign('CustomerID')->references('CustomerID')->on('customers')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('KitchenOwnerID')->references('KitchenOwnerID')->on('kitchen_owners')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('CatererID')->references('CatererID')->on('caterers')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('OrderID')->references('OrderID')->on('orders')->onDelete('set null')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
