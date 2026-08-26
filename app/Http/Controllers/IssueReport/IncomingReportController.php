<?php

namespace App\Http\Controllers\IssueReport;

use App\Models\Feedback;
use App\Models\IssueReport;
use App\Models\FeedbackReply;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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

        return view('incoming-reports.index', [
            'issueReports' => $issueReports,
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

        return view('incoming-reports.detail', [
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
        ]);

        // prefer 'decision_analysis' if present, otherwise use 'repair_analysis'
        $analysis = $validated['decision_analysis'] ?? $validated['repair_analysis'] ?? null;

        DB::beginTransaction();
        try {
            // update the report status first
            $issueReport->update(['status' => 'Completed']);

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
            $feedback->status = 'Completed';
            $feedback->save();

            DB::commit();

            return redirect()->back()->with('success', 'Report status changed to "Completed".');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to complete the report: ' . $e->getMessage());
        }
    }
}
