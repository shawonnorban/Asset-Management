<?php

namespace App\Http\Controllers\IssueReport;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IssueReport;
use App\Models\Feedback;
use App\Models\FeedbackReply;

class ReviewReportController extends Controller
{
    public function index()
    {
        return view('review-reports.index', [
            'issueReports' => IssueReport::orderBy('id', 'DESC')->get()
        ]);
    }

    /**
     * Report detail with its feedback and reply
     */
    public function detail($id)
    {
        $issueReport = IssueReport::findOrFail($id);

        // Take the first feedback (one report has one admin feedback)
        $feedback = Feedback::where('issue_report_id', $issueReport->id)->first();

        // Take the user reply if there is one
        $feedbackReply = $feedback
            ? FeedbackReply::where('feedback_id', $feedback->id)->first()
            : null;

        return view('review-reports.detail', [
            'issueReport'   => $issueReport,
            'feedback'      => $feedback,
            'feedbackReply' => $feedbackReply,
        ]);
    }

    /**
     * The user sends a reply to the feedback
     */
    public function store(Request $request, IssueReport $issueReport)
    {
        $request->validate([
            'feedback_replies' => 'required'
        ]);

        $feedback = $issueReport->feedbacks()->first();

        if (!$feedback) {
            return back()->with('error', 'Admin feedback is not available yet.');
        }

        FeedbackReply::create([
            'feedback_id'    => $feedback->id,
            'user_id'        => auth()->id(),
            'feedback_reply' => $request->feedback_replies,
        ]);

        return back()->with('success', 'Reply sent successfully.');
    }
}
