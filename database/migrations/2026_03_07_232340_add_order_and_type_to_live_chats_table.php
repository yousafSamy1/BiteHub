<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_chats', function (Blueprint $table) {
            $table->unsignedBigInteger('OrderID')->nullable()->after('LiveChatID');
            $table->enum('Type', ['message', 'request', 'approved', 'rejected'])->default('message')->after('Message');
            $table->foreign('OrderID')->references('OrderID')->on('orders')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('live_chats', function (Blueprint $table) {
            $table->dropForeign(['OrderID']);
            $table->dropColumn(['OrderID', 'Type']);
        });
    }
};
