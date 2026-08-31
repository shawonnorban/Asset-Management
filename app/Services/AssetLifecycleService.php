<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetLifecycleLog;
use App\Support\AssetLifecycleStatus;
use Illuminate\Database\Eloquent\Collection;

class AssetLifecycleService
{
    public function createLog(Asset $asset, string $eventType, ?string $description = null, ?array $oldValues = null, ?array $newValues = null, ?int $userId = null): AssetLifecycleLog
    {
        return AssetLifecycleLog::create([
            'asset_id' => $asset->getKey(),
            'user_id' => $userId,
            'event_type' => $eventType,
            'description' => $description ?? 'Lifecycle event recorded.',
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'event_at' => now(),
        ]);
    }

    public function getActiveWarrantyAssets(): Collection
    {
        return Asset::whereHas('warranties', function ($query) {
            $query->where('status', 'ACTIVE');
        })->get();
    }

    public function getMaintenanceAlerts(): Collection
    {
        return Asset::whereHas('maintenanceRequests', function ($query) {
            $query->whereIn('status', ['OPEN', 'IN_PROGRESS']);
        })->get();
    }

    public function isValidStatus(string $type, string $status): bool
    {
        $map = [
            'maintenance' => AssetLifecycleStatus::MAINTENANCE_STATUSES,
            'warranty' => AssetLifecycleStatus::WARRANTY_STATUSES,
            'transfer' => AssetLifecycleStatus::TRANSFER_STATUSES,
            'disposal' => AssetLifecycleStatus::DISPOSAL_STATUSES,
        ];

        return isset($map[$type][$status]);
    }
}
