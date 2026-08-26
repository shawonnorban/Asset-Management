{{-- Specification panel for categories of type NETWORK_DEVICE --}}
<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>Device Role</label>
            <select class="form-control" name="spec[device_role]">
                @foreach (\App\Models\NetworkDeviceSpec::DEVICE_ROLES as $r)
                    <option value="{{ $r }}" {{ $specValue('device_role', 'SWITCH') == $r ? 'selected' : '' }}>
                        {{ ucwords(strtolower(str_replace('_', ' ', $r))) }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Hostname</label>
            <input type="text" class="form-control" name="spec[hostname]"
                   value="{{ $specValue('hostname') }}" placeholder="SW-CORE-01" maxlength="60">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Rack Position</label>
            <input type="text" class="form-control" name="spec[rack_position]"
                   value="{{ $specValue('rack_position') }}" placeholder="Rack A / U12" maxlength="40">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Firmware Version</label>
            <input type="text" class="form-control" name="spec[firmware_version]"
                   value="{{ $specValue('firmware_version') }}" maxlength="40">
        </div>
    </div>
</div>

<h6 class="mt-2 mb-2 text-muted">Addressing</h6>
<div class="row">
    <div class="col-md-2">
        <div class="form-group">
            <label>IP Type</label>
            <select class="form-control" name="spec[ip_type]">
                <option value="">--</option>
                <option value="STATIC" {{ $specValue('ip_type') == 'STATIC' ? 'selected' : '' }}>Static</option>
                <option value="DHCP" {{ $specValue('ip_type') == 'DHCP' ? 'selected' : '' }}>DHCP</option>
            </select>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label>IP Address</label>
            <input type="text" class="form-control @error('spec.ip_address') is-invalid @enderror"
                   name="spec[ip_address]" value="{{ $specValue('ip_address') }}" placeholder="192.168.1.1">
            @error('spec.ip_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label>Subnet Mask</label>
            <input type="text" class="form-control" name="spec[subnet_mask]"
                   value="{{ $specValue('subnet_mask') }}" placeholder="255.255.255.0" maxlength="45">
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label>Gateway</label>
            <input type="text" class="form-control @error('spec.gateway') is-invalid @enderror"
                   name="spec[gateway]" value="{{ $specValue('gateway') }}" placeholder="192.168.1.1">
            @error('spec.gateway')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label>MAC Address</label>
            <input type="text" class="form-control" name="spec[mac_address]"
                   value="{{ $specValue('mac_address') }}" maxlength="17">
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label>VLAN</label>
            <input type="text" class="form-control" name="spec[vlan]"
                   value="{{ $specValue('vlan') }}" placeholder="VLAN 10" maxlength="40">
        </div>
    </div>
</div>

<h6 class="mt-2 mb-2 text-muted">Capability</h6>
<div class="row">
    <div class="col-md-2">
        <div class="form-group">
            <label>Port Count</label>
            <input type="number" class="form-control" name="spec[port_count]"
                   value="{{ $specValue('port_count') }}" placeholder="24" min="1">
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label>Port Speed</label>
            <input type="text" class="form-control" name="spec[port_speed]"
                   value="{{ $specValue('port_speed') }}" placeholder="1Gbps" maxlength="20">
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label>WiFi Standard</label>
            <input type="text" class="form-control" name="spec[wifi_standard]"
                   value="{{ $specValue('wifi_standard') }}" placeholder="WiFi 6" maxlength="20">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Admin URL</label>
            <input type="text" class="form-control @error('spec.management_url') is-invalid @enderror"
                   name="spec[management_url]" value="{{ $specValue('management_url') }}"
                   placeholder="https://192.168.1.1">
            @error('spec.management_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label class="d-block">Features</label>
            <div class="custom-control custom-checkbox custom-control-inline">
                <input type="checkbox" class="custom-control-input" id="is_managed" name="spec[is_managed]" value="1"
                       {{ $specValue('is_managed') ? 'checked' : '' }}>
                <label class="custom-control-label" for="is_managed">Managed</label>
            </div>
            <div class="custom-control custom-checkbox custom-control-inline">
                <input type="checkbox" class="custom-control-input" id="supports_poe" name="spec[supports_poe]" value="1"
                       {{ $specValue('supports_poe') ? 'checked' : '' }}>
                <label class="custom-control-label" for="supports_poe">PoE</label>
            </div>
        </div>
    </div>
</div>

<div class="form-group mb-0">
    <label>Technical Note</label>
    <textarea class="form-control" name="spec[note]" rows="2">{{ $specValue('note') }}</textarea>
</div>
