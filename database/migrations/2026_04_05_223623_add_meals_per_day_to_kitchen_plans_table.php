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
        Schema::table('kitchen_plans', function (Blueprint $table) {
            $table->integer('MealsPerDay')->default(1)->after('PlanTime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kitchen_plans', function (Blueprint $table) {
            $table->dropColumn('MealsPerDay');
        });
    }
};
