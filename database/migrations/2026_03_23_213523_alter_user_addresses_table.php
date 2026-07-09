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
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->dropForeign(['UserID']);
            $table->dropPrimary(['UserID', 'Address']);
        });

        Schema::table('user_addresses', function (Blueprint $table) {
            $table->id('AddressID')->first();
            $table->decimal('Latitude', 10, 8)->nullable();
            $table->decimal('Longitude', 11, 8)->nullable();
            $table->boolean('IsPrimary')->default(false);
            $table->timestamps();
            
            $table->foreign('UserID')->references('UserID')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->dropColumn(['AddressID', 'Latitude', 'Longitude', 'IsPrimary', 'created_at', 'updated_at']);
            $table->primary(['UserID', 'Address']);
        });
    }
};
