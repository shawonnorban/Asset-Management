@extends('layouts.main')

@section('content')
    <div class="section-header">
        <h1>Edit Location</h1>
        <div class="ml-auto">
            <a href="{{ route('locations.index') }}" class="btn btn-primary"><i class="fa fa-back"></i> Back</a>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col">
                <div class="card card-primary">
                    <div class="card-body">
                        <form action="{{ route('locations.update', $location->id) }}" method="POST" novalidate>
                            @method('PUT')
                            @csrf

                            <div class="form-group">
                                <label for="location_name">Location <span class="text-danger">*</span></label>
                                <input id="location_name"
                                       type="text"
                                       class="form-control @error('location_name') is-invalid @enderror"
                                       name="location_name"
                                       value="{{ old('location_name', $location->location_name) }}"
                                       required>
                                @error('location_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">The location name may be at most 50 characters.</small>
                            </div>

                            <button type="submit" class="btn btn-primary float-right">Save</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
