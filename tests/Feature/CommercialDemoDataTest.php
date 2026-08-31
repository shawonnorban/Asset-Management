<?php

namespace Tests\Feature;

use App\Models\AssetDisposal;
use App\Models\AssetLifecycleLog;
use App\Models\AssetTransfer;
use App\Models\MaintenanceRequest;
use App\Models\Notification;
use App\Models\NotificationTemplate;
use App\Models\User;
use App\Models\Warranty;
use App\Services\ReportService;
use Database\Seeders\AssetCategorySeeder;
use Database\Seeders\AssetLocationSeeder;
use Database\Seeders\AssetSeeder;
use Database\Seeders\CommercialDemoSeeder;
use Database\Seeders\NotificationTemplateSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The staging demo data set: enough records that every commercial screen, and
 * every alert state, has something to show.
 */
class CommercialDemoDataTest extends TestCase
{
    use RefreshDatabase;

    private function seedDemo(): void
    {
        $this->seed(UserSeeder::class);
        $this->seed(AssetCategorySeeder::class);
        $this->seed(AssetLocationSeeder::class);
        $this->seed(AssetSeeder::class);
        $this->seed(NotificationTemplateSeeder::class);
        $this->seed(CommercialDemoSeeder::class);
    }

    public function test_notification_templates_cover_every_alert_type(): void
    {
        $this->seed(NotificationTemplateSeeder::class);

        foreach (['maintenance_due', 'maintenance_overdue', 'warranty_expiring', 'warranty_expired',
            'transfer_approved', 'transfer_rejected', 'disposal_approved', 'disposal_rejected'] as $name) {
            $this->assertDatabaseHas('notification_templates', ['name' => $name, 'is_active' => true]);
        }
    }

    public function test_template_seeder_can_be_run_twice_without_duplicating(): void
    {
        $this->seed(NotificationTemplateSeeder::class);
        $this->seed(NotificationTemplateSeeder::class);

        $this->assertSame(8, NotificationTemplate::count());
    }

    public function test_demo_seeder_populates_every_commercial_module(): void
    {
        $this->seedDemo();

        $this->assertGreaterThan(0, MaintenanceRequest::count(), 'Demo maintenance records are missing.');
        $this->assertGreaterThan(0, Warranty::count(), 'Demo warranty records are missing.');
        $this->assertGreaterThan(0, AssetTransfer::count(), 'Demo transfer records are missing.');
        $this->assertGreaterThan(0, AssetDisposal::count(), 'Demo disposal records are missing.');
        $this->assertGreaterThan(0, Notification::count(), 'Demo notification records are missing.');
        $this->assertGreaterThan(0, AssetLifecycleLog::count(), 'Demo lifecycle logs are missing.');
    }

    public function test_demo_data_exercises_every_alert_state(): void
    {
        $this->seedDemo();

        $metrics = app(ReportService::class)->executiveMetrics();

        $this->assertGreaterThan(0, $metrics['warranty_alerts'], 'The demo set should trigger the warranty banner.');
        $this->assertGreaterThan(0, $metrics['under_maintenance'], 'The demo set should show assets under maintenance.');
        $this->assertGreaterThan(0, $metrics['overdue_transfers'], 'The demo set should show an overdue transfer.');
        $this->assertGreaterThan(0, $metrics['disposed_assets'], 'The demo set should show a disposal.');

        $report = app(ReportService::class)->maintenanceReport();
        $this->assertGreaterThan(0, $report['summary']['overdue'], 'The demo set should show an overdue job.');
        $this->assertGreaterThan(0, $report['summary']['total_cost'], 'The demo set should show maintenance cost.');

        $warranties = app(ReportService::class)->warrantyReport();
        $this->assertGreaterThan(0, $warranties['summary']['expiring_soon']);
        $this->assertGreaterThan(0, $warranties['summary']['expired']);
    }

    public function test_demo_seeder_leaves_unread_notifications_for_the_header_badge(): void
    {
        $this->seedDemo();

        $this->assertGreaterThan(0, Notification::where('is_read', false)->count());
    }

    public function test_demo_seeder_is_safe_to_run_twice(): void
    {
        $this->seedDemo();

        $counts = [
            MaintenanceRequest::count(),
            Warranty::count(),
            AssetTransfer::count(),
            AssetDisposal::count(),
            Notification::count(),
        ];

        $this->seed(CommercialDemoSeeder::class);

        $this->assertSame($counts, [
            MaintenanceRequest::count(),
            Warranty::count(),
            AssetTransfer::count(),
            AssetDisposal::count(),
            Notification::count(),
        ], 'Re-running the demo seeder must not duplicate records.');
    }

    public function test_demo_seeder_stops_cleanly_when_there_are_no_assets(): void
    {
        $this->seed(UserSeeder::class);

        $this->seed(CommercialDemoSeeder::class);

        $this->assertSame(0, MaintenanceRequest::count());
    }

    public function test_demo_seeder_stops_cleanly_when_there_are_no_users(): void
    {
        $this->seed(AssetCategorySeeder::class);
        $this->seed(AssetLocationSeeder::class);
        $this->seed(AssetSeeder::class);

        User::query()->delete();

        $this->seed(CommercialDemoSeeder::class);

        $this->assertSame(0, MaintenanceRequest::count());
    }
}
