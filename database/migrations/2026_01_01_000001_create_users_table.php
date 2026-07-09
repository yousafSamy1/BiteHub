<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('UserID');
            $table->string('FullName', 255);
            $table->string('Email', 255)->unique();
            $table->string('Password', 255);
            $table->string('Image', 255)->nullable();
            $table->enum('Role', ['Admin', 'Customer', 'KitchenOwner', 'Caterer', 'DeliveryAgent']);
            $table->decimal('Wallet_balance', 10, 2)->default(0.00);
            $table->timestamp('CreatedAt')->useCurrent();
            $table->timestamp('UpdatedAt')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
