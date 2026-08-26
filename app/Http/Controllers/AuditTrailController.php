<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditTrailController extends Controller
{
    /**
     * =========================
     * AUDIT LOG LIST
     * =========================
     * ADMIN only
     */
    public function index(Request $request)
    {
        $query = AuditLog::with('user')
            ->orderByDesc('occurred_at');

        // Filter action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter table
        if ($request->filled('table')) {
            $query->where('table_name', $request->table);
        }

        $logs = $query->paginate(20)->withQueryString();

        // Dropdown filter
        $actions = AuditLog::select('action')->distinct()->pluck('action');
        $tables  = AuditLog::select('table_name')->distinct()->pluck('table_name');

        return view('audit.index', compact(
            'logs',
            'actions',
            'tables'
        ));
    }

    /**
     * =========================
     * AUDIT LOG DETAIL
     * =========================
     */
    public function show(AuditLog $auditLog)
    {
        return view('audit.show', compact('auditLog'));
    }
}
