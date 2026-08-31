<?php

namespace Tests\Feature;

use App\Support\AssetLifecycleStatus;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CommercialWarrantyModuleTest extends TestCase
{
    public function test_warranty_routes_are_registered(): void
    {
        $this->assertTrue(Route::has('warranties.index'));
        $this->assertTrue(Route::has('warranties.create'));
        $this->assertTrue(Route::has('warranties.store'));
        $this->assertTrue(Route::has('warranties.show'));
    }

    public function test_warranty_statuses_cover_expiry_alert_states(): void
    {
        $this->assertArrayHasKey('ACTIVE', AssetLifecycleStatus::WARRANTY_STATUSES);
        $this->assertArrayHasKey('EXPIRING_SOON', AssetLifecycleStatus::WARRANTY_STATUSES);
        $this->assertArrayHasKey('EXPIRED', AssetLifecycleStatus::WARRANTY_STATUSES);
    }
}
