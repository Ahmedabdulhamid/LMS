<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->timestamp('expiry_warning_for')->nullable()->after('ends_at');
            $table->index(['status', 'ends_at', 'expiry_warning_for'], 'subscriptions_expiry_warning_index');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->dropIndex('subscriptions_expiry_warning_index');
            $table->dropColumn('expiry_warning_for');
        });
    }
};
