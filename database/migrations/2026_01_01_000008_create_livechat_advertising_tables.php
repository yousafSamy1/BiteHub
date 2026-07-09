<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_chats', function (Blueprint $table) {
            $table->id('LiveChatID');
            $table->unsignedBigInteger('SenderID')->nullable();
            $table->unsignedBigInteger('ReceiverID')->nullable();
            $table->text('Message')->nullable();
            $table->timestamp('Timestamp')->useCurrent();
            $table->foreign('SenderID')->references('UserID')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('ReceiverID')->references('UserID')->on('users')->onDelete('set null')->onUpdate('cascade');
        });

        Schema::create('advertisings', function (Blueprint $table) {
            $table->id('AdvertisingID');
            $table->unsignedBigInteger('PaymentID')->nullable();
            $table->unsignedBigInteger('KitchenOwnerID')->nullable();
            $table->unsignedBigInteger('CatererID')->nullable();
            $table->string('Title', 255)->nullable();
            $table->text('Description')->nullable();
            $table->date('StartDate')->nullable();
            $table->date('EndDate')->nullable();
            $table->enum('Status', ['Active', 'Inactive'])->default('Active');
            $table->foreign('PaymentID')->references('PaymentID')->on('payments')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('KitchenOwnerID')->references('KitchenOwnerID')->on('kitchen_owners')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('CatererID')->references('CatererID')->on('caterers')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advertisings');
        Schema::dropIfExists('live_chats');
    }
};
