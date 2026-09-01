<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * The dashboard is behind auth and requires a valid user with dashboard access.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('dashboard.view');

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
    }
}
