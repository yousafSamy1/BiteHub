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
        Schema::table('menu_subscribes', function (Blueprint $table) {
            $table->enum('Status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->enum('ModifiedStatus', ['None', 'Pending Modification'])->default('None');
            $table->text('KitchenNotes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_subscribes', function (Blueprint $table) {
            $table->dropColumn(['Status', 'ModifiedStatus', 'KitchenNotes']);
        });
    }
};
