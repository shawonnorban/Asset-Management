@extends('layouts.main')

@section('content')
<div class="section-header">
    <h1>Stock Take Detail</h1>
    <div class="ml-auto">
        <a href="{{ route('stock-takes.index') }}" class="btn btn-primary">
            <i class="fa fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="section-body">

    {{-- ALERT --}}
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card card-primary mb-3">
        <div class="card-header">
            <h4>Stock Take Information</h4>
        </div>
        <div class="card-body">
            <table class="table table-sm">
                <tr>
                    <th width="200">Stock Take Code</th>
                    <td>{{ $stockTake->stock_take_code }}</td>
                </tr>
                <tr>
                    <th>Stock Take Name</th>
                    <td>{{ $stockTake->name }}</td>
                </tr>
                <tr>
                    <th>Stock Take Date</th>
                    <td>{{ \Carbon\Carbon::parse($stockTake->stock_take_date)->format('d-m-Y') }}</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        @if ($stockTake->status === 'DRAFT')
                            <span class="badge badge-warning">DRAFT</span>
                        @else
                            <span class="badge badge-success">FINAL</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Created By</th>
                    <td>{{ $stockTake->user->name }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">

            @if ($stockTake->status === 'DRAFT')
                <a href="{{ route('stock-takes.input', $stockTake->id) }}"
                   class="btn btn-primary">
                    <i class="fa fa-plus"></i> Input Asset
                </a>

                <form action="{{ route('stock-takes.final', $stockTake->id) }}"
                      method="POST"
                      class="d-inline"
                      onsubmit="return confirm('Finalize this stock take? The data can no longer be changed.')">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger">
                        <i class="fa fa-lock"></i> Finalisasi
                    </button>
                </form>
            @endif

            @if ($stockTake->status === 'FINAL')
                <a href="{{ route('stock-takes.pdf', $stockTake->id) }}"
                   target="_blank"
                   class="btn btn-danger">
                    <i class="fa fa-file-pdf"></i> Export PDF
                </a>
            @endif

        </div>
    </div>


    <div class="card card-primary">
        <div class="card-header">
            <h4>Stock Take Results</h4>
        </div>
        <div class="card-body">
            @if ($details->isEmpty())
                <div class="alert alert-info">
                    No asset has been recorded yet.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="table-stock-takes">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Asset Code</th>
                                <th>Asset Name</th>
                                <th>Physical Status</th>
                                <th>Location</th>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Note</th>
                                <th>Officer</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($details as $row)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $row->asset->asset_code }}</td>
                                    <td>{{ $row->asset->asset_name }}</td>
                                    <td>
                                        <span class="badge badge-info">
                                            {{ $row->physical_status }}
                                        </span>
                                    </td>
                                    <td>{{ $row->location->location_name ?? '-' }}</td>
                                    <td>{{ $row->employee->name ?? '-' }}</td>
                                    <td>{{ $row->employee->department->name ?? '-' }}</td>
                                    <td>{{ $row->note ?? '-' }}</td>
                                    <td>{{ $row->user->name }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

</div>

<script>
    $(document).ready(function () {
        $('#table-stock-takes').DataTable();
    });
</script>
@endsection
