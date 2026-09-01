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
        Schema::create('course_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade'); // الكورس اللي بيتراجع
            $table->unsignedTinyInteger('rating'); // 1-5, smaller than enum
            $table->text('comment')->nullable();
            $table->boolean('is_approved')->default(true); // when constructor make comment report if There is abuse in it
            $table->timestamps();
            $table->unique(['user_id', 'course_id']); // prevent duplicate reviews
            $table->index('course_id');
            $table->index(['course_id', 'is_approved', 'rating']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_reviews');
    }
};
