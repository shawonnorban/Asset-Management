@extends('layouts.main')

@php
    $statusColors = [
        'IN_USE' => 'badge-success',
        'IN_STORAGE' => 'badge-secondary',
        'UNDER_REPAIR' => 'badge-warning',
        'RETIRED' => 'badge-dark',
        'DISPOSED' => 'badge-danger',
    ];
    $label = fn ($v) => $v ? ucwords(strtolower(str_replace('_', ' ', $v))) : '-';
@endphp

@section('content')
    <div class="section-header">
        <h1>Employee Detail</h1>
        <div class="ml-auto">
            @if (auth()->user()->inRoles(['admin', 'manager']))
                <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-warning">
                    <i class="fa fa-edit"></i> Edit
                </a>
            @endif
            <a href="{{ route('employees.index') }}" class="btn btn-primary">
                <i class="fa fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="section-body">

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row">

            {{-- ================= LEFT: photo + identity ================= --}}
            <div class="col-lg-4">

                <div class="card card-primary">
                    <div class="card-body text-center">
                        @if ($employee->image && Storage::disk('public')->exists($employee->image))
                            <img src="{{ Storage::url($employee->image) }}" alt="employee photo"
                                 class="img-fluid mb-3"
                                 style="width:180px; height:180px; object-fit:cover; border-radius:50%;">
                        @else
                            <div class="mb-3 mx-auto d-flex align-items-center justify-content-center"
                                 style="width:180px; height:180px; border-radius:50%; background:#f2f3f7; color:#999;">
                                <i class="fa fa-user fa-4x"></i>
                            </div>
                        @endif

                        <h4 class="mb-1">{{ $employee->name }}</h4>
                        <div class="text-muted">{{ $employee->position->name ?? '-' }}</div>
                        <div class="text-muted">
                            <small>{{ $employee->department->name ?? '-' }}</small>
                        </div>
                        <span class="badge badge-primary mt-2">{{ $employee->employee_code }}</span>
                    </div>
                </div>

                <div class="card card-primary">
                    <div class="card-header"><h4>Contact</h4></div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr><th width="110">Mobile</th><td>{{ $employee->mobile ?? '-' }}</td></tr>
                            <tr><th>Mail Address</th>
                                <td>
                                    @if ($employee->mail_address)
                                        <a href="mailto:{{ $employee->mail_address }}">{{ $employee->mail_address }}</a>
                                    @else
                                        -
                                    @endif
                                </td></tr>
                            <tr><th>Location</th><td>{{ $employee->location->location_name ?? '-' }}</td></tr>
                            <tr><th>Join Date</th>
                                <td>{{ optional($employee->join_date)->format('d M Y') ?? '-' }}</td></tr>
                        </table>
                    </div>
                </div>

            </div>

            {{-- ================= RIGHT: details + assets ================= --}}
            <div class="col-lg-8">

                <div class="card card-primary">
                    <div class="card-header"><h4>Personal</h4></div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr><th width="180">Father's Name</th><td>{{ $employee->father_name ?? '-' }}</td></tr>
                            <tr><th>Mother's Name</th><td>{{ $employee->mother_name ?? '-' }}</td></tr>
                            <tr><th>NID Number</th><td>{{ $employee->nid_number ?? '-' }}</td></tr>
                            <tr><th>Present Address</th><td>{{ $employee->present_address ?? '-' }}</td></tr>
                            <tr><th>Permanent Address</th><td>{{ $employee->permanent_address ?? '-' }}</td></tr>
                        </table>
                    </div>
                </div>

                {{-- ---------- assets currently held ---------- --}}
                <div class="card card-primary">
                    <div class="card-header">
                        <h4>Assets Currently Held</h4>
                        <div class="card-header-action">
                            <span class="badge badge-primary">{{ $employee->assets->count() }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th width="8%">Image</th>
                                        <th>Asset Code</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Location</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($employee->assets as $asset)
                                        <tr>
                                            <td class="text-center">
                                                @if ($asset->image && Storage::disk('public')->exists($asset->image))
                                                    <img src="{{ Storage::url($asset->image) }}" alt="asset image"
                                                         style="width:44px; height:44px; object-fit:cover; border-radius:4px;">
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('assets.show', $asset->id) }}">
                                                    {{ $asset->asset_code }}
                                                </a>
                                            </td>
                                            <td>{{ $asset->asset_name }}</td>
                                            <td>{{ $asset->category->category_name ?? '-' }}</td>
                                            <td>{{ $asset->location->location_name ?? '-' }}</td>
                                            <td>
                                                <span class="badge {{ $statusColors[$asset->status] ?? 'badge-secondary' }}">
                                                    {{ $label($asset->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">
                                                No asset is assigned to this employee.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- ---------- handover history ---------- --}}
                <div class="card card-primary">
                    <div class="card-header"><h4>Handover History</h4></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Asset</th><th>From</th><th>To</th>
                                        <th>Condition</th><th>Handed By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($employee->assetAssignments->sortByDesc('assigned_at') as $row)
                                        <tr>
                                            <td>
                                                <a href="{{ route('assets.show', $row->asset_id) }}">
                                                    {{ $row->asset->asset_code ?? '-' }}
                                                </a>
                                                <br><small class="text-muted">{{ $row->asset->asset_name ?? '' }}</small>
                                            </td>
                                            <td>{{ optional($row->assigned_at)->format('d M Y') }}</td>
                                            <td>
                                                @if ($row->returned_at)
                                                    {{ $row->returned_at->format('d M Y') }}
                                                @else
                                                    <span class="badge badge-success">current</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $label($row->condition_on_assign) }}
                                                @if ($row->condition_on_return)
                                                    &rarr; {{ $label($row->condition_on_return) }}
                                                @endif
                                            </td>
                                            <td>{{ $row->handler->name ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No handover recorded yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
