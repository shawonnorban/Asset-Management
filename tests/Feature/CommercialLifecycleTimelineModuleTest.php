<?php

namespace Tests\Feature;

use App\Support\AssetLifecycleStatus;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CommercialLifecycleTimelineModuleTest extends TestCase
{
    public function test_asset_lifecycle_route_is_registered(): void
    {
        $this->assertTrue(Route::has('assets.lifecycle'));
    }

    public function test_lifecycle_event_types_cover_core_asset_flow(): void
    {
        $this->assertArrayHasKey('ASSET_CREATED', AssetLifecycleStatus::LIFECYCLE_EVENTS);
        $this->assertArrayHasKey('MAINTENANCE_REQUESTED', AssetLifecycleStatus::LIFECYCLE_EVENTS);
        $this->assertArrayHasKey('TRANSFER_COMPLETED', AssetLifecycleStatus::LIFECYCLE_EVENTS);
        $this->assertArrayHasKey('DISPOSAL_COMPLETED', AssetLifecycleStatus::LIFECYCLE_EVENTS);
    }
}
