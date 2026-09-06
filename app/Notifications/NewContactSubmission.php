<?php

namespace App\Notifications;

use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewContactSubmission extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Contact $contact,
        public string $submissionLocale = 'en',
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $locale = str_starts_with($this->submissionLocale, 'ar') ? 'ar' : 'en';

        return (new MailMessage)
            ->subject(__('contacts.admin_email.subject', ['name' => $this->contact->name], $locale))
            ->view('emails.new-contact-submission', [
                'contact' => $this->contact,
                'locale' => $locale,
            ]);
    }
}
