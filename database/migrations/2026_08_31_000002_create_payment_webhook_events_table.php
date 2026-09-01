<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_webhook_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider', 32);
            $table->string('deduplication_key', 64);
            $table->string('event_type', 64);
            $table->string('provider_transaction_id')->nullable();
            $table->string('provider_order_id')->nullable();
            $table->longText('payload');
            $table->string('status', 32)->default('received');
            $table->unsignedInteger('delivery_count')->default(1);
            $table->unsignedInteger('processing_attempts')->default(0);
            $table->text('failure_reason')->nullable();
            $table->timestamp('received_at');
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('next_retry_at')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'deduplication_key']);
            $table->index(['status', 'next_retry_at']);
            $table->index(['provider_order_id', 'received_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_webhook_events');
    }
};
