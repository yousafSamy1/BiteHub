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
        Schema::table('live_chats', function (Blueprint $table) {
            $table->unsignedBigInteger('MenuItemID')->nullable()->after('OrderID');
            $table->unsignedBigInteger('OrderID')->nullable()->change();
            
            // If foreign keys exist, you'd handle them here. 
            // Since we're in a simple setup, we'll just add the column.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('live_chats', function (Blueprint $table) {
            $table->dropColumn('MenuItemID');
            $table->unsignedBigInteger('OrderID')->nullable(false)->change();
        });
    }
};
