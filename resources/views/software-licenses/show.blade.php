@extends('layouts.main')

@php
    $label = fn ($v) => $v ? ucwords(strtolower(str_replace('_', ' ', $v))) : '-';
@endphp

@section('content')
    <div class="section-header">
        <h1>{{ $license->name }}</h1>
        <div class="ml-auto">
            <a href="{{ route('software-licenses.edit', $license->id) }}" class="btn btn-warning">
                <i class="fa fa-edit"></i> Edit
            </a>
            <a href="{{ route('software-licenses.index') }}" class="btn btn-primary">
                <i class="fa fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="section-body">
        <div class="row">

            <div class="col-lg-4">
                <div class="card card-primary">
                    <div class="card-header"><h4>License</h4></div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr><th width="120">Publisher</th><td>{{ $license->publisher ?? '-' }}</td></tr>
                            <tr><th>Version</th><td>{{ $license->version ?? '-' }}</td></tr>
                            <tr><th>Type</th><td>{{ $label($license->license_type) }}</td></tr>
                            <tr><th>Key</th><td><code>{{ $license->license_key ?? '-' }}</code></td></tr>
                            <tr><th>Seats</th>
                                <td>
                                    {{ $license->seats_used }} / {{ $license->seats_total }} in use
                                    <span class="badge {{ $license->seats_available > 0 ? 'badge-success' : 'badge-danger' }}">
                                        {{ $license->seats_available }} free
                                    </span>
                                </td></tr>
                            <tr><th>Vendor</th><td>{{ $license->vendor ?? '-' }}</td></tr>
                            <tr><th>Invoice</th><td>{{ $license->invoice_no ?? '-' }}</td></tr>
                            <tr><th>Purchased</th><td>{{ optional($license->purchase_date)->format('d M Y') ?? '-' }}</td></tr>
                            <tr><th>Cost</th>
                                <td>{{ $license->purchase_cost ? number_format($license->purchase_cost, 2) : '-' }}</td></tr>
                            <tr><th>Expiry</th>
                                <td>
                                    @if ($license->expiry_date)
                                        {{ $license->expiry_date->format('d M Y') }}
                                        @if ($license->isExpired())
                                            <span class="badge badge-danger">expired</span>
                                        @else
                                            <span class="badge badge-success">active</span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td></tr>
                        </table>

                        @if ($license->note)
                            <div class="alert alert-light mb-0"><b>Note:</b> {{ $license->note }}</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card card-primary">
                    <div class="card-header"><h4>Where It Is Installed</h4></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Asset</th><th>Held By</th><th>Installed</th><th>Removed</th><th>By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($license->assignments->sortByDesc('installed_at') as $row)
                                        <tr class="{{ $row->removed_at ? 'text-muted' : '' }}">
                                            <td>
                                                <a href="{{ route('assets.show', $row->asset_id) }}">
                                                    {{ $row->asset->asset_code ?? '-' }}
                                                </a>
                                                <br><small class="text-muted">{{ $row->asset->asset_name ?? '' }}</small>
                                            </td>
                                            <td>{{ $row->asset->employee->name ?? '-' }}</td>
                                            <td>{{ optional($row->installed_at)->format('d M Y') }}</td>
                                            <td>
                                                @if ($row->removed_at)
                                                    {{ $row->removed_at->format('d M Y') }}
                                                @else
                                                    <span class="badge badge-success">active</span>
                                                @endif
                                            </td>
                                            <td>{{ $row->handler->name ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center">Not installed anywhere yet.</td></tr>
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
