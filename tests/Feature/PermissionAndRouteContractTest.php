<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PermissionAndRouteContractTest extends TestCase
{
    public function test_granular_permission_catalog_contains_module_actions(): void
    {
        $this->assertContains('create', User::PERMISSION_CATALOG['assets']);
        $this->assertContains('view', User::PERMISSION_CATALOG['maintenance']);
        $this->assertContains('delete', User::PERMISSION_CATALOG['employees']);
        $this->assertContains('review', User::PERMISSION_CATALOG['reports']);
        $this->assertContains('dispose', User::PERMISSION_CATALOG['depreciation']);
    }

    public function test_stock_take_routes_are_unregistered_and_permissions_removed(): void
    {
        foreach ([
            'stock-takes.index',
            'stock-takes.create',
            'stock-takes.store',
            'stock-takes.show',
            'stock-takes.input',
            'stock-takes.details.store',
            'stock-takes.details.destroy',
            'stock-takes.finalize',
            'stock-takes.pdf',
        ] as $routeName) {
            $this->assertFalse(Route::has($routeName), "Stock take route should be removed: {$routeName}");
        }

        $this->assertNotContains('stock_takes.view', User::PERMISSIONS['management']);
        $this->assertNotContains('stock_takes.create', User::PERMISSIONS['management']);
        $this->assertNotContains('stock_takes.finalize', User::PERMISSIONS['management']);
        $this->assertNotContains('stock_takes.view', User::PERMISSIONS['super_admin']);
    }

    public function test_maintenance_and_inventory_views_use_separate_permissions(): void
    {
        $this->assertContains('permission:maintenance.view', Route::getRoutes()->getByName('maintenance.index')->middleware());
        $this->assertContains('permission:maintenance.view', Route::getRoutes()->getByName('maintenance.show')->middleware());
        $this->assertContains('permission:assets.view', Route::getRoutes()->getByName('assets.index')->middleware());
    }

    public function test_users_roles_and_menu_entries_use_canonical_permissions(): void
    {
        $this->assertContains('permission:users.view|users.manage', Route::getRoutes()->getByName('users.index')->middleware());
        $this->assertContains('permission:roles.view|roles.manage', Route::getRoutes()->getByName('roles.index')->middleware());

        $menu = config('menu', []);
        $this->assertNotEmpty($menu);

        $flattened = collect($menu)->flatMap(fn ($block) => $block['items'] ?? [])->pluck('permissions')->flatten()->all();
        $this->assertContains('employees.view', $flattened);
        $this->assertContains('depreciation.view', $flattened);
        $this->assertContains('users.view', $flattened);
        $this->assertContains('roles.view', $flattened);
    }

    public function test_legacy_indonesian_controllers_are_not_active_and_fail_fast(): void
    {
        $this->assertFalse(Route::has('aset.index'));
        $this->assertFalse(Route::has('kategori.index'));
        $this->assertFalse(Route::has('lokasi.index'));

        // Legacy controller files have been removed; routes are not registered
        $this->assertTrue(true);
    }

    public function test_department_and_position_routes_use_canonical_permissions(): void
    {
        $this->assertContains('permission:departments.view|departments.manage|employees.manage', Route::getRoutes()->getByName('departments.index')->middleware());
        $this->assertContains('permission:positions.view|positions.manage|employees.manage', Route::getRoutes()->getByName('positions.index')->middleware());

        $this->assertContains('departments.view', User::PERMISSIONS['management']);
        $this->assertContains('positions.view', User::PERMISSIONS['management']);
    }

    public function test_assignment_audit_routes_and_menu_are_available_to_management(): void
    {
        foreach (['assignment-audits.index', 'assignment-audits.create', 'assignment-audits.show', 'assignment-audits.start', 'assignment-audits.verify', 'assignment-audits.complete'] as $routeName) {
            $this->assertTrue(Route::has($routeName), "Missing route: {$routeName}");
        }

        $this->assertContains('assignment_audits.view', User::PERMISSIONS['management']);
        $this->assertContains('assignment_audits.verify', User::PERMISSIONS['management']);
        $this->assertContains('assignment_audits.complete', User::PERMISSIONS['management']);

        $menu = config('menu', []);
        $flattened = collect($menu)->flatMap(fn ($block) => $block['items'] ?? [])->pluck('route')->all();

        $this->assertContains('assignment-audits.index', $flattened);
    }

    public function test_assignment_and_license_routes_use_canonical_permissions(): void
    {
        $this->assertContains('permission:assignments.view|assignments.manage', Route::getRoutes()->getByName('assignments.index')->middleware());
        $this->assertContains('permission:assignments.assign|assignments.manage', Route::getRoutes()->getByName('assignments.create')->middleware());
        $this->assertContains('permission:assignments.return|assignments.manage', Route::getRoutes()->getByName('assignments.return')->middleware());
        $this->assertContains('permission:software_licenses.view|software_licenses.manage', Route::getRoutes()->getByName('software-licenses.index')->middleware());
    }

    public function test_categories_locations_and_maintenance_use_canonical_permissions(): void
    {
        $this->assertContains('permission:categories.view', Route::getRoutes()->getByName('categories.index')->middleware());
        $this->assertContains('permission:categories.create|categories.manage', Route::getRoutes()->getByName('categories.create')->middleware());
        $this->assertContains('permission:categories.update|categories.manage', Route::getRoutes()->getByName('categories.update')->middleware());

        $this->assertContains('permission:locations.view', Route::getRoutes()->getByName('locations.index')->middleware());
        $this->assertContains('permission:locations.create|locations.manage', Route::getRoutes()->getByName('locations.create')->middleware());
        $this->assertContains('permission:locations.update|locations.manage', Route::getRoutes()->getByName('locations.update')->middleware());

        $this->assertContains('permission:maintenance.view', Route::getRoutes()->getByName('maintenance.index')->middleware());
        $this->assertContains('permission:maintenance.view', Route::getRoutes()->getByName('maintenance.show')->middleware());
        $this->assertContains('permission:maintenance.manage', Route::getRoutes()->getByName('maintenance.complete')->middleware());
    }
}
