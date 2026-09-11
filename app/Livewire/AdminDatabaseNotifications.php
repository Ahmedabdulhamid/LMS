<?php

namespace App\Livewire;

use Filament\Livewire\DatabaseNotifications;

class AdminDatabaseNotifications extends DatabaseNotifications
{
    public function getListeners(): array
    {
        $listeners = parent::getListeners();
        if ($channel = parent::getBroadcastChannel()) {
            $listeners["echo-private:{$channel},.database-notifications.sent"] = 'refresh';
        }

        return $listeners;
    }

    public function getBroadcastChannel(): ?string
    {
        // Register through Livewire even when the notification list is empty.
        return null;
    }
}
