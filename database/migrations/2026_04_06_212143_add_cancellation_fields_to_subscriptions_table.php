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
            $table->text('cancel_reason')->nullable();
            $table->text('pause_reason')->nullable();
            $table->boolean('is_paused')->default(false);
            $table->timestamp('paused_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['cancel_reason', 'pause_reason', 'is_paused', 'paused_at']);
        });
    }
};
