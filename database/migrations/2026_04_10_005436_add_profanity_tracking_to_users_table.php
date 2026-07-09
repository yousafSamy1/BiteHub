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
        Schema::table('users', function (Blueprint $table) {
            $table->string('Status', 30)->default('Active')->after('Role'); // Active, Suspended
            $table->integer('ProfanityStrikes')->default(0)->after('Status');
            $table->timestamp('LastViolationAt')->nullable()->after('ProfanityStrikes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['Status', 'ProfanityStrikes', 'LastViolationAt']);
        });
    }
};
