@extends('layouts.main')

@section('content')
<div class="section-header">
    <h1>Audit Detail</h1>
    <div class="ml-auto">
        <a href="{{ route('audit.index') }}" class="btn btn-primary">
            Back
        </a>
    </div>
</div>

<div class="section-body">

    <div class="card card-primary mb-3">
        <div class="card-body">
            <table class="table table-sm">
                <tr><th>Time</th><td>{{ $auditLog->occurred_at }}</td></tr>
                <tr><th>User</th><td>{{ $auditLog->user_name }}</td></tr>
                <tr><th>Action</th><td>{{ $auditLog->action }}</td></tr>
                <tr><th>Table</th><td>{{ $auditLog->table_name }}</td></tr>
                <tr><th>Row ID</th><td>{{ $auditLog->row_id }}</td></tr>
                <tr><th>URL</th><td>{{ $auditLog->url }}</td></tr>
                <tr><th>IP</th><td>{{ $auditLog->ip_address }}</td></tr>
                <tr><th>Method</th><td>{{ $auditLog->http_method }}</td></tr>
                <tr><th>Message</th><td>{{ $auditLog->message ?? '-' }}</td></tr>
            </table>
        </div>
    </div>

    <div class="row">
        {{-- BEFORE --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Before</div>
                <div class="card-body">
                    <pre>{{ json_encode($auditLog->before_data, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
        </div>

        {{-- AFTER --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">After</div>
                <div class="card-body">
                    <pre>{{ json_encode($auditLog->after_data, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
