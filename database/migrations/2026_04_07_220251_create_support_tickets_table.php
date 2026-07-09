<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id('TicketID');
            $table->unsignedBigInteger('UserID');           // who submitted
            $table->string('SenderType', 30);              // Customer | KitchenOwner | Caterer
            $table->string('Category', 100);               // e.g. "Payment Issue", "Order Problem"
            $table->string('Subject', 255);
            $table->text('Description');
            $table->unsignedBigInteger('OrderID')->nullable();  // relevant order if any
            $table->string('Status', 30)->default('Open');  // Open | InProgress | Resolved | Closed
            $table->text('AdminReply')->nullable();
            $table->timestamps();

            $table->foreign('UserID')->references('UserID')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
