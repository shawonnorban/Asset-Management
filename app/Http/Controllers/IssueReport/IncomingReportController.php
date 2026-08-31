<?php

namespace App\Http\Controllers\IssueReport;

use App\Models\Feedback;
use App\Models\IssueReport;
use App\Models\FeedbackReply;
use App\Models\Asset;
use App\Models\MaintenanceRecord;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class IncomingReportController extends Controller
{
    /**
     * Show the list of reports that are not completed yet.
     */
    public function index()
    {
        // Get every report whose status is not "Completed"
        $issueReports = IssueReport::where(function ($q) {
            $q->whereNull('status')
              ->orWhere('status', '!=', 'Completed');
        })->orderBy('id', 'DESC')->get();

        return Inertia::render('incoming-reports/index', [
            'title' => 'Incoming Reports', 'description' => 'Review unresolved issues reported by staff.',
            'rows' => $issueReports->map(fn ($report) => [
                'id' => $report->id, 'title' => $report->title, 'status' => $report->status ?? 'Pending',
                'asset' => optional($report->asset)->asset_name ?? '-', 'created' => optional($report->created_at)->format('Y-m-d'),
            ])->values(),
        ]);
    }

    /**
     * Show a single report (for the admin to review and respond to).
     */
    public function detail($id)
    {
        $issueReport = IssueReport::with(['asset', 'user'])->findOrFail($id);

        // get the related feedback (if any)
        $feedback = Feedback::where('issue_report_id', $issueReport->id)->first();

        // get every reply for that feedback (if any)
        $feedbackReplies = $feedback
            ? FeedbackReply::where('feedback_id', $feedback->id)->orderBy('created_at', 'asc')->get()
            : collect();

        return Inertia::render('incoming-reports/detail', [
            'title' => 'Incoming Report Detail',
            'issueReport'     => $issueReport,
            'feedback'        => $feedback,
            'feedbackReplies' => $feedbackReplies,
        ]);
    }

    /**
     * Mark a report as "In Review".
     */
    public function review($id)
    {
        $issueReport = IssueReport::findOrFail($id);

        $issueReport->update(['status' => 'In Review']);

        return redirect()->back()->with('success', 'Report status changed to "In Review".');
    }

    /**
     * Mark a report as completed and store the decision analysis as feedback.
     *
     * Accepts request fields:
     *  - decision_analysis (recommended)
     *  - repair_analysis (compatibility with previous naming)
     */
    public function complete(Request $request, $id)
    {
        $issueReport = IssueReport::findOrFail($id);

        $validated = $request->validate([
            // accept either field name, but require at least one
            'decision_analysis' => ['nullable', 'string', 'max:255'],
            'repair_analysis'   => ['nullable', 'string', 'max:255'],
            'resolution'        => ['required', Rule::in(['maintenance', 'normal'])],
        ]);

        // prefer 'decision_analysis' if present, otherwise use 'repair_analysis'
        $analysis = $validated['decision_analysis'] ?? $validated['repair_analysis'] ?? null;

        DB::beginTransaction();
        try {
            $asset = Asset::findOrFail($issueReport->asset_id);

            // Record the operational decision on both the report and asset.
            $asset->update(['status' => $validated['resolution'] === 'maintenance' ? 'UNDER_REPAIR' : 'IN_USE']);
            if ($validated['resolution'] === 'maintenance') {
                MaintenanceRecord::firstOrCreate(
                    ['issue_report_id' => $issueReport->id],
                    [
                        'asset_id' => $asset->id,
                        'title' => 'Issue report: ' . $issueReport->title,
                        'maintenance_type' => 'CORRECTIVE',
                        'description' => $issueReport->description,
                        'status' => 'SCHEDULED',
                        'created_by' => auth()->id(),
                    ]
                );
            }

            $issueReport->update([
                'status' => $validated['resolution'] === 'maintenance' ? 'In Review' : 'Completed',
                'resolution' => $validated['resolution'],
            ]);

            // create or update the feedback tied to this report
            $feedback = Feedback::firstOrNew(['issue_report_id' => $issueReport->id]);
            $feedback->issue_report_id = $issueReport->id;
            $feedback->asset_id = $issueReport->asset_id;

            if ($analysis) {
                $feedback->decision_analysis = $analysis;
            }

            // user_id can be filled from auth when available (optional)
            if (auth()->check()) {
                $feedback->user_id = auth()->id();
            }
            $feedback->status = $validated['resolution'] === 'maintenance' ? 'In Review' : 'Completed';
            $feedback->save();

            DB::commit();

            return redirect()->back()->with('success', 'Report status changed to "Completed".');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to complete the report: ' . $e->getMessage());
        }
    }
}
