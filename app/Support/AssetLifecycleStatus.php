<?php

namespace App\Support;

class AssetLifecycleStatus
{
    public const MAINTENANCE_STATUSES = [
        'DRAFT' => 'Draft',
        'OPEN' => 'Open',
        'IN_PROGRESS' => 'In Progress',
        'COMPLETED' => 'Completed',
        'CANCELLED' => 'Cancelled',
    ];

    public const WARRANTY_STATUSES = [
        'ACTIVE' => 'Active',
        'EXPIRING_SOON' => 'Expiring Soon',
        'EXPIRED' => 'Expired',
        'CLAIMED' => 'Claimed',
        'VOID' => 'Void',
    ];

    public const TRANSFER_STATUSES = [
        'REQUESTED' => 'Requested',
        'APPROVED' => 'Approved',
        'IN_TRANSIT' => 'In Transit',
        'COMPLETED' => 'Completed',
        'REJECTED' => 'Rejected',
    ];

    public const DISPOSAL_STATUSES = [
        'REQUESTED' => 'Requested',
        'APPROVED' => 'Approved',
        'DISPOSED' => 'Disposed',
        'REJECTED' => 'Rejected',
    ];

    public const LIFECYCLE_EVENTS = [
        'ASSET_CREATED' => 'Asset Created',
        'ASSIGNED' => 'Assigned',
        'RETURNED' => 'Returned',
        'MAINTENANCE_REQUESTED' => 'Maintenance Requested',
        'MAINTENANCE_COMPLETED' => 'Maintenance Completed',
        'TRANSFER_REQUESTED' => 'Transfer Requested',
        'TRANSFER_COMPLETED' => 'Transfer Completed',
        'DISPOSAL_REQUESTED' => 'Disposal Requested',
        'DISPOSAL_COMPLETED' => 'Disposal Completed',
        'WARRANTY_REGISTERED' => 'Warranty Registered',
        'WARRANTY_EXPIRING' => 'Warranty Expiring Soon',
        'WARRANTY_EXPIRED' => 'Warranty Expired',
    ];

    /** Event types that represent a physical or custodial movement of the asset. */
    public const MOVEMENT_EVENTS = [
        'ASSIGNED',
        'RETURNED',
        'TRANSFER_REQUESTED',
        'TRANSFER_COMPLETED',
        'DISPOSAL_REQUESTED',
        'DISPOSAL_COMPLETED',
    ];
}
