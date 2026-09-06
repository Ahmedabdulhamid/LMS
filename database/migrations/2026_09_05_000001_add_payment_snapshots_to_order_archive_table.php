<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_archive', function (Blueprint $table): void {
            $table->string('user_name')->nullable()->after('user_id');
            $table->string('user_email')->nullable()->after('user_name');
            $table->json('payment_transactions')->nullable()->after('items');
            $table->longText('payment_webhook_events')->nullable()->after('payment_transactions');
            $table->json('instructor_ids')->nullable()->after('payment_webhook_events');
            $table->unique('original_order_id');
            $table->index('user_name');
        });
    }

    public function down(): void
    {
        Schema::table('order_archive', function (Blueprint $table): void {
            $table->dropUnique(['original_order_id']);
            $table->dropIndex(['user_name']);
            $table->dropColumn(['user_name', 'user_email', 'payment_transactions', 'payment_webhook_events', 'instructor_ids']);
        });
    }
};
