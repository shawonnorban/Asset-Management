@php
    $license = $license ?? null;
    $val = fn ($field, $default = null) => old($field, $license->{$field} ?? $default);
    $dateVal = fn ($field) => $val($field) ? \Carbon\Carbon::parse($val($field))->format('Y-m-d') : '';
@endphp

<div class="row">
    <div class="col-lg-8">
        <div class="card card-primary">
            <div class="card-header"><h4>License</h4></div>
            <div class="card-body">

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Software Name <span class="text-danger">*</span></label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                                   name="name" value="{{ $val('name') }}" placeholder="Microsoft Office"
                                   maxlength="120" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="publisher">Publisher</label>
                            <input id="publisher" type="text" class="form-control @error('publisher') is-invalid @enderror"
                                   name="publisher" value="{{ $val('publisher') }}" placeholder="Microsoft" maxlength="100">
                            @error('publisher')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="version">Version</label>
                            <input id="version" type="text" class="form-control @error('version') is-invalid @enderror"
                                   name="version" value="{{ $val('version') }}" placeholder="2021" maxlength="40">
                            @error('version')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="license_type">License Type <span class="text-danger">*</span></label>
                            <select id="license_type" class="form-control @error('license_type') is-invalid @enderror"
                                    name="license_type" required>
                                @foreach ($licenseTypes as $type)
                                    <option value="{{ $type }}" {{ $val('license_type', 'PERPETUAL') == $type ? 'selected' : '' }}>
                                        {{ ucwords(strtolower(str_replace('_', ' ', $type))) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('license_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="license_key">License Key</label>
                            <input id="license_key" type="text" class="form-control @error('license_key') is-invalid @enderror"
                                   name="license_key" value="{{ $val('license_key') }}" maxlength="120">
                            @error('license_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="seats_total">Seats <span class="text-danger">*</span></label>
                            <input id="seats_total" type="number" min="1"
                                   class="form-control @error('seats_total') is-invalid @enderror"
                                   name="seats_total" value="{{ $val('seats_total', 1) }}" required>
                            @error('seats_total')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="form-text text-muted">How many machines this license covers.</small>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-0">
                    <label for="note">Note</label>
                    <textarea id="note" class="form-control @error('note') is-invalid @enderror"
                              name="note" rows="3">{{ $val('note') }}</textarea>
                    @error('note')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card card-primary">
            <div class="card-header"><h4>Purchase</h4></div>
            <div class="card-body">

                <div class="form-group">
                    <label for="vendor">Vendor</label>
                    <input id="vendor" type="text" class="form-control @error('vendor') is-invalid @enderror"
                           name="vendor" value="{{ $val('vendor') }}" maxlength="100">
                    @error('vendor')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="invoice_no">Invoice No</label>
                            <input id="invoice_no" type="text" class="form-control @error('invoice_no') is-invalid @enderror"
                                   name="invoice_no" value="{{ $val('invoice_no') }}" maxlength="60">
                            @error('invoice_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="purchase_cost">Cost</label>
                            <input id="purchase_cost" type="number" step="0.01" min="0"
                                   class="form-control @error('purchase_cost') is-invalid @enderror"
                                   name="purchase_cost" value="{{ $val('purchase_cost') }}">
                            @error('purchase_cost')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="purchase_date">Purchase Date</label>
                    <input id="purchase_date" type="date" class="form-control @error('purchase_date') is-invalid @enderror"
                           name="purchase_date" value="{{ $dateVal('purchase_date') }}">
                    @error('purchase_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group mb-0">
                    <label for="expiry_date">Expiry Date</label>
                    <input id="expiry_date" type="date" class="form-control @error('expiry_date') is-invalid @enderror"
                           name="expiry_date" value="{{ $dateVal('expiry_date') }}">
                    @error('expiry_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="form-text text-muted">Subscriptions only. Leave empty for perpetual.</small>
                </div>

            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block mb-4">
            <i class="fa fa-save"></i> {{ $license ? 'Update License' : 'Save License' }}
        </button>
    </div>
</div>
