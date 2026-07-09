<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id('SubscriptionID');
            $table->unsignedBigInteger('CustomerID')->nullable();
            $table->string('PlanTime', 50)->nullable();
            $table->enum('Status', ['Active', 'Expired', 'Cancelled'])->default('Active');
            $table->decimal('Price', 10, 2)->nullable();
            $table->date('StartDate')->nullable();
            $table->date('EndDate')->nullable();
            $table->foreign('CustomerID')->references('CustomerID')->on('customers')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('PaymentID');
            $table->unsignedBigInteger('SubscriptionID');
            $table->primary(['PaymentID', 'SubscriptionID']);
            $table->foreign('PaymentID')->references('PaymentID')->on('payments')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('SubscriptionID')->references('SubscriptionID')->on('subscriptions')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::create('menu_subscribes', function (Blueprint $table) {
            $table->unsignedBigInteger('SubscriptionID');
            $table->unsignedBigInteger('MenuItemID');
            $table->primary(['SubscriptionID', 'MenuItemID']);
            $table->foreign('SubscriptionID')->references('SubscriptionID')->on('subscriptions')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('MenuItemID')->references('MenuItemID')->on('menu_items')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_subscribes');
        Schema::dropIfExists('subscription_payments');
        Schema::dropIfExists('subscriptions');
    }
};
