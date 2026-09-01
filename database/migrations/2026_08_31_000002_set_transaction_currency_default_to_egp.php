<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('table_transactions', function (Blueprint $table): void {
            $table->string('currency')->default('EGP')->change();
        });
    }

    public function down(): void
    {
        Schema::table('table_transactions', function (Blueprint $table): void {
            $table->string('currency')->default('USD')->change();
        });
    }
};
