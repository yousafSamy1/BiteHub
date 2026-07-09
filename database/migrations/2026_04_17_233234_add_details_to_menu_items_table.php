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
        Schema::table('menu_items', function (Blueprint $table) {
            $table->text('Ingredients')->nullable()->after('Description');
            $table->string('PortionSize')->nullable()->after('Ingredients');
            $table->integer('Calories')->nullable()->after('PortionSize');
            $table->integer('PrepTime')->nullable()->after('Calories');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn(['Ingredients', 'PortionSize', 'Calories', 'PrepTime']);
        });
    }
};
