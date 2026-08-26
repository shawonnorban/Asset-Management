<?php

namespace App\Http\Controllers\IssueReport;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IssueReport;
use App\Models\Feedback;
use App\Models\FeedbackReply;
use App\Services\AuditTrailService;
use Barryvdh\DomPDF\Facade\Pdf;

class CompletedReportController extends Controller
{
    /**
     * List the reports that have been completed
     */
    public function index()
    {
        return view('completed-reports.index', [
            'issueReports' => IssueReport::with(['asset'])
                ->where('status', 'Completed')
                ->orderBy('updated_at', 'DESC')
                ->get()
        ]);
    }

    /**
     * Print a completed report (PDF)
     */
    public function printReport(
        $id,
        AuditTrailService $auditTrailService
    ) {
        $issueReport = IssueReport::with(['asset'])->findOrFail($id);

        // feedback (a report may or may not have one)
        $feedback = Feedback::where('issue_report_id', $issueReport->id)->first();

        // user reply (optional)
        $feedbackReply = $feedback
            ? FeedbackReply::where('feedback_id', $feedback->id)->first()
            : null;

        // =========================
        // AUDIT TRAIL
        // =========================
        $auditTrailService->log(
            action: 'EXPORT_COMPLETED_REPORT_PDF',
            table: 'issue_reports',
            rowId: $issueReport->id,
            message: 'Printed a completed issue report (PDF)',
            before: null,
            after: [
                'issue_report_id' => $issueReport->id,
                'status'          => $issueReport->status,
            ]
        );

        $pdf = Pdf::loadView('completed-reports.print-report', [
            'issueReport'   => $issueReport,
            'feedback'      => $feedback,
            'feedbackReply' => $feedbackReply,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream('issue-report-' . $issueReport->id . '.pdf');
    }
}
