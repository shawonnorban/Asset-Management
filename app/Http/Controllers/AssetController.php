<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLocation;
use App\Models\Employee;
use App\Models\IssueReport;
use App\Models\SoftwareLicense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\AuditTrailService;
use App\Services\AssetSpecService;
use Inertia\Inertia;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AssetExport;
use App\Support\AssetLifecycleStatus;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssetController extends Controller
{
    public function __construct(private AssetSpecService $specService)
    {
    }

    public function index(Request $request)
    {
        $query = $this->visibleAssets(Asset::with(['category', 'location', 'employee']));

        if ($request->filled('asset_type')) {
            $query->whereHas('category', fn ($q) => $q->where('asset_type', $request->asset_type));
        }

        if ($request->filled('status')) {
            // a card may cover more than one status, e.g. "RETIRED,DISPOSED"
            $query->whereIn('status', array_filter(explode(',', (string) $request->input('status'))));
        }

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';

            $query->where(function ($q) use ($term) {
                $q->where('asset_code', 'like', $term)
                  ->orWhere('asset_name', 'like', $term)
                  ->orWhere('brand', 'like', $term)
                  ->orWhere('model', 'like', $term)
                  ->orWhere('serial_number', 'like', $term);
            });
        }

        $assets = $query->orderByDesc('id')->paginate(15)->withQueryString();

        return Inertia::render('assets/index', [
            'assets' => $assets->through(fn (Asset $asset) => [
                'id'            => $asset->id,
                'asset_code'    => $asset->asset_code,
                'asset_name'    => $asset->asset_name,
                'brand'         => $asset->brand,
                'model'         => $asset->model,
                'serial_number' => $asset->serial_number,
                'image_url'     => $asset->image ? Storage::url($asset->image) : null,
                'category'      => $asset->category->category_name ?? null,
                'location'      => $asset->location->location_name ?? null,
                'employee'      => $asset->employee->name ?? null,
                // the stored status is the truth; fall back to the assignment
                // only for older rows that never had one set
                'status'        => $asset->status ?: ($asset->employee_id ? 'IN_USE' : 'IN_STORAGE'),
            ]),
            'assetTypes' => AssetCategory::ASSET_TYPES,
            'statuses'   => self::STATUSES,
            'summary'    => $this->indexSummary(),
            'filters'    => [
                'search'     => $request->input('search'),
                'asset_type' => $request->input('asset_type'),
                'status'     => $request->input('status'),
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('assets/form', $this->formData() + [
            'asset' => null,
            'spec'  => (object) [],
        ]);
    }

    public function store(Request $request)
    {
        $assetType = $this->assetTypeFor($request->input('category_id'));

        $validated = $request->validate(
            $this->baseRules() + $this->prefixedSpecRules($assetType)
        );

        $specData = $validated['spec'] ?? [];
        unset($validated['spec'], $validated['image']);

        DB::beginTransaction();
        $imagePath = null;
        $qrPath = null;

        try {
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $fileName = uniqid('asset_') . '.' . $file->getClientOriginalExtension();
                $imagePath = $file->storeAs('images', $fileName, 'public');
            }

            $asset = Asset::create($validated + ['status' => 'IN_STORAGE', 'employee_id' => null, 'image' => $imagePath]);

            $this->specService->sync($asset, $assetType, $specData);

            // AUDIT CREATE
            AuditTrailService::log(
                'CREATE',
                'assets',
                $asset->id,
                'Created a new asset',
                null,
                $asset->toArray()
            );

            $code = $asset->asset_code;
            $qrPath = 'qrcode/' . $code . '.png';

            $writer = new PngWriter();
            $qrCode = new QrCode($code);
            $qrImage = $writer->write($qrCode);
            Storage::disk('public')->put($qrPath, $qrImage->getString());

            DB::commit();

            return redirect()->route('assets.show', $asset->id)
                ->with('success', 'Asset created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            if ($qrPath && Storage::disk('public')->exists($qrPath)) {
                Storage::disk('public')->delete($qrPath);
            }

            return back()->withInput()->with('error', 'Something went wrong while saving the asset.');
        }
    }

    public function show(Asset $asset)
    {
        $this->ensureVisible($asset);

        $asset->load([
            'category',
            'location',
            'employee',
            'parentAsset',
            'childAssets.category',
            'computerSpec',
            'peripheralSpec',
            'printerSpec',
            'networkDeviceSpec',
            'assignments.employee.department',
            'assignments.handler',
            'licenseAssignments.license',
        ]);

        $issueReports = IssueReport::with([
                'user',
                'feedbacks.user',
                'feedbacks.replies.user'
            ])
            ->where('asset_id', $asset->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // licenses that still have a free seat, for the install dropdown
        $availableLicenses = SoftwareLicense::orderBy('name')->get()
            ->filter(fn ($license) => $license->seats_available > 0);

        $qrPath = 'qrcode/' . $asset->asset_code . '.png';
        $qrUrl = Storage::disk('public')->exists($qrPath)
            ? Storage::url($qrPath)
            : null;

        return Inertia::render('assets/show', [
            'asset' => [
                'id'             => $asset->id,
                'asset_code'     => $asset->asset_code,
                'asset_name'     => $asset->asset_name,
                'brand'          => $asset->brand,
                'model'          => $asset->model,
                'serial_number'  => $asset->serial_number,
                'description'    => $asset->description,
                'status'         => $asset->status ?: ($asset->employee_id ? 'IN_USE' : 'IN_STORAGE'),
                'condition'      => $asset->condition,
                'category'       => $asset->category->category_name ?? null,
                'asset_type'     => $asset->asset_type,
                'location'       => $asset->location->location_name ?? null,
                'employee'       => $asset->employee ? [
                    'name'       => $asset->employee->name,
                    'department' => $asset->employee->department->name ?? null,
                ] : null,
                'added_date'     => optional($asset->added_date)->format('d M Y'),
                'vendor'         => $asset->vendor,
                'invoice_no'     => $asset->invoice_no,
                'purchase_date'  => optional($asset->purchase_date)->format('d M Y'),
                'purchase_cost'  => $asset->purchase_cost ? number_format((float) $asset->purchase_cost, 2) : null,
                'warranty_start' => optional($asset->warranty_start)->format('d M Y'),
                'warranty_end'   => optional($asset->warranty_end)->format('d M Y'),
                'under_warranty' => $asset->isUnderWarranty(),
                'image_url'      => $asset->image ? Storage::url($asset->image) : null,
                'qr_url'         => $qrUrl,
                'parent'         => $asset->parentAsset ? [
                    'id'         => $asset->parentAsset->id,
                    'asset_code' => $asset->parentAsset->asset_code,
                    'asset_name' => $asset->parentAsset->asset_name,
                ] : null,
            ],

            'specGroups' => $this->specService->display($asset),

            'children' => $asset->childAssets->map(fn (Asset $child) => [
                'id'         => $child->id,
                'asset_code' => $child->asset_code,
                'asset_name' => $child->asset_name,
                'category'   => $child->category->category_name ?? null,
                'status'     => $child->employee_id ? 'IN_USE' : 'IN_STORAGE',
            ])->values(),

            'assignments' => $asset->assignments->sortByDesc('assigned_at')
                ->map(fn ($row) => [
                    'id'          => $row->id,
                    'employee'    => $row->employee->name ?? null,
                    'department'  => $row->employee->department->name ?? null,
                    'assigned_at' => optional($row->assigned_at)->format('d M Y'),
                    'returned_at' => optional($row->returned_at)->format('d M Y'),
                    'handler'     => $row->handler->name ?? null,
                ])->values(),

            'availableLicenses' => $availableLicenses->map(fn ($license) => [
                'id' => $license->id,
                'label' => trim($license->name . ' ' . ($license->version ?? '')),
            ])->values(),

            'software' => $asset->licenseAssignments->map(fn ($row) => [
                'id'           => $row->id,
                'name'         => trim(($row->license->name ?? '-') . ' ' . ($row->license->version ?? '')),
                'installed_at' => optional($row->installed_at)->format('d M Y'),
                'removed_at'   => optional($row->removed_at)->format('d M Y'),
            ])->values(),
        ]);
    }

    public function lifecycle(Asset $asset, Request $request)
    {
        $asset->load(['category', 'location', 'employee']);

        $eventType = (string) $request->query('event_type', '');

        return Inertia::render('assets/lifecycle', [
            'asset' => [
                'id' => $asset->id,
                'asset_code' => $asset->asset_code,
                'asset_name' => $asset->asset_name,
                'status' => $asset->status ?? ($asset->employee_id ? 'IN_USE' : 'IN_STORAGE'),
                'location' => $asset->location->location_name ?? null,
                'employee' => $asset->employee ? $asset->employee->name : null,
            ],
            'logs' => $this->lifecycleLogs($asset, $eventType)->map(fn ($log) => [
                'id' => $log->id,
                'event_type' => $log->event_type,
                'event_label' => AssetLifecycleStatus::LIFECYCLE_EVENTS[$log->event_type] ?? $log->event_type,
                'description' => $log->description,
                'event_at' => optional($log->event_at)->format('d M Y, h:i A'),
                'user' => $log->user ? ['name' => $log->user->name] : null,
                'old_values' => $log->old_values,
                'new_values' => $log->new_values,
            ])->values(),
            'movements' => $this->assetMovements($asset),
            'eventTypes' => AssetLifecycleStatus::LIFECYCLE_EVENTS,
            'filters' => ['event_type' => $eventType],
            'exportUrl' => route('assets.lifecycle.export', $asset) . ($eventType ? '?event_type=' . urlencode($eventType) : ''),
        ]);
    }

    /**
     * The same timeline as the lifecycle page, streamed as CSV so the history can
     * leave the app for an audit pack.
     */
    public function exportLifecycle(Asset $asset, Request $request): StreamedResponse
    {
        $eventType = (string) $request->query('event_type', '');
        $logs = $this->lifecycleLogs($asset, $eventType);
        $filename = 'asset-' . $asset->asset_code . '-lifecycle.csv';

        return response()->streamDownload(function () use ($logs) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Event', 'Description', 'Performed by', 'Old values', 'New values']);

            foreach ($logs as $log) {
                fputcsv($handle, [
                    optional($log->event_at)->format('Y-m-d H:i'),
                    AssetLifecycleStatus::LIFECYCLE_EVENTS[$log->event_type] ?? $log->event_type,
                    $log->description,
                    $log->user?->name ?? 'System',
                    $log->old_values ? json_encode($log->old_values) : '',
                    $log->new_values ? json_encode($log->new_values) : '',
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function lifecycleLogs(Asset $asset, string $eventType = '')
    {
        return $asset->lifecycleLogs()
            ->with('user')
            ->when($eventType, fn ($query) => $query->where('event_type', $eventType))
            ->orderByDesc('event_at')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Every custodial change the asset has been through - transfers and disposals
     * merged into one chronological trail with reason, notes, and approver.
     */
    private function assetMovements(Asset $asset): array
    {
        $transfers = $asset->transfers()
            ->with(['requester', 'approver', 'fromLocation', 'toLocation', 'fromEmployee', 'toEmployee'])
            ->get()
            ->map(fn ($transfer) => [
                'id' => 'transfer-' . $transfer->id,
                'kind' => 'Transfer',
                'url' => route('transfers.show', $transfer),
                'from' => $transfer->fromLocation?->location_name ?? $transfer->fromEmployee?->name,
                'to' => $transfer->toLocation?->location_name ?? $transfer->toEmployee?->name,
                'status' => $transfer->status,
                'status_label' => AssetLifecycleStatus::TRANSFER_STATUSES[$transfer->status] ?? $transfer->status,
                'reason' => $transfer->reason,
                'notes' => $transfer->notes,
                'requested_by' => $transfer->requester?->name,
                'approved_by' => $transfer->approver?->name,
                'date' => optional($transfer->transferred_at ?? $transfer->requested_at)->format('d M Y'),
                'sort' => optional($transfer->transferred_at ?? $transfer->requested_at)->timestamp ?? 0,
            ]);

        $disposals = $asset->disposals()
            ->with(['requester', 'approver'])
            ->get()
            ->map(fn ($disposal) => [
                'id' => 'disposal-' . $disposal->id,
                'kind' => 'Disposal',
                'url' => route('disposals.show', $disposal),
                'from' => $asset->location?->location_name,
                'to' => $disposal->method ?: 'Disposed',
                'status' => $disposal->status,
                'status_label' => AssetLifecycleStatus::DISPOSAL_STATUSES[$disposal->status] ?? $disposal->status,
                'reason' => $disposal->reason,
                'notes' => $disposal->notes,
                'requested_by' => $disposal->requester?->name,
                'approved_by' => $disposal->approver?->name,
                'date' => optional($disposal->disposed_at ?? $disposal->requested_at)->format('d M Y'),
                'sort' => optional($disposal->disposed_at ?? $disposal->requested_at)->timestamp ?? 0,
            ]);

        return $transfers->concat($disposals)
            ->sortByDesc('sort')
            ->values()
            ->all();
    }

    public function edit(Asset $asset)
    {
        $asset->load(['computerSpec', 'peripheralSpec', 'printerSpec', 'networkDeviceSpec']);

        return Inertia::render('assets/form', $this->formData() + [
            'asset' => [
                'id'              => $asset->id,
                'asset_code'      => $asset->asset_code,
                'asset_name'      => $asset->asset_name,
                'brand'           => $asset->brand,
                'model'           => $asset->model,
                'serial_number'   => $asset->serial_number,
                'description'     => $asset->description,
                'added_date'      => optional($asset->added_date)->format('Y-m-d'),
                'vendor'          => $asset->vendor,
                'invoice_no'      => $asset->invoice_no,
                'purchase_date'   => optional($asset->purchase_date)->format('Y-m-d'),
                'purchase_cost'   => $asset->purchase_cost,
                'warranty_start'  => optional($asset->warranty_start)->format('Y-m-d'),
                'warranty_end'    => optional($asset->warranty_end)->format('Y-m-d'),
                'status'          => $asset->status,
                'condition'       => $asset->condition,
                'category_id'     => (string) $asset->category_id,
                'location_id'     => (string) $asset->location_id,
                'employee_id'     => (string) ($asset->employee_id ?? ''),
                'parent_asset_id' => (string) ($asset->parent_asset_id ?? ''),
                'image_url'       => $asset->image ? Storage::url($asset->image) : null,
            ],
            'spec'  => (object) $this->specService->current($asset),
        ]);
    }

    public function update(Request $request, Asset $asset)
    {
        $assetType = $this->assetTypeFor($request->input('category_id'));

        $validated = $request->validate(
            $this->baseRules($asset->id) + $this->prefixedSpecRules($assetType)
        );

        $specData = $validated['spec'] ?? [];
        unset($validated['spec'], $validated['image']);

        DB::beginTransaction();
        $newImagePath = null;
        $qrPath = null;

        try {
            $before = $asset->toArray();
            $newImagePath = $asset->image;
            if ($request->hasFile('image')) {
                if ($asset->image && Storage::disk('public')->exists($asset->image)) {
                    Storage::disk('public')->delete($asset->image);
                }
                $file = $request->file('image');
                $fileName = uniqid('asset_') . '.' . $file->getClientOriginalExtension();
                $newImagePath = $file->storeAs('images', $fileName, 'public');
            }

            $oldCode = $asset->asset_code;
            $newCode = $validated['asset_code'];

            $asset->update($validated + [
                'status' => $asset->employee_id ? 'IN_USE' : 'IN_STORAGE',
                'image' => $newImagePath,
            ]);

            $this->specService->sync($asset, $assetType, $specData);

            // AUDIT UPDATE
            AuditTrailService::log(
                'UPDATE',
                'assets',
                $asset->id,
                'Updated asset data',
                $before,
                $asset->fresh()->toArray()
            );

            if ($oldCode !== $newCode) {
                $oldQrPath = 'qrcode/' . $oldCode . '.png';
                if (Storage::disk('public')->exists($oldQrPath)) {
                    Storage::disk('public')->delete($oldQrPath);
                }

                $qrPath = 'qrcode/' . $newCode . '.png';
                $writer = new PngWriter();
                $qrCode = new QrCode($newCode);
                $qrImage = $writer->write($qrCode);
                Storage::disk('public')->put($qrPath, $qrImage->getString());
            }

            DB::commit();

            return redirect()->route('assets.index')->with('success', 'Asset updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($newImagePath && $newImagePath !== $asset->image && Storage::disk('public')->exists($newImagePath)) {
                Storage::disk('public')->delete($newImagePath);
            }
            if ($qrPath && Storage::disk('public')->exists($qrPath)) {
                Storage::disk('public')->delete($qrPath);
            }

            return back()->withInput()->with('error', 'Something went wrong while updating the asset.');
        }
    }

    public function destroy(Asset $asset)
    {
        try {
            $before = $asset->toArray();

            if ($asset->image && Storage::disk('public')->exists($asset->image)) {
                Storage::disk('public')->delete($asset->image);
            }

            $qrPath = 'qrcode/' . $asset->asset_code . '.png';
            if (Storage::disk('public')->exists($qrPath)) {
                Storage::disk('public')->delete($qrPath);
            }

            $asset->delete();

            // AUDIT DELETE
            AuditTrailService::log(
                'DELETE',
                'assets',
                $asset->id,
                'Deleted an asset',
                $before,
                null
            );

            return redirect()->route('assets.index')->with('success', 'Asset deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->route('assets.index')->with('error', 'Failed to delete the asset.');
        }
    }

    // print pdf
    public function exportPdf()
    {
        $assets = $this->visibleAssets(Asset::query())->orderBy('asset_code')->get();

        $pdf = Pdf::loadView('assets.pdf', [
            'assets' => $assets,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream('asset-list.pdf');
    }

    public function exportFullReport()
    {
        $assets = $this->visibleAssets(Asset::with(['category', 'location', 'employee']))
            ->orderBy('asset_code')
            ->get();

        $pdf = Pdf::loadView('assets.full-asset-report', [
            'assets' => $assets,
            'date' => now()->format('d-m-Y'),
            'total' => $assets->count(),
        ])->setPaper('A4', 'portrait');

        return $pdf->download('full-asset-report.pdf');
    }

    public function exportExcel()
    {
        return Excel::download(new AssetExport(auth()->user()), 'company-asset-data.xlsx');
    }

    /**
     * =========================
     * HELPERS
     * =========================
     */

    public const STATUSES = [
        'IN_USE'       => 'In use',
        'IN_STORAGE'   => 'In storage',
        'UNDER_REPAIR' => 'Under repair',
        'RETIRED'      => 'Retired',
        'DISPOSED'     => 'Disposed',
    ];

    public const CONDITIONS = [
        'NEW'  => 'New',
        'GOOD' => 'Good',
        'FAIR' => 'Fair',
        'POOR' => 'Poor',
    ];

    /** Shared data for the create and edit forms. */
    private function formData(): array
    {
        return [
            'categories' => AssetCategory::orderBy('category_name')
                ->get(['id', 'category_name', 'asset_type']),
            'locations'  => AssetLocation::orderBy('location_name')->get()
                ->map(fn ($l) => ['id' => $l->id, 'label' => $l->location_name])->values(),
            'computers'  => Asset::whereHas('category', fn ($q) => $q->where('asset_type', 'COMPUTER'))
                ->orderBy('asset_name')->get()
                ->map(fn ($a) => ['id' => $a->id, 'label' => $a->asset_code . ' - ' . $a->asset_name])->values(),
            'statuses'   => self::STATUSES,
            'conditions' => self::CONDITIONS,
        ];
    }

    /**
     * Headline counts for the cards above the list. Uses the stored status so
     * the cards and the Status column can never disagree.
     *
     * @return array<int, array<string, mixed>>
     */
    private function indexSummary(): array
    {
        $counts = $this->visibleAssets(Asset::query())
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $of = fn (string ...$keys) => array_sum(array_map(fn ($key) => (int) ($counts[$key] ?? 0), $keys));

        return [
            [
                'label' => 'Total assets',
                'value' => (int) $counts->sum(),
                'description' => 'Every asset on record',
                'status' => null,
                'tone' => 'neutral',
            ],
            [
                'label' => 'In use',
                'value' => $of('IN_USE'),
                'description' => 'Deployed and in service',
                'status' => 'IN_USE',
                'tone' => 'success',
            ],
            [
                'label' => 'In storage',
                'value' => $of('IN_STORAGE'),
                'description' => 'Available to allocate',
                'status' => 'IN_STORAGE',
                'tone' => 'neutral',
            ],
            [
                'label' => 'Under repair',
                'value' => $of('UNDER_REPAIR'),
                'description' => 'Out of service for maintenance',
                'status' => 'UNDER_REPAIR',
                'tone' => $of('UNDER_REPAIR') > 0 ? 'warning' : 'neutral',
            ],
            [
                'label' => 'Retired & disposed',
                'value' => $of('RETIRED', 'DISPOSED'),
                'description' => 'No longer part of the estate',
                'status' => 'RETIRED,DISPOSED',
                'tone' => $of('RETIRED', 'DISPOSED') > 0 ? 'danger' : 'neutral',
            ],
        ];
    }

    private function visibleAssets($query)
    {
        $user = auth()->user();

        if ($user?->canonicalRole() === 'employee') {
            return $query->where('employee_id', $user->employee?->id ?? 0);
        }

        return $query;
    }

    private function ensureVisible(Asset $asset): void
    {
        if (! $this->visibleAssets(Asset::query())->whereKey($asset->id)->exists()) {
            abort(403, 'You can only view assets assigned to you.');
        }
    }

    /** The asset_type declared by the chosen category. */
    private function assetTypeFor($categoryId): string
    {
        return AssetCategory::find($categoryId)?->asset_type ?? 'OTHER';
    }

    private function baseRules(?int $ignoreId = null): array
    {
        $codeUnique = 'unique:assets,asset_code' . ($ignoreId ? ',' . $ignoreId : '');
        $serialUnique = 'unique:assets,serial_number' . ($ignoreId ? ',' . $ignoreId : '');

        return [
            'asset_code'      => 'required|string|max:35|' . $codeUnique,
            'asset_name'      => 'required|string|max:150',
            'brand'           => 'nullable|string|max:100',
            'model'           => 'nullable|string|max:100',
            'serial_number'   => 'nullable|string|max:100|' . $serialUnique,
            'description'     => 'nullable|string',
            'added_date'      => 'required|date',
            'vendor'          => 'nullable|string|max:100',
            'invoice_no'      => 'nullable|string|max:60',
            'purchase_date'   => 'nullable|date',
            'purchase_cost'   => 'nullable|numeric|min:0',
            'warranty_start'  => 'nullable|date',
            'warranty_end'    => 'nullable|date|after_or_equal:warranty_start',
            'status'          => 'nullable',
            'condition'       => 'required|in:' . implode(',', array_keys(self::CONDITIONS)),
            'category_id'     => 'required|exists:asset_categories,id',
            'location_id'     => 'required|exists:asset_locations,id',
            'parent_asset_id' => 'nullable|exists:assets,id',
            'image'           => 'nullable|image|mimes:jpeg,jpg,png|max:4096',
        ];
    }

    /** Spec rules, re-keyed to the spec[...] input group. */
    private function prefixedSpecRules(string $assetType): array
    {
        $rules = [];

        foreach ($this->specService->rules($assetType) as $field => $rule) {
            $rules['spec.' . $field] = $rule;
        }

        return $rules;
    }
}
