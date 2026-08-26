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
        <h1>Assets</h1>
        <div class="ml-auto">
            <a href="{{ route('assets.export.excel') }}" class="btn btn-success mr-2">
                <i class="fa fa-file-excel"></i> Export Excel
            </a>

            <a href="{{ route('assets.export.pdf') }}" target="_blank" class="btn btn-danger mr-2">
                <i class="fa fa-file-pdf"></i> Export QR Codes
            </a>

            <a href="{{ route('assets.export.full') }}" target="_blank" class="btn btn-danger mr-2">
                <i class="fa fa-file-pdf"></i> Export Report
            </a>

            <a href="{{ route('assets.create') }}" class="btn btn-primary">
                <i class="fa fa-plus"></i> Add Asset
            </a>
        </div>
    </div>

    <div class="section-body">
        @if (session()->has('success'))
            <div class="alert alert-success" role="alert">{{ session('success') }}</div>
        @endif
        @if (session()->has('error'))
            <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
        @endif

        <div class="card card-primary">
            <div class="card-body">

                <form method="GET" class="form-row align-items-end mb-3">
                    <div class="col-md-3">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="">-- All Statuses --</option>
                            @foreach ($statuses as $key => $text)
                                <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                    {{ $text }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Asset Type</label>
                        <select name="asset_type" class="form-control">
                            <option value="">-- All Types --</option>
                            @foreach ($assetTypes as $key => $text)
                                <option value="{{ $key }}" {{ request('asset_type') == $key ? 'selected' : '' }}>
                                    {{ $text }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary"><i class="fa fa-filter"></i> Filter</button>
                        <a href="{{ route('assets.index') }}" class="btn btn-light">Reset</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table id="table_id" class="table table-bordered table-hover table-striped table-sm">
                        <thead>
                            <tr>
                                <th width="4%">No</th>
                                <th width="8%">Image</th>
                                <th>Asset Code</th>
                                <th>Name</th>
                                <th>Brand</th>
                                <th>Model</th>
                                <th>Serial</th>
                                <th>Category</th>
                                <th>Assigned To</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th width="16%">Options</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($assets as $asset)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>

                                    <td class="text-center">
                                        @if ($asset->image && Storage::disk('public')->exists($asset->image))
                                            <img src="{{ Storage::url($asset->image) }}" alt="asset image"
                                                 style="width:56px; height:56px; object-fit:cover; border-radius:4px;">
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    <td><b>{{ $asset->asset_code }}</b></td>
                                    <td>{{ $asset->asset_name }}</td>
                                    <td>{{ $asset->brand ?? '-' }}</td>
                                    <td>{{ $asset->model ?? '-' }}</td>
                                    <td>{{ $asset->serial_number ?? '-' }}</td>
                                    <td>{{ $asset->category->category_name ?? '-' }}</td>
                                    <td>{{ $asset->employee->name ?? '-' }}</td>
                                    <td>{{ $asset->location->location_name ?? '-' }}</td>
                                    <td>
                                        <span class="badge {{ $statusColors[$asset->status] ?? 'badge-secondary' }}">
                                            {{ $label($asset->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('assets.show', $asset->id) }}" class="btn btn-success btn-sm">
                                            Detail
                                        </a>
                                        <a href="{{ route('assets.edit', $asset->id) }}" class="btn btn-warning btn-sm">
                                            Edit
                                        </a>
                                        <form id="delete-form-{{ $asset->id }}"
                                              action="{{ route('assets.destroy', $asset->id) }}"
                                              method="POST" class="d-inline">
                                            @method('DELETE')
                                            @csrf
                                            <button type="button" class="btn btn-danger btn-sm swal-confirm"
                                                    data-form="delete-form-{{ $asset->id }}">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            $('#table_id').DataTable();
        });
    </script>
@endsection
