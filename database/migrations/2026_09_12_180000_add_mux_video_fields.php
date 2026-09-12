<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_videos', function (Blueprint $table): void {
            foreach (['mux_asset_id', 'mux_playback_id', 'mux_pending_reference'] as $column) {
                $table->string($column)->nullable()->unique();
            }
        });
    }

    public function down(): void
    {
        Schema::table('course_videos', function (Blueprint $table): void {
            $table->dropColumn(['mux_asset_id', 'mux_playback_id', 'mux_pending_reference']);
        });
    }
};
