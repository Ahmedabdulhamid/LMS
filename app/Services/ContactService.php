<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Contact;
use App\Notifications\NewContactSubmission;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class ContactService
{
    private const CACHE_KEY = 'contacts.all';

    public function getContacts(): Collection
    {
        return Cache::remember(
            self::CACHE_KEY,
            now()->addMinute(),
            fn (): Collection => Contact::query()->latest()->get(),
        );
    }

    public function storeContact(array $data, ?string $locale = null): Contact
    {
        return DB::transaction(function () use ($data, $locale): Contact {
            $data['locale'] = str_starts_with((string) $locale, 'ar') ? 'ar' : 'en';
            $contact = Contact::query()->create($data);

            $this->forgetContactCache();

            DB::afterCommit(function () use ($contact, $locale): void {
                $admins = Admin::query()->get();

                if ($admins->isNotEmpty()) {
                    Notification::send(
                        $admins,
                        new NewContactSubmission($contact, $locale ?? app()->getLocale()),
                    );
                }
            });

            return $contact;
        });
    }

    public function forgetContactCache(): bool
    {
        return Cache::forget(self::CACHE_KEY);
    }
}
