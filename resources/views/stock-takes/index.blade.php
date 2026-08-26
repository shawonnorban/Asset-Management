@extends('layouts.main')

@section('content')
<div class="section-header">
    <h1>Asset Stock Take List</h1>
    <div class="ml-auto">
        <a href="{{ route('stock-takes.create') }}" class="btn btn-primary">
            <i class="fa fa-plus"></i> New Stock Take
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

    <div class="card card-primary">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="table-stock-takes">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Stock Take Code</th>
                            <th>Stock Take Name</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($stockTakes as $stockTake)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $stockTake->stock_take_code }}</td>
                                <td>{{ $stockTake->name }}</td>
                                <td>{{ \Carbon\Carbon::parse($stockTake->stock_take_date)->format('d-m-Y') }}</td>
                                <td>
                                    @if ($stockTake->status === 'DRAFT')
                                        <span class="badge badge-warning">DRAFT</span>
                                    @else
                                        <span class="badge badge-success">FINAL</span>
                                    @endif
                                </td>
                                <td>{{ $stockTake->user->name }}</td>
                                <td>
                                    <a href="{{ route('stock-takes.show', $stockTake->id) }}"
                                    class="btn btn-info btn-sm">
                                        <i class="fa fa-eye"></i> Detail
                                    </a>

                                    @if ($stockTake->status === 'FINAL')
                                        <a href="{{ route('stock-takes.pdf', $stockTake->id) }}"
                                        target="_blank"
                                        class="btn btn-danger btn-sm ml-1">
                                            <i class="fa fa-file-pdf"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
    $(document).ready(function () {
        $('#table-stock-takes').DataTable();
    });
</script>
@endsection
