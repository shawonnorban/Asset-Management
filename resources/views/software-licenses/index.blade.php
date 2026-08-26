@extends('layouts.main')

@php
    $label = fn ($v) => $v ? ucwords(strtolower(str_replace('_', ' ', $v))) : '-';
@endphp

@section('content')
    <div class="section-header">
        <h1>Software Licenses</h1>
        <div class="ml-auto">
            <a href="{{ route('software-licenses.create') }}" class="btn btn-primary">
                <i class="fa fa-plus"></i> Add License
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
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table_id" class="table table-bordered table-hover table-striped table-sm">
                        <thead>
                            <tr>
                                <th width="4%">No</th>
                                <th>Software</th>
                                <th>Publisher</th>
                                <th>Type</th>
                                <th>Seats</th>
                                <th>Expiry</th>
                                <th width="16%">Options</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($licenses as $license)
                                @php
                                    $free = $license->seats_total - $license->seats_in_use;
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <b>{{ $license->name }}</b>
                                        @if ($license->version)
                                            <small class="text-muted">{{ $license->version }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $license->publisher ?? '-' }}</td>
                                    <td>{{ $label($license->license_type) }}</td>
                                    <td>
                                        {{ $license->seats_in_use }} / {{ $license->seats_total }}
                                        <span class="badge {{ $free > 0 ? 'badge-success' : 'badge-danger' }}">
                                            {{ $free > 0 ? $free . ' free' : 'full' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($license->expiry_date)
                                            {{ $license->expiry_date->format('d M Y') }}
                                            @if ($license->isExpired())
                                                <span class="badge badge-danger">expired</span>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('software-licenses.show', $license->id) }}"
                                           class="btn btn-success btn-sm">Detail</a>
                                        <a href="{{ route('software-licenses.edit', $license->id) }}"
                                           class="btn btn-warning btn-sm">Edit</a>
                                        <form id="delete-license-{{ $license->id }}"
                                              action="{{ route('software-licenses.destroy', $license->id) }}"
                                              method="POST" class="d-inline">
                                            @method('DELETE')
                                            @csrf
                                            <button type="button" class="btn btn-danger btn-sm swal-confirm"
                                                    data-form="delete-license-{{ $license->id }}">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center">No software license recorded yet.</td></tr>
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
