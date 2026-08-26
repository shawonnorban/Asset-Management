@extends('layouts.main')

@section('content')
<div class="section-header">
    <h1>Asset Depreciation Setting</h1>
    <div class="ml-auto">
        <a href="{{ route('depreciation-settings.create') }}" class="btn btn-primary">
            <i class="fa fa-plus"></i> Create Setting
        </a>
    </div>
</div>

<div class="section-body">
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card card-primary">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="table-setting">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Asset Code</th>
                            <th>Asset Name</th>
                            <th>Category</th>
                            <th>Method</th>
                            <th>Setting Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($assets as $asset)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $asset->asset_code }}</td>
                                <td>{{ $asset->asset_name }}</td>
                                <td>{{ $asset->category->category_name ?? '-' }}</td>
                                <td>
                                    @if ($asset->depreciationSetting)
                                        <span class="badge badge-info">
                                            {{ $asset->depreciationSetting->method }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if ($asset->depreciationSetting)
                                        <span class="badge badge-success">Configured</span>
                                    @else
                                        <span class="badge badge-secondary">Not Set</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($asset->depreciationSetting)
                                        <a href="{{ route('depreciation-settings.edit', $asset->id) }}"
                                           class="btn btn-warning btn-sm">
                                            <i class="fa fa-edit"></i> Edit
                                        </a>
                                    @else
                                        <a href="{{ route('depreciation-settings.create', ['asset_id' => $asset->id]) }}"
                                           class="btn btn-primary btn-sm">
                                            <i class="fa fa-plus"></i> Create
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
        $('#table-setting').DataTable();
    });
</script>
@endsection
