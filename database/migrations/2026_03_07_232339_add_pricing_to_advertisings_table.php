<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('advertisings', function (Blueprint $table) {
            $table->decimal('PricePerDay', 10, 2)->default(50.00)->after('Status');
            $table->decimal('TotalAmount', 10, 2)->nullable()->after('PricePerDay');
            $table->timestamp('PaidAt')->nullable()->after('TotalAmount');
        });
    }

    public function down(): void
    {
        Schema::table('advertisings', function (Blueprint $table) {
            $table->dropColumn(['PricePerDay', 'TotalAmount', 'PaidAt']);
        });
    }
};
