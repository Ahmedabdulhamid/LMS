<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_video_progress', function (Blueprint $table): void {
            $table->unsignedInteger('last_position_seconds')->default(0)->after('progress');
            $table->timestamp('last_watched_at')->nullable()->after('completed_at');
            $table->timestamp('completed_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('user_video_progress', function (Blueprint $table): void {
            $table->dropColumn(['last_position_seconds', 'last_watched_at']);
            $table->timestamp('completed_at')->nullable(false)->change();
        });
    }
};
