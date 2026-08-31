<?php

namespace Tests\Feature;

use App\Support\AssetLifecycleStatus;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CommercialMaintenanceRequestModuleTest extends TestCase
{
    public function test_commercial_maintenance_request_routes_are_registered(): void
    {
        $this->assertTrue(Route::has('maintenance-requests.index'));
        $this->assertTrue(Route::has('maintenance-requests.create'));
        $this->assertTrue(Route::has('maintenance-requests.store'));
        $this->assertTrue(Route::has('maintenance-requests.show'));
    }

    public function test_commercial_maintenance_statuses_are_available_for_work_orders(): void
    {
        $this->assertArrayHasKey('OPEN', AssetLifecycleStatus::MAINTENANCE_STATUSES);
        $this->assertArrayHasKey('IN_PROGRESS', AssetLifecycleStatus::MAINTENANCE_STATUSES);
        $this->assertArrayHasKey('COMPLETED', AssetLifecycleStatus::MAINTENANCE_STATUSES);
    }
}
