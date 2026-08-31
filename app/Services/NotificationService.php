<?php

namespace App\Services;

use App\Mail\LifecycleNotificationMail;
use App\Models\Notification;
use App\Models\NotificationTemplate;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Record an in-app notification, optionally mirroring it to e-mail.
     *
     * @param  array<string, mixed>  $metadata
     * @param  \DateTimeInterface|\DateInterval|int|null  $delay  queue the mail instead of sending it now
     */
    public function send(
        User $user,
        string $type,
        string $title,
        string $message,
        array $metadata = [],
        bool $email = false,
        $delay = null,
    ): Notification {
        $notification = Notification::create([
            'user_id' => $user->getKey(),
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'metadata' => $metadata,
            'is_read' => false,
            'sent_at' => now(),
        ]);

        if ($email) {
            $this->email($notification, $delay);
        }

        return $notification;
    }

    /**
     * Send the same notification to several recipients - used by the reminder
     * sweeps, which alert every manager rather than one named user.
     *
     * @param  iterable<User>  $users
     * @return Collection<int, Notification>
     */
    public function sendMany(iterable $users, string $type, string $title, string $message, array $metadata = [], bool $email = false): Collection
    {
        $sent = collect();

        foreach ($users as $user) {
            $sent->push($this->send($user, $type, $title, $message, $metadata, $email));
        }

        return $sent;
    }

    /**
     * Build a notification from a stored template, substituting :placeholders.
     *
     * @param  array<string, mixed>  $variables
     */
    public function sendFromTemplate(User $user, string $templateName, array $variables = [], array $metadata = [], bool $email = false): ?Notification
    {
        $template = $this->template($templateName);

        if (! $template) {
            return null;
        }

        return $this->send(
            $user,
            $templateName,
            $this->render($template->subject ?? $templateName, $variables),
            $this->render($template->body, $variables),
            $metadata,
            $email || $template->channel === 'mail',
        );
    }

    public function template(string $name): ?NotificationTemplate
    {
        return NotificationTemplate::query()->where('name', $name)->where('is_active', true)->first();
    }

    /** Replace :placeholder tokens in a template string. */
    public function render(string $text, array $variables = []): string
    {
        foreach ($variables as $key => $value) {
            $text = str_replace(':' . $key, (string) $value, $text);
        }

        return $text;
    }

    /**
     * Queue the e-mail copy of a notification. A mail failure must never take
     * the in-app notification (or the surrounding request) down with it.
     */
    public function email(Notification $notification, $delay = null): bool
    {
        $notification->loadMissing('user');
        $recipient = $notification->user;

        if (! $recipient?->email) {
            return false;
        }

        $mailable = new LifecycleNotificationMail(
            $notification,
            $this->actionUrlFor($notification),
        );

        try {
            $pending = Mail::to($recipient->email);

            $delay === null
                ? $pending->queue($mailable)
                : $pending->later($delay, $mailable);
        } catch (\Throwable $exception) {
            Log::warning('Notification e-mail could not be queued.', [
                'notification_id' => $notification->getKey(),
                'error' => $exception->getMessage(),
            ]);

            return false;
        }

        return true;
    }

    public function markAsRead(Notification $notification): Notification
    {
        $notification->update(['is_read' => true]);

        return $notification->fresh();
    }

    public function markAllAsRead(User $user): int
    {
        return $user->notifications()->where('is_read', false)->update(['is_read' => true]);
    }

    public function unreadCount(User $user): int
    {
        return $user->notifications()->where('is_read', false)->count();
    }

    /** Deep-link the e-mail straight at the record the notification is about. */
    private function actionUrlFor(Notification $notification): ?string
    {
        $metadata = $notification->metadata ?? [];

        return match (true) {
            isset($metadata['transfer_id']) => url('/transfers/' . $metadata['transfer_id']),
            isset($metadata['disposal_id']) => url('/disposals/' . $metadata['disposal_id']),
            isset($metadata['maintenance_request_id']) => url('/maintenance-requests/' . $metadata['maintenance_request_id']),
            isset($metadata['warranty_id']) => url('/warranties/' . $metadata['warranty_id']),
            isset($metadata['asset_id']) => url('/inventory/' . $metadata['asset_id'] . '/lifecycle'),
            default => url('/notifications'),
        };
    }
}
