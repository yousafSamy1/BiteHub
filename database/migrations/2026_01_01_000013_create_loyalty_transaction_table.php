<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->id('TransactionID');
            $table->unsignedBigInteger('CustomerID')->nullable();
            $table->integer('Points');
            $table->enum('Type', ['Earned', 'Redeemed', 'Bonus', 'Referral'])->default('Earned');
            $table->string('Description', 255)->nullable();
            $table->timestamp('CreatedAt')->useCurrent();
            $table->foreign('CustomerID')->references('CustomerID')->on('customers')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_transactions');
    }
};
