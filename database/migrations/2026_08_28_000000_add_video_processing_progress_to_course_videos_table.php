<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_videos', function (Blueprint $table): void {
            $table->string('processing_stage')->nullable()->after('source_height');
            $table->unsignedTinyInteger('processing_progress')->default(0)->after('processing_stage');
            $table->timestamp('processing_started_at')->nullable()->after('processing_error');
        });
    }

    public function down(): void
    {
        Schema::table('course_videos', function (Blueprint $table): void {
            $table->dropColumn(['processing_stage', 'processing_progress', 'processing_started_at']);
        });
    }
};
