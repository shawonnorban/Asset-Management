<?php

namespace App\Mail;

use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The e-mail half of an in-app notification. Queued so a reminder sweep over
 * hundreds of assets never blocks the request or the scheduler tick.
 */
class LifecycleNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Notification $notification,
        public ?string $actionUrl = null,
        public ?string $actionLabel = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->notification->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.lifecycle-notification',
            with: [
                'greetingName' => $this->notification->user?->name ?? 'there',
                'title' => $this->notification->title,
                'body' => $this->notification->message,
                'actionUrl' => $this->actionUrl,
                'actionLabel' => $this->actionLabel ?? 'Open in Asset Management',
                'sentAt' => optional($this->notification->sent_at)->format('d M Y, h:i A'),
            ],
        );
    }
}
