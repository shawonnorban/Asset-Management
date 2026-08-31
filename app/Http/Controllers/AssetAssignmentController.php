<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\Employee;
use App\Services\AuditTrailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

/**
 * Handing an asset to an employee and taking it back. Every handover is kept
 * as a row so IT can answer "who had this desktop last March?".
 */
class AssetAssignmentController extends Controller
{
    public function create(Request $request)
    {
        return Inertia::render('assignments/create', [
            'assets' => Asset::with('category')
                ->whereDoesntHave('assignments', fn ($query) => $query->whereNull('returned_at'))
                ->whereNull('employee_id')
                ->orderBy('asset_code')->get()
                ->map(fn ($asset) => [
                    'id' => $asset->id,
                    'label' => $asset->asset_code . ' - ' . $asset->asset_name,
                    'category' => $asset->category->category_name ?? null,
                    'status' => $asset->status,
                ])->values(),
            'employees' => Employee::orderBy('name')->get()
                ->map(fn ($employee) => ['id' => $employee->id, 'label' => $employee->name . ' (' . $employee->employee_code . ')'])->values(),
            'locations' => \App\Models\AssetLocation::orderBy('location_name')->get()
                ->map(fn ($location) => ['id' => $location->id, 'label' => $location->location_name])->values(),
            'selectedAssetId' => $request->integer('asset_id') ?: null,
        ]);
    }

    /** Every open handover across the company. */
    public function index()
    {
        $assignments = AssetAssignment::with(['asset.category', 'employee.department', 'location', 'handler'])
            ->whereNull('returned_at')
            ->orderByDesc('assigned_at')
            ->get();

        // Keep the page truthful for older assets that were assigned before
        // assignment history was introduced.
        $openAssetIds = $assignments->pluck('asset_id');
        $legacyAssignments = Asset::with(['category', 'location', 'employee.department'])
            ->whereNotNull('employee_id')
            ->whereNotIn('id', $openAssetIds)
            ->get()
            ->map(fn ($asset) => [
                'id' => 'asset-' . $asset->id,
                'asset_id' => $asset->id,
                'asset_code' => $asset->asset_code,
                'asset_name' => $asset->asset_name,
                'category' => $asset->category->category_name ?? null,
                'employee_id' => $asset->employee_id,
                'employee' => $asset->employee->name ?? null,
                'employee_code' => $asset->employee->employee_code ?? null,
                'department' => $asset->employee->department->name ?? null,
                'location' => $asset->location->location_name ?? null,
                'assigned_at' => null,
                'condition' => $asset->condition,
                'handler' => null,
            ]);

        $assignmentRows = $assignments->map(fn ($row) => [
            'id' => $row->id,
            'asset_id' => $row->asset_id,
            'asset_code' => $row->asset->asset_code ?? null,
            'asset_name' => $row->asset->asset_name ?? null,
            'category' => $row->asset->category->category_name ?? null,
            'employee_id' => $row->employee_id,
            'employee' => $row->employee->name ?? null,
            'employee_code' => $row->employee->employee_code ?? null,
            'department' => $row->employee->department->name ?? null,
            'location' => $row->location->location_name ?? ($row->asset->location->location_name ?? null),
            'assigned_at' => optional($row->assigned_at)->format('d M Y'),
            'condition' => $row->condition_on_assign,
            'handler' => $row->handler->name ?? null,
        ]);

        return Inertia::render('assignments/index', [
            'assignments' => $assignmentRows->concat($legacyAssignments)->values(),
        ]);
    }

    /** Hand an asset over to an employee. */
    public function store(Request $request, AuditTrailService $auditTrailService, ?Asset $asset = null)
    {
        if (! $asset) {
            $request->validate(['asset_id' => 'required|exists:assets,id']);
            $asset = Asset::findOrFail($request->input('asset_id'));
        }

        if ($asset->currentAssignment || $asset->employee_id) {
            return back()->with(
                'error',
                'This asset is still assigned. Record the return before assigning it to someone else.'
            );
        }

        $validated = $request->validate([
            'employee_id'         => 'required|exists:employees,id',
            'location_id'         => 'nullable|exists:asset_locations,id',
            'assigned_at'         => 'required|date',
            'condition_on_assign' => 'nullable|in:NEW,GOOD,FAIR,POOR',
            'note'                => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $before = $asset->toArray();

            $assignment = AssetAssignment::create($validated + [
                'asset_id'   => $asset->id,
                'handled_by' => Auth::id(),
            ]);

            $asset->update([
                'employee_id' => $validated['employee_id'],
                'location_id' => $validated['location_id'] ?? $asset->location_id,
                'status'      => 'IN_USE',
            ]);

            $employeeName = Employee::find($validated['employee_id'])?->name ?? '-';

            $auditTrailService->log(
                action: 'ASSIGN_ASSET',
                table: 'asset_assignments',
                rowId: $assignment->id,
                message: 'Assigned ' . $asset->asset_code . ' to ' . $employeeName,
                before: $before,
                after: $asset->fresh()->toArray()
            );

            DB::commit();

            return back()->with('success', 'Asset assigned to ' . $employeeName . '.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Failed to assign the asset: ' . $e->getMessage());
        }
    }

    /** Take the asset back from whoever holds it. */
    public function returnAsset(Request $request, Asset $asset, AuditTrailService $auditTrailService)
    {
        $assignment = $asset->currentAssignment;

        if (! $assignment) {
            return back()->with('error', 'This asset is not assigned to anyone.');
        }

        $validated = $request->validate([
            'returned_at'         => 'required|date|after_or_equal:' . $assignment->assigned_at->toDateString(),
            'condition_on_return' => 'nullable|in:NEW,GOOD,FAIR,POOR',
            'note'                => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $before = $assignment->toArray();

            $assignment->update([
                'returned_at'         => $validated['returned_at'],
                'condition_on_return' => $validated['condition_on_return'] ?? null,
                'note'                => $validated['note'] ?? $assignment->note,
            ]);

            $asset->update([
                'employee_id' => null,
                'status'      => 'IN_STORAGE',
                'condition'   => $validated['condition_on_return'] ?? $asset->condition,
            ]);

            $auditTrailService->log(
                action: 'RETURN_ASSET',
                table: 'asset_assignments',
                rowId: $assignment->id,
                message: 'Returned ' . $asset->asset_code . ' from ' . ($assignment->employee->name ?? '-'),
                before: $before,
                after: $assignment->fresh()->toArray()
            );

            DB::commit();

            return back()->with('success', 'Asset return recorded.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Failed to record the return: ' . $e->getMessage());
        }
    }
}
