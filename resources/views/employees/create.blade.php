@extends('layouts.main')

@section('content')
    <div class="section-header">
        <h1>Add Employee</h1>
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

        <form action="{{ route('employees.store') }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf
            @include('employees.partials.form', ['employee' => null])
        </form>
    </div>
@endsection
