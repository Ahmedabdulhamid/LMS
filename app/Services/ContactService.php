<?php

namespace App\Services;

use App\Events\ContactCreated;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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

            DB::afterCommit(fn () => ContactCreated::dispatch($contact));

            return $contact;
        });
    }

    public function forgetContactCache(): bool
    {
        return Cache::forget(self::CACHE_KEY);
    }
}
