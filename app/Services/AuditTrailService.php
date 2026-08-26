<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditTrailService
{
    /**
     * Store an audit log entry
     */
    public static function log(
        string $action,
        string $table,
        $rowId = null,
        ?string $message = null,
        $before = null,
        $after = null
    ): void {
        AuditLog::create([
            'occurred_at' => now(),
            'user_id'     => Auth::id(),
            'user_name'   => Auth::user()->name ?? 'SYSTEM',
            'action'      => $action,
            'table_name'  => $table,
            'row_id'      => $rowId,
            'message'     => $message,
            'before_data' => $before,
            'after_data'  => $after,
            'url'         => Request::fullUrl(),
            'ip_address'  => Request::ip(),
            'http_method' => Request::method(),
            'created_at'  => now(),
        ]);
    }

    /**
     * Shortcut for CREATE
     */
    public static function created(
        string $tableName,
        int $rowId,
        array $after,
        ?string $message = null
    ): void {
        self::log('CREATE', $tableName, $rowId, $message, null, $after);
    }

    /**
     * Shortcut for UPDATE
     */
    public static function updated(
        string $tableName,
        int $rowId,
        array $before,
        array $after,
        ?string $message = null
    ): void {
        self::log('UPDATE', $tableName, $rowId, $message, $before, $after);
    }

    /**
     * Shortcut for DELETE
     */
    public static function deleted(
        string $tableName,
        int $rowId,
        array $before,
        ?string $message = null
    ): void {
        self::log('DELETE', $tableName, $rowId, $message, $before, null);
    }

    /**
     * Shortcut for custom actions
     * (FINAL_STOCK_TAKE, DISPOSAL, LOGIN, etc.)
     */
    public static function action(
        string $action,
        string $tableName,
        ?int $rowId = null,
        ?array $before = null,
        ?array $after = null,
        ?string $message = null
    ): void {
        self::log($action, $tableName, $rowId, $message, $before, $after);
    }
}
