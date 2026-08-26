@extends('layouts.main')

@section('content')
    <div class="section-header">
        <h1>Report an Issue</h1>
    </div>

    <div class="section-body">
        @if (session()->has('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session()->has('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row">
            <div class="col-lg-4">
                <div class="card card-primary">
                    <div class="card-header">Scan QR Code</div>
                    <div class="card-body">
                        <div id="reader" style="width:100%"></div>
                        <div id="scan-status" class="mt-2 text-muted">Waiting for scan...</div>
                    </div>
                </div>
            </div>

            {{-- Form --}}
            <div class="col-lg-8">
                <div class="card card-primary">
                    <div class="card-header">Inventory Asset Detail</div>
                    <div class="card-body">
                        <form action="{{ route('report-issue.store') }}" method="POST">
                            @csrf

                            <input type="hidden" name="asset_id" id="asset_id">

                            <div class="form-group row">
                                <div class="col-md-6">
                                    <label>Asset Name</label>
                                    <input type="text" id="asset_name" class="form-control" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label>Asset Code</label>
                                    <input type="text" id="asset_code" class="form-control" readonly>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-md-4">
                                    <label>Category</label>
                                    <input type="text" id="category" class="form-control" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label>Brand</label>
                                    <input type="text" id="brand" class="form-control" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label>Location</label>
                                    <input type="text" id="location" class="form-control" readonly>
                                </div>
                            </div>

                            <hr>

                            <div class="form-group">
                                <label>Report Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                                @error('title') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>

                            <div class="form-group">
                                <label>Report Description <span class="text-danger">*</span></label>
                                <textarea name="description" class="form-control" rows="4" required>{{ old('description') }}</textarea>
                                @error('description') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>

                            <div class="form-group">
                                <label>Enter Asset Code (manually if needed)</label>
                                <div class="input-group">
                                    <input type="text" id="manual_kode" class="form-control" placeholder="Enter asset code">
                                    <div class="input-group-append">
                                        <button type="button" id="btn-fill-manual" class="btn btn-outline-secondary">Content</button>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary float-right">Submit Report</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/page/html5-qrcode.min.js') }}"></script>


    <script>
        let isProcessingScan = false;
        const debounceMs = 800; // minimal delay between requests

        function clearFields() {
            document.getElementById('asset_id').value = '';
            document.getElementById('asset_name').value = '';
            document.getElementById('asset_code').value = '';
            document.getElementById('category').value = '';
            document.getElementById('brand').value = '';
            document.getElementById('location').value = '';
        }

        function fillFields(data, code) {
            document.getElementById('asset_id').value = data.id ?? '';
            document.getElementById('asset_name').value = data.asset_name ?? '';
            document.getElementById('asset_code').value = code ?? '';
            document.getElementById('category').value = data.category ?? '';
            document.getElementById('brand').value = data.brand ?? '';
            document.getElementById('location').value = data.location ?? '';
        }

        function fetchAsetByCode(code) {
            if (!code) return;
            if (isProcessingScan) return;
            isProcessingScan = true;
            document.getElementById('scan-status').innerText = 'Checking asset data...';

            fetch('/get-asset-data?result=' + encodeURIComponent(code), {
                method: 'GET',
                credentials: 'same-origin' // same origin cookies if available
            })
            .then(res => {
                if (!res.ok) throw res; // trigger catch for non-200
                return res.json();
            })
            .then(json => {
                if (json && json.id) {
                    fillFields(json, code);
                    document.getElementById('scan-status').innerText = 'Asset ditemukan';
                } else {
                    clearFields();
                    document.getElementById('asset_code').value = code;
                    document.getElementById('scan-status').innerText = 'Asset not found';
                }
            })
            .catch(err => {
                console.warn('Fetch error:', err);
                clearFields();
                document.getElementById('asset_code').value = code;
                if (err.status === 419) {
                    document.getElementById('scan-status').innerText = 'Invalid session (419). Please refresh the page.';
                } else {
                    document.getElementById('scan-status').innerText = 'Something went wrong while checking the asset.';
                }
            })
            .finally(() => {
                setTimeout(() => { isProcessingScan = false; }, debounceMs);
            });
        }

        const scanner = new Html5QrcodeScanner(
            "reader",
            { fps: 8, qrbox: { width: 250, height: 250 } },
            false
        );

        function onScanSuccess(decodedText, decodedResult) {
            fetchAsetByCode(decodedText);
        }

        function onScanFailure(error) {
        }

        scanner.render(onScanSuccess, onScanFailure);

        document.getElementById('btn-fill-manual').addEventListener('click', () => {
            const code = document.getElementById('manual_kode').value.trim();
            if (!code) return alert('Enter an asset code first.');
            fetchAsetByCode(code);
        });

        clearFields();
    </script>
@endsection
