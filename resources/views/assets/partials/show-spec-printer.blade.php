<div class="row">
    <div class="col-md-6">
        <h6 class="text-muted">Device</h6>
        <table class="table table-sm table-borderless">
            <tr><th width="150">Printer Type</th><td>{{ $label($spec->printer_type) }}</td></tr>
            <tr><th>Max Paper Size</th><td>{{ $spec->max_paper_size ?? '-' }}</td></tr>
            <tr><th>Capabilities</th>
                <td>
                    <span class="badge {{ $spec->is_color ? 'badge-success' : 'badge-light' }}">Color</span>
                    <span class="badge {{ $spec->is_multifunction ? 'badge-success' : 'badge-light' }}">Scan/Copy</span>
                    <span class="badge {{ $spec->supports_duplex ? 'badge-success' : 'badge-light' }}">Duplex</span>
                </td></tr>
            <tr><th>Duty Cycle</th>
                <td>{{ $spec->monthly_duty_cycle ? number_format($spec->monthly_duty_cycle) . ' pages/month' : '-' }}</td></tr>
        </table>

        <h6 class="text-muted">Consumables</h6>
        <table class="table table-sm table-borderless">
            <tr><th width="150">Toner</th><td>{{ $spec->toner_model ?? '-' }}</td></tr>
            <tr><th>Drum</th><td>{{ $spec->drum_model ?? '-' }}</td></tr>
        </table>
    </div>
    <div class="col-md-6">
        <h6 class="text-muted">Network</h6>
        <table class="table table-sm table-borderless">
            <tr><th width="150">Connection</th><td>{{ $label($spec->connection) }}</td></tr>
            <tr><th>Hostname</th><td>{{ $spec->hostname ?? '-' }}</td></tr>
            <tr><th>IP Address</th>
                <td>{{ $spec->ip_address ?? '-' }}
                    @if ($spec->ip_type)<span class="badge badge-light">{{ $spec->ip_type }}</span>@endif
                </td></tr>
            <tr><th>MAC Address</th><td>{{ $spec->mac_address ?? '-' }}</td></tr>
            <tr><th>Admin URL</th>
                <td>
                    @if ($spec->management_url)
                        <a href="{{ $spec->management_url }}" target="_blank" rel="noopener">{{ $spec->management_url }}</a>
                    @else
                        -
                    @endif
                </td></tr>
        </table>
    </div>
</div>
@if ($spec->note)
    <div class="alert alert-light mb-0"><b>Note:</b> {{ $spec->note }}</div>
@endif
