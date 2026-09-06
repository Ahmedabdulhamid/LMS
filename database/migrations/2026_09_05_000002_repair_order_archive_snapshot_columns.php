<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $addUserName = ! Schema::hasColumn('order_archive', 'user_name');
        $addUserEmail = ! Schema::hasColumn('order_archive', 'user_email');
        $addInstructorIds = ! Schema::hasColumn('order_archive', 'instructor_ids');

        if (! $addUserName && ! $addUserEmail && ! $addInstructorIds) {
            return;
        }

        Schema::table('order_archive', function (Blueprint $table) use ($addUserName, $addUserEmail, $addInstructorIds): void {
            if ($addUserName) {
                $table->string('user_name')->nullable()->after('user_id');
                $table->index('user_name');
            }

            if ($addUserEmail) {
                $table->string('user_email')->nullable()->after('user_name');
            }

            if ($addInstructorIds) {
                $table->json('instructor_ids')->nullable()->after('payment_webhook_events');
            }
        });
    }

    public function down(): void
    {
        $dropUserName = Schema::hasColumn('order_archive', 'user_name');
        $dropUserEmail = Schema::hasColumn('order_archive', 'user_email');
        $dropInstructorIds = Schema::hasColumn('order_archive', 'instructor_ids');

        if (! $dropUserName && ! $dropUserEmail && ! $dropInstructorIds) {
            return;
        }

        Schema::table('order_archive', function (Blueprint $table) use ($dropUserName, $dropUserEmail, $dropInstructorIds): void {
            if ($dropUserName) {
                $table->dropIndex(['user_name']);
                $table->dropColumn('user_name');
            }

            if ($dropUserEmail) {
                $table->dropColumn('user_email');
            }

            if ($dropInstructorIds) {
                $table->dropColumn('instructor_ids');
            }
        });
    }
};
