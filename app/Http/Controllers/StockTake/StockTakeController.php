<?php

namespace App\Http\Controllers\StockTake;

use App\Http\Controllers\Controller;
use App\Models\StockTake;
use App\Models\StockTakeDetail;
use App\Services\AuditTrailService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class StockTakeController extends Controller
{
    /**
     * ===============================
     * STOCK TAKE LIST
     * ===============================
     */
    public function index()
    {
        $stockTakes = StockTake::with('user')
            ->orderByDesc('stock_take_date')
            ->get();

        return view('stock-takes.index', compact('stockTakes'));
    }

    /**
     * ===============================
     * NEW STOCK TAKE FORM
     * ===============================
     */
    public function create()
    {
        return view('stock-takes.create');
    }

    /**
     * ===============================
     * STORE A NEW STOCK TAKE
     * ===============================
     */
    public function store(Request $request, AuditTrailService $auditTrailService)
    {
        $request->validate([
            'name'            => 'required|string|max:100',
            'stock_take_date' => 'required|date',
        ]);

        $date = Carbon::parse($request->stock_take_date)->format('Ymd');
        $stockTakeCode = 'STK-' . $date . '-' . strtoupper(Str::random(4));

        $stockTake = StockTake::create([
            'stock_take_code' => $stockTakeCode,
            'name'            => $request->name,
            'stock_take_date' => $request->stock_take_date,
            'status'          => 'DRAFT',
            'user_id'         => Auth::id(),
        ]);

        // =========================
        // AUDIT TRAIL
        // =========================
        $auditTrailService->log(
            action: 'CREATE_STOCK_TAKE',
            table: 'stock_takes',
            rowId: $stockTake->id,
            message: 'Created a new stock take: ' . $stockTake->stock_take_code,
            before: null,
            after: $stockTake->toArray()
        );

        return redirect()
            ->route('stock-takes.show', $stockTake->id)
            ->with('success', 'Stock take created successfully.');
    }

    /**
     * ===============================
     * STOCK TAKE DETAIL
     * ===============================
     */
    public function show(StockTake $stockTake)
    {
        $stockTake->load('user');

        $details = $stockTake->details()
            ->with(['asset', 'location', 'employee.department', 'user'])
            ->orderBy('id')
            ->get();

        return view('stock-takes.show', compact('stockTake', 'details'));
    }

    /**
     * ===============================
     * FINALIZE A STOCK TAKE
     * ===============================
     */
    public function finalize(StockTake $stockTake, AuditTrailService $auditTrailService)
    {
        if ($stockTake->status === 'FINAL') {
            return back()->with('error', 'This stock take has already been finalized.');
        }

        if ($stockTake->details()->count() === 0) {
            return back()->with('error', 'There is no stock take data yet.');
        }

        $before = $stockTake->toArray();

        $stockTake->update([
            'status' => 'FINAL',
        ]);

        // =========================
        // AUDIT TRAIL
        // =========================
        $auditTrailService->log(
            action: 'FINALIZE_STOCK_TAKE',
            table: 'stock_takes',
            rowId: $stockTake->id,
            message: 'Finalized stock take: ' . $stockTake->stock_take_code,
            before: $before,
            after: $stockTake->fresh()->toArray()
        );

        return redirect()
            ->route('stock-takes.index')
            ->with('success', 'Stock take finalized successfully.');
    }

    /**
     * ===============================
     * EXPORT STOCK TAKE PDF
     * ===============================
     */
    public function pdf(StockTake $stockTake, AuditTrailService $auditTrailService)
    {
        if ($stockTake->status !== 'FINAL') {
            return redirect()
                ->route('stock-takes.show', $stockTake->id)
                ->with('error', 'This stock take has not been finalized yet.');
        }

        $details = StockTakeDetail::with([
                'asset.category',
                'asset.location',
                'employee.department',
                'user'
            ])
            ->where('stock_take_id', $stockTake->id)
            ->orderBy('id')
            ->get();

        $summary = [
            'PRESENT'   => $details->where('physical_status', 'PRESENT')->count(),
            'DAMAGED'   => $details->where('physical_status', 'DAMAGED')->count(),
            'NOT_FOUND' => $details->where('physical_status', 'NOT_FOUND')->count(),
            'LOST'      => $details->where('physical_status', 'LOST')->count(),
            'TOTAL'     => $details->count(),
        ];

        // =========================
        // AUDIT TRAIL
        // =========================
        $auditTrailService->log(
            action: 'EXPORT_STOCK_TAKE_PDF',
            table: 'stock_takes',
            rowId: $stockTake->id,
            message: 'Exported stock take PDF: ' . $stockTake->stock_take_code,
            before: null,
            after: null
        );

        $pdf = Pdf::loadView('stock-takes.pdf', [
            'stockTake' => $stockTake,
            'details'   => $details,
            'summary'   => $summary,
        ])->setPaper('A4', 'landscape');

        return $pdf->stream(
            'stock-take-report-' . $stockTake->stock_take_code . '.pdf'
        );
    }
}
