<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_archive', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('original_order_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('number', 32);
            $table->char('currency', 3)->default('EGP');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->string('status', 24);
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->string('coupon_code')->nullable();
            $table->json('billing_details')->nullable();
            $table->string('paymob_order_id')->nullable();
            $table->string('payment_status')->nullable();
            $table->string('paymob_transaction_id')->nullable();
            $table->json('items')->nullable();
            $table->text('archive_reason')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('original_created_at')->nullable();
            $table->timestamp('original_updated_at')->nullable();
            $table->timestamp('archived_at')->useCurrent();
            $table->timestamps();

            $table->index('original_order_id');
            $table->index(['user_id', 'status']);
            $table->index('number');
            $table->index('payment_status');
            $table->index('archived_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_archive');
    }
};
