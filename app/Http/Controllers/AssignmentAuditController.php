<?php

namespace App\Http\Controllers;

use App\Models\AssetAssignment;
use App\Models\AssignmentAudit;
use App\Models\AssignmentAuditVerification;
use App\Models\Employee;
use App\Services\AuditTrailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AssignmentAuditController extends Controller
{
    /**
     * List all audits
     */
    public function index()
    {
        $audits = AssignmentAudit::orderByDesc('audit_period')
            ->paginate(10);

        return Inertia::render('assignment-audits/index', [
            'audits' => $audits->through(fn ($audit) => [
                'id' => $audit->id,
                'audit_name' => $audit->audit_name,
                'audit_period' => $audit->audit_period->format('F Y'),
                'status' => $audit->status,
                'started_at' => $audit->started_at?->format('M d, Y H:i'),
                'completed_at' => $audit->completed_at?->format('M d, Y H:i'),
                'progress' => $audit->getProgressPercentage(),
                'total' => $audit->total_assignments,
                'verified' => $audit->verified_count,
                'missing' => $audit->missing_count,
                'damaged' => $audit->damaged_count,
            ]),
        ]);
    }

    /**
     * Create new audit for a period
     */
    public function create()
    {
        $lastAudit = AssignmentAudit::orderByDesc('audit_period')->first();
        $suggestedPeriod = $lastAudit
            ? $lastAudit->audit_period->addMonth()->startOfMonth()
            : now()->startOfMonth();

        return Inertia::render('assignment-audits/create', [
            'suggested_period' => $suggestedPeriod->format('Y-m-d'),
        ]);
    }

    /**
     * Store new audit
     */
    public function store(Request $request, AuditTrailService $auditTrailService)
    {
        $validated = $request->validate([
            'audit_name' => 'required|string|max:255',
            'audit_period' => 'required|date',
        ]);

        $audit = AssignmentAudit::create([
            'audit_name' => $validated['audit_name'],
            'audit_period' => $validated['audit_period'],
            'status' => 'pending',
        ]);

        $auditTrailService->log(
            'AUDIT_CREATED',
            'assignment_audits',
            $audit->id,
            "Created audit: {$audit->audit_name} for " . $audit->audit_period->format('F Y'),
            [],
            $audit->toArray()
        );

        return redirect()->route('assignment-audits.show', $audit->id)
            ->with('success', 'Audit created successfully');
    }

    /**
     * Show audit details and start verification
     */
    public function show(AssignmentAudit $audit)
    {
        $audit->load(['verifications.asset', 'verifications.employee', 'verifications.verifiedBy']);

        $verifiedAssignmentIds = $audit->verifications
            ->whereNotNull('verified_at')
            ->pluck('assignment_id')
            ->all();

        $activeAssignments = AssetAssignment::with(['asset.category', 'employee.department', 'location', 'handler'])
            ->whereNull('returned_at')
            ->when(! empty($verifiedAssignmentIds), function ($query) use ($verifiedAssignmentIds) {
                $query->whereNotIn('id', $verifiedAssignmentIds);
            })
            ->orderBy('assigned_at')
            ->get();

        return Inertia::render('assignment-audits/show', [
            'audit' => [
                'id' => $audit->id,
                'audit_name' => $audit->audit_name,
                'audit_period' => $audit->audit_period->format('F Y'),
                'status' => $audit->status,
                'started_at' => $audit->started_at,
                'completed_at' => $audit->completed_at,
                'progress' => $audit->getProgressPercentage(),
                'total_assignments' => $audit->total_assignments,
                'verified_count' => $audit->verified_count,
                'missing_count' => $audit->missing_count,
                'damaged_count' => $audit->damaged_count,
                'notes' => $audit->notes,
            ],
            'active_assignments' => $activeAssignments->map(fn ($assignment) => [
                'id' => $assignment->id,
                'asset_id' => $assignment->asset_id,
                'asset_code' => $assignment->asset->asset_code,
                'asset_name' => $assignment->asset->asset_name,
                'category' => $assignment->asset->category->category_name ?? null,
                'employee_id' => $assignment->employee_id,
                'employee_name' => $assignment->employee->name,
                'employee_code' => $assignment->employee->employee_code,
                'department' => $assignment->employee->department->name ?? null,
                'assigned_at' => $assignment->assigned_at->format('M d, Y'),
                'location' => $assignment->location->location_name ?? null,
            ])->values(),
            'verifications' => $audit->verifications->map(fn ($v) => [
                'id' => $v->id,
                'assignment_id' => $v->assignment_id,
                'asset_id' => $v->asset_id,
                'employee_id' => $v->employee_id,
                'status' => $v->verification_status,
                'condition' => $v->condition_observed,
                'verified_at' => $v->verified_at?->format('M d, Y H:i'),
                'remarks' => $v->remarks,
            ])->keyBy('assignment_id')->all(),
        ]);
    }

    /**
     * Start the audit (lock it and prevent editing)
     */
    public function start(AssignmentAudit $audit, AuditTrailService $auditTrailService)
    {
        if ($audit->status !== 'pending') {
            return back()->with('error', 'Audit has already been started');
        }

        // Get all active assignments for this period
        $activeAssignments = AssetAssignment::whereNull('returned_at')->get();

        // Create verification records for each assignment
        foreach ($activeAssignments as $assignment) {
            AssignmentAuditVerification::firstOrCreate(
                ['audit_id' => $audit->id, 'assignment_id' => $assignment->id],
                [
                    'asset_id' => $assignment->asset_id,
                    'employee_id' => $assignment->employee_id,
                    'verification_status' => 'pending',
                ]
            );
        }

        $audit->update([
            'status' => 'in_progress',
            'started_by' => Auth::id(),
            'started_at' => now(),
            'total_assignments' => $activeAssignments->count(),
        ]);

        $auditTrailService->log(
            'AUDIT_STARTED',
            'assignment_audits',
            $audit->id,
            "Started audit: {$audit->audit_name}",
            ['status' => 'pending'],
            $audit->toArray()
        );

        return back()->with('success', 'Audit started. Begin verifications.');
    }

    /**
     * Record a verification
     */
    public function verify(AssignmentAudit $audit, Request $request, AuditTrailService $auditTrailService)
    {
        if ($audit->status !== 'in_progress') {
            return back()->with('error', 'Audit is not in progress');
        }

        $validated = $request->validate([
            'assignment_id' => 'required|exists:asset_assignments,id',
            'verification_status' => 'required|in:confirmed,missing,lost,damaged,returned,transferred',
            'condition_observed' => 'nullable|in:good,fair,poor,damaged',
            'remarks' => 'nullable|string|max:500',
        ]);

        $verification = AssignmentAuditVerification::where('audit_id', $audit->id)
            ->where('assignment_id', $validated['assignment_id'])
            ->firstOrFail();

        $oldStatus = $verification->verification_status;

        $verification->update([
            'verification_status' => $validated['verification_status'],
            'condition_observed' => $validated['condition_observed'],
            'remarks' => $validated['remarks'],
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        // Update audit counts
        $verifiedCount = $audit->verifications()->whereNotNull('verified_at')->count();
        $missingCount = $audit->verifications()->where('verification_status', 'missing')->count();
        $damagedCount = $audit->verifications()->where('verification_status', 'damaged')->count();

        $audit->update([
            'verified_count' => $verifiedCount,
            'missing_count' => $missingCount,
            'damaged_count' => $damagedCount,
        ]);

        $auditTrailService->log(
            'ASSET_VERIFIED',
            'assignment_audit_verifications',
            $verification->id,
            "Verified asset assignment - Status: {$validated['verification_status']}",
            ['status' => $oldStatus],
            ['status' => $validated['verification_status']]
        );

        return back()->with('success', 'Verification recorded');
    }

    /**
     * Complete the audit
     */
    public function complete(AssignmentAudit $audit, Request $request, AuditTrailService $auditTrailService)
    {
        if ($audit->status !== 'in_progress') {
            return back()->with('error', 'Audit is not in progress');
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $unverified = $audit->verifications()->whereNull('verified_at')->count();

        if ($unverified > 0) {
            return back()->with('error', "Cannot complete audit with $unverified unverified items");
        }

        $audit->update([
            'status' => 'completed',
            'completed_by' => Auth::id(),
            'completed_at' => now(),
            'notes' => $validated['notes'],
        ]);

        $auditTrailService->log(
            'AUDIT_COMPLETED',
            'assignment_audits',
            $audit->id,
            "Completed audit: {$audit->audit_name}",
            ['status' => 'in_progress'],
            $audit->toArray()
        );

        return redirect()->route('assignment-audits.show', $audit->id)
            ->with('success', 'Audit completed successfully');
    }

    /**
     * Generate audit report
     */
    public function report(AssignmentAudit $audit)
    {
        $audit->load(['verifications.asset.category', 'verifications.employee.department', 'verifications.verifiedBy']);

        $issues = $audit->verifications()
            ->whereIn('verification_status', ['missing', 'lost', 'damaged'])
            ->with(['asset', 'employee'])
            ->get();

        return Inertia::render('assignment-audits/report', [
            'audit' => [
                'id' => $audit->id,
                'audit_name' => $audit->audit_name,
                'audit_period' => $audit->audit_period->format('F Y'),
                'status' => $audit->status,
                'started_at' => $audit->started_at?->format('M d, Y H:i'),
                'completed_at' => $audit->completed_at?->format('M d, Y H:i'),
                'total' => $audit->total_assignments,
                'verified' => $audit->verified_count,
                'missing' => $audit->missing_count,
                'damaged' => $audit->damaged_count,
                'notes' => $audit->notes,
            ],
            'issues' => $issues->map(fn ($v) => [
                'id' => $v->id,
                'asset_code' => $v->asset->asset_code,
                'asset_name' => $v->asset->asset_name,
                'category' => $v->asset->category->category_name ?? null,
                'employee_name' => $v->employee->name,
                'employee_code' => $v->employee->employee_code,
                'department' => $v->employee->department->name ?? null,
                'status' => $v->verification_status,
                'condition' => $v->condition_observed,
                'remarks' => $v->remarks,
                'verified_by' => $v->verifiedBy->name ?? null,
            ])->values(),
        ]);
    }
}
