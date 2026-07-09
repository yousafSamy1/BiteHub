<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id('AdminID');
            $table->unsignedBigInteger('UserID');
            $table->foreign('UserID')->references('UserID')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id('CustomerID');
            $table->unsignedBigInteger('UserID');
            $table->decimal('WalletBalance', 10, 2)->default(0.00);
            $table->foreign('UserID')->references('UserID')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::create('kitchen_owners', function (Blueprint $table) {
            $table->id('KitchenOwnerID');
            $table->unsignedBigInteger('UserID');
            $table->string('KitchenName', 255)->nullable();
            $table->text('Description')->nullable();
            $table->enum('Status', ['Active', 'Inactive', 'Suspended'])->default('Inactive');
            $table->enum('VerifyStatus', ['Pending', 'Verified', 'Rejected'])->default('Pending');
            $table->string('Attachment', 255)->nullable();
            $table->foreign('UserID')->references('UserID')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::create('caterers', function (Blueprint $table) {
            $table->id('CatererID');
            $table->unsignedBigInteger('UserID');
            $table->string('BusinessName', 255)->nullable();
            $table->text('Description')->nullable();
            $table->string('Attachment', 255)->nullable();
            $table->boolean('IsActive')->default(true);
            $table->foreign('UserID')->references('UserID')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::create('delivery_agents', function (Blueprint $table) {
            $table->id('DeliveryAgentID');
            $table->unsignedBigInteger('UserID');
            $table->string('VehicleType', 50)->nullable();
            $table->string('PlateNumber', 50)->nullable();
            $table->enum('Status', ['Available', 'Busy', 'Offline'])->default('Offline');
            $table->string('Attachment', 255)->nullable();
            $table->foreign('UserID')->references('UserID')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_agents');
        Schema::dropIfExists('caterers');
        Schema::dropIfExists('kitchen_owners');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('admins');
    }
};
