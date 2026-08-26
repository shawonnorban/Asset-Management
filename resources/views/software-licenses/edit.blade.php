@extends('layouts.main')

@section('content')
    <div class="section-header">
        <h1>Edit Software License</h1>
        <div class="ml-auto">
            <a href="{{ route('software-licenses.index') }}" class="btn btn-primary">
                <i class="fa fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="section-body">
        <form action="{{ route('software-licenses.update', $license->id) }}" method="POST" novalidate>
            @method('PUT')
            @csrf
            @include('software-licenses.partials.form')
        </form>
    </div>
@endsection
