@extends('layouts.main')

@section('content')
    <div class="section-header">
        <h1>Edit Employee</h1>
        <div class="ml-auto">
            <a href="{{ route('employees.index') }}" class="btn btn-primary">
                <i class="fa fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="section-body">
        @if ($errors->any())
            <div class="alert alert-danger">Please correct the highlighted fields below.</div>
        @endif

        <form action="{{ route('employees.update', $employee->id) }}" method="POST" enctype="multipart/form-data" novalidate>
            @method('PUT')
            @csrf
            @include('employees.partials.form')
        </form>
    </div>
@endsection
