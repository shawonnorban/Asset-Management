@extends('layouts.main')

@section('content')
    <div class="section-header">
        <h1>Edit Asset</h1>
        <div class="ml-auto">
            <a href="{{ route('assets.show', $asset->id) }}" class="btn btn-primary">
                <i class="fa fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="section-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                Please correct the highlighted fields below.
            </div>
        @endif

        <form action="{{ route('assets.update', $asset->id) }}" method="POST" enctype="multipart/form-data" novalidate>
            @method('PUT')
            @csrf
            @include('assets.partials.form')
        </form>
    </div>
@endsection
