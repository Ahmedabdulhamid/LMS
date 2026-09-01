<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_videos', function (Blueprint $table): void {
            $table->string('status', 20)->default('uploading')->after('url')->index();
            $table->string('hls_path')->nullable()->after('status');
            $table->unsignedInteger('source_width')->nullable()->after('duration');
            $table->unsignedInteger('source_height')->nullable()->after('source_width');
            $table->text('processing_error')->nullable()->after('source_height');
            $table->timestamp('processed_at')->nullable()->after('processing_error');
        });
    }

    public function down(): void
    {
        Schema::table('course_videos', function (Blueprint $table): void {
            $table->dropIndex(['status']);
            $table->dropColumn(['status', 'hls_path', 'source_width', 'source_height', 'processing_error', 'processed_at']);
        });
    }
};
