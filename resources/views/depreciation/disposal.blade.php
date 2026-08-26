@extends('layouts.main')

@section('content')
<div class="section-header">
    <h1>Asset Disposal</h1>
    <div class="ml-auto">
        <a href="{{ route('depreciation.show', $asset->id) }}" class="btn btn-primary">
            Back
        </a>
    </div>
</div>

<div class="section-body">
    <div class="card card-danger">
        <div class="card-header">
            <h4>Asset Disposal Form</h4>
        </div>

        <form action="{{ route('depreciation.dispose.store', $asset->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card-body">

                <div class="alert alert-warning">
                    <b>Note:</b>  
                    Once the asset is disposed, depreciation <u>cannot continue</u>.
                </div>

                <div class="form-group">
                    <label>Asset Code</label>
                    <input class="form-control" value="{{ $asset->asset_code }}" disabled>
                </div>

                <div class="form-group">
                    <label>Asset Name</label>
                    <input class="form-control" value="{{ $asset->asset_name }}" disabled>
                </div>

                <div class="form-group">
                    <label>Disposal Reason <span class="text-danger">*</span></label>
                    <select name="disposal_reason" class="form-control" required>
                        <option value="">-- Select Reason --</option>
                        <option value="DAMAGED">Damaged</option>
                        <option value="SOLD">Sold</option>
                        <option value="DONATED">Donated</option>
                        <option value="LOST">Lost</option>
                        <option value="OTHER">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Disposal Note</label>
                    <textarea name="disposal_note" class="form-control" rows="4" placeholder="Optional"></textarea>
                </div>

            </div>

            <div class="card-footer text-right">
                <button type="submit" class="btn btn-danger">
                    <i class="fa fa-trash"></i> Konfirmasi Disposal
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
