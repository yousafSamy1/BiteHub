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
        Schema::table('kitchen_owners', function (Blueprint $table) {
            $table->string('Location', 255)->nullable()->after('KitchenName');
            $table->string('DeliveryTime', 50)->nullable()->after('Location');
        });
    }

    public function down(): void
    {
        Schema::table('kitchen_owners', function (Blueprint $table) {
            $table->dropColumn(['Location', 'DeliveryTime']);
        });
    }
};
