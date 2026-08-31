<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLifecycleLog;
use App\Models\AssetLocation;
use App\Models\AssetTransfer;
use App\Models\MaintenanceRequest;
use App\Models\Notification;
use App\Models\User;
use App\Models\Warranty;
use App\Services\NotificationService;
use App\Services\ReminderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * End-to-end coverage for the commercial lifecycle flows: maintenance creation,
 * warranty expiry, notification delivery, and the audit trail behind them.
 */
class CommercialLifecycleFunctionalTest extends TestCase
{
    use RefreshDatabase;

    private function asset(string $code = 'FN-100'): Asset
    {
        $location = AssetLocation::firstOrCreate(['location_name' => 'Head Office']);
        $category = AssetCategory::firstOrCreate([
            'category_name' => 'IT Equipment',
            'asset_type' => 'COMPUTER',
        ]);

        return Asset::create([
            'asset_code' => $code,
            'asset_name' => 'Test Laptop',
            'status' => 'IN_STORAGE',
            'location_id' => $location->id,
            'category_id' => $category->id,
            'added_date' => today(),
        ]);
    }

    private function manager(string ...$permissions): User
    {
        $user = User::factory()->create();

        foreach ($permissions as $permission) {
            $user->givePermissionTo($permission);
        }

        return $user;
    }

    // =========================
    //     MAINTENANCE CREATION
    // =========================

    public function test_maintenance_request_can_be_created_through_the_form(): void
    {
        $user = $this->manager('maintenance.view');
        $asset = $this->asset('MT-100');

        $this->actingAs($user)
            ->post(route('maintenance-requests.store'), [
                'asset_id' => $asset->id,
                'title' => 'Replace failing SSD',
                'maintenance_type' => 'CORRECTIVE',
                'priority' => 'HIGH',
                'description' => 'Drive reports SMART errors.',
                'vendor_name' => 'TechCare Ltd',
                'scheduled_at' => today()->addWeek()->toDateString(),
            ])
            ->assertRedirect();

        $request = MaintenanceRequest::where('asset_id', $asset->id)->first();

        $this->assertNotNull($request, 'The maintenance request was not persisted.');
        $this->assertSame('Replace failing SSD', $request->title);
        $this->assertSame('OPEN', $request->status);
        $this->assertSame($user->id, $request->requested_by);
        $this->assertNotNull($request->requested_at, 'requested_at must be filled in even when the form omits it.');
    }

    public function test_maintenance_request_requires_an_asset_and_a_title(): void
    {
        $user = $this->manager('maintenance.view');

        $this->actingAs($user)
            ->post(route('maintenance-requests.store'), ['priority' => 'HIGH'])
            ->assertSessionHasErrors(['asset_id', 'title']);

        $this->assertSame(0, MaintenanceRequest::count());
    }

    public function test_maintenance_list_and_detail_pages_load(): void
    {
        $user = $this->manager('maintenance.view');
        $asset = $this->asset('MT-101');

        $request = MaintenanceRequest::create([
            'asset_id' => $asset->id,
            'title' => 'Annual servicing',
            'priority' => 'MEDIUM',
            'status' => 'OPEN',
            'requested_at' => today(),
        ]);

        $this->actingAs($user)->get(route('maintenance-requests.index'))->assertOk()->assertSee('Maintenance Requests');
        $this->actingAs($user)->get(route('maintenance-requests.show', $request))->assertOk()->assertSee('Annual servicing');
    }

    // =========================
    //     WARRANTY EXPIRY
    // =========================

    public function test_warranty_status_is_derived_from_the_expiry_date(): void
    {
        $this->assertSame('ACTIVE', Warranty::deriveStatus(today()->addMonths(6)));
        $this->assertSame('EXPIRING_SOON', Warranty::deriveStatus(today()->addDays(10)));
        $this->assertSame('EXPIRED', Warranty::deriveStatus(today()->subDay()));
    }

    public function test_creating_a_warranty_flags_near_expiry_cover_and_logs_it(): void
    {
        $user = $this->manager('maintenance.view');
        $asset = $this->asset('WR-100');

        $this->actingAs($user)
            ->post(route('warranties.store'), [
                'asset_id' => $asset->id,
                'vendor_name' => 'HP Care Pack',
                'warranty_type' => 'EXTENDED',
                'start_date' => today()->subYear()->toDateString(),
                'end_date' => today()->addDays(15)->toDateString(),
            ])
            ->assertRedirect();

        $warranty = Warranty::where('asset_id', $asset->id)->first();

        $this->assertNotNull($warranty);
        $this->assertSame('EXPIRING_SOON', $warranty->status);
        $this->assertDatabaseHas('asset_lifecycle_logs', [
            'asset_id' => $asset->id,
            'event_type' => 'WARRANTY_REGISTERED',
        ]);
    }

    public function test_expiry_sweep_marks_lapsed_warranties_and_logs_the_lapse(): void
    {
        Mail::fake();

        $manager = $this->manager('maintenance.manage');
        $asset = $this->asset('WR-101');

        $warranty = Warranty::create([
            'asset_id' => $asset->id,
            'vendor_name' => 'Lenovo Premier',
            'start_date' => today()->subYears(2),
            'end_date' => today()->subDays(5),
            'status' => 'ACTIVE',
        ]);

        $result = app(ReminderService::class)->runWarrantyCheck();

        $this->assertSame(1, $result['expired']);
        $this->assertSame('EXPIRED', $warranty->fresh()->status);

        $this->assertDatabaseHas('asset_lifecycle_logs', [
            'asset_id' => $asset->id,
            'event_type' => 'WARRANTY_EXPIRED',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $manager->id,
            'type' => 'warranty_expired',
        ]);
    }

    public function test_expiry_sweep_warns_about_cover_inside_the_warning_window(): void
    {
        Mail::fake();

        $manager = $this->manager('maintenance.manage');
        $asset = $this->asset('WR-102');

        Warranty::create([
            'asset_id' => $asset->id,
            'vendor_name' => 'Canon Service',
            'start_date' => today()->subYear(),
            'end_date' => today()->addDays(12),
            'status' => 'ACTIVE',
        ]);

        $result = app(ReminderService::class)->runWarrantyCheck();

        $this->assertSame(1, $result['expiring']);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $manager->id,
            'type' => 'warranty_expiring',
        ]);
    }

    public function test_expiry_sweep_does_not_repeat_the_same_alert_on_the_same_day(): void
    {
        Mail::fake();

        $manager = $this->manager('maintenance.manage');
        $asset = $this->asset('WR-103');

        Warranty::create([
            'asset_id' => $asset->id,
            'vendor_name' => 'Asus Support',
            'start_date' => today()->subYear(),
            'end_date' => today()->addDays(7),
            'status' => 'ACTIVE',
        ]);

        $reminders = app(ReminderService::class);
        $reminders->runWarrantyCheck();
        $reminders->runWarrantyCheck();

        $this->assertSame(1, Notification::where('user_id', $manager->id)->where('type', 'warranty_expiring')->count());
    }

    // =========================
    //     MAINTENANCE REMINDERS
    // =========================

    public function test_overdue_maintenance_raises_a_reminder(): void
    {
        Mail::fake();

        $manager = $this->manager('maintenance.manage');
        $asset = $this->asset('MT-102');

        MaintenanceRequest::create([
            'asset_id' => $asset->id,
            'title' => 'Battery replacement',
            'priority' => 'HIGH',
            'status' => 'IN_PROGRESS',
            'requested_at' => today()->subDays(20),
            'scheduled_at' => today()->subDays(10),
        ]);

        $result = app(ReminderService::class)->runMaintenanceCheck();

        $this->assertSame(1, $result['overdue']);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $manager->id,
            'type' => 'maintenance_overdue',
        ]);
    }

    public function test_reminder_commands_run_and_report_their_work(): void
    {
        Mail::fake();

        $this->manager('maintenance.manage');

        $this->artisan('reminders:warranty-expiry')->assertSuccessful();
        $this->artisan('reminders:maintenance-due')->assertSuccessful();
    }

    // =========================
    //     NOTIFICATION SENDING
    // =========================

    public function test_notification_service_queues_an_email_copy_when_asked(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        app(NotificationService::class)->send(
            $user,
            'warranty_expiring',
            'Warranty expiring',
            'Cover ends in 12 days.',
            ['warranty_id' => 1],
            true,
        );

        $this->assertDatabaseHas('notifications', ['user_id' => $user->id, 'type' => 'warranty_expiring']);
        Mail::assertQueued(\App\Mail\LifecycleNotificationMail::class);
    }

    public function test_notification_service_records_in_app_only_by_default(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        app(NotificationService::class)->send($user, 'maintenance_due', 'Maintenance due', 'Scheduled tomorrow.');

        Mail::assertNothingQueued();
    }

    public function test_notification_can_be_built_from_a_stored_template(): void
    {
        Mail::fake();

        $this->seed(\Database\Seeders\NotificationTemplateSeeder::class);

        $user = User::factory()->create();

        $notification = app(NotificationService::class)->sendFromTemplate($user, 'transfer_approved', [
            'asset_code' => 'AS-9001',
            'approved_by' => 'Ada Manager',
        ]);

        $this->assertNotNull($notification);
        $this->assertStringContainsString('AS-9001', $notification->title);
        $this->assertStringContainsString('Ada Manager', $notification->message);
    }

    public function test_all_notifications_can_be_marked_read_at_once(): void
    {
        $user = $this->manager('notifications.view');

        $service = app(NotificationService::class);
        $service->send($user, 'maintenance_due', 'One', 'First');
        $service->send($user, 'maintenance_due', 'Two', 'Second');

        $this->assertSame(2, $service->unreadCount($user));

        $this->actingAs($user)->post(route('notifications.read-all'))->assertRedirect();

        $this->assertSame(0, $service->unreadCount($user->fresh()));
    }

    public function test_a_user_cannot_mark_someone_elses_notification_as_read(): void
    {
        $owner = User::factory()->create();
        $intruder = $this->manager('notifications.view');

        $notification = app(NotificationService::class)->send($owner, 'maintenance_due', 'Private', 'Not yours');

        $this->actingAs($intruder)
            ->post(route('notifications.read', $notification))
            ->assertForbidden();

        $this->assertFalse((bool) $notification->fresh()->is_read);
    }

    public function test_rejecting_a_transfer_notifies_the_requester(): void
    {
        $requester = User::factory()->create();
        $approver = $this->manager('transfers.manage');
        $asset = $this->asset('TR-200');

        $transfer = AssetTransfer::create([
            'asset_id' => $asset->id,
            'requested_by' => $requester->id,
            'status' => 'REQUESTED',
            'reason' => 'Desk move',
            'requested_at' => today(),
        ]);

        $this->actingAs($approver)
            ->post(route('transfers.reject', $transfer), ['notes' => 'Asset is already allocated.'])
            ->assertRedirect();

        $this->assertSame('REJECTED', $transfer->fresh()->status);
        $this->assertSame($approver->id, $transfer->fresh()->approved_by);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $requester->id,
            'type' => 'transfer_rejected',
        ]);
    }

    // =========================
    //     LIFECYCLE LOGGING
    // =========================

    public function test_every_lifecycle_stage_writes_an_audit_log(): void
    {
        $manager = $this->manager('maintenance.view', 'transfers.view', 'transfers.manage', 'disposals.view');
        $asset = $this->asset('LC-100');

        $this->actingAs($manager)->post(route('maintenance-requests.store'), [
            'asset_id' => $asset->id,
            'title' => 'Fan replacement',
            'priority' => 'MEDIUM',
        ]);

        $this->actingAs($manager)->post(route('transfers.store'), [
            'asset_id' => $asset->id,
            'reason' => 'Relocation',
        ]);

        $transfer = AssetTransfer::where('asset_id', $asset->id)->firstOrFail();
        $this->actingAs($manager)->post(route('transfers.approve', $transfer));

        $events = AssetLifecycleLog::where('asset_id', $asset->id)->pluck('event_type');

        $this->assertContains('MAINTENANCE_REQUESTED', $events->all());
        $this->assertContains('TRANSFER_REQUESTED', $events->all());
        $this->assertContains('TRANSFER_COMPLETED', $events->all());
    }

    public function test_lifecycle_log_records_who_acted_and_what_changed(): void
    {
        $manager = $this->manager('transfers.view', 'transfers.manage');
        $asset = $this->asset('LC-101');

        $transfer = AssetTransfer::create([
            'asset_id' => $asset->id,
            'requested_by' => $manager->id,
            'status' => 'REQUESTED',
            'reason' => 'Branch setup',
            'requested_at' => today(),
        ]);

        $this->actingAs($manager)->post(route('transfers.approve', $transfer), ['notes' => 'Approved by facilities.']);

        $log = AssetLifecycleLog::where('asset_id', $asset->id)->where('event_type', 'TRANSFER_COMPLETED')->firstOrFail();

        $this->assertSame($manager->id, $log->user_id);
        $this->assertSame('REQUESTED', $log->old_values['status']);
        $this->assertSame('APPROVED', $log->new_values['status']);
        $this->assertSame($manager->name, $log->new_values['approved_by']);
        $this->assertNotNull($log->event_at);
    }

    public function test_lifecycle_timeline_can_be_filtered_and_exported(): void
    {
        $user = $this->manager('assets.view');
        $asset = $this->asset('LC-102');

        $asset->lifecycleLogs()->createMany([
            ['event_type' => 'ASSET_CREATED', 'description' => 'Added to the register', 'event_at' => now()->subDays(3)],
            ['event_type' => 'MAINTENANCE_REQUESTED', 'description' => 'Service booked', 'event_at' => now()->subDay()],
        ]);

        $this->actingAs($user)
            ->get(route('assets.lifecycle', $asset) . '?event_type=MAINTENANCE_REQUESTED')
            ->assertOk()
            ->assertSee('Service booked')
            ->assertDontSee('Added to the register');

        $response = $this->actingAs($user)->get(route('assets.lifecycle.export', $asset));
        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $csv = $response->streamedContent();
        $this->assertStringContainsString('Service booked', $csv);
        $this->assertStringContainsString('Added to the register', $csv);
    }

    public function test_lifecycle_page_shows_the_movement_history_with_its_approver(): void
    {
        $manager = $this->manager('assets.view', 'transfers.view');
        $asset = $this->asset('LC-103');
        $destination = AssetLocation::firstOrCreate(['location_name' => 'Branch Office']);

        AssetTransfer::create([
            'asset_id' => $asset->id,
            'to_location_id' => $destination->id,
            'requested_by' => $manager->id,
            'approved_by' => $manager->id,
            'status' => 'APPROVED',
            'reason' => 'Branch office setup',
            'notes' => 'Collected by courier.',
            'requested_at' => today()->subWeek(),
            'transferred_at' => today()->subDays(4),
        ]);

        $this->actingAs($manager)
            ->get(route('assets.lifecycle', $asset))
            ->assertOk()
            ->assertSee('Branch office setup')
            ->assertSee('Collected by courier.')
            ->assertSee($manager->name);
    }
}
