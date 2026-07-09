<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_chats', function (Blueprint $table) {
            $table->string('SessionID', 36)->nullable()->after('MenuItemID')->index();
        });
    }

    public function down(): void
    {
        Schema::table('live_chats', function (Blueprint $table) {
            $table->dropColumn('SessionID');
        });
    }
};
