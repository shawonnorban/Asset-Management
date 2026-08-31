<?php

namespace App\Http\Controllers;

use App\Exports\ReportExport;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /** Report keys the export endpoint will serve. */
    public const TYPES = [
        'maintenance' => 'Maintenance report',
        'warranty' => 'Warranty report',
        'movement' => 'Transfer and disposal report',
    ];

    public function __construct(private ReportService $reports)
    {
    }

    /** 6.4 - executive dashboard cards. */
    public function index()
    {
        $metrics = $this->reports->executiveMetrics();

        return Inertia::render('reports/index', [
            'title' => 'Executive asset report',
            'description' => 'Commercial overview of active assets, maintenance load, warranty risk, and asset movement.',
            'summary' => [
                [
                    'label' => 'Active assets',
                    'value' => $metrics['active_assets'],
                    'description' => 'Assets currently in the estate, excluding disposed and retired items',
                    'tone' => 'neutral',
                ],
                [
                    'label' => 'Under maintenance',
                    'value' => $metrics['under_maintenance'],
                    'description' => 'Assets with an open service ticket or maintenance record',
                    'tone' => $metrics['under_maintenance'] > 0 ? 'warning' : 'neutral',
                    'href' => route('reports.maintenance'),
                ],
                [
                    'label' => 'Warranty alerts',
                    'value' => $metrics['warranty_alerts'],
                    'description' => 'Warranties expiring within 30 days or already lapsed',
                    'tone' => $metrics['warranty_alerts'] > 0 ? 'danger' : 'success',
                    'href' => route('reports.warranty'),
                ],
                [
                    'label' => 'Overdue transfers',
                    'value' => $metrics['overdue_transfers'],
                    'description' => 'Transfer requests still unsettled after seven days',
                    'tone' => $metrics['overdue_transfers'] > 0 ? 'warning' : 'success',
                    'href' => route('reports.movement'),
                ],
                [
                    'label' => 'Disposal stats',
                    'value' => $metrics['disposed_assets'],
                    'description' => 'Assets disposed, recovering ' . number_format($metrics['value_recovered'], 2),
                    'tone' => 'neutral',
                    'href' => route('reports.movement'),
                ],
                [
                    'label' => 'Assigned assets',
                    'value' => $metrics['assigned_assets'],
                    'description' => 'Active assets currently in the hands of an employee',
                    'tone' => 'neutral',
                ],
            ],
            'metrics' => $metrics,
            'totalAssets' => $metrics['total_assets'],
            'maintenanceOpen' => $metrics['under_maintenance'],
            'warrantyRisk' => $metrics['warranty_alerts'],
            'reportLinks' => [
                ['label' => 'Maintenance report', 'href' => route('reports.maintenance'), 'description' => 'Open jobs, overdue work, and monthly maintenance cost.'],
                ['label' => 'Warranty report', 'href' => route('reports.warranty'), 'description' => 'Expiring cover, lapsed warranties, and vendor claim tracking.'],
                ['label' => 'Transfer & disposal report', 'href' => route('reports.movement'), 'description' => 'Movement volumes, reason summary, and value recovered.'],
            ],
        ]);
    }

    /** 6.1 - maintenance report. */
    public function maintenance()
    {
        return Inertia::render('reports/maintenance', [
            'title' => 'Maintenance report',
            'description' => 'Open maintenance count, overdue jobs, and monthly maintenance cost.',
            'report' => $this->reports->maintenanceReport(),
            'exportBase' => route('reports.export', 'maintenance'),
        ]);
    }

    /** 6.2 - warranty report. */
    public function warranty()
    {
        return Inertia::render('reports/warranty', [
            'title' => 'Warranty report',
            'description' => 'Warranties expiring in 30 days, expired cover, and vendor-wise claim tracking.',
            'report' => $this->reports->warrantyReport(),
            'exportBase' => route('reports.export', 'warranty'),
        ]);
    }

    /** 6.3 - transfer and disposal report. */
    public function movement()
    {
        return Inertia::render('reports/movement', [
            'title' => 'Transfer & disposal report',
            'description' => 'Transferred and disposed asset volumes, reason summary, and value recovered.',
            'report' => $this->reports->movementReport(),
            'exportBase' => route('reports.export', 'movement'),
        ]);
    }

    /** 6.5 - one export endpoint serving PDF, Excel, and CSV. */
    public function export(Request $request, string $type)
    {
        abort_unless(array_key_exists($type, self::TYPES), 404);

        $format = strtolower((string) $request->query('format', 'csv'));
        abort_unless(in_array($format, ['csv', 'xlsx', 'pdf'], true), 404);

        $report = $this->reports->exportable($type);
        $filename = $type . '-report-' . now()->format('Y-m-d');

        return match ($format) {
            'xlsx' => Excel::download(
                new ReportExport($report['title'], $report['headings'], $report['rows']),
                $filename . '.xlsx'
            ),
            'pdf' => Pdf::loadView('reports.pdf', [
                'title' => $report['title'],
                'headings' => $report['headings'],
                'rows' => $report['rows'],
                'generatedAt' => now()->format('d M Y, h:i A'),
                'generatedBy' => auth()->user()?->name,
            ])->setPaper('a4', 'landscape')->download($filename . '.pdf'),
            default => $this->csv($report, $filename . '.csv'),
        };
    }

    private function csv(array $report, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($report) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $report['headings']);

            foreach ($report['rows'] as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
