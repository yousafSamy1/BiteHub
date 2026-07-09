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
        Schema::create('refund_requests', function (Blueprint $table) {
            $table->id('RequestID');
            $table->unsignedBigInteger('CustomerID');
            $table->unsignedBigInteger('RefundableID'); // OrderID or SubscriptionID
            $table->string('RefundableType'); // 'Order' or 'Subscription'
            $table->decimal('Amount', 10, 2);
            $table->text('Reason')->nullable();
            $table->enum('Status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->text('AdminNotes')->nullable();
            $table->timestamps();

            // Foreign key for customer (assuming customers table exists with CustomerID)
            // If the table name is different, this might need adjustment.
            $table->foreign('CustomerID')->references('CustomerID')->on('customers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refund_requests');
    }
};
