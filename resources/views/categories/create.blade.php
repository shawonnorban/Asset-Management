@extends('layouts.main')

@section('content')
    <div class="section-header">
        <h1>Add Category</h1>
        <div class="ml-auto">
            <a href="{{ route('categories.index') }}" class="btn btn-primary">
                <i class="fa fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col">
                <div class="card card-primary">
                    <div class="card-body">
                        <form action="{{ route('categories.store') }}" method="POST">
                            @csrf

                            <div class="form-group">
                                <label for="category_name">Category <span class="text-danger">*</span></label>
                                <input
                                    id="category_name"
                                    type="text"
                                    class="form-control @error('category_name') is-invalid @enderror"
                                    name="category_name"
                                    value="{{ old('category_name') }}"
                                    maxlength="50"
                                    required
                                >
                                @error('category_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>


                            <div class="form-group">
                                <label for="asset_type">Asset Type <span class="text-danger">*</span></label>
                                <select id="asset_type"
                                        class="form-control @error('asset_type') is-invalid @enderror"
                                        name="asset_type" required>
                                    @foreach ($assetTypes as $key => $text)
                                        <option value="{{ $key }}" {{ old('asset_type', 'OTHER') == $key ? 'selected' : '' }}>
                                            {{ $text }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('asset_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    Decides which specification fields an asset in this category gets.
                                </small>
                            </div>

                            <button type="submit" class="btn btn-primary float-right">Save</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
