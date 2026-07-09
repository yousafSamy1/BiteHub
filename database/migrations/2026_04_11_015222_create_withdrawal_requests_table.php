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
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id('RequestID');
            $table->unsignedBigInteger('UserID');
            $table->decimal('Amount', 15, 2);
            $table->string('Method'); // Bank, VodafoneCash, InstaPay
            $table->json('MethodDetails');
            $table->enum('Status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->text('AdminNotes')->nullable();
            $table->timestamps();

            $table->foreign('UserID')->references('UserID')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawal_requests');
    }
};
