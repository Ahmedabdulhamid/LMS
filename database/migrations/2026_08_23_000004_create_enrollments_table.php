<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('source_type', 32);
            $table->unsignedBigInteger('source_id')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->string('status', 24)->default('active');
            $table->timestamps();

            $table->index(['user_id', 'course_id', 'status'], 'enrollments_access_index');
            $table->index('ends_at');
            $table->index(['source_type', 'source_id'], 'enrollments_source_index');
        });

        DB::table('course_purchases')
            ->where('payment_status', 'completed')
            ->orderBy('id')
            ->each(function (object $purchase): void {
                DB::table('enrollments')->updateOrInsert(
                    [
                        'source_type' => 'one_time',
                        'source_id' => $purchase->id,
                    ],
                    [
                        'user_id' => $purchase->user_id,
                        'course_id' => $purchase->course_id,
                        'starts_at' => $purchase->purchased_at ?? $purchase->created_at,
                        'ends_at' => null,
                        'status' => 'active',
                        'created_at' => $purchase->created_at,
                        'updated_at' => now(),
                    ],
                );
            });

        DB::table('courses')->select('id')->orderBy('id')->each(function (object $course): void {
            DB::table('courses')->where('id', $course->id)->update([
                'students_count' => DB::table('enrollments')
                    ->where('course_id', $course->id)
                    ->where('status', 'active')
                    ->where('starts_at', '<=', now())
                    ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>', now()))
                    ->distinct()
                    ->count('user_id'),
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
