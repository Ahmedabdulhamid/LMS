<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('user_video_progress')
            ->select(['user_id', 'video_id'])
            ->groupBy('user_id', 'video_id')
            ->havingRaw('COUNT(*) > 1')
            ->orderBy('user_id')
            ->each(function (object $duplicate): void {
                $rows = DB::table('user_video_progress')
                    ->where('user_id', $duplicate->user_id)
                    ->where('video_id', $duplicate->video_id)
                    ->orderByDesc('progress')
                    ->orderByDesc('is_completed')
                    ->orderByDesc('id')
                    ->get();

                $keeper = $rows->first();

                DB::table('user_video_progress')->where('id', $keeper->id)->update([
                    'progress' => $rows->max('progress'),
                    'is_completed' => $rows->contains(fn (object $row): bool => (bool) $row->is_completed),
                    'completed_at' => $rows->max('completed_at'),
                ]);

                DB::table('user_video_progress')
                    ->whereIn('id', $rows->pluck('id')->reject(fn (int $id): bool => $id === $keeper->id))
                    ->delete();
            });

        Schema::table('user_video_progress', function (Blueprint $table): void {
            $table->unique(['user_id', 'video_id'], 'user_video_progress_user_video_unique');
        });
    }

    public function down(): void
    {
        Schema::table('user_video_progress', function (Blueprint $table): void {
            $table->dropUnique('user_video_progress_user_video_unique');
        });
    }
};
