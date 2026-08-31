<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetDisposal;
use App\Models\AssetLocation;
use App\Models\AssetTransfer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Who can reach and act on the commercial modules. Admin sees and approves
 * everything, a manager runs the day to day, an employee sees none of it.
 */
class CommercialPermissionMatrixTest extends TestCase
{
    use RefreshDatabase;

    /** Routes every commercial module exposes, and the permission that guards them. */
    private const GUARDED_ROUTES = [
        'maintenance-requests.index' => 'maintenance.view',
        'warranties.index' => 'maintenance.view',
        'transfers.index' => 'transfers.view',
        'disposals.index' => 'disposals.view',
        'notifications.index' => 'notifications.view',
        'reports.index' => 'reports.view',
        'reports.maintenance' => 'reports.view',
        'reports.warranty' => 'reports.view',
        'reports.movement' => 'reports.view',
    ];

    private function user(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function asset(string $code): Asset
    {
        $location = AssetLocation::firstOrCreate(['location_name' => 'Head Office']);
        $category = AssetCategory::firstOrCreate(['category_name' => 'IT Equipment', 'asset_type' => 'COMPUTER']);

        return Asset::create([
            'asset_code' => $code,
            'asset_name' => 'Permission Laptop',
            'status' => 'IN_STORAGE',
            'location_id' => $location->id,
            'category_id' => $category->id,
            'added_date' => today(),
        ]);
    }

    // =========================
    //     ADMIN
    // =========================

    public function test_admin_reaches_every_commercial_module(): void
    {
        $admin = $this->user('super_admin');

        foreach (array_keys(self::GUARDED_ROUTES) as $route) {
            $this->actingAs($admin)->get(route($route))->assertOk("Admin should reach {$route}.");
        }
    }

    public function test_admin_can_approve_a_transfer(): void
    {
        $admin = $this->user('super_admin');
        $asset = $this->asset('PM-100');

        $transfer = AssetTransfer::create([
            'asset_id' => $asset->id,
            'requested_by' => $admin->id,
            'status' => 'REQUESTED',
            'requested_at' => today(),
        ]);

        $this->actingAs($admin)->post(route('transfers.approve', $transfer))->assertRedirect();

        $this->assertSame('APPROVED', $transfer->fresh()->status);
    }

    // =========================
    //     MANAGER
    // =========================

    public function test_manager_reaches_every_commercial_module(): void
    {
        $manager = $this->user('management');

        foreach (array_keys(self::GUARDED_ROUTES) as $route) {
            $this->actingAs($manager)->get(route($route))->assertOk("A manager should reach {$route}.");
        }
    }

    public function test_manager_can_approve_transfers_and_disposals(): void
    {
        $manager = $this->user('management');
        $asset = $this->asset('PM-101');

        $transfer = AssetTransfer::create([
            'asset_id' => $asset->id,
            'requested_by' => $manager->id,
            'status' => 'REQUESTED',
            'requested_at' => today(),
        ]);

        $disposal = AssetDisposal::create([
            'asset_id' => $asset->id,
            'requested_by' => $manager->id,
            'status' => 'REQUESTED',
            'reason' => 'End of life',
            'requested_at' => today(),
        ]);

        $this->actingAs($manager)->post(route('transfers.approve', $transfer))->assertRedirect();
        $this->actingAs($manager)->post(route('disposals.approve', $disposal))->assertRedirect();

        $this->assertSame('APPROVED', $transfer->fresh()->status);
        $this->assertSame('DISPOSED', $disposal->fresh()->status);
    }

    public function test_manager_can_export_reports(): void
    {
        $manager = $this->user('management');

        $this->actingAs($manager)
            ->get(route('reports.export', 'warranty') . '?format=csv')
            ->assertOk();
    }

    // =========================
    //     DEPARTMENT HEAD
    // =========================

    public function test_department_head_sees_maintenance_and_reports_but_not_movement_modules(): void
    {
        $head = $this->user('department_head');

        $this->actingAs($head)->get(route('maintenance-requests.index'))->assertOk();
        $this->actingAs($head)->get(route('reports.index'))->assertOk();

        $this->actingAs($head)->get(route('transfers.index'))->assertForbidden();
        $this->actingAs($head)->get(route('disposals.index'))->assertForbidden();
        $this->actingAs($head)->get(route('notifications.index'))->assertForbidden();
    }

    // =========================
    //     EMPLOYEE
    // =========================

    public function test_employee_is_locked_out_of_every_commercial_module(): void
    {
        $employee = $this->user('employee');

        foreach (array_keys(self::GUARDED_ROUTES) as $route) {
            $this->actingAs($employee)->get(route($route))->assertForbidden("An employee must not reach {$route}.");
        }
    }

    public function test_employee_cannot_approve_or_reject_a_transfer(): void
    {
        $employee = $this->user('employee');
        $manager = $this->user('management');
        $asset = $this->asset('PM-102');

        $transfer = AssetTransfer::create([
            'asset_id' => $asset->id,
            'requested_by' => $manager->id,
            'status' => 'REQUESTED',
            'requested_at' => today(),
        ]);

        $this->actingAs($employee)->post(route('transfers.approve', $transfer))->assertForbidden();
        $this->actingAs($employee)->post(route('transfers.reject', $transfer))->assertForbidden();

        $this->assertSame('REQUESTED', $transfer->fresh()->status);
    }

    public function test_employee_cannot_export_a_report(): void
    {
        $employee = $this->user('employee');

        $this->actingAs($employee)
            ->get(route('reports.export', 'maintenance') . '?format=csv')
            ->assertForbidden();
    }

    // =========================
    //     GUESTS AND ROUTE GUARDS
    // =========================

    public function test_guests_are_redirected_to_login(): void
    {
        foreach (array_keys(self::GUARDED_ROUTES) as $route) {
            $this->get(route($route))->assertRedirect(route('login'));
        }
    }

    public function test_every_commercial_route_declares_its_permission_middleware(): void
    {
        foreach (self::GUARDED_ROUTES as $name => $permission) {
            $middleware = \Illuminate\Support\Facades\Route::getRoutes()->getByName($name)->middleware();

            $this->assertContains(
                'permission:' . $permission,
                $middleware,
                "Route {$name} should be guarded by {$permission}.",
            );
        }
    }

    public function test_approval_routes_require_the_manage_permission(): void
    {
        $expected = [
            'transfers.approve' => 'permission:transfers.manage',
            'transfers.reject' => 'permission:transfers.manage',
            'disposals.approve' => 'permission:disposals.manage',
            'disposals.reject' => 'permission:disposals.manage',
        ];

        foreach ($expected as $name => $middleware) {
            $this->assertContains(
                $middleware,
                \Illuminate\Support\Facades\Route::getRoutes()->getByName($name)->middleware(),
                "Route {$name} should be guarded by {$middleware}.",
            );
        }
    }

    public function test_role_catalog_grants_managers_the_full_commercial_permission_set(): void
    {
        foreach (['maintenance', 'transfers', 'disposals', 'notifications'] as $module) {
            foreach (['view', 'create', 'update', 'delete', 'manage'] as $action) {
                $this->assertContains(
                    $module . '.' . $action,
                    User::PERMISSIONS['management'],
                    "A manager should hold {$module}.{$action}.",
                );
            }
        }
    }

    public function test_employees_hold_no_commercial_permissions(): void
    {
        foreach (User::PERMISSIONS['employee'] as $permission) {
            $this->assertStringStartsNotWith('transfers.', $permission);
            $this->assertStringStartsNotWith('disposals.', $permission);
            $this->assertStringStartsNotWith('notifications.', $permission);
            $this->assertStringStartsNotWith('maintenance.', $permission);
        }
    }
}
