<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catering_requests', function (Blueprint $table) {
            $table->id('RequestID');
            $table->unsignedBigInteger('CustomerID')->nullable();
            $table->unsignedBigInteger('CatererID')->nullable();
            $table->string('EventType', 100)->nullable();
            $table->date('EventDate')->nullable();
            $table->integer('GuestCount')->nullable();
            $table->decimal('Budget', 10, 2)->nullable();
            $table->text('Details')->nullable();
            $table->enum('Status', ['Pending', 'Accepted', 'Rejected', 'Completed', 'Cancelled'])->default('Pending');
            $table->timestamp('CreatedAt')->useCurrent();
            $table->foreign('CustomerID')->references('CustomerID')->on('customers')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('CatererID')->references('CatererID')->on('caterers')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catering_requests');
    }
};
