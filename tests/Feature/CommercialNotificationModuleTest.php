<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommercialNotificationModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifications_index_page_loads_for_users_with_permission(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('notifications.view');

        $this->actingAs($user)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Notifications');
    }

    public function test_notification_service_can_create_and_mark_as_read(): void
    {
        $user = User::factory()->create();
        $notification = app(\App\Services\NotificationService::class)->send(
            $user,
            'maintenance_due',
            'Maintenance due',
            'A maintenance task is overdue.'
        );

        $this->assertFalse($notification->is_read);

        $updated = app(\App\Services\NotificationService::class)->markAsRead($notification);

        $this->assertTrue($updated->fresh()->is_read);
    }
}
