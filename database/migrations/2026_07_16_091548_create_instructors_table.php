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
        Schema::create('instructors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique(); // Added slug
            $table->string('email')->unique();
            $table->string('password');
            $table->text('bio');
            $table->string('small_description')->nullable();
            $table->string('profile_picture')->nullable();
            $table->string('phone')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->date('birthday')->nullable();

            $table->json('educations'); // Changed to JSON
            $table->json('certifications'); // Changed to JSON
            $table->json('skills'); // Changed to JSON
            $table->json('experiences'); // Changed to JSON
            $table->json('specialization'); // Changed to JSON
            $table->json('achivements'); // Changed to JSON

            $table->integer('years_of_experience')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('youtube_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('email_verified_at')->nullable();

            $table->foreignId('rank_id')->nullable()->constrained('instructor_ranks')->nullOnDelete();

            $table->index('slug');
            $table->index('is_active');
            $table->index(['rank_id', 'is_active']);
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instructors');
    }
};
