<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\MaintenanceRecord;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use App\Services\AuditTrailService;

class MaintenanceController extends Controller
{
    private const STATUSES = ['SCHEDULED' => 'Scheduled', 'IN_PROGRESS' => 'In Progress', 'COMPLETED' => 'Completed', 'CANCELLED' => 'Cancelled'];

    public function index()
    {
        $records = MaintenanceRecord::with('asset')->latest('scheduled_at')->latest('id')->paginate(15);

        return Inertia::render('maintenance/index', [
            'title' => 'Maintenance', 'description' => 'Track preventive and corrective asset maintenance.',
            'records' => $records->getCollection()->map(fn ($record) => [
                'id' => $record->id, 'title' => $record->title, 'asset_id' => $record->asset_id,
                'asset_code' => $record->asset?->asset_code, 'asset_name' => $record->asset?->asset_name,
                'type' => $record->maintenance_type, 'vendor' => $record->vendor,
                'scheduled_at' => optional($record->scheduled_at)->format('d M Y'),
                'completed_at' => optional($record->completed_at)->format('d M Y'), 'cost' => $record->cost,
                'final_cost' => $record->final_cost,
                'status' => $record->status, 'status_label' => self::STATUSES[$record->status] ?? $record->status,
            ])->values(), 'pagination' => $records->toArray(), 'statuses' => self::STATUSES,
            'canManage' => auth()->user()->isSuperAdmin(),
            'canView' => auth()->user()->hasPermission('maintenance.view'),
        ]);
    }

    public function show(MaintenanceRecord $maintenance)
    {
        $maintenance->load('asset');

        return Inertia::render('maintenance/show', [
            'title' => $maintenance->title,
            'record' => [
                'id' => $maintenance->id,
                'title' => $maintenance->title,
                'asset' => ($maintenance->asset?->asset_code ?? '-') . ' - ' . ($maintenance->asset?->asset_name ?? '-'),
                'maintenance_type' => $maintenance->maintenance_type,
                'description' => $maintenance->description,
                'vendor' => $maintenance->vendor,
                'scheduled_at' => optional($maintenance->scheduled_at)->format('d M Y'),
                'completed_at' => optional($maintenance->completed_at)->format('d M Y'),
                'cost' => $maintenance->cost,
                'final_cost' => $maintenance->final_cost,
                'completion_remarks' => $maintenance->completion_remarks,
                'status' => self::STATUSES[$maintenance->status] ?? $maintenance->status,
            ],
        ]);
    }

    public function create()
    {
        $this->ensureSuperAdmin();
        return Inertia::render('maintenance/form', ['title' => 'Schedule Maintenance', 'record' => null, 'assets' => $this->assetOptions(), 'statuses' => self::STATUSES]);
    }

    public function store(Request $request, AuditTrailService $auditTrailService)
    {
        $this->ensureSuperAdmin();
        $record = MaintenanceRecord::create($this->validated($request) + ['created_by' => auth()->id()]);
        $auditTrailService->created('maintenance_records', $record->id, $record->toArray(), 'Scheduled maintenance: ' . $record->title);
        return redirect()->route('maintenance.index')->with('success', "Maintenance scheduled for {$record->title}.");
    }

    public function edit(MaintenanceRecord $maintenance)
    {
        $this->ensureSuperAdmin();
        return Inertia::render('maintenance/form', ['title' => 'Edit Maintenance', 'record' => [
            'id' => $maintenance->id, 'asset_id' => $maintenance->asset_id, 'title' => $maintenance->title,
            'maintenance_type' => $maintenance->maintenance_type, 'description' => $maintenance->description,
            'vendor' => $maintenance->vendor, 'scheduled_at' => optional($maintenance->scheduled_at)->format('Y-m-d'),
            'completed_at' => optional($maintenance->completed_at)->format('Y-m-d'), 'cost' => $maintenance->cost,
            'status' => $maintenance->status,
        ], 'assets' => $this->assetOptions(), 'statuses' => self::STATUSES]);
    }

    public function update(Request $request, MaintenanceRecord $maintenance, AuditTrailService $auditTrailService)
    {
        $this->ensureSuperAdmin();
        $before = $maintenance->toArray();
        $maintenance->update($this->validated($request));
        $auditTrailService->updated('maintenance_records', $maintenance->id, $before, $maintenance->fresh()->toArray(), 'Updated maintenance: ' . $maintenance->title);
        return redirect()->route('maintenance.index')->with('success', 'Maintenance record updated.');
    }

    public function complete(MaintenanceRecord $maintenance)
    {
        $this->ensureSuperAdmin();
        $validated = request()->validate([
            'final_cost' => ['required', 'numeric', 'min:0'],
            'completed_at' => ['required', 'date', 'after_or_equal:' . optional($maintenance->scheduled_at)->toDateString()],
            'completion_remarks' => ['nullable', 'string', 'max:2000'],
        ]);
        $maintenance->update($validated + ['status' => 'COMPLETED']);

        if ($maintenance->issue_report_id) {
            $maintenance->issueReport()->update(['status' => 'Completed']);
        }
        $maintenance->asset()->update([
            'status' => $maintenance->asset?->employee_id ? 'IN_USE' : 'IN_STORAGE',
        ]);

        return back()->with('success', 'Maintenance work order completed.');
    }

    public function destroy(MaintenanceRecord $maintenance, AuditTrailService $auditTrailService)
    {
        $this->ensureSuperAdmin();
        $before = $maintenance->toArray();
        $maintenance->delete();
        $auditTrailService->deleted('maintenance_records', $before['id'], $before, 'Deleted maintenance: ' . ($before['title'] ?? $before['id']));
        return back()->with('success', 'Maintenance record deleted.');
    }

    private function assetOptions()
    {
        return Asset::orderBy('asset_code')->get()->map(fn ($asset) => ['id' => $asset->id, 'label' => $asset->asset_code . ' - ' . $asset->asset_name])->values();
    }

    private function ensureSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'asset_id' => ['required', 'exists:assets,id'], 'title' => ['required', 'string', 'max:150'],
            'maintenance_type' => ['nullable', 'string', 'max:50'], 'description' => ['nullable', 'string'],
            'vendor' => ['nullable', 'string', 'max:120'], 'scheduled_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date', 'after_or_equal:scheduled_at'], 'cost' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
        ]);
    }
}
