@extends('layouts.main')

@section('content')
    <div class="section-header">
        <h1>Add Software License</h1>
        <div class="ml-auto">
            <a href="{{ route('software-licenses.index') }}" class="btn btn-primary">
                <i class="fa fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="section-body">
        <form action="{{ route('software-licenses.store') }}" method="POST" novalidate>
            @csrf
            @include('software-licenses.partials.form', ['license' => null])
        </form>
    </div>
@endsection
