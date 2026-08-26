@extends('layouts.main')

@php
    $statusColors = [
        'IN_USE' => 'badge-success',
        'IN_STORAGE' => 'badge-secondary',
        'UNDER_REPAIR' => 'badge-warning',
        'RETIRED' => 'badge-dark',
        'DISPOSED' => 'badge-danger',
    ];
    $current = $asset->currentAssignment;
    $label = fn ($v) => $v ? ucwords(strtolower(str_replace('_', ' ', $v))) : '-';
@endphp

@section('content')
    <div class="section-header">
        <h1>Asset Detail</h1>
        <div class="ml-auto">
            <a href="{{ route('assets.edit', $asset->id) }}" class="btn btn-warning">
                <i class="fa fa-edit"></i> Edit
            </a>
            <a href="{{ route('assets.index') }}" class="btn btn-primary">
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
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row">

            {{-- ================= LEFT: identity + specs ================= --}}
            <div class="col-lg-8">

                <div class="card card-primary">
                    <div class="card-header">
                        <h4>{{ $asset->asset_name }}</h4>
                        <div class="card-header-action">
                            <span class="badge {{ $statusColors[$asset->status] ?? 'badge-secondary' }}">
                                {{ $label($asset->status) }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr><th width="180">Asset Code</th><td>{{ $asset->asset_code }}</td></tr>
                            <tr><th>Serial Number</th><td>{{ $asset->serial_number ?? '-' }}</td></tr>
                            <tr><th>Brand / Model</th>
                                <td>{{ $asset->brand ?? '-' }} {{ $asset->model ? '/ ' . $asset->model : '' }}</td></tr>
                            <tr><th>Category</th>
                                <td>
                                    {{ $asset->category->category_name ?? '-' }}
                                    <span class="badge badge-light">{{ $label($asset->asset_type) }}</span>
                                </td></tr>
                            <tr><th>Location</th><td>{{ $asset->location->location_name ?? '-' }}</td></tr>
                            <tr><th>Condition</th><td>{{ $label($asset->condition) }}</td></tr>
                            <tr><th>Date Added</th><td>{{ optional($asset->added_date)->format('d M Y') ?? '-' }}</td></tr>
                            @if ($asset->parentAsset)
                                <tr><th>Attached To</th>
                                    <td>
                                        <a href="{{ route('assets.show', $asset->parentAsset->id) }}">
                                            {{ $asset->parentAsset->asset_code }} - {{ $asset->parentAsset->asset_name }}
                                        </a>
                                    </td></tr>
                            @endif
                            @if ($asset->description)
                                <tr><th>Description</th><td>{{ $asset->description }}</td></tr>
                            @endif
                        </table>
                    </div>
                </div>

                {{-- ---------- specification ---------- --}}
                @php $spec = $asset->spec(); @endphp
                @if ($spec)
                    <div class="card card-primary">
                        <div class="card-header"><h4>Specification</h4></div>
                        <div class="card-body">
                            @includeIf('assets.partials.show-spec-' . strtolower(str_replace('_', '-', $asset->asset_type)))
                        </div>
                    </div>
                @endif

                {{-- ---------- attached peripherals ---------- --}}
                @if ($asset->childAssets->count())
                    <div class="card card-primary">
                        <div class="card-header"><h4>Attached Peripherals</h4></div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm mb-0">
                                    <thead><tr><th>Code</th><th>Name</th><th>Category</th><th>Status</th></tr></thead>
                                    <tbody>
                                        @foreach ($asset->childAssets as $child)
                                            <tr>
                                                <td><a href="{{ route('assets.show', $child->id) }}">{{ $child->asset_code }}</a></td>
                                                <td>{{ $child->asset_name }}</td>
                                                <td>{{ $child->category->category_name ?? '-' }}</td>
                                                <td>{{ $label($child->status) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ---------- installed software ---------- --}}
                <div class="card card-primary">
                    <div class="card-header"><h4>Installed Software</h4></div>
                    <div class="card-body">

                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Software</th><th>Type</th><th>Installed</th><th>Removed</th><th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($asset->licenseAssignments as $row)
                                        <tr class="{{ $row->removed_at ? 'text-muted' : '' }}">
                                            <td>
                                                <a href="{{ route('software-licenses.show', $row->software_license_id) }}">
                                                    {{ $row->license->name ?? '-' }}
                                                </a>
                                                {{ $row->license->version ?? '' }}
                                            </td>
                                            <td>{{ $label($row->license->license_type ?? null) }}</td>
                                            <td>{{ optional($row->installed_at)->format('d M Y') }}</td>
                                            <td>{{ optional($row->removed_at)->format('d M Y') ?? '-' }}</td>
                                            <td>
                                                @if (! $row->removed_at)
                                                    <form method="POST"
                                                          action="{{ route('software-licenses.uninstall', [$asset->id, $row->id]) }}"
                                                          onsubmit="return confirm('Remove this license from the asset?')">
                                                        @csrf @method('PUT')
                                                        <button class="btn btn-danger btn-sm">Remove</button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center">No software recorded yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if ($availableLicenses->count())
                            <form method="POST" action="{{ route('software-licenses.install', $asset->id) }}"
                                  class="form-row align-items-end">
                                @csrf
                                <div class="col-md-5">
                                    <label>Install License</label>
                                    <select name="software_license_id" class="form-control" required>
                                        <option value="">-- Select Software --</option>
                                        @foreach ($availableLicenses as $license)
                                            <option value="{{ $license->id }}">
                                                {{ $license->name }} ({{ $license->seats_available }} seats free)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label>Installed On</label>
                                    <input type="date" name="installed_at" class="form-control"
                                           value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-4">
                                    <button class="btn btn-primary btn-block">
                                        <i class="fa fa-plus"></i> Install
                                    </button>
                                </div>
                            </form>
                        @else
                            <p class="text-muted mb-0">No license has a free seat right now.</p>
                        @endif

                    </div>
                </div>

                {{-- ---------- issue reports ---------- --}}
                <div class="card card-primary">
                    <div class="card-header"><h4>Issue Report History</h4></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>No</th><th>Title</th><th>Description</th>
                                        <th>Status</th><th>Analysis</th><th>Update</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($issueReports as $issueReport)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $issueReport->title }}</td>
                                            <td>{{ $issueReport->description }}</td>
                                            <td>
                                                <span class="badge
                                                    @if($issueReport->status === 'Pending') badge-warning
                                                    @elseif($issueReport->status === 'In Review') badge-primary
                                                    @elseif($issueReport->status === 'Completed') badge-success
                                                    @endif">
                                                    {{ $issueReport->status }}
                                                </span>
                                            </td>
                                            <td>{{ $issueReport->feedbacks->last()->decision_analysis ?? '-' }}</td>
                                            <td>{{ ($issueReport->updated_at ?? $issueReport->created_at)->format('Y-m-d H:i') }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center">No report history yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ================= RIGHT: image, assignment, purchase ================= --}}
            <div class="col-lg-4">

                <div class="card card-primary">
                    <div class="card-body text-center">
                        @if ($asset->image)
                            <img src="{{ asset('storage/' . $asset->image) }}" alt="asset image"
                                 class="img-fluid mb-3" style="border-radius:5px; max-height:220px; object-fit:contain;">
                        @else
                            <p class="text-muted">No image</p>
                        @endif

                        @if ($qrUrl)
                            <div class="mt-2">
                                <img src="{{ $qrUrl }}" alt="QR Code" style="max-width:150px;">
                                <div><small class="text-muted">{{ $asset->asset_code }}</small></div>
                            </div>
                        @else
                            <p class="text-muted mb-0">QR code not available yet</p>
                        @endif
                    </div>
                </div>

                {{-- ---------- assignment ---------- --}}
                <div class="card card-primary">
                    <div class="card-header"><h4>Assignment</h4></div>
                    <div class="card-body">

                        @if ($current)
                            <table class="table table-sm table-borderless">
                                <tr><th width="110">Holder</th>
                                    <td><b>{{ $current->employee->name ?? '-' }}</b><br>
                                        <small class="text-muted">{{ $current->employee->employee_code ?? '' }}
                                            &middot; {{ $current->employee->department->name ?? '-' }}</small></td></tr>
                                <tr><th>Since</th><td>{{ optional($current->assigned_at)->format('d M Y') }}</td></tr>
                                <tr><th>Condition</th><td>{{ $label($current->condition_on_assign) }}</td></tr>
                                <tr><th>Handed by</th><td>{{ $current->handler->name ?? '-' }}</td></tr>
                            </table>

                            <hr>
                            <h6>Record Return</h6>
                            <form method="POST" action="{{ route('assignments.return', $asset->id) }}">
                                @csrf @method('PUT')
                                <div class="form-group">
                                    <label>Returned On</label>
                                    <input type="date" name="returned_at" class="form-control"
                                           value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="form-group">
                                    <label>Condition On Return</label>
                                    <select name="condition_on_return" class="form-control">
                                        <option value="">-- Unchanged --</option>
                                        @foreach (['NEW', 'GOOD', 'FAIR', 'POOR'] as $c)
                                            <option value="{{ $c }}">{{ ucfirst(strtolower($c)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>New Status</label>
                                    <select name="status" class="form-control" required>
                                        <option value="IN_STORAGE">In storage</option>
                                        <option value="UNDER_REPAIR">Under repair</option>
                                        <option value="RETIRED">Retired</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Note</label>
                                    <textarea name="note" class="form-control" rows="2" maxlength="500"></textarea>
                                </div>
                                <button class="btn btn-danger btn-block">
                                    <i class="fa fa-undo"></i> Record Return
                                </button>
                            </form>
                        @else
                            <p class="text-muted">This asset is not assigned to anyone.</p>

                            <form method="POST" action="{{ route('assignments.store', $asset->id) }}">
                                @csrf
                                <div class="form-group">
                                    <label>Assign To <span class="text-danger">*</span></label>
                                    <select name="employee_id" class="form-control" required>
                                        <option value="">-- Select Employee --</option>
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}">
                                                {{ $employee->name }} ({{ $employee->employee_code }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Assigned On <span class="text-danger">*</span></label>
                                    <input type="date" name="assigned_at" class="form-control"
                                           value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="form-group">
                                    <label>Condition At Handover</label>
                                    <select name="condition_on_assign" class="form-control">
                                        <option value="">-- Select --</option>
                                        @foreach (['NEW', 'GOOD', 'FAIR', 'POOR'] as $c)
                                            <option value="{{ $c }}"
                                                {{ $asset->condition === $c ? 'selected' : '' }}>
                                                {{ ucfirst(strtolower($c)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Note</label>
                                    <textarea name="note" class="form-control" rows="2" maxlength="500"></textarea>
                                </div>
                                <button class="btn btn-success btn-block">
                                    <i class="fa fa-user-check"></i> Assign
                                </button>
                            </form>
                        @endif

                    </div>
                </div>

                {{-- ---------- handover history ---------- --}}
                <div class="card card-primary">
                    <div class="card-header"><h4>Handover History</h4></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead><tr><th>Employee</th><th>From</th><th>To</th></tr></thead>
                                <tbody>
                                    @forelse ($asset->assignments->sortByDesc('assigned_at') as $row)
                                        <tr>
                                            <td>{{ $row->employee->name ?? '-' }}</td>
                                            <td>{{ optional($row->assigned_at)->format('d M Y') }}</td>
                                            <td>
                                                {{ $row->returned_at
                                                    ? $row->returned_at->format('d M Y')
                                                    : '' }}
                                                @unless ($row->returned_at)
                                                    <span class="badge badge-success">current</span>
                                                @endunless
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-center">No handover recorded yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- ---------- purchase & warranty ---------- --}}
                <div class="card card-primary">
                    <div class="card-header"><h4>Purchase &amp; Warranty</h4></div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr><th width="120">Vendor</th><td>{{ $asset->vendor ?? '-' }}</td></tr>
                            <tr><th>Invoice</th><td>{{ $asset->invoice_no ?? '-' }}</td></tr>
                            <tr><th>Purchased</th><td>{{ optional($asset->purchase_date)->format('d M Y') ?? '-' }}</td></tr>
                            <tr><th>Cost</th><td>{{ $asset->purchase_cost ? number_format($asset->purchase_cost, 2) : '-' }}</td></tr>
                            <tr><th>Warranty</th>
                                <td>
                                    @if ($asset->warranty_end)
                                        {{ optional($asset->warranty_start)->format('d M Y') ?? '?' }}
                                        &rarr; {{ $asset->warranty_end->format('d M Y') }}
                                        @if ($asset->isUnderWarranty())
                                            <span class="badge badge-success">active</span>
                                        @else
                                            <span class="badge badge-danger">expired</span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td></tr>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
