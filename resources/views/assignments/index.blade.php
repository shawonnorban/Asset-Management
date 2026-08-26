@extends('layouts.main')

@php
    $label = fn ($v) => $v ? ucwords(strtolower(str_replace('_', ' ', $v))) : '-';
@endphp

@section('content')
    <div class="section-header">
        <h1>Assets In Use</h1>
        <div class="ml-auto">
            <a href="{{ route('assets.index') }}" class="btn btn-primary">
                <i class="fa fa-cubes"></i> All Assets
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

        <div class="card card-primary">
            <div class="card-header">
                <h4>Open Handovers</h4>
                <div class="card-header-action">
                    <span class="badge badge-primary">{{ $assignments->count() }} assigned</span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table_id" class="table table-bordered table-hover table-striped table-sm">
                        <thead>
                            <tr>
                                <th width="4%">No</th>
                                <th>Asset</th>
                                <th>Category</th>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Location</th>
                                <th>Assigned On</th>
                                <th>Condition</th>
                                <th>Handed By</th>
                                <th width="8%">Options</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($assignments as $row)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <b>{{ $row->asset->asset_code ?? '-' }}</b><br>
                                        <small class="text-muted">{{ $row->asset->asset_name ?? '' }}</small>
                                    </td>
                                    <td>{{ $row->asset->category->category_name ?? '-' }}</td>
                                    <td>
                                        {{ $row->employee->name ?? '-' }}<br>
                                        <small class="text-muted">{{ $row->employee->employee_code ?? '' }}</small>
                                    </td>
                                    <td>{{ $row->employee->department->name ?? '-' }}</td>
                                    <td>{{ $row->location->location_name ?? ($row->asset->location->location_name ?? '-') }}</td>
                                    <td>{{ optional($row->assigned_at)->format('d M Y') }}</td>
                                    <td>{{ $label($row->condition_on_assign) }}</td>
                                    <td>{{ $row->handler->name ?? '-' }}</td>
                                    <td>
                                        <a href="{{ route('assets.show', $row->asset_id) }}"
                                           class="btn btn-success btn-sm">Detail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center">No asset is currently assigned.</td>
                                </tr>
                            @endforelse
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
