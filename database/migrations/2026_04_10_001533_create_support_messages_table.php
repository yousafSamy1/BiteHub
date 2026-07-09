<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_messages', function (Blueprint $table) {
            $table->id('MessageID');
            $table->unsignedBigInteger('InquiryID');
            $table->string('SenderType', 20); // Bot | User | Admin
            $table->unsignedBigInteger('SenderID')->nullable(); // UserID if not Bot
            $table->text('Message');
            $table->boolean('IsRead')->default(false);
            $table->timestamps();

            $table->foreign('InquiryID')->references('InquiryID')->on('support_inquiries')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_messages');
    }
};
