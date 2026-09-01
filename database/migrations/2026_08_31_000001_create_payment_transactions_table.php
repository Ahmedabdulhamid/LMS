<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 32);
            $table->string('provider_transaction_id');
            $table->string('provider_order_id')->nullable();
            $table->unsignedBigInteger('amount_cents')->nullable();
            $table->char('currency', 3)->nullable();
            $table->string('integration_id')->nullable();
            $table->string('status', 32);
            $table->string('failure_reason')->nullable();
            $table->timestamp('received_at');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->unique(['provider', 'provider_transaction_id']);
            $table->index(['order_id', 'status']);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->unique('paymob_order_id');
            $table->unique('paymob_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropUnique(['paymob_order_id']);
            $table->dropUnique(['paymob_transaction_id']);
        });
        Schema::dropIfExists('payment_transactions');
    }
};
