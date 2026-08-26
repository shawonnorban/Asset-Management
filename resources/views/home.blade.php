@extends('layouts.main')

@section('content')
<div class="section-header">
    <h1>Dashboard</h1>
</div>

<div class="section-body">

{{-- =========================
    DASHBOARD ADMIN
========================= --}}
@if (auth()->user()->role->role === 'admin')

    {{-- ===== ROW CARD RINGKASAN ===== --}}
    <div class="row">

        {{-- CARD: Total Assets --}}
        <div class="col-lg-3">
            <div class="card card-primary">
                <div class="card-header">Total Assets</div>
                <div class="card-body">
                    <p>{{ $totalAssets ?? 0 }} Inventory</p>
                </div>
            </div>
        </div>

        {{-- CARD: Total Locations --}}
        <div class="col-lg-3">
            <div class="card card-danger">
                <div class="card-header">Total Locations</div>
                <div class="card-body">
                    <p>{{ $totalLocations ?? 0 }} Location</p>
                </div>
            </div>
        </div>

        {{-- CARD: Total Accounts --}}
        <div class="col-lg-3">
            <div class="card card-warning">
                <div class="card-header">Total Accounts</div>
                <div class="card-body">
                    <p>{{ $totalAccounts ?? 0 }} Account</p>
                </div>
            </div>
        </div>

        {{-- CARD: Reports In Progress --}}
        <div class="col-lg-3">
            <div class="card card-success">
                <div class="card-header">Reports In Progress</div>
                <div class="card-body">
                    <p>{{ $pelaporanProses ?? 0 }} Report</p>
                </div>
            </div>
        </div>

    </div>

    {{-- ===== ROW TABLE ===== --}}
    <div class="row">

        {{-- AKTIVITAS AKUN / AUDIT LOG --}}
        <div class="col-lg-6">
            <div class="card card-warning">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Account Activity</span>
                    <a href="{{ route('audit.index') ?? '#' }}" class="btn btn-warning btn-sm">
                        Audit Log
                    </a>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Account</th>
                                <th>Time</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($auditLogs as $log)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        {{ $log->user_name ?? 'System' }}
                                    </td>
                                    <td>
                                        {{ optional($log->occurred_at)->format('d-m-Y H:i') }}
                                    </td>
                                    <td>
                                        @if ($log->action === 'CREATE')
                                            <span class="badge badge-success">CREATE</span>
                                        @elseif ($log->action === 'UPDATE')
                                            <span class="badge badge-info">UPDATE</span>
                                        @elseif ($log->action === 'DELETE')
                                            <span class="badge badge-danger">DELETE</span>
                                        @else
                                            <span class="badge badge-secondary">{{ $log->action }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        No system activity yet
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- STATUS AKUN --}}
        <div class="col-lg-6">
            <div class="card card-primary">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Account Status</span>
                    <a href="#" class="btn btn-primary btn-sm">
                        Status Login
                    </a>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Account</th>
                                <th>Status</th>
                                <th>Last Login</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($usersStatus as $user)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        {{ $user->name }}
                                        <br>
                                        <small class="text-muted">{{ $user->role->role ?? '-' }}</small>
                                    </td>
                                    <td>
                                        @if ($user->is_online)
                                            <span class="badge badge-success">Online</span>
                                        @else
                                            <span class="badge badge-secondary">Offline</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $user->last_login_at
                                            ? $user->last_login_at->format('d M Y H:i')
                                            : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        No account data
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>


{{-- =========================
    DASHBOARD USER (STAFF)
========================== --}}
@elseif (auth()->user()->role->role === 'staff')

<div class="row">

    {{-- Total Assets --}}
    <div class="col-lg-3">
        <div class="card card-primary">
            <div class="card-header">Total Assets</div>
            <div class="card-body">
                <p>{{ $totalAssets ?? 0 }} Asset</p>
            </div>
        </div>
    </div>

    {{-- Total Locations --}}
    <div class="col-lg-3">
        <div class="card card-danger">
            <div class="card-header">Total Locations</div>
            <div class="card-body">
                <p>{{ $totalLocations ?? 0 }} Location</p>
            </div>
        </div>
    </div>

    {{-- Total Categories --}}
    <div class="col-lg-3">
        <div class="card card-warning">
            <div class="card-header">Total Categories</div>
            <div class="card-body">
                <p>{{ $totalCategories ?? 0 }} Category</p>
            </div>
        </div>
    </div>

    {{-- Total Stock Takes --}}
    <div class="col-lg-3">
        <div class="card card-success">
            <div class="card-header">Total Stock Takes</div>
            <div class="card-body">
                <p>{{ $totalStockTakes ?? 0 }} Stock Take</p>
            </div>
        </div>
    </div>

</div>
<div class="row">
    <div class="col">
        <div class="card card-primary">

            <div class="card-header">
                Report Status Inventory
                <div class="ml-auto">
                    <a href="{{ url('/review-reports') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-search"></i> Check All
                    </a>
                </div>
            </div>

            <div class="card-body">

                @if ($staffReports->isEmpty())
                    <div class="alert alert-info mb-0">
                        You have not submitted any reports yet.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="text-center">
                                <tr>
                                    <th>No</th>
                                    <th>Title</th>
                                    <th>Asset Name</th>
                                    <th>Status</th>
                                    <th>Location</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($staffReports as $row)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>{{ $row->title }}</td>
                                        <td>{{ $row->asset->asset_name ?? '-' }}</td>
                                        <td class="text-center">
                                            @if ($row->status === 'Pending')
                                                <span class="badge badge-warning">Pending</span>
                                            @elseif (in_array($row->status, ['In Progress','In Review']))
                                                <span class="badge badge-info">In Progress</span>
                                            @elseif ($row->status === 'Completed')
                                                <span class="badge badge-success">Completed</span>
                                            @else
                                                <span class="badge badge-secondary">{{ $row->status }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            {{ $row->asset->location->location_name ?? '-' }}
                                        </td>
                                        <td class="text-center">
                                            {{ $row->created_at->format('d-m-Y') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

            </div>

        </div>
    </div>
</div>

{{-- =========================
    DASHBOARD MANAGER
========================== --}}
@elseif (auth()->user()->role->role === 'manager')

<div class="row">

    <div class="col-lg-3">
        <div class="card card-primary">
            <div class="card-header">Total Assets</div>
            <div class="card-body">
                <p>{{ $totalAssets ?? 0 }} Asset</p>
            </div>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="card card-danger">
            <div class="card-header">Total Locations</div>
            <div class="card-body">
                <p>{{ $totalLocations ?? 0 }} Location</p>
            </div>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="card card-warning">
            <div class="card-header">Depreciated Assets</div>
            <div class="card-body">
                <p>{{ $totalDepreciations ?? 0 }} Asset</p>
            </div>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="card card-success">
            <div class="card-header">Total Stock Takes</div>
            <div class="card-body">
                <p>{{ $totalStockTakes ?? 0 }} Stock Take</p>
            </div>
        </div>
    </div>

</div>
<div class="row">
    <div class="col-lg-6">
        <div class="card card-warning">
            <div class="card-header">
                Incoming Reports
                <div class="ml-auto">
                    <a href="{{ url('/incoming-reports') }}" class="btn btn-warning btn-sm">
                        View All
                    </a>
                </div>
            </div>

            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead class="text-center">
                        <tr>
                            <th>No</th>
                            <th>Title</th>
                            <th>Asset</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($incomingReports as $row)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $row->title }}</td>
                                <td>{{ $row->asset->asset_name ?? '-' }}</td>
                                <td class="text-center">
                                    @if ($row->status === 'Pending')
                                        <span class="badge badge-warning">Pending</span>
                                    @elseif (in_array($row->status, ['In Progress','In Review']))
                                        <span class="badge badge-info">In Progress</span>
                                    @elseif ($row->status === 'Completed')
                                        <span class="badge badge-success">Completed</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $row->status }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    No reports
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
   <div class="col-lg-6">
    <div class="card card-primary">
        <div class="card-header">
            Asset Depreciation
            <div class="ml-auto">
                <a href="{{ route('depreciation.index') }}" class="btn btn-primary btn-sm">
                    Detail Depreciation
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="text-center">
                        <tr>
                            <th>No</th>
                            <th>Asset</th>
                            <th>Latest Book Value</th>
                            <th>Latest Period</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($latestDepreciations as $asset)
                            @php
                                $last = $asset->monthlyDepreciations->first();
                            @endphp
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $asset->asset_name }}</td>
                                <td class="text-right">
                                    Rp {{ number_format($last->ending_book_value ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    {{ isset($last->period) 
                                        ? \Carbon\Carbon::parse($last->period)->format('m-Y') 
                                        : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    No depreciation data yet
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

</div>

@endif

</div>
@endsection

