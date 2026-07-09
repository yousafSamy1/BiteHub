<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id('OrderID');
            $table->unsignedBigInteger('CustomerID')->nullable();
            $table->unsignedBigInteger('DeliveryAgentID')->nullable();
            $table->unsignedBigInteger('PaymentID')->nullable();
            $table->unsignedBigInteger('LiveChatID')->nullable();
            $table->decimal('Deposit', 10, 2)->default(0.00);
            $table->decimal('TotalPrice', 10, 2);
            $table->integer('LoyaltyPoints')->default(0);
            $table->decimal('Amount', 10, 2)->nullable();
            $table->decimal('UnitPrice', 10, 2)->nullable();
            $table->enum('OrderStatus', ['Pending', 'Confirmed', 'Preparing', 'Ready', 'Delivering', 'Delivered', 'Cancelled'])->default('Pending');
            $table->text('SpecialRequests')->nullable();
            $table->timestamp('CreatedAt')->useCurrent();
            $table->foreign('CustomerID')->references('CustomerID')->on('customers')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('DeliveryAgentID')->references('DeliveryAgentID')->on('delivery_agents')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('LiveChatID')->references('LiveChatID')->on('live_chats')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('PaymentID')->references('PaymentID')->on('payments')->onDelete('set null')->onUpdate('cascade');
        });

        Schema::create('menu_order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('MenuItemID');
            $table->unsignedBigInteger('OrderID');
            $table->integer('Quantity')->default(1);
            $table->primary(['MenuItemID', 'OrderID']);
            $table->foreign('MenuItemID')->references('MenuItemID')->on('menu_items')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('OrderID')->references('OrderID')->on('orders')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_order_items');
        Schema::dropIfExists('orders');
    }
};
