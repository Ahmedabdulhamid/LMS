<?php

namespace App\Events;

use Filament\Notifications\Events\DatabaseNotificationsSent;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class AdminDatabaseNotificationsSent extends DatabaseNotificationsSent implements ShouldBroadcastNow {}
