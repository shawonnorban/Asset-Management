<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
{
    /** Human labels for the notification types the lifecycle modules raise. */
    public const TYPES = [
        'maintenance_due' => 'Maintenance due',
        'maintenance_overdue' => 'Maintenance overdue',
        'warranty_expiring' => 'Warranty expiring',
        'warranty_expired' => 'Warranty expired',
        'transfer_approved' => 'Transfer approved',
        'transfer_rejected' => 'Transfer rejected',
        'disposal_approved' => 'Disposal approved',
        'disposal_rejected' => 'Disposal rejected',
    ];

    public function index(Request $request)
    {
        $user = $request->user();

        $filters = [
            'type' => (string) $request->query('type', ''),
            'status' => (string) $request->query('status', ''),
        ];

        $notifications = $user->notifications()
            ->when($filters['type'], fn ($query, $type) => $query->where('type', $type))
            ->when($filters['status'] === 'unread', fn ($query) => $query->where('is_read', false))
            ->when($filters['status'] === 'read', fn ($query) => $query->where('is_read', true))
            ->latest('sent_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('notifications/index', [
            'title' => 'Notifications',
            'description' => 'Review alerts, reminders, and lifecycle updates.',
            'notifications' => $notifications->getCollection()->map(fn ($notification) => [
                'id' => $notification->id,
                'type' => $notification->type,
                'type_label' => self::TYPES[$notification->type] ?? ucfirst(str_replace('_', ' ', $notification->type)),
                'title' => $notification->title,
                'message' => $notification->message,
                'is_read' => (bool) $notification->is_read,
                'link' => $this->linkFor($notification),
                'sent_at' => optional($notification->sent_at)->format('d M Y, h:i A'),
            ])->values(),
            'pagination' => $notifications->toArray(),
            'types' => self::TYPES,
            'filters' => $filters,
            'unread_count' => $user->notifications()->where('is_read', false)->count(),
        ]);
    }

    public function markAsRead(Notification $notification, NotificationService $service)
    {
        abort_if($notification->user_id !== auth()->id(), 403);

        $service->markAsRead($notification);

        return redirect()->back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead(Request $request, NotificationService $service)
    {
        $count = $service->markAllAsRead($request->user());

        return redirect()->back()->with('success', $count
            ? $count . ' notifications marked as read.'
            : 'No unread notifications left.');
    }

    /** Deep-link a notification at the record it is about. */
    private function linkFor(Notification $notification): ?string
    {
        $metadata = $notification->metadata ?? [];

        return match (true) {
            isset($metadata['transfer_id']) => '/transfers/' . $metadata['transfer_id'],
            isset($metadata['disposal_id']) => '/disposals/' . $metadata['disposal_id'],
            isset($metadata['maintenance_request_id']) => '/maintenance-requests/' . $metadata['maintenance_request_id'],
            isset($metadata['warranty_id']) => '/warranties/' . $metadata['warranty_id'],
            isset($metadata['asset_id']) => '/inventory/' . $metadata['asset_id'] . '/lifecycle',
            default => null,
        };
    }
}
