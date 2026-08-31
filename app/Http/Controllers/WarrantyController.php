<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Warranty;
use App\Services\AssetLifecycleService;
use App\Support\AssetLifecycleStatus;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WarrantyController extends Controller
{
    public function index()
    {
        $warranties = Warranty::with('asset')->latest('end_date')->paginate(15);

        return Inertia::render('warranties/index', [
            'title' => 'Warranty Management',
            'description' => 'Track asset warranties, expiry alerts, and claim status.',
            'warranties' => $warranties->getCollection()->map(fn ($warranty) => [
                'id' => $warranty->id,
                'asset_id' => $warranty->asset_id,
                'asset_code' => $warranty->asset?->asset_code,
                'asset_name' => $warranty->asset?->asset_name,
                'vendor_name' => $warranty->vendor_name,
                'warranty_type' => $warranty->warranty_type,
                'start_date' => optional($warranty->start_date)->format('d M Y'),
                'end_date' => optional($warranty->end_date)->format('d M Y'),
                'status' => $warranty->status,
                'status_label' => AssetLifecycleStatus::WARRANTY_STATUSES[$warranty->status] ?? $warranty->status,
                'claim_status' => $warranty->claim_status,
            ])->values(),
            'pagination' => $warranties->toArray(),
            'statuses' => AssetLifecycleStatus::WARRANTY_STATUSES,
        ]);
    }

    public function create()
    {
        return Inertia::render('warranties/form', [
            'title' => 'Add warranty',
            'warranty' => null,
            'assets' => Asset::orderBy('asset_code')->get()->map(fn ($asset) => ['id' => $asset->id, 'label' => $asset->asset_code . ' - ' . $asset->asset_name])->values()->all(),
            'statuses' => AssetLifecycleStatus::WARRANTY_STATUSES,
        ]);
    }

    public function store(Request $request, AssetLifecycleService $lifecycle)
    {
        $data = $this->validated($request);
        $data['status'] = $data['status'] ?? Warranty::deriveStatus($data['end_date']);
        $warranty = Warranty::create($data);

        $lifecycle->createLog(
            $warranty->asset,
            'WARRANTY_REGISTERED',
            'Warranty record created for asset ' . ($warranty->asset?->asset_code ?? 'N/A'),
            null,
            [
                'warranty_id' => $warranty->id,
                'vendor_name' => $warranty->vendor_name,
                'end_date' => $warranty->end_date?->toDateString(),
                'status' => $warranty->status,
            ],
            auth()->id(),
        );

        return redirect()->route('warranties.show', $warranty)->with('success', 'Warranty added successfully.');
    }

    public function show(Warranty $warranty)
    {
        $warranty->load('asset');

        return Inertia::render('warranties/show', [
            'title' => 'Warranty details',
            'warranty' => [
                'id' => $warranty->id,
                'asset' => $warranty->asset ? ['id' => $warranty->asset->id, 'code' => $warranty->asset->asset_code, 'name' => $warranty->asset->asset_name] : null,
                'vendor_name' => $warranty->vendor_name,
                'warranty_type' => $warranty->warranty_type,
                'start_date' => optional($warranty->start_date)->format('Y-m-d'),
                'end_date' => optional($warranty->end_date)->format('Y-m-d'),
                'status' => $warranty->status,
                'status_label' => AssetLifecycleStatus::WARRANTY_STATUSES[$warranty->status] ?? $warranty->status,
                'coverage_details' => $warranty->coverage_details,
                'claim_status' => $warranty->claim_status,
            ],
        ]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'asset_id' => ['required', 'exists:assets,id'],
            'vendor_name' => ['nullable', 'string', 'max:150'],
            'warranty_type' => ['nullable', 'string', 'max:100'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', 'string', 'max:30'],
            'coverage_details' => ['nullable', 'string'],
            'claim_status' => ['nullable', 'string', 'max:30'],
        ]);
    }
}
