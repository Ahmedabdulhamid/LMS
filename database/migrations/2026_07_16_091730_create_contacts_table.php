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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable(); // nullable
            $table->string('subject');
            $table->string('email');
            $table->text('message');
            $table->enum('status', ['new', 'read', 'replied', 'archived'])->default('new'); // added
            $table->timestamp('read_at')->nullable(); // added
            $table->timestamps();

            $table->index('status');
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
