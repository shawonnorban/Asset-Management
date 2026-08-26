{{-- Specification panel for categories of type PRINTER --}}
<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>Printer Type</label>
            <select class="form-control" name="spec[printer_type]">
                @foreach (\App\Models\PrinterSpec::PRINTER_TYPES as $pt)
                    <option value="{{ $pt }}" {{ $specValue('printer_type', 'LASER') == $pt ? 'selected' : '' }}>
                        {{ ucwords(strtolower(str_replace('_', ' ', $pt))) }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Max Paper Size</label>
            <input type="text" class="form-control" name="spec[max_paper_size]"
                   value="{{ $specValue('max_paper_size') }}" placeholder="A4" maxlength="20">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label class="d-block">Capabilities</label>
            <div class="custom-control custom-checkbox custom-control-inline">
                <input type="checkbox" class="custom-control-input" id="is_color" name="spec[is_color]" value="1"
                       {{ $specValue('is_color') ? 'checked' : '' }}>
                <label class="custom-control-label" for="is_color">Color</label>
            </div>
            <div class="custom-control custom-checkbox custom-control-inline">
                <input type="checkbox" class="custom-control-input" id="is_multifunction" name="spec[is_multifunction]" value="1"
                       {{ $specValue('is_multifunction') ? 'checked' : '' }}>
                <label class="custom-control-label" for="is_multifunction">Scan / Copy</label>
            </div>
            <div class="custom-control custom-checkbox custom-control-inline">
                <input type="checkbox" class="custom-control-input" id="supports_duplex" name="spec[supports_duplex]" value="1"
                       {{ $specValue('supports_duplex') ? 'checked' : '' }}>
                <label class="custom-control-label" for="supports_duplex">Duplex</label>
            </div>
        </div>
    </div>
</div>

<h6 class="mt-2 mb-2 text-muted">Consumables</h6>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Toner / Cartridge Model</label>
            <input type="text" class="form-control" name="spec[toner_model]"
                   value="{{ $specValue('toner_model') }}" placeholder="CF217A" maxlength="60">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Drum Model</label>
            <input type="text" class="form-control" name="spec[drum_model]"
                   value="{{ $specValue('drum_model') }}" placeholder="CF219A" maxlength="60">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Monthly Duty Cycle (pages)</label>
            <input type="number" class="form-control" name="spec[monthly_duty_cycle]"
                   value="{{ $specValue('monthly_duty_cycle') }}" placeholder="8000" min="1">
        </div>
    </div>
</div>

<h6 class="mt-2 mb-2 text-muted">Network identity</h6>
<div class="row">
    <div class="col-md-2">
        <div class="form-group">
            <label>Connection</label>
            <select class="form-control" name="spec[connection]">
                <option value="">--</option>
                @foreach (\App\Models\PrinterSpec::CONNECTIONS as $c)
                    <option value="{{ $c }}" {{ $specValue('connection') == $c ? 'selected' : '' }}>
                        {{ ucwords(strtolower($c)) }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label>Hostname</label>
            <input type="text" class="form-control" name="spec[hostname]"
                   value="{{ $specValue('hostname') }}" maxlength="60">
        </div>
    </div>
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
                   name="spec[ip_address]" value="{{ $specValue('ip_address') }}" placeholder="192.168.1.50">
            @error('spec.ip_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
            <label>Admin URL</label>
            <input type="text" class="form-control @error('spec.management_url') is-invalid @enderror"
                   name="spec[management_url]" value="{{ $specValue('management_url') }}"
                   placeholder="http://192.168.1.50">
            @error('spec.management_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="form-group mb-0">
    <label>Technical Note</label>
    <textarea class="form-control" name="spec[note]" rows="2">{{ $specValue('note') }}</textarea>
</div>
