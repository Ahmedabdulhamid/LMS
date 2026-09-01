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
        Schema::create('instructor_ranks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->integer('required_courses')->default(0);
            $table->integer('required_hours')->default(0);
            $table->integer('required_students')->default(0); // Optional
            $table->text('benefits')->nullable();
            $table->integer('order')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instructor_ranks');
    }
};
