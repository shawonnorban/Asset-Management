<div class="row">
    <div class="col-md-6">
        <h6 class="text-muted">Device</h6>
        <table class="table table-sm table-borderless">
            <tr><th width="150">Role</th><td>{{ $label($spec->device_role) }}</td></tr>
            <tr><th>Hostname</th><td>{{ $spec->hostname ?? '-' }}</td></tr>
            <tr><th>Rack Position</th><td>{{ $spec->rack_position ?? '-' }}</td></tr>
            <tr><th>Firmware</th><td>{{ $spec->firmware_version ?? '-' }}</td></tr>
            <tr><th>Features</th>
                <td>
                    <span class="badge {{ $spec->is_managed ? 'badge-success' : 'badge-light' }}">Managed</span>
                    <span class="badge {{ $spec->supports_poe ? 'badge-success' : 'badge-light' }}">PoE</span>
                </td></tr>
        </table>
    </div>
    <div class="col-md-6">
        <h6 class="text-muted">Addressing</h6>
        <table class="table table-sm table-borderless">
            <tr><th width="150">IP Address</th>
                <td>{{ $spec->ip_address ?? '-' }}
                    @if ($spec->ip_type)<span class="badge badge-light">{{ $spec->ip_type }}</span>@endif
                </td></tr>
            <tr><th>Subnet Mask</th><td>{{ $spec->subnet_mask ?? '-' }}</td></tr>
            <tr><th>Gateway</th><td>{{ $spec->gateway ?? '-' }}</td></tr>
            <tr><th>MAC Address</th><td>{{ $spec->mac_address ?? '-' }}</td></tr>
            <tr><th>VLAN</th><td>{{ $spec->vlan ?? '-' }}</td></tr>
            <tr><th>Ports</th>
                <td>{{ $spec->port_count ? $spec->port_count . ' x ' . $spec->port_speed : '-' }}</td></tr>
            <tr><th>WiFi</th><td>{{ $spec->wifi_standard ?? '-' }}</td></tr>
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
