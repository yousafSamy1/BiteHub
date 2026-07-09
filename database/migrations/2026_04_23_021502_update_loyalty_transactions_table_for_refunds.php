<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Using raw SQL for ENUM change to avoid doctrine/dbal dependency issues and ensure precision
        DB::statement("ALTER TABLE loyalty_transactions MODIFY COLUMN Type ENUM('Earned', 'Redeemed', 'Bonus', 'Referral', 'Refund') DEFAULT 'Earned'");
        
        Schema::table('loyalty_transactions', function (Blueprint $table) {
            $table->decimal('Points', 10, 2)->change();
        });
    }

    public function down(): void
    {
        Schema::table('loyalty_transactions', function (Blueprint $table) {
            $table->integer('Points')->change();
        });
        
        DB::statement("ALTER TABLE loyalty_transactions MODIFY COLUMN Type ENUM('Earned', 'Redeemed', 'Bonus', 'Referral') DEFAULT 'Earned'");
    }
};
