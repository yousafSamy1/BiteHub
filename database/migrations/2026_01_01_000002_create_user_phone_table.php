<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_phones', function (Blueprint $table) {
            $table->unsignedBigInteger('UserID');
            $table->string('PhoneNumber', 20);
            $table->primary(['UserID', 'PhoneNumber']);
            $table->foreign('UserID')->references('UserID')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_phones');
    }
};
