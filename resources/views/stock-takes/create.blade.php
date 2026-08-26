@extends('layouts.main')

@section('content')
<div class="section-header">
    <h1>Create Asset Stock Take</h1>
    <div class="ml-auto">
        <a href="{{ route('stock-takes.index') }}" class="btn btn-primary">
            <i class="fa fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="section-body">

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('stock-takes.store') }}" method="POST">
        @csrf

        <div class="card card-primary">
            <div class="card-header">
                <h4>Stock Take Information</h4>
            </div>

            <div class="card-body">

                <div class="form-group">
                    <label>Stock Take Name <span class="text-danger">*</span></label>
                    <input type="text"
                           name="name"
                           class="form-control"
                           placeholder="Contoh: Stock Take Semester 1 2026"
                           value="{{ old('name') }}"
                           required>
                </div>

                <div class="form-group">
                    <label>Stock Take Date <span class="text-danger">*</span></label>
                    <input type="date"
                           name="stock_take_date"
                           class="form-control"
                           value="{{ old('stock_take_date', now()->toDateString()) }}"
                           required>
                </div>

                <div class="alert alert-info">
                    <b>Note:</b><br>
                    - The stock take will be created with status <b>DRAFT</b><br>
                    - Once created, record the count result for each asset<br>
                    - A stock take can no longer be changed once <b>FINAL</b>
                </div>

            </div>

            <div class="card-footer text-right">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Save & Lanjut Input
                </button>
            </div>
        </div>

    </form>

</div>
@endsection
