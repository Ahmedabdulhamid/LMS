<?php

use App\Notifications\ContactBroadcastNotification;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add the Filament database-notification metadata to legacy contact rows.
     */
    public function up(): void
    {
        DB::table('notifications')
            ->where('type', ContactBroadcastNotification::class)
            ->orderBy('id')
            ->chunk(100, function ($notifications): void {
                foreach ($notifications as $notification) {
                    $data = json_decode($notification->data, true, 512, JSON_THROW_ON_ERROR);

                    if (($data['format'] ?? null) === 'filament') {
                        continue;
                    }

                    $data['format'] = 'filament';
                    $data['body'] = $data['body'] ?? $data['message'] ?? '';
                    $data['duration'] = $data['duration'] ?? 'persistent';
                    unset($data['message']);

                    DB::table('notifications')
                        ->where('id', $notification->id)
                        ->update(['data' => json_encode($data, JSON_THROW_ON_ERROR)]);
                }
            });
    }

    /**
     * Legacy notification payloads are not reverted to avoid data loss.
     */
    public function down(): void
    {
        //
    }
};
