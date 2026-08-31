<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetDisposal;
use App\Models\AssetLocation;
use App\Models\AssetTransfer;
use App\Models\MaintenanceRequest;
use App\Models\User;
use App\Models\Warranty;
use App\Services\AssetLifecycleService;
use App\Services\NotificationService;
use Illuminate\Database\Seeder;

/**
 * Demo data for staging and sales walkthroughs: enough maintenance, warranty,
 * transfer, disposal and notification records that every commercial screen has
 * something to show, including the alert states.
 *
 * Safe to re-run - it clears only the rows it created for the demo assets.
 */
class CommercialDemoSeeder extends Seeder
{
    public function run(): void
    {
        $assets = Asset::orderBy('id')->take(8)->get();

        if ($assets->isEmpty()) {
            $this->command?->warn('No assets found. Run AssetSeeder first.');

            return;
        }

        $manager = User::whereHas('roles', fn ($query) => $query->whereIn('name', ['super_admin', 'management']))->first()
            ?? User::first();

        if (! $manager) {
            $this->command?->warn('No users found. Run UserSeeder first.');

            return;
        }

        $lifecycle = app(AssetLifecycleService::class);
        $notifications = app(NotificationService::class);
        $locations = AssetLocation::orderBy('id')->get();

        $this->seedMaintenance($assets, $manager, $lifecycle);
        $this->seedWarranties($assets, $lifecycle);
        $this->seedTransfers($assets, $locations, $manager, $lifecycle);
        $this->seedDisposals($assets, $manager, $lifecycle);
        $this->seedNotifications($assets, $manager, $notifications);

        $this->command?->info('Commercial demo data seeded.');
    }

    private function seedMaintenance($assets, User $manager, AssetLifecycleService $lifecycle): void
    {
        $samples = [
            ['title' => 'Annual servicing', 'type' => 'PREVENTIVE', 'priority' => 'MEDIUM', 'status' => 'OPEN', 'scheduled' => 5, 'estimated' => 4500, 'actual' => 0],
            ['title' => 'Screen flickering', 'type' => 'CORRECTIVE', 'priority' => 'HIGH', 'status' => 'IN_PROGRESS', 'scheduled' => -4, 'estimated' => 8000, 'actual' => 0],
            ['title' => 'Battery replacement', 'type' => 'CORRECTIVE', 'priority' => 'CRITICAL', 'status' => 'IN_PROGRESS', 'scheduled' => -12, 'estimated' => 12000, 'actual' => 0],
            ['title' => 'Toner and drum replacement', 'type' => 'PREVENTIVE', 'priority' => 'LOW', 'status' => 'COMPLETED', 'scheduled' => -40, 'estimated' => 6000, 'actual' => 6350],
            ['title' => 'Motherboard repair', 'type' => 'CORRECTIVE', 'priority' => 'HIGH', 'status' => 'COMPLETED', 'scheduled' => -75, 'estimated' => 15000, 'actual' => 18200],
        ];

        foreach ($samples as $index => $sample) {
            $asset = $assets[$index % $assets->count()];

            $request = MaintenanceRequest::updateOrCreate(
                ['asset_id' => $asset->id, 'title' => $sample['title']],
                [
                    'maintenance_type' => $sample['type'],
                    'description' => 'Demo record: ' . strtolower($sample['title']) . ' for ' . $asset->asset_code . '.',
                    'priority' => $sample['priority'],
                    'status' => $sample['status'],
                    'requested_at' => today()->addDays($sample['scheduled'] - 3),
                    'scheduled_at' => today()->addDays($sample['scheduled']),
                    'completed_at' => $sample['status'] === 'COMPLETED' ? today()->addDays($sample['scheduled'] + 2) : null,
                    'estimated_cost' => $sample['estimated'],
                    'actual_cost' => $sample['actual'],
                    'vendor_name' => ['TechCare Ltd', 'Nova Service', 'CityFix Solutions'][$index % 3],
                    'requested_by' => $manager->id,
                    'assigned_to' => $manager->id,
                ],
            );

            $lifecycle->createLog(
                $asset,
                $sample['status'] === 'COMPLETED' ? 'MAINTENANCE_COMPLETED' : 'MAINTENANCE_REQUESTED',
                'Demo maintenance record: ' . $request->title,
                null,
                ['request_id' => $request->id, 'status' => $request->status],
                $manager->id,
            );
        }
    }

    private function seedWarranties($assets, AssetLifecycleService $lifecycle): void
    {
        // Deliberately spans healthy, expiring, and lapsed cover so the alert
        // banners and the warranty report have something to render.
        $samples = [
            ['vendor' => 'Dell Bangladesh', 'type' => 'MANUFACTURER', 'ends' => 400, 'claim' => 'NOT_STARTED'],
            ['vendor' => 'HP Care Pack', 'type' => 'EXTENDED', 'ends' => 18, 'claim' => 'NOT_STARTED'],
            ['vendor' => 'Canon Service', 'type' => 'MANUFACTURER', 'ends' => 9, 'claim' => 'IN_PROGRESS'],
            ['vendor' => 'Lenovo Premier', 'type' => 'EXTENDED', 'ends' => -30, 'claim' => 'CLAIMED'],
            ['vendor' => 'Asus Support', 'type' => 'MANUFACTURER', 'ends' => -120, 'claim' => 'NOT_STARTED'],
        ];

        foreach ($samples as $index => $sample) {
            $asset = $assets[$index % $assets->count()];
            $endDate = today()->addDays($sample['ends']);

            $warranty = Warranty::updateOrCreate(
                ['asset_id' => $asset->id, 'vendor_name' => $sample['vendor']],
                [
                    'warranty_type' => $sample['type'],
                    'start_date' => $endDate->copy()->subYear(),
                    'end_date' => $endDate,
                    'status' => Warranty::deriveStatus($endDate),
                    'coverage_details' => 'Demo cover: parts and labour, on-site response within two business days.',
                    'claim_status' => $sample['claim'],
                ],
            );

            $lifecycle->createLog(
                $asset,
                $warranty->status === 'EXPIRED' ? 'WARRANTY_EXPIRED' : 'WARRANTY_REGISTERED',
                'Demo warranty from ' . $warranty->vendor_name . ' ending ' . $endDate->format('d M Y') . '.',
                null,
                ['warranty_id' => $warranty->id, 'status' => $warranty->status],
            );
        }
    }

    private function seedTransfers($assets, $locations, User $manager, AssetLifecycleService $lifecycle): void
    {
        if ($locations->count() < 2) {
            return;
        }

        $samples = [
            ['status' => 'REQUESTED', 'reason' => 'Department restructure', 'days' => -12],
            ['status' => 'REQUESTED', 'reason' => 'New joiner allocation', 'days' => -2],
            ['status' => 'APPROVED', 'reason' => 'Branch office setup', 'days' => -25],
            ['status' => 'APPROVED', 'reason' => 'Desk relocation', 'days' => -50],
            ['status' => 'REJECTED', 'reason' => 'Requested asset already allocated', 'days' => -18],
        ];

        foreach ($samples as $index => $sample) {
            $asset = $assets[$index % $assets->count()];
            $settled = in_array($sample['status'], ['APPROVED', 'COMPLETED'], true);

            $transfer = AssetTransfer::updateOrCreate(
                ['asset_id' => $asset->id, 'reason' => $sample['reason']],
                [
                    'from_location_id' => $locations[$index % $locations->count()]->id,
                    'to_location_id' => $locations[($index + 1) % $locations->count()]->id,
                    'requested_by' => $manager->id,
                    'approved_by' => $sample['status'] === 'REQUESTED' ? null : $manager->id,
                    'status' => $sample['status'],
                    'notes' => 'Demo transfer record.',
                    'requested_at' => today()->addDays($sample['days']),
                    'transferred_at' => $settled ? today()->addDays($sample['days'] + 3) : null,
                ],
            );

            $lifecycle->createLog(
                $asset,
                $settled ? 'TRANSFER_COMPLETED' : 'TRANSFER_REQUESTED',
                'Demo transfer: ' . $transfer->reason,
                null,
                ['transfer_id' => $transfer->id, 'status' => $transfer->status],
                $manager->id,
            );
        }
    }

    private function seedDisposals($assets, User $manager, AssetLifecycleService $lifecycle): void
    {
        $samples = [
            ['status' => 'REQUESTED', 'reason' => 'Beyond economical repair', 'method' => 'RECYCLE', 'value' => 0, 'days' => -6],
            ['status' => 'DISPOSED', 'reason' => 'End of useful life', 'method' => 'SALE', 'value' => 7500, 'days' => -60],
            ['status' => 'DISPOSED', 'reason' => 'Damaged beyond repair', 'method' => 'SCRAP', 'value' => 1200, 'days' => -95],
            ['status' => 'REJECTED', 'reason' => 'Asset still under warranty', 'method' => 'RECYCLE', 'value' => 0, 'days' => -30],
        ];

        // Disposals target the tail of the list so demo transfers and disposals
        // do not fight over the same asset status.
        $pool = $assets->reverse()->values();

        foreach ($samples as $index => $sample) {
            $asset = $pool[$index % $pool->count()];

            $disposal = AssetDisposal::updateOrCreate(
                ['asset_id' => $asset->id, 'reason' => $sample['reason']],
                [
                    'requested_by' => $manager->id,
                    'approved_by' => $sample['status'] === 'REQUESTED' ? null : $manager->id,
                    'status' => $sample['status'],
                    'method' => $sample['method'],
                    'value_recovered' => $sample['value'],
                    'requested_at' => today()->addDays($sample['days']),
                    'disposed_at' => $sample['status'] === 'DISPOSED' ? today()->addDays($sample['days'] + 5) : null,
                    'notes' => 'Demo disposal record.',
                ],
            );

            if ($sample['status'] === 'DISPOSED') {
                $asset->update(['status' => 'DISPOSED', 'employee_id' => null]);
            }

            $lifecycle->createLog(
                $asset,
                $sample['status'] === 'DISPOSED' ? 'DISPOSAL_COMPLETED' : 'DISPOSAL_REQUESTED',
                'Demo disposal: ' . $disposal->reason,
                null,
                ['disposal_id' => $disposal->id, 'status' => $disposal->status],
                $manager->id,
            );
        }
    }

    private function seedNotifications($assets, User $manager, NotificationService $notifications): void
    {
        $manager->notifications()->where('metadata->demo', true)->delete();

        $asset = $assets->first();

        $samples = [
            ['type' => 'warranty_expiring', 'title' => 'Warranty expiring: ' . $asset->asset_code, 'message' => 'Cover from HP Care Pack expires in 18 days.'],
            ['type' => 'warranty_expired', 'title' => 'Warranty expired: ' . $asset->asset_code, 'message' => 'Cover from Lenovo Premier lapsed 30 days ago.'],
            ['type' => 'maintenance_overdue', 'title' => 'Maintenance overdue: Battery replacement', 'message' => 'The scheduled service date passed 12 days ago.'],
            ['type' => 'transfer_approved', 'title' => 'Transfer approved', 'message' => 'Branch office setup transfer has been approved.'],
            ['type' => 'disposal_approved', 'title' => 'Disposal approved', 'message' => 'End of useful life disposal has been approved.'],
        ];

        foreach ($samples as $index => $sample) {
            $notification = $notifications->send(
                $manager,
                $sample['type'],
                $sample['title'],
                $sample['message'],
                ['demo' => true, 'asset_id' => $asset->id],
            );

            // Leave the two newest unread so the header badge has something to show.
            if ($index >= 2) {
                $notifications->markAsRead($notification);
            }
        }
    }
}
