<?php

namespace App\Http\Controllers\IssueReport;

use App\Models\Asset;
use App\Models\IssueReport;
use App\Http\Controllers\Controller;
use App\Services\AuditTrailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ReportIssueController extends Controller
{
    /**
     * Show the create issue report form.
     */
    public function index()
    {
        return view('report-issue.index');
    }

    /**
     * Look up an asset by QR code (param "result").
     * Returns JSON: id, asset_name, category, brand, location
     */
    public function getAssetData(Request $request)
    {
        $qrCode = $request->input('result');

        // Default when the asset is not found
        $response = [
            'id' => null,
            'asset_name' => null,
            'category' => null,
            'brand' => null,
            'location' => null,
        ];

        if (! $qrCode) {
            return response()->json($response);
        }

        $asset = Asset::with(['category', 'location'])
            ->where('asset_code', $qrCode)
            ->first();

        if ($asset) {
            $response = [
                'id' => $asset->id,
                'asset_name' => $asset->asset_name,
                'category' => optional($asset->category)->category_name,
                'brand' => $asset->brand,
                'location' => optional($asset->location)->location_name,
            ];
        }

        return response()->json($response);
    }

    /**
     * Store a new issue report.
     */
    public function store(
        Request $request,
        AuditTrailService $auditTrailService
    ) {
        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:200',
            'description' => 'required|string',
            'asset_id'    => 'required|exists:assets,id',
        ], [
            'title.required' => 'The report title is required.',
            'description.required' => 'The description is required.',
            'asset_id.required' => 'No asset selected, or the QR code is invalid.',
            'asset_id.exists' => 'Asset not found.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $issueReport = IssueReport::create([
            'title'       => $request->title,
            'description' => $request->description,
            'asset_id'    => $request->asset_id,
            'user_id'     => Auth::id(),
            'status'      => 'Pending',
        ]);

        // =========================
        // AUDIT TRAIL
        // =========================
        $auditTrailService->log(
            action: 'CREATE_ISSUE_REPORT',
            table: 'issue_reports',
            rowId: $issueReport->id,
            message: 'Created an issue report for asset ID #' . $issueReport->asset_id,
            before: null,
            after: $issueReport->toArray()
        );

        return redirect()
            ->route('report-issue.index')
            ->with('success', 'Issue report submitted successfully.');
    }
}
