@extends('layouts.main')

@section('content')
    <div class="section-header">
        <h1>Check Report Status</h1>
    </div>

    <div class="section-body">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="row">
            <div class="col">
                <div class="card card-primary">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="table_id" class="table table-bordered table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Title</th>
                                        <th>Status</th>
                                        <th>Asset Name</th>
                                        <th>Location</th>
                                        <th>Report Date</th>
                                        <th>Options</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($issueReports as $issueReport)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $issueReport->title }}</td>
                                            <td>
                                                @if ($issueReport->status === 'Pending')
                                                    <span class="badge badge-warning">Pending</span>
                                                @elseif ($issueReport->status === 'In Review')
                                                    <span class="badge badge-primary">In Review</span>
                                                @elseif ($issueReport->status === 'Completed')
                                                    <span class="badge badge-success">Completed</span>
                                                @else
                                                    <span class="badge badge-secondary">-</span>
                                                @endif
                                            </td>
                                            <td>{{ $issueReport->asset->asset_name ?? '-' }}</td>
                                            <td>{{ $issueReport->asset->location->location_name ?? '-' }}</td>
                                            <td>{{ $issueReport->created_at->format('d-m-Y H:i') }}</td>
                                            <td>
                                                <a href="{{ url('/review-reports/detail/' . $issueReport->id) }}"
                                                   class="btn btn-sm btn-primary">
                                                    Detail
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Datatables --}}
    <script>
        $(document).ready(function () {
            $('#table_id').DataTable();
        });
    </script>
@endsection
