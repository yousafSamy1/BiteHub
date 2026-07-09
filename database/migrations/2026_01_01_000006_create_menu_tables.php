<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id('MenuItemID');
            $table->unsignedBigInteger('CategoryID')->nullable();
            $table->unsignedBigInteger('KitchenOwnerID')->nullable();
            $table->unsignedBigInteger('CatererID')->nullable();
            $table->string('ItemName', 255);
            $table->text('Description')->nullable();
            $table->decimal('ItemPrice', 10, 2);
            $table->enum('Status', ['Available', 'Unavailable'])->default('Available');
            $table->foreign('CategoryID')->references('CategoryID')->on('categories')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('KitchenOwnerID')->references('KitchenOwnerID')->on('kitchen_owners')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('CatererID')->references('CatererID')->on('caterers')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::create('item_images', function (Blueprint $table) {
            $table->unsignedBigInteger('MenuItemID');
            $table->string('Image', 255);
            $table->primary(['MenuItemID', 'Image']);
            $table->foreign('MenuItemID')->references('MenuItemID')->on('menu_items')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_images');
        Schema::dropIfExists('menu_items');
    }
};
