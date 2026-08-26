@extends('layouts.main')

@section('content')
<div class="section-header">
    <h1>Asset Depreciation</h1>
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
            <h4>Assets & Depreciation</h4>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="table-depreciation">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Asset Code</th>
                            <th>Asset Name</th>
                            <th>Method</th>
                            <th>Acquisition Cost</th>
                            <th>Latest Book Value</th>
                            <th>Latest Period</th>
                            <th>Status</th>
                            <th width="180">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($assets as $asset)
                            @php
                                $last = $asset->monthlyDepreciations->first();
                                $setting = $asset->depreciationSetting;
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $asset->asset_code }}</td>
                                <td>{{ $asset->asset_name }}</td>
                                <td>
                                    {{ $setting?->method ?? '-' }}
                                </td>
                                <td>
                                    {{ $setting ? number_format($setting->acquisition_cost, 0, ',', '.') : '-' }}
                                </td>
                                <td>
                                    {{ $last ? number_format($last->ending_book_value, 0, ',', '.') : '-' }}
                                </td>
                                <td>
                                    {{ $last?->period ?? '-' }}
                                </td>
                                <td>
                                    @if (!$setting)
                                        <span class="badge badge-secondary">Not Configured</span>
                                    @elseif ($setting->is_disposed)
                                        <span class="badge badge-danger">Disposed</span>
                                    @else
                                        <span class="badge badge-success">Active</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('depreciation.show', $asset->id) }}"
                                       class="btn btn-info btn-sm">
                                        Detail
                                    </a>

                                    @if (auth()->user()->inRoles(['admin','manager']) && $setting && !$setting->is_disposed)
                                        <form action="{{ route('depreciation.depreciate', $asset->id) }}"
                                              method="POST"
                                              class="d-inline">
                                            @csrf
                                            <button type="submit"
                                                    class="btn btn-warning btn-sm"
                                                    onclick="return confirm('Generate this month's depreciation?')">
                                                Depreciate
                                            </button>
                                        </form>
                                    @endif
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
        $('#table-depreciation').DataTable();
    });
</script>
@endsection
