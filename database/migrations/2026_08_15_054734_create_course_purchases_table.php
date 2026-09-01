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
        Schema::create('course_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->restrictOnDelete();
            $table->decimal('price', 10, 2);
            $table->string('coupon_code')->nullable();
            $table->decimal('coupon_discount', 10, 2)->default(0);
            $table->timestamp('purchased_at')->useCurrent();
            $table->enum('payment_status', ['pending', 'completed', 'failed'])->default('pending'); // حالة الدفع
            $table->timestamps();

            $table->unique(['user_id', 'course_id']);
            $table->index('user_id');
            $table->index(['course_id', 'payment_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_purchases');
    }
};
