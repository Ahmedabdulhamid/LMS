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
        Schema::create('points_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('points');
            $table->enum('activity_type', ['course_completion', 'video_watch', 'course_review', 'quiz_completion']);
            $table->foreignId('related_id')->nullable(); //(course_id, quiz_id, etc.)
            $table->timestamps();

            $table->unique(['user_id', 'activity_type', 'related_id'], 'unique_user_activity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('points_logs');
    }
};
