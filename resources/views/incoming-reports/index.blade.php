@extends('layouts.main')

@section('content')
    <div class="section-header">
        <h1>Incoming Issue Reports</h1>
    </div>

    <div class="section-body">
        @if (session()->has('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif

        <div class="row">
            <div class="col">
                <div class="card card-primary">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="table_id"
                                class="table table-bordered table-hover table-striped table-condensed">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Title</th>
                                        <th>Status</th>
                                        <th>Asset Name</th>
                                        <th>Location</th>
                                        <th>View</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($issueReports as $issueReport)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $issueReport->title }}</td>
                                            <td>
                                                @if ($issueReport->status === 'Pending')
                                                    <span class="badge badge-warning m-2">Pending</span>
                                                @elseif ($issueReport->status === 'In Review')
                                                    <span class="badge badge-primary m-2">Under Repair</span>
                                                @elseif ($issueReport->status === 'Completed')
                                                    <span class="badge badge-success m-2">Completed</span>
                                                @else
                                                    <span class="badge badge-secondary m-2">-</span>
                                                @endif
                                            </td>
                                            <td>{{ $issueReport->asset->asset_name ?? '-' }}</td>
                                            <td>{{ $issueReport->asset->location->location_name ?? '-' }}</td>
                                            <td>
                                                <a href="/incoming-reports/detail/{{ $issueReport->id }}"
                                                    class="btn btn-success btn-sm">Detail</a>
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

    <script>
        $(document).ready(function() {
            $('#table_id').DataTable();
        });
    </script>
@endsection
