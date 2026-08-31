<?php

namespace App\Http\Controllers\IssueReport;

use App\Models\Asset;
use App\Models\IssueReport;
use App\Http\Controllers\Controller;
use App\Services\AuditTrailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ReportIssueController extends Controller
{
    /**
     * Show the create issue report form.
     */
    public function index(Request $request)
    {
        $asset = $request->filled('asset_id')
            ? $this->visibleAssets()->with(['category', 'location'])->find($request->integer('asset_id'))
            : null;

        return Inertia::render('report-issue/index', [
            'title' => 'Report an Issue',
            'selectedAsset' => $asset ? [
                'id' => $asset->id,
                'asset_code' => $asset->asset_code,
                'asset_name' => $asset->asset_name,
                'category' => optional($asset->category)->category_name,
                'brand' => $asset->brand,
                'location' => optional($asset->location)->location_name,
            ] : null,
        ]);
    }

    public function mine()
    {
        $reports = IssueReport::with('asset')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return Inertia::render('my-reports/index', [
            'title' => 'My Issue Reports',
            'description' => 'Track problems reported for your assigned assets.',
            'rows' => $reports->map(fn ($report) => [
                'id' => $report->id,
                'title' => $report->title,
                'asset' => $report->asset?->asset_name ?? '-',
                'status' => $report->status ?? 'Pending',
                'created' => optional($report->created_at)->format('Y-m-d H:i'),
            ])->values(),
        ]);
    }

    public function showMine($id)
    {
        $report = IssueReport::with('asset')
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return Inertia::render('my-reports/show', [
            'title' => 'Issue Report',
            'report' => [
                'id' => $report->id,
                'title' => $report->title,
                'description' => $report->description,
                'status' => $report->status ?? 'Pending',
                'created' => optional($report->created_at)->format('Y-m-d H:i'),
                'image_url' => $report->image ? Storage::url($report->image) : null,
                'asset' => $report->asset ? [
                    'id' => $report->asset->id,
                    'code' => $report->asset->asset_code,
                    'name' => $report->asset->asset_name,
                    'status' => $report->asset->status,
                ] : null,
            ],
        ]);
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

        $asset = $this->visibleAssets()->with(['category', 'location'])
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

    /** Search assets for the issue form's code/name suggestions. */
    public function searchAssets(Request $request)
    {
        $term = trim((string) $request->query('q', ''));

        if (mb_strlen($term) < 2) {
            return response()->json([]);
        }

        $like = '%' . $term . '%';
        $assets = $this->visibleAssets()->with(['category', 'location'])
            ->where(function ($query) use ($like) {
                $query->where('asset_code', 'like', $like)
                    ->orWhere('asset_name', 'like', $like);
            })
            ->orderBy('asset_code')
            ->limit(8)
            ->get();

        return response()->json($assets->map(fn ($asset) => [
            'id' => $asset->id,
            'asset_code' => $asset->asset_code,
            'asset_name' => $asset->asset_name,
            'category' => optional($asset->category)->category_name,
            'brand' => $asset->brand,
            'location' => optional($asset->location)->location_name,
        ])->values());
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
            'image'       => 'nullable|image|max:10240',
        ], [
            'title.required' => 'The report title is required.',
            'description.required' => 'The description is required.',
            'asset_id.required' => 'No asset selected, or the QR code is invalid.',
            'asset_id.exists' => 'Asset not found.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $asset = $this->visibleAssets()->find($request->integer('asset_id'));
        if (! $asset) {
            return back()->withErrors(['asset_id' => 'You can only report a problem with an asset assigned to you.'])->withInput();
        }

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('issue-reports', 'public')
            : null;

        $issueReport = IssueReport::create([
            'title'       => $request->title,
            'description' => $request->description,
            'asset_id'    => $request->asset_id,
            'user_id'     => Auth::id(),
            'status'      => 'Pending',
            'image'       => $imagePath,
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

    private function visibleAssets()
    {
        $user = Auth::user();

        if ($user?->canonicalRole() === 'employee') {
            return Asset::where('employee_id', $user->employee?->id ?? 0);
        }

        return Asset::query();
    }
}
