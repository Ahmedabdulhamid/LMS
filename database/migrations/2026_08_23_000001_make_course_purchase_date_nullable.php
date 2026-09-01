<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('course_purchases')
            ->where('payment_status', '!=', 'completed')
            ->update(['purchased_at' => null]);

        Schema::table('course_purchases', function (Blueprint $table): void {
            $table->timestamp('purchased_at')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        DB::table('course_purchases')
            ->whereNull('purchased_at')
            ->update(['purchased_at' => DB::raw('created_at')]);

        Schema::table('course_purchases', function (Blueprint $table): void {
            $table->timestamp('purchased_at')->useCurrent()->nullable(false)->change();
        });
    }
};
