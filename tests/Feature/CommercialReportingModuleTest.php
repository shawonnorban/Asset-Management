<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommercialReportingModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_index_page_loads_for_users_with_permission(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('reports.view');

        $this->actingAs($user)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Executive asset report');
    }

    public function test_report_summary_has_core_business_metrics(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('reports.view');

        $this->actingAs($user)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Active assets')
            ->assertSee('Under maintenance')
            ->assertSee('Warranty alerts');
    }
}
