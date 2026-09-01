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
        Schema::table('orders', function (Blueprint $table): void {
            // Add payment status column
            $table->string('payment_status')->nullable()->default('pending')->after('paymob_order_id');

            // Add Paymob transaction ID
            $table->string('paymob_transaction_id')->nullable()->after('payment_status');

            // Index for payment status queries
            $table->index('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex(['payment_status']);
            $table->dropColumn(['payment_status', 'paymob_transaction_id']);
        });
    }
};
