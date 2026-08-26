<div class="row">
    <div class="col-md-6">
        <table class="table table-sm table-borderless">
            <tr><th width="150">Type</th><td>{{ $label($spec->peripheral_type) }}</td></tr>
            <tr><th>Connection</th><td>{{ $label($spec->connection) }}</td></tr>
        </table>
    </div>
    <div class="col-md-6">
        <table class="table table-sm table-borderless">
            @if ($spec->screen_size_inch || $spec->resolution)
                <tr><th width="150">Screen</th>
                    <td>{{ $spec->screen_size_inch ? $spec->screen_size_inch . ' inch' : '' }}
                        {{ $spec->resolution }} {{ $spec->panel_type }}
                        {{ $spec->refresh_rate_hz ? $spec->refresh_rate_hz . 'Hz' : '' }}</td></tr>
            @endif
            @if ($spec->capacity_va)
                <tr><th width="150">Capacity</th><td>{{ $spec->capacity_va }} VA</td></tr>
                <tr><th>Backup</th><td>{{ $spec->backup_minutes ? $spec->backup_minutes . ' min' : '-' }}</td></tr>
            @endif
        </table>
    </div>
</div>
@if ($spec->note)
    <div class="alert alert-light mb-0"><b>Note:</b> {{ $spec->note }}</div>
@endif
