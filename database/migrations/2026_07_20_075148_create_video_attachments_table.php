<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('video_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_id')->constrained('course_videos')->onDelete('cascade');
            $table->string('file_name');
            $table->string('file_path');
            $table->unsignedInteger('file_size')->nullable();

            $table->timestamps();
            $table->index('video_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_attachments');
    }
};
