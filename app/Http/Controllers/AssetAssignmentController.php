<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\Employee;
use App\Services\AuditTrailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Handing an asset to an employee and taking it back. Every handover is kept
 * as a row so IT can answer "who had this desktop last March?".
 */
class AssetAssignmentController extends Controller
{
    /** Every open handover across the company. */
    public function index()
    {
        $assignments = AssetAssignment::with(['asset.category', 'employee.department', 'location', 'handler'])
            ->whereNull('returned_at')
            ->orderByDesc('assigned_at')
            ->get();

        return view('assignments.index', compact('assignments'));
    }

    /** Hand an asset over to an employee. */
    public function store(Request $request, Asset $asset, AuditTrailService $auditTrailService)
    {
        if ($asset->currentAssignment) {
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
            'status'              => 'required|in:IN_STORAGE,UNDER_REPAIR,RETIRED',
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
                'status'      => $validated['status'],
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
