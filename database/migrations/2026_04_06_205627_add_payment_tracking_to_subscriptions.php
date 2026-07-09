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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->decimal('PaidAmount', 10, 2)->default(0)->after('Price');
            $table->decimal('DeliveryCharge', 10, 2)->default(0)->after('PaidAmount');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('Amount', 10, 2)->default(0)->after('Method');
            $table->enum('Status', ['Pending', 'Completed'])->default('Completed')->after('Amount');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['PaidAmount', 'DeliveryCharge']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['Amount', 'Status', 'created_at', 'updated_at']);
        });
    }
};
