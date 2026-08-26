<?php

namespace App\Http\Controllers\StockTake;

use App\Http\Controllers\Controller;
use App\Models\StockTake;
use App\Models\StockTakeDetail;
use App\Models\Asset;
use App\Models\AssetLocation;
use App\Models\Employee;
use App\Services\AuditTrailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockTakeDetailController extends Controller
{
    /**
     * ===============================
     * STOCK TAKE INPUT FORM
     * ===============================
     */
    public function create(StockTake $stockTake)
    {
        if ($stockTake->status === 'FINAL') {
            return redirect()
                ->route('stock-takes.show', $stockTake->id)
                ->with('error', 'This stock take has already been finalized.');
        }

        $assetLocations = AssetLocation::orderBy('location_name')->get();
        $employees      = Employee::orderBy('name')->get();

        $details = StockTakeDetail::with(['asset'])
            ->where('stock_take_id', $stockTake->id)
            ->orderBy('id', 'DESC')
            ->get();

        return view('stock-takes.input', compact(
            'stockTake',
            'assetLocations',
            'employees',
            'details'
        ));
    }

    /**
     * ===============================
     * AJAX: GET ASSET DATA BY QR
     * ===============================
     */
    public function getAssetData(Request $request)
    {
        $code = $request->query('code');

        $response = [
            'found'       => false,
            'id'          => null,
            'asset_code'  => null,
            'asset_name'  => null,
            'location_id' => null,
            'location'    => null,
            'employee_id' => null,
            'employee'    => null,
            'department'  => null,
        ];

        if (! $code) {
            return response()->json($response);
        }

        $asset = Asset::with(['location', 'employee'])
            ->where('asset_code', $code)
            ->first();

        if (! $asset) {
            return response()->json($response);
        }

        return response()->json([
            'found'       => true,
            'id'          => $asset->id,
            'asset_code'  => $asset->asset_code,
            'asset_name'  => $asset->asset_name,
            'location_id' => $asset->location?->id,
            'location'    => $asset->location?->location_name,
            'employee_id' => $asset->employee?->id,
            'employee'    => $asset->employee?->name,
            'department'  => $asset->employee?->department?->name,
        ]);
    }

    /**
     * ===============================
     * STORE A STOCK TAKE RESULT
     * ===============================
     */
    public function store(
        Request $request,
        StockTake $stockTake,
        AuditTrailService $auditTrailService
    ) {
        if ($stockTake->status === 'FINAL') {
            return redirect()
                ->route('stock-takes.show', $stockTake->id)
                ->with('error', 'This stock take has already been finalized.');
        }

        $request->validate([
            'asset_id'        => 'required|exists:assets,id',
            'physical_status' => 'required|in:PRESENT,NOT_FOUND,DAMAGED,LOST',
            'location_id'     => 'nullable|exists:asset_locations,id',
            'employee_id'     => 'nullable|exists:employees,id',
            'note'            => 'nullable|string|max:500',
        ]);

        $exists = StockTakeDetail::where('stock_take_id', $stockTake->id)
            ->where('asset_id', $request->asset_id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'This asset has already been recorded in this stock take.');
        }

        $detail = StockTakeDetail::create([
            'stock_take_id'   => $stockTake->id,
            'asset_id'        => $request->asset_id,
            'physical_status' => $request->physical_status,
            'location_id'     => $request->location_id,
            'employee_id'     => $request->employee_id,
            'note'            => $request->note,
            'user_id'         => Auth::id(),
        ]);

        // =========================
        // AUDIT TRAIL
        // =========================
        $auditTrailService->log(
            action: 'CREATE_STOCK_TAKE_DETAIL',
            table: 'stock_take_details',
            rowId: $detail->id,
            message: 'Recorded stock take result for asset: ' . $detail->asset->asset_code,
            before: null,
            after: $detail->toArray()
        );

        return redirect()
            ->route('stock-takes.input', $stockTake->id)
            ->with('success', 'Stock take result saved successfully.');
    }

    /**
     * ===============================
     * DELETE A STOCK TAKE DETAIL
     * ===============================
     */
    public function destroy(
        StockTake $stockTake,
        StockTakeDetail $detail,
        AuditTrailService $auditTrailService
    ) {
        if ($stockTake->status === 'FINAL') {
            return back()->with('error', 'This stock take has already been finalized.');
        }

        if ($detail->stock_take_id !== $stockTake->id) {
            abort(403);
        }

        $before = $detail->toArray();
        $assetCode = $detail->asset->asset_code ?? '-';

        $detail->delete();

        // =========================
        // AUDIT TRAIL
        // =========================
        $auditTrailService->log(
            action: 'DELETE_STOCK_TAKE_DETAIL',
            table: 'stock_take_details',
            rowId: $before['id'],
            message: 'Deleted stock take result for asset: ' . $assetCode,
            before: $before,
            after: null
        );

        return back()->with('success', 'Stock take entry deleted successfully.');
    }
}
