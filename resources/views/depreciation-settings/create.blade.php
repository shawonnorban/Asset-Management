@extends('layouts.main')

@section('content')
<div class="section-header">
    <h1>Create Asset Depreciation Setting</h1>
    <div class="ml-auto">
        <a href="{{ route('depreciation-settings.index') }}" class="btn btn-primary">
            <i class="fa fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="section-body">

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card card-primary">
        <div class="card-body">
            <form action="{{ route('depreciation-settings.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Asset <span class="text-danger">*</span></label>
                    <select name="asset_id" class="form-control" required>
                        <option value="">-- Select Asset --</option>
                        @foreach ($assets as $asset)
                            <option value="{{ $asset->id }}"
                                {{ old('asset_id', request('asset_id')) == $asset->id ? 'selected' : '' }}>
                                {{ $asset->asset_code }} - {{ $asset->asset_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Depreciation Method <span class="text-danger">*</span></label>
                            <select name="method" class="form-control" required>
                                <option value="STRAIGHT_LINE">Straight Line</option>
                                <option value="DECLINING_BALANCE">Declining Balance</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tax Group <span class="text-danger">*</span></label>
                            <select name="tax_depreciation_group_id" class="form-control" required>
                                <option value="">-- Select Tax Group --</option>
                                @foreach ($taxGroups as $row)
                                    <option value="{{ $row->id }}">
                                        {{ $row->name }} ({{ $row->useful_life_years }} years)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Acquisition Cost <span class="text-danger">*</span></label>
                            <input type="number" name="acquisition_cost" class="form-control"
                                   value="{{ old('acquisition_cost') }}" required>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Salvage Value</label>
                            <input type="number" name="salvage_value" class="form-control"
                                   value="{{ old('salvage_value') }}">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Age (months)</label>
                            <input type="number" name="useful_life_months" class="form-control"
                                   placeholder="Optional (auto from tax group)">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>In-Service Date <span class="text-danger">*</span></label>
                    <input type="date" name="in_service_date"
                           value="{{ old('in_service_date') }}"
                           class="form-control" required>
                    <small class="text-muted">
                        Depreciation starts next month.
                    </small>
                </div>

                <div class="text-right">
                    <button class="btn btn-primary">
                        <i class="fa fa-save"></i> Save Setting
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>
@endsection
