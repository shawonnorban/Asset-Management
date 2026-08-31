<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\MaintenanceRequest;
use App\Models\Notification;
use App\Models\User;
use App\Models\Warranty;
use Illuminate\Support\Collection;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class ReminderService
{
    public function __construct(
        private NotificationService $notifications,
        private AssetLifecycleService $lifecycle,
    ) {
    }

    // =========================
    //     QUERIES
    // =========================

    /** Live warranties inside the warning window. */
    public function upcomingWarranties(int $days = Warranty::WARNING_DAYS): Collection
    {
        return Warranty::with('asset')->expiringWithin($days)->orderBy('end_date')->get();
    }

    /** Warranties whose end date has passed but that are not flagged expired yet. */
    public function lapsedWarranties(): Collection
    {
        return Warranty::with('asset')->lapsed()->orderBy('end_date')->get();
    }

    /** Open work whose scheduled date has already gone by. */
    public function overdueMaintenance(): Collection
    {
        return MaintenanceRequest::with(['asset', 'requester', 'assignee'])
            ->whereIn('status', ['OPEN', 'IN_PROGRESS'])
            ->whereNotNull('scheduled_at')
            ->whereDate('scheduled_at', '<', today())
            ->orderBy('scheduled_at')
            ->get();
    }

    /** Open work scheduled to land within the next few days. */
    public function dueMaintenance(int $days = 3): Collection
    {
        return MaintenanceRequest::with(['asset', 'requester', 'assignee'])
            ->whereIn('status', ['OPEN', 'IN_PROGRESS'])
            ->whereNotNull('scheduled_at')
            ->whereDate('scheduled_at', '>=', today())
            ->whereDate('scheduled_at', '<=', today()->addDays($days))
            ->orderBy('scheduled_at')
            ->get();
    }

    public function expiringAssets(int $days = Warranty::WARNING_DAYS): Collection
    {
        return Asset::query()
            ->whereNotNull('warranty_end')
            ->whereDate('warranty_end', '<=', today()->addDays($days))
            ->get();
    }

    // =========================
    //     DAILY SWEEPS
    // =========================

    /**
     * Daily warranty check: flag expiring and lapsed warranties, log the lapse on
     * the asset timeline, and alert the people who can act on it.
     *
     * @return array{expiring: int, expired: int, notified: int}
     */
    public function runWarrantyCheck(int $days = Warranty::WARNING_DAYS, bool $email = true): array
    {
        $recipients = $this->recipientsFor('maintenance.manage');
        $notified = 0;

        $lapsed = $this->lapsedWarranties();
        foreach ($lapsed as $warranty) {
            $warranty->update(['status' => 'EXPIRED']);

            if ($warranty->asset) {
                $this->lifecycle->createLog(
                    $warranty->asset,
                    'WARRANTY_EXPIRED',
                    'Warranty expired on ' . $warranty->end_date->format('d M Y') . '.',
                    ['status' => 'ACTIVE'],
                    ['status' => 'EXPIRED', 'warranty_id' => $warranty->id],
                );
            }

            $notified += $this->alert(
                $recipients,
                'warranty_expired',
                'Warranty expired: ' . ($warranty->asset?->asset_code ?? 'Asset #' . $warranty->asset_id),
                'The warranty from ' . ($warranty->vendor_name ?: 'the vendor') . ' expired on '
                    . $warranty->end_date->format('d M Y') . '. The asset is no longer covered.',
                ['warranty_id' => $warranty->id, 'asset_id' => $warranty->asset_id],
                $email,
            );
        }

        $expiring = $this->upcomingWarranties($days);
        foreach ($expiring as $warranty) {
            if ($warranty->status !== 'EXPIRING_SOON') {
                $warranty->update(['status' => 'EXPIRING_SOON']);
            }

            $notified += $this->alert(
                $recipients,
                'warranty_expiring',
                'Warranty expiring: ' . ($warranty->asset?->asset_code ?? 'Asset #' . $warranty->asset_id),
                'The warranty expires on ' . $warranty->end_date->format('d M Y') . ' ('
                    . max(0, (int) $warranty->days_remaining) . ' days left). Renew or plan a replacement.',
                ['warranty_id' => $warranty->id, 'asset_id' => $warranty->asset_id],
                $email,
            );
        }

        return [
            'expiring' => $expiring->count(),
            'expired' => $lapsed->count(),
            'notified' => $notified,
        ];
    }

    /**
     * Daily maintenance check: chase overdue jobs and warn about work due shortly.
     *
     * @return array{overdue: int, due_soon: int, notified: int}
     */
    public function runMaintenanceCheck(int $dueWithinDays = 3, bool $email = true): array
    {
        $managers = $this->recipientsFor('maintenance.manage');
        $notified = 0;

        $overdue = $this->overdueMaintenance();
        foreach ($overdue as $request) {
            $notified += $this->alert(
                $this->recipientsForRequest($request, $managers),
                'maintenance_overdue',
                'Maintenance overdue: ' . $request->title,
                ($request->asset?->asset_code ?? 'The asset') . ' was scheduled for '
                    . $request->scheduled_at->format('d M Y') . ' and is still ' . $request->status . '.',
                ['maintenance_request_id' => $request->id, 'asset_id' => $request->asset_id],
                $email,
            );
        }

        $dueSoon = $this->dueMaintenance($dueWithinDays);
        foreach ($dueSoon as $request) {
            $notified += $this->alert(
                $this->recipientsForRequest($request, $managers),
                'maintenance_due',
                'Maintenance due: ' . $request->title,
                ($request->asset?->asset_code ?? 'The asset') . ' is scheduled for maintenance on '
                    . $request->scheduled_at->format('d M Y') . '.',
                ['maintenance_request_id' => $request->id, 'asset_id' => $request->asset_id],
                $email,
            );
        }

        return [
            'overdue' => $overdue->count(),
            'due_soon' => $dueSoon->count(),
            'notified' => $notified,
        ];
    }

    // =========================
    //     HELPERS
    // =========================

    /**
     * Send one alert per recipient, skipping anyone who already got the same
     * alert today - a daily scheduler must not turn into a daily inbox flood.
     */
    private function alert(Collection $recipients, string $type, string $title, string $message, array $metadata, bool $email): int
    {
        $sent = 0;

        foreach ($recipients as $recipient) {
            if ($this->alreadySentToday($recipient, $type, $metadata)) {
                continue;
            }

            $this->notifications->send($recipient, $type, $title, $message, $metadata, $email);
            $sent++;
        }

        return $sent;
    }

    private function alreadySentToday(User $user, string $type, array $metadata): bool
    {
        $query = Notification::query()
            ->where('user_id', $user->getKey())
            ->where('type', $type)
            ->whereDate('sent_at', today());

        foreach (['warranty_id', 'maintenance_request_id'] as $key) {
            if (isset($metadata[$key])) {
                $query->where('metadata->' . $key, $metadata[$key]);
            }
        }

        return $query->exists();
    }

    /** Managers, plus the people directly attached to the request. */
    private function recipientsForRequest(MaintenanceRequest $request, Collection $managers): Collection
    {
        return $managers
            ->concat(array_filter([$request->assignee, $request->requester]))
            ->unique(fn (User $user) => $user->getKey())
            ->values();
    }

    /** @return Collection<int, User> */
    private function recipientsFor(string $permission): Collection
    {
        try {
            return User::permission($permission)->get();
        } catch (PermissionDoesNotExist) {
            return collect();
        }
    }
}
