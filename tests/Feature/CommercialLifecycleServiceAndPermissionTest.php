<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\AssetLifecycleService;
use App\Services\NotificationService;
use App\Services\ReminderService;
use Tests\TestCase;

class CommercialLifecycleServiceAndPermissionTest extends TestCase
{
    public function test_lifecycle_services_are_available(): void
    {
        $this->assertTrue(class_exists(AssetLifecycleService::class));
        $this->assertTrue(class_exists(NotificationService::class));
        $this->assertTrue(class_exists(ReminderService::class));
    }

    public function test_commercial_permissions_are_registered_in_catalog_and_roles(): void
    {
        $this->assertContains('view', User::PERMISSION_CATALOG['maintenance']);
        $this->assertContains('view', User::PERMISSION_CATALOG['transfers']);
        $this->assertContains('view', User::PERMISSION_CATALOG['disposals']);
        $this->assertContains('view', User::PERMISSION_CATALOG['notifications']);

        $this->assertContains('maintenance.view', User::PERMISSIONS['management']);
        $this->assertContains('transfers.view', User::PERMISSIONS['management']);
        $this->assertContains('disposals.view', User::PERMISSIONS['management']);
        $this->assertContains('notifications.view', User::PERMISSIONS['management']);
    }
}
