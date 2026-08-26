@extends('layouts.main')

@section('content')
<div class="section-header">
    <h1>Asset Depreciation Detail</h1>
    <div class="ml-auto">
        <a href="{{ route('depreciation.index') }}" class="btn btn-primary">
            <i class="fa fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="section-body">
    <div class="card card-primary mb-3">
        <div class="card-header">
            <h4>Asset Information</h4>
        </div>
        <div class="card-body">
            <table class="table table-sm">
                <tr>
                    <th width="200">Asset Code</th>
                    <td>{{ $asset->asset_code }}</td>
                </tr>
                <tr>
                    <th>Asset Name</th>
                    <td>{{ $asset->asset_name }}</td>
                </tr>
                <tr>
                    <th>Category</th>
                    <td>{{ $asset->category->category_name ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Location</th>
                    <td>{{ $asset->location->location_name ?? '-' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="card card-warning mb-3">
        <div class="card-header">
            <h4>Depreciation Setting</h4>
        </div>
        <div class="card-body">
            @if ($asset->depreciationSetting)
                <table class="table table-sm">
                    <tr>
                        <th width="200">Method</th>
                        <td>{{ $asset->depreciationSetting->method }}</td>
                    </tr>
                    <tr>
                        <th>Acquisition Cost</th>
                        <td>
                            Rp {{ number_format($asset->depreciationSetting->acquisition_cost, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <th>Salvage Value</th>
                        <td>
                            Rp {{ number_format($asset->depreciationSetting->salvage_value ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <th>In-Service Date</th>
                        <td>
                            {{ \Carbon\Carbon::parse($asset->depreciationSetting->in_service_date)->format('d-m-Y') }}
                        </td>
                    </tr>
                </table>
            @else
                <div class="alert alert-secondary">
                    No depreciation setting has been created for this asset.
                </div>
            @endif
        </div>
    </div>

    @if ($asset->depreciationSetting && $asset->depreciationSetting->is_disposed)
        <div class="alert alert-danger mb-3">
            <h6 class="mb-2">
                <i class="fa fa-ban"></i> Asset Disposed
            </h6>
            <table class="table table-sm mb-0">
                <tr>
                    <th width="200">Disposal Reason</th>
                    <td>{{ $asset->depreciationSetting->disposal_reason }}</td>
                </tr>
                <tr>
                    <th>Note</th>
                    <td>{{ $asset->depreciationSetting->disposal_note ?? '-' }}</td>
                </tr>
            </table>
        </div>
    @endif


    <div class="card mb-3">
        <div class="card-body">

            @if (auth()->user()->inRoles(['admin','manager']) && $asset->depreciationSetting && !$asset->depreciationSetting->is_disposed)
                <form action="{{ route('depreciation.depreciate', $asset->id) }}"
                    method="POST"
                    class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-warning">
                        <i class="fa fa-calculator"></i> Depreciate This Month
                    </button>
                </form>
            @endif


            @if ($asset->depreciationSetting && !$asset->depreciationSetting->is_disposed)
                <a href="{{ route('depreciation.dispose.form', $asset->id) }}"
                   class="btn btn-danger ml-2">
                    <i class="fa fa-trash"></i> Asset Disposal
                </a>
            @endif

        </div>
    </div>

    <div class="card card-primary">
        <div class="card-header d-flex align-items-center">
            <h4 class="mb-0">Monthly Depreciation History</h4>

            <div class="ml-auto">
                @if ($asset->monthlyDepreciations->isNotEmpty())
                    <a href="{{ route('depreciation.export-pdf', $asset->id) }}"
                    target="_blank"
                    class="btn btn-danger btn-sm">
                        <i class="fa fa-file-pdf"></i> Export PDF
                    </a>
                @else
                    <button class="btn btn-secondary btn-sm" disabled>
                        <i class="fa fa-file-pdf"></i> Export PDF
                    </button>
                @endif
            </div>
        </div>
        <div class="card-body">
            @if ($asset->monthlyDepreciations->isEmpty())
                <div class="alert alert-info">
                    No depreciation data yet.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="table-history">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Period</th>
                                <th>Method</th>
                                <th>Monthly Expense</th>
                                <th>Accumulated</th>
                                <th>Ending Book Value</th>
                                <th>Entered By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($asset->monthlyDepreciations as $row)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ \Carbon\Carbon::parse($row->period)->format('m-Y') }}</td>
                                    <td>{{ $row->method }}</td>
                                    <td>Rp {{ number_format($row->monthly_expense, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($row->accumulated_depreciation, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($row->ending_book_value, 0, ',', '.') }}</td>
                                    <td>{{ optional($row->user)->name ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

</div>

<script>
    $(document).ready(function () {
        $('#table-history').DataTable();
    });
</script>
@endsection
