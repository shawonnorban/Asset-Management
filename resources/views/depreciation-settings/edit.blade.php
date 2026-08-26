@extends('layouts.main')

@section('content')
<div class="section-header">
    <h1>Edit Asset Depreciation Setting</h1>
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

    <div class="card card-warning">
        <div class="card-body">
            <form action="{{ route('depreciation-settings.update', $setting->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>Asset</label>
                    <input type="text" class="form-control" disabled
                        value="{{ $setting->asset->asset_code }} - {{ $setting->asset->asset_name }}">
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Depreciation Method <span class="text-danger">*</span></label>
                            <select name="method" class="form-control" required>
                                <option value="STRAIGHT_LINE"
                                    {{ $setting->method === 'STRAIGHT_LINE' ? 'selected' : '' }}>
                                    Straight Line
                                </option>
                                <option value="DECLINING_BALANCE"
                                    {{ $setting->method === 'DECLINING_BALANCE' ? 'selected' : '' }}>
                                    Declining Balance
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tax Group <span class="text-danger">*</span></label>
                            <select name="tax_depreciation_group_id" class="form-control" required>
                                @foreach ($taxGroups as $row)
                                    <option value="{{ $row->id }}"
                                        {{ $setting->tax_depreciation_group_id == $row->id ? 'selected' : '' }}>
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
                                value="{{ $setting->acquisition_cost }}" required>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Salvage Value</label>
                            <input type="number" name="salvage_value" class="form-control"
                                value="{{ $setting->salvage_value }}">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Age (months)</label>
                            <input type="number" name="useful_life_months" class="form-control"
                                value="{{ $setting->useful_life_months }}">
                            <small class="text-muted">
                                Leave empty to follow the tax group
                            </small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>In-Service Date <span class="text-danger">*</span></label>
                    <input type="date" name="in_service_date"
                        value="{{ \Carbon\Carbon::parse($setting->in_service_date)->format('Y-m-d') }}"
                        class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Asset Status</label>
                    <select name="is_disposed" class="form-control">
                        <option value="0" {{ !$setting->is_disposed ? 'selected' : '' }}>Active</option>
                        <option value="1" {{ $setting->is_disposed ? 'selected' : '' }}>Disposed</option>
                    </select>
                </div>

                <div class="text-right">
                    <button class="btn btn-warning">
                        <i class="fa fa-save"></i> Update Setting
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>
@endsection
