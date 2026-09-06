<?php

namespace App\Notifications;

use App\Models\Contact;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReplyContactQuestion extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Contact $record,
        public string | array | null $replyMessage,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $locale = str_starts_with((string) $this->record->locale, 'ar') ? 'ar' : 'en';

        return (new MailMessage)
            ->subject(__('contacts.reply_email.subject', ['subject' => $this->record->subject], $locale))
            ->view('emails.contact-reply', [
                'contact' => $this->record,
                'replyHtml' => RichContentRenderer::make($this->replyMessage)->toHtml(),
                'locale' => $locale,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
