@extends('layouts.main')

@section('content')
<div class="section-header">
    <h1>Stock Take Input</h1>
    <div class="ml-auto">
        <a href="{{ route('stock-takes.show', $stockTake->id) }}" class="btn btn-primary">
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

    <div class="row">
        <div class="col-lg-4">
            <div class="card card-primary">
                <div class="card-header">
                    <h4>Scan Asset QR</h4>
                </div>
                <div class="card-body">
                    <div id="reader" style="width:100%"></div>
                    <div class="text-muted mt-2" id="scan-status">
                        Waiting for scan...
                    </div>

                    <hr>

                    <div class="form-group">
                        <label>Input Manual Asset Code</label>
                        <div class="input-group">
                            <input type="text" id="manual_kode" class="form-control"
                                   placeholder="Enter asset code">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary"
                                        id="btn-manual">
                                    Search
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card card-warning">
                <div class="card-header">
                    <h4>Stock Take Result Form</h4>
                </div>

                <form action="{{ route('stock-takes.input.store', $stockTake->id) }}" method="POST">
                    @csrf

                    <input type="hidden" name="asset_id" id="asset_id">

                    <div class="card-body">

                        <div class="form-group row">
                            <div class="col-md-6">
                                <label>Asset Code</label>
                                <input type="text" id="asset_code" class="form-control" disabled>
                            </div>
                            <div class="col-md-6">
                                <label>Asset Name</label>
                                <input type="text" id="asset_name" class="form-control" disabled>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Department</label>
                            <input type="text" id="department" class="form-control" disabled>
                        </div>

                        <div class="form-group">
                            <label>Physical Status <span class="text-danger">*</span></label>
                            <select name="physical_status" class="form-control" required>
                                <option value="">-- Select Status --</option>
                                <option value="PRESENT">Present</option>
                                <option value="DAMAGED">Damaged</option>
                                <option value="NOT_FOUND">Not Found</option>
                                <option value="LOST">Lost</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Location (optional)</label>
                            <select name="location_id" id="location_id" class="form-control">
                                <option value="">-- Unchanged --</option>
                                @foreach ($assetLocations as $location)
                                    <option value="{{ $location->id }}">
                                        {{ $location->location_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Employee (optional)</label>
                            <select name="employee_id" id="employee_id" class="form-control">
                                <option value="">-- Unchanged --</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">
                                        {{ $employee->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Note</label>
                            <textarea name="note"
                                      class="form-control"
                                      rows="3"
                                      placeholder="Additional note (optional)"></textarea>
                        </div>

                    </div>

                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Save Stock Take Result
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <div class="card card-primary mt-3">
        <div class="card-header">
            <h4>Counted Assets</h4>
        </div>
        <div class="card-body">
            @if ($details->isEmpty())
                <div class="alert alert-info">
                    No asset has been recorded yet.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Asset Code</th>
                                <th>Asset Name</th>
                                <th>Status</th>
                                <th>Note</th>
                                <th>Action</th>
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
                                    <td>{{ $row->note ?? '-' }}</td>
                                    <td>
                                        <form action="{{ route('stock-takes.detail.destroy', [$stockTake->id, $row->id]) }}"
                                              method="POST"
                                              onsubmit="return confirm('Delete this entry?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

</div>

{{-- ======================
    SCRIPT QR
======================= --}}
<script src="{{ asset('assets/js/page/html5-qrcode.min.js') }}"></script>
<script>
let isProcessing = false;

function resetForm() {
    document.getElementById('asset_id').value = '';
    document.getElementById('asset_code').value = '';
    document.getElementById('asset_name').value = '';
    document.getElementById('department').value = '';
    document.getElementById('location_id').value = '';
    document.getElementById('employee_id').value = '';
}

function fetchAset(code) {
    if (isProcessing) return;
    isProcessing = true;

    fetch("{{ route('stock-takes.getAssetData') }}?code=" + encodeURIComponent(code))
        .then(res => res.json())
        .then(data => {
            if (!data.found) {
                resetForm();
                document.getElementById('scan-status').innerText = 'Asset not found';
                alert('Asset not found');
                return;
            }

            document.getElementById('asset_id').value = data.id;
            document.getElementById('asset_code').value = data.asset_code;
            document.getElementById('asset_name').value = data.asset_name;
            document.getElementById('department').value = data.department ?? '';

            if (data.location_id) {
                document.getElementById('location_id').value = data.location_id;
            }

            if (data.employee_id) {
                document.getElementById('employee_id').value = data.employee_id;
            }

            document.getElementById('scan-status').innerText = 'Asset ditemukan';
        })
        .catch(() => alert('Failed to fetch asset data'))
        .finally(() => {
            setTimeout(() => isProcessing = false, 800);
        });
}

const scanner = new Html5QrcodeScanner(
    "reader",
    { fps: 8, qrbox: { width: 250, height: 250 } },
    false
);

scanner.render(
    text => fetchAset(text),
    () => {}
);

document.getElementById('btn-manual').addEventListener('click', () => {
    const code = document.getElementById('manual_kode').value.trim();
    if (!code) return alert('Enter an asset code');
    fetchAset(code);
});
</script>
@endsection
