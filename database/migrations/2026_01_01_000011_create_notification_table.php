<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id('NotificationID');
            $table->unsignedBigInteger('UserID')->nullable();
            $table->string('Title', 255)->nullable();
            $table->text('Message')->nullable();
            $table->boolean('IsRead')->default(false);
            $table->enum('Type', ['Order', 'Promotion', 'System', 'Chat'])->default('System');
            $table->timestamp('CreatedAt')->useCurrent();
            $table->foreign('UserID')->references('UserID')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
