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
        Schema::table('promo_codes', function (Blueprint $table) {
            $table->unsignedBigInteger('KitchenOwnerID')->nullable()->after('IsActive');
            $table->unsignedBigInteger('CatererID')->nullable()->after('KitchenOwnerID');
            $table->string('CreatorRole')->default('Admin')->after('CatererID');

            // Optionally add foreign keys if you want strict referential integrity
            // $table->foreign('KitchenOwnerID')->references('KitchenOwnerID')->on('kitchen_owners')->onDelete('cascade');
            // $table->foreign('CatererID')->references('CatererID')->on('caterers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promo_codes', function (Blueprint $table) {
            $table->dropColumn(['KitchenOwnerID', 'CatererID', 'CreatorRole']);
        });
    }
};
