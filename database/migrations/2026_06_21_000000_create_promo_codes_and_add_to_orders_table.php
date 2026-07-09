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
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id('PromoCodeID');
            $table->string('Code', 50)->unique();
            $table->enum('Type', ['Fixed', 'Percentage']);
            $table->decimal('Value', 10, 2);
            $table->dateTime('ExpiryDate')->nullable();
            $table->integer('MaxUses')->nullable();
            $table->integer('UsedCount')->default(0);
            $table->decimal('MinOrderAmount', 10, 2)->default(0.00);
            $table->boolean('IsActive')->default(true);
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('PromoCode', 50)->nullable()->after('PointsDiscount');
            $table->decimal('PromoDiscount', 10, 2)->default(0.00)->after('PromoCode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['PromoCode', 'PromoDiscount']);
        });
        Schema::dropIfExists('promo_codes');
    }
};
