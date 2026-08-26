@extends('layouts.main')

@section('content')
    <div class="section-header">
        <h1>Edit Category</h1>
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
                        <form action="{{ route('categories.update', $category->id) }}" method="POST">
                            @method('PUT')
                            @csrf

                            <div class="form-group">
                                <label for="category_name">Category <span class="text-danger">*</span></label>
                                <input
                                    id="category_name"
                                    type="text"
                                    class="form-control @error('category_name') is-invalid @enderror"
                                    name="category_name"
                                    value="{{ old('category_name', $category->category_name) }}"
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
                                        <option value="{{ $key }}" {{ old('asset_type', $category->asset_type) == $key ? 'selected' : '' }}>
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

                            <button type="submit" class="btn btn-primary float-right">Update</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
