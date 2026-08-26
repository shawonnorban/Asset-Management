<div class="row">
    <div class="col-md-6">
        <h6 class="text-muted">Hardware</h6>
        <table class="table table-sm table-borderless">
            <tr><th width="150">Form Factor</th><td>{{ $label($spec->form_factor) }}</td></tr>
            <tr><th>Processor</th>
                <td>{{ $spec->cpu ?? '-' }}
                    {{ $spec->cpu_cores ? '(' . $spec->cpu_cores . ' cores)' : '' }}</td></tr>
            <tr><th>Memory</th><td>{{ $spec->ram_gb ? $spec->ram_gb . ' GB ' . $spec->ram_type : '-' }}</td></tr>
            <tr><th>Storage</th><td>{{ $spec->storage_summary }}</td></tr>
            <tr><th>Graphics</th><td>{{ $spec->gpu ?? '-' }}</td></tr>
            <tr><th>Motherboard</th><td>{{ $spec->motherboard ?? '-' }}</td></tr>
            <tr><th>Power Supply</th><td>{{ $spec->psu ?? '-' }}</td></tr>
        </table>
    </div>
    <div class="col-md-6">
        <h6 class="text-muted">Network</h6>
        <table class="table table-sm table-borderless">
            <tr><th width="150">Hostname</th><td>{{ $spec->hostname ?? '-' }}</td></tr>
            <tr><th>Domain</th><td>{{ $spec->domain ?? '-' }}</td></tr>
            <tr><th>IP Address</th>
                <td>{{ $spec->ip_address ?? '-' }}
                    @if ($spec->ip_type)<span class="badge badge-light">{{ $spec->ip_type }}</span>@endif
                </td></tr>
            <tr><th>MAC Address</th><td>{{ $spec->mac_address ?? '-' }}</td></tr>
        </table>

        <h6 class="text-muted">Software</h6>
        <table class="table table-sm table-borderless">
            <tr><th width="150">Operating System</th><td>{{ $spec->os ?? '-' }} {{ $spec->os_version }}</td></tr>
            <tr><th>OS Key</th><td><code>{{ $spec->os_license_key ?? '-' }}</code></td></tr>
            <tr><th>Office Key</th><td><code>{{ $spec->office_license_key ?? '-' }}</code></td></tr>
            <tr><th>Antivirus</th><td>{{ $spec->antivirus ?? '-' }}</td></tr>
        </table>
    </div>
</div>
@if ($spec->note)
    <div class="alert alert-light mb-0"><b>Note:</b> {{ $spec->note }}</div>
@endif
