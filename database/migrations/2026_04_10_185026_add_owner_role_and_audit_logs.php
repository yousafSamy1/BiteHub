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
        // Add 'Owner' to Role enum in users table
        DB::statement("ALTER TABLE users MODIFY COLUMN Role ENUM('Admin', 'Customer', 'KitchenOwner', 'Caterer', 'DeliveryAgent', 'Owner') NOT NULL");

        // Create audit_logs table
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id('AuditID');
            $table->unsignedBigInteger('UserID')->nullable();
            $table->string('Action');
            $table->text('Details')->nullable();
            $table->string('IPAddress')->nullable();
            $table->timestamp('CreatedAt')->useCurrent();

            $table->foreign('UserID')->references('UserID')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        DB::statement("ALTER TABLE users MODIFY COLUMN Role ENUM('Admin', 'Customer', 'KitchenOwner', 'Caterer', 'DeliveryAgent') NOT NULL");
    }
};
