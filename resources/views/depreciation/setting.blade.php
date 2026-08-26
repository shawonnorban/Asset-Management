@extends('layouts.main')

@section('content')
<div class="section-header">
    <h1>Asset Depreciation Setting</h1>
    <div class="ml-auto">
        <a href="{{ route('depreciation.show', $asset->id) }}" class="btn btn-primary">
            <i class="fa fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="section-body">

    <div class="card card-primary">
        <div class="card-header">
            <h4>{{ $asset->asset_name }} ({{ $asset->asset_code }})</h4>
        </div>

        <div class="card-body">
            <form action="{{ route('depreciation-settings.store', $asset->id) }}" method="POST">
                @csrf

                <div class="form-group">
                    <label>Depreciation Method <span class="text-danger">*</span></label>
                    <select name="method" class="form-control" required>
                        <option value="">-- Select Method --</option>
                        <option value="STRAIGHT_LINE">Straight Line</option>
                        <option value="DECLINING_BALANCE">Declining Balance</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Tax Group <span class="text-danger">*</span></label>
                    <select name="tax_depreciation_group_id" class="form-control" required>
                        @foreach ($taxGroups as $group)
                            <option value="{{ $group->id }}">
                                {{ $group->name }} ({{ $group->useful_life_years }} th)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Acquisition Cost <span class="text-danger">*</span></label>
                    <input type="number" name="acquisition_cost" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Salvage Value (optional)</label>
                    <input type="number" name="salvage_value" class="form-control">
                </div>

                <div class="form-group">
                    <label>In-Service Date <span class="text-danger">*</span></label>
                    <input type="date" name="in_service_date" class="form-control" required>
                    <small class="text-muted">
                        Depreciation starts next month
                    </small>
                </div>

                <button type="submit" class="btn btn-primary float-right">
                    Save Setting
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
