<?php

namespace Tests\Feature;

use App\Support\AssetLifecycleStatus;
use Tests\TestCase;

class CommercialLifecycleStatusTest extends TestCase
{
    public function test_asset_lifecycle_status_constants_are_available(): void
    {
        $this->assertNotEmpty(AssetLifecycleStatus::MAINTENANCE_STATUSES);
        $this->assertNotEmpty(AssetLifecycleStatus::WARRANTY_STATUSES);
        $this->assertNotEmpty(AssetLifecycleStatus::TRANSFER_STATUSES);
        $this->assertNotEmpty(AssetLifecycleStatus::DISPOSAL_STATUSES);
        $this->assertNotEmpty(AssetLifecycleStatus::LIFECYCLE_EVENTS);

        $this->assertContains('OPEN', array_keys(AssetLifecycleStatus::MAINTENANCE_STATUSES));
        $this->assertContains('ACTIVE', array_keys(AssetLifecycleStatus::WARRANTY_STATUSES));
        $this->assertContains('REQUESTED', array_keys(AssetLifecycleStatus::TRANSFER_STATUSES));
        $this->assertContains('REQUESTED', array_keys(AssetLifecycleStatus::DISPOSAL_STATUSES));
        $this->assertContains('ASSET_CREATED', array_keys(AssetLifecycleStatus::LIFECYCLE_EVENTS));
    }
}
