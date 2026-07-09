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
        \Illuminate\Support\Facades\DB::statement("UPDATE delivery_agents SET Status = 'Available' WHERE Status = 'Busy'");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE delivery_agents MODIFY Status ENUM('Available', 'Offline') DEFAULT 'Available'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE delivery_agents MODIFY Status ENUM('Available', 'Busy', 'Offline') DEFAULT 'Available'");
    }
};
