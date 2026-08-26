@extends('layouts.main')

@section('content')
    <div class="section-header">
        <h1>Completed Issue Reports</h1>
    </div>

    <div class="section-body">
        @if (session()->has('success'))
            <div class="alert alert-success">
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
                                        <th>Report Title</th>
                                        <th>Status</th>
                                        <th>Asset Name</th>
                                        <th>Location</th>
                                        <th>Report Date</th>
                                        <th>Repair Completed</th>
                                        <th>Options</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($issueReports as $issueReport)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $issueReport->title }}</td>
                                            <td>
                                                <span class="badge badge-success">
                                                    {{ $issueReport->status }}
                                                </span>
                                            </td>
                                            <td>
                                                {{ $issueReport->asset?->asset_name ?? '-' }}
                                            </td>
                                            <td>
                                                {{ $issueReport->asset?->location?->location_name ?? '-' }}
                                            </td>
                                            <td>
                                                {{ $issueReport->created_at->format('d-m-Y H:i') }}
                                            </td>
                                            <td>
                                                {{ $issueReport->updated_at->format('d-m-Y H:i') }}
                                            </td>
                                            <td>
                                                <a href="/completed-reports/print-report/{{ $issueReport->id }}"
                                                   class="btn btn-primary btn-sm">
                                                    <i class="fa fa-print"></i> Print
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

    <script>
        $(document).ready(function () {
            $('#table_id').DataTable();
        });
    </script>
@endsection
