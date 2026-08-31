<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CommercialLifecycleDataModelTest extends TestCase
{
    public function test_core_commercial_lifecycle_tables_exist(): void
    {
        $this->assertTrue(Schema::hasTable('maintenance_requests'));
        $this->assertTrue(Schema::hasTable('warranties'));
        $this->assertTrue(Schema::hasTable('asset_transfers'));
        $this->assertTrue(Schema::hasTable('asset_disposals'));
        $this->assertTrue(Schema::hasTable('asset_lifecycle_logs'));
        $this->assertTrue(Schema::hasTable('notifications'));
        $this->assertTrue(Schema::hasTable('notification_templates'));
    }
}
