<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('advertisings', function (Blueprint $table) {
            DB::statement("ALTER TABLE advertisings MODIFY COLUMN Status ENUM('Pending', 'Approved', 'Rejected', 'Active', 'Inactive') DEFAULT 'Pending'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('advertisings', function (Blueprint $table) {
            DB::statement("ALTER TABLE advertisings MODIFY COLUMN Status ENUM('Active', 'Inactive') DEFAULT 'Active'");
        });
    }
};
