<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id('PaymentID');
            $table->enum('Method', ['Cash', 'Card', 'Wallet', 'Online']);
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id('CategoryID');
            $table->string('Name', 100);
            $table->text('Description')->nullable();
            $table->enum('Status', ['Active', 'Inactive'])->default('Active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
        Schema::dropIfExists('payments');
    }
};
