{{-- Specification panel for categories of type COMPUTER --}}
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Form Factor</label>
            <select class="form-control" name="spec[form_factor]">
                @foreach (\App\Models\ComputerSpec::FORM_FACTORS as $ff)
                    <option value="{{ $ff }}" {{ $specValue('form_factor', 'DESKTOP') == $ff ? 'selected' : '' }}>
                        {{ ucwords(strtolower(str_replace('_', ' ', $ff))) }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-8">
        <div class="form-group">
            <label>Processor (CPU)</label>
            <input type="text" class="form-control" name="spec[cpu]"
                   value="{{ $specValue('cpu') }}" placeholder="Intel Core i5-11400 @ 2.60GHz" maxlength="120">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>CPU Cores</label>
            <input type="number" class="form-control" name="spec[cpu_cores]"
                   value="{{ $specValue('cpu_cores') }}" min="1" max="512" placeholder="6">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>RAM (GB)</label>
            <input type="number" class="form-control" name="spec[ram_gb]"
                   value="{{ $specValue('ram_gb') }}" min="1" max="4096" placeholder="16">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>RAM Type</label>
            <input type="text" class="form-control" name="spec[ram_type]"
                   value="{{ $specValue('ram_type') }}" placeholder="DDR4" maxlength="20">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Power Supply</label>
            <input type="text" class="form-control" name="spec[psu]"
                   value="{{ $specValue('psu') }}" placeholder="500W" maxlength="60">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>Primary Storage</label>
            <select class="form-control" name="spec[storage_type]">
                <option value="">-- Type --</option>
                @foreach (\App\Models\ComputerSpec::STORAGE_TYPES as $st)
                    <option value="{{ $st }}" {{ $specValue('storage_type') == $st ? 'selected' : '' }}>{{ $st }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Size (GB)</label>
            <input type="number" class="form-control" name="spec[storage_gb]"
                   value="{{ $specValue('storage_gb') }}" min="1" placeholder="512">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Secondary Storage</label>
            <select class="form-control" name="spec[secondary_storage_type]">
                <option value="">-- None --</option>
                @foreach (['HDD', 'SSD', 'NVME'] as $st)
                    <option value="{{ $st }}" {{ $specValue('secondary_storage_type') == $st ? 'selected' : '' }}>{{ $st }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Size (GB)</label>
            <input type="number" class="form-control" name="spec[secondary_storage_gb]"
                   value="{{ $specValue('secondary_storage_gb') }}" min="1" placeholder="1000">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Graphics (GPU)</label>
            <input type="text" class="form-control" name="spec[gpu]"
                   value="{{ $specValue('gpu') }}" placeholder="Intel UHD Graphics 730" maxlength="120">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Motherboard</label>
            <input type="text" class="form-control" name="spec[motherboard]"
                   value="{{ $specValue('motherboard') }}" placeholder="Dell 0J37F5" maxlength="120">
        </div>
    </div>
</div>

<h6 class="mt-3 mb-2 text-muted">Network identity</h6>
<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>Hostname</label>
            <input type="text" class="form-control" name="spec[hostname]"
                   value="{{ $specValue('hostname') }}" placeholder="IT-PC-014" maxlength="60">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Domain / Workgroup</label>
            <input type="text" class="form-control" name="spec[domain]"
                   value="{{ $specValue('domain') }}" placeholder="corp.local" maxlength="60">
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
                   name="spec[ip_address]" value="{{ $specValue('ip_address') }}" placeholder="192.168.1.24">
            @error('spec.ip_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label>MAC Address</label>
            <input type="text" class="form-control" name="spec[mac_address]"
                   value="{{ $specValue('mac_address') }}" placeholder="A4:BB:6D:11:22:33" maxlength="17">
        </div>
    </div>
</div>

<h6 class="mt-3 mb-2 text-muted">Software</h6>
<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>Operating System</label>
            <input type="text" class="form-control" name="spec[os]"
                   value="{{ $specValue('os') }}" placeholder="Windows 11 Pro" maxlength="60">
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label>OS Version</label>
            <input type="text" class="form-control" name="spec[os_version]"
                   value="{{ $specValue('os_version') }}" placeholder="23H2" maxlength="40">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>OS License Key</label>
            <input type="text" class="form-control" name="spec[os_license_key]"
                   value="{{ $specValue('os_license_key') }}" maxlength="60">
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label>Office Key</label>
            <input type="text" class="form-control" name="spec[office_license_key]"
                   value="{{ $specValue('office_license_key') }}" maxlength="60">
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label>Antivirus</label>
            <input type="text" class="form-control" name="spec[antivirus]"
                   value="{{ $specValue('antivirus') }}" placeholder="Defender" maxlength="60">
        </div>
    </div>
</div>

<div class="form-group mb-0">
    <label>Technical Note</label>
    <textarea class="form-control" name="spec[note]" rows="2">{{ $specValue('note') }}</textarea>
</div>
