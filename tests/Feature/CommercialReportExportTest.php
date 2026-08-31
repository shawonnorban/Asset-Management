<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetDisposal;
use App\Models\AssetLocation;
use App\Models\AssetTransfer;
use App\Models\MaintenanceRequest;
use App\Models\User;
use App\Models\Warranty;
use App\Services\ReportService;
use Inertia\Testing\AssertableInertia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Reporting pages, the figures behind them, and the three export formats.
 */
class CommercialReportExportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->user->givePermissionTo('reports.view');

        $location = AssetLocation::firstOrCreate(['location_name' => 'Head Office']);
        $branch = AssetLocation::firstOrCreate(['location_name' => 'Branch Office']);
        $category = AssetCategory::firstOrCreate(['category_name' => 'IT Equipment', 'asset_type' => 'COMPUTER']);

        $this->asset = Asset::create([
            'asset_code' => 'RP-100',
            'asset_name' => 'Reporting Laptop',
            'status' => 'IN_STORAGE',
            'location_id' => $location->id,
            'category_id' => $category->id,
            'added_date' => today(),
        ]);

        MaintenanceRequest::create([
            'asset_id' => $this->asset->id,
            'title' => 'Overdue fan repair',
            'priority' => 'HIGH',
            'status' => 'IN_PROGRESS',
            'requested_at' => today()->subDays(20),
            'scheduled_at' => today()->subDays(6),
            'estimated_cost' => 5000,
            'actual_cost' => 0,
            'vendor_name' => 'TechCare Ltd',
        ]);

        MaintenanceRequest::create([
            'asset_id' => $this->asset->id,
            'title' => 'Completed toner swap',
            'priority' => 'LOW',
            'status' => 'COMPLETED',
            'requested_at' => today()->subDays(40),
            'scheduled_at' => today()->subDays(38),
            'completed_at' => today()->subDays(37),
            'estimated_cost' => 3000,
            'actual_cost' => 3200,
            'vendor_name' => 'Nova Service',
        ]);

        Warranty::create([
            'asset_id' => $this->asset->id,
            'vendor_name' => 'HP Care Pack',
            'start_date' => today()->subYear(),
            'end_date' => today()->addDays(12),
            'status' => 'ACTIVE',
            'claim_status' => 'NOT_STARTED',
        ]);

        Warranty::create([
            'asset_id' => $this->asset->id,
            'vendor_name' => 'Lenovo Premier',
            'start_date' => today()->subYears(2),
            'end_date' => today()->subDays(20),
            'status' => 'EXPIRED',
            'claim_status' => 'CLAIMED',
        ]);

        AssetTransfer::create([
            'asset_id' => $this->asset->id,
            'to_location_id' => $branch->id,
            'requested_by' => $this->user->id,
            'approved_by' => $this->user->id,
            'status' => 'APPROVED',
            'reason' => 'Branch office setup',
            'requested_at' => today()->subDays(30),
            'transferred_at' => today()->subDays(27),
        ]);

        AssetTransfer::create([
            'asset_id' => $this->asset->id,
            'requested_by' => $this->user->id,
            'status' => 'REQUESTED',
            'reason' => 'Desk relocation',
            'requested_at' => today()->subDays(15),
        ]);

        AssetDisposal::create([
            'asset_id' => $this->asset->id,
            'requested_by' => $this->user->id,
            'approved_by' => $this->user->id,
            'status' => 'DISPOSED',
            'reason' => 'End of useful life',
            'method' => 'SALE',
            'value_recovered' => 7500,
            'requested_at' => today()->subDays(60),
            'disposed_at' => today()->subDays(55),
        ]);
    }

    // =========================
    //     6.4 DASHBOARD
    // =========================

    public function test_executive_dashboard_shows_the_headline_cards(): void
    {
        $this->actingAs($this->user)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('reports/index')
                ->where('title', 'Executive asset report')
                ->has('summary', 6)
                ->has('metrics')
                ->has('reportLinks', 3)
                ->where('summary.0.label', 'Active assets')
                ->where('summary.1.label', 'Under maintenance')
                ->where('summary.2.label', 'Warranty alerts')
                ->where('summary.3.label', 'Overdue transfers')
                ->where('summary.4.label', 'Disposal stats'));
    }

    public function test_executive_metrics_count_risk_correctly(): void
    {
        $metrics = app(ReportService::class)->executiveMetrics();

        $this->assertSame(1, $metrics['under_maintenance']);
        $this->assertSame(2, $metrics['warranty_alerts'], 'One expiring and one lapsed warranty should both count.');
        $this->assertSame(1, $metrics['overdue_transfers'], 'The 15 day old pending transfer is overdue.');
        $this->assertSame(1, $metrics['disposed_assets']);
        $this->assertEqualsWithDelta(7500.0, $metrics['value_recovered'], 0.001);
    }

    // =========================
    //     6.1 - 6.3 REPORTS
    // =========================

    public function test_maintenance_report_counts_open_overdue_and_cost(): void
    {
        $report = app(ReportService::class)->maintenanceReport();

        $this->assertSame(1, $report['summary']['open']);
        $this->assertSame(1, $report['summary']['overdue']);
        $this->assertSame(1, $report['summary']['completed']);
        $this->assertEqualsWithDelta(3200.0, $report['summary']['total_cost'], 0.001);
        $this->assertCount(12, $report['monthly_cost'], 'The cost trend covers the last twelve months.');

        $this->actingAs($this->user)
            ->get(route('reports.maintenance'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('reports/maintenance')
                ->where('report.summary.overdue', 1)
                ->where('report.overdue.0.title', 'Overdue fan repair')
                ->has('report.monthly_cost', 12));
    }

    public function test_warranty_report_splits_expiring_expired_and_vendors(): void
    {
        $report = app(ReportService::class)->warrantyReport();

        $this->assertSame(1, $report['summary']['expiring_soon']);
        $this->assertSame(1, $report['summary']['expired']);
        $this->assertSame(1, $report['summary']['claimed']);
        $this->assertCount(2, $report['vendors']);

        $this->actingAs($this->user)
            ->get(route('reports.warranty'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('reports/warranty')
                ->where('report.summary.expiring_soon', 1)
                ->where('report.expiring.0.vendor_name', 'HP Care Pack')
                ->where('report.expired.0.vendor_name', 'Lenovo Premier')
                ->has('report.vendors', 2));
    }

    public function test_movement_report_totals_transfers_disposals_and_recovery(): void
    {
        $report = app(ReportService::class)->movementReport();

        $this->assertSame(2, $report['summary']['transfers_total']);
        $this->assertSame(1, $report['summary']['transfers_completed']);
        $this->assertSame(1, $report['summary']['disposals_completed']);
        $this->assertEqualsWithDelta(7500.0, $report['summary']['value_recovered'], 0.001);

        $reasons = collect($report['transfer_reasons'])->pluck('reason')->all();
        $this->assertContains('Branch office setup', $reasons);
        $this->assertContains('Desk relocation', $reasons);

        $this->actingAs($this->user)
            ->get(route('reports.movement'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('reports/movement')
                ->where('report.summary.transfers_completed', 1)
                ->where('report.summary.value_recovered', 7500)
                ->where('report.disposals.0.reason', 'End of useful life'));
    }

    // =========================
    //     6.5 EXPORTS
    // =========================

    public function test_every_report_exports_as_csv(): void
    {
        foreach (['maintenance', 'warranty', 'movement'] as $type) {
            $response = $this->actingAs($this->user)->get(route('reports.export', $type) . '?format=csv');

            $response->assertOk();
            $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
            $this->assertStringContainsString('RP-100', $response->streamedContent(), "The {$type} CSV should list the asset.");
        }
    }

    public function test_every_report_exports_as_pdf(): void
    {
        foreach (['maintenance', 'warranty', 'movement'] as $type) {
            $response = $this->actingAs($this->user)->get(route('reports.export', $type) . '?format=pdf');

            $response->assertOk();
            $this->assertStringStartsWith('%PDF', $response->getContent());
            $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
        }
    }

    public function test_every_report_exports_as_excel(): void
    {
        foreach (['maintenance', 'warranty', 'movement'] as $type) {
            $this->actingAs($this->user)
                ->get(route('reports.export', $type) . '?format=xlsx')
                ->assertOk()
                ->assertDownload($type . '-report-' . now()->format('Y-m-d') . '.xlsx');
        }
    }

    public function test_unknown_report_types_and_formats_are_rejected(): void
    {
        $this->actingAs($this->user)->get(route('reports.export', 'salary') . '?format=csv')->assertNotFound();
        $this->actingAs($this->user)->get(route('reports.export', 'warranty') . '?format=docx')->assertNotFound();
    }

    public function test_exported_rows_match_the_report_shown_on_screen(): void
    {
        $service = app(ReportService::class);

        $this->assertCount(count($service->maintenanceReport()['rows']), $service->exportable('maintenance')['rows']);
        $this->assertCount(count($service->warrantyReport()['rows']), $service->exportable('warranty')['rows']);
    }
}
