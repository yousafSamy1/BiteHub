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
        Schema::table('refund_requests', function (Blueprint $table) {
            $table->decimal('OriginalAmount', 10, 2)->after('RefundableType');
            $table->decimal('ConsumedAmount', 10, 2)->after('OriginalAmount')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('refund_requests', function (Blueprint $table) {
            $table->dropColumn(['OriginalAmount', 'ConsumedAmount']);
        });
    }
};
