{{-- Specification panel for categories of type PERIPHERAL --}}
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Peripheral Type</label>
            <select class="form-control" name="spec[peripheral_type]">
                @foreach (\App\Models\PeripheralSpec::PERIPHERAL_TYPES as $pt)
                    <option value="{{ $pt }}" {{ $specValue('peripheral_type', 'OTHER') == $pt ? 'selected' : '' }}>
                        {{ ucwords(strtolower(str_replace('_', ' ', $pt))) }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Connection</label>
            <select class="form-control" name="spec[connection]">
                <option value="">-- Select --</option>
                @foreach (\App\Models\PeripheralSpec::CONNECTIONS as $c)
                    <option value="{{ $c }}" {{ $specValue('connection') == $c ? 'selected' : '' }}>
                        {{ ucwords(strtolower(str_replace('_', ' ', $c))) }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<h6 class="mt-2 mb-2 text-muted">Display (monitors and projectors)</h6>
<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>Screen Size (inch)</label>
            <input type="number" step="0.1" class="form-control" name="spec[screen_size_inch]"
                   value="{{ $specValue('screen_size_inch') }}" placeholder="23.8">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Resolution</label>
            <input type="text" class="form-control" name="spec[resolution]"
                   value="{{ $specValue('resolution') }}" placeholder="1920x1080" maxlength="20">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Panel Type</label>
            <input type="text" class="form-control" name="spec[panel_type]"
                   value="{{ $specValue('panel_type') }}" placeholder="IPS" maxlength="20">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Refresh Rate (Hz)</label>
            <input type="number" class="form-control" name="spec[refresh_rate_hz]"
                   value="{{ $specValue('refresh_rate_hz') }}" placeholder="75" min="1">
        </div>
    </div>
</div>

<h6 class="mt-2 mb-2 text-muted">Power backup (UPS)</h6>
<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>Capacity (VA)</label>
            <input type="number" class="form-control" name="spec[capacity_va]"
                   value="{{ $specValue('capacity_va') }}" placeholder="650" min="1">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Backup (minutes)</label>
            <input type="number" class="form-control" name="spec[backup_minutes]"
                   value="{{ $specValue('backup_minutes') }}" placeholder="20" min="1">
        </div>
    </div>
</div>

<div class="form-group mb-0">
    <label>Technical Note</label>
    <textarea class="form-control" name="spec[note]" rows="2">{{ $specValue('note') }}</textarea>
</div>
