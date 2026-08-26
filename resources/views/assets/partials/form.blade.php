@php
    /** @var \App\Models\Asset|null $asset */
    $asset = $asset ?? null;
    $spec = $spec ?? [];

    // current value of a base field: old input wins, then the stored asset
    $val = fn ($field, $default = null) => old($field, $asset->{$field} ?? $default);

    // current value of a specification field
    $specValue = fn ($field, $default = null) => old("spec.$field", $spec[$field] ?? $default);

    $selectedCategoryId = $val('category_id');
    $selectedType = optional($categories->firstWhere('id', (int) $selectedCategoryId))->asset_type ?? 'OTHER';
@endphp

<div class="row">
    <div class="col-lg-8">

        {{-- ================= IDENTITY ================= --}}
        <div class="card card-primary">
            <div class="card-header"><h4>Identity</h4></div>
            <div class="card-body">

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="asset_code">Asset Code <span class="text-danger">*</span></label>
                            <input id="asset_code" type="text"
                                   class="form-control @error('asset_code') is-invalid @enderror"
                                   name="asset_code" value="{{ $val('asset_code') }}" maxlength="35" required>
                            @error('asset_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="asset_name">Asset Name <span class="text-danger">*</span></label>
                            <input id="asset_name" type="text"
                                   class="form-control @error('asset_name') is-invalid @enderror"
                                   name="asset_name" value="{{ $val('asset_name') }}" maxlength="150" required>
                            @error('asset_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="brand">Brand</label>
                            <input id="brand" type="text" class="form-control @error('brand') is-invalid @enderror"
                                   name="brand" value="{{ $val('brand') }}" placeholder="Dell" maxlength="100">
                            @error('brand')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="model">Model</label>
                            <input id="model" type="text" class="form-control @error('model') is-invalid @enderror"
                                   name="model" value="{{ $val('model') }}" placeholder="OptiPlex 7090" maxlength="100">
                            @error('model')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="serial_number">Serial Number</label>
                            <input id="serial_number" type="text"
                                   class="form-control @error('serial_number') is-invalid @enderror"
                                   name="serial_number" value="{{ $val('serial_number') }}" maxlength="100">
                            @error('serial_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="category_id">Category <span class="text-danger">*</span></label>
                            <select id="category_id" class="form-control @error('category_id') is-invalid @enderror"
                                    name="category_id" required>
                                <option value="">-- Select Category --</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" data-asset-type="{{ $category->asset_type }}"
                                        {{ $selectedCategoryId == $category->id ? 'selected' : '' }}>
                                        {{ $category->category_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="form-text text-muted">
                                The category decides which specification fields appear below.
                            </small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="location_id">Location <span class="text-danger">*</span></label>
                            <select id="location_id" class="form-control @error('location_id') is-invalid @enderror"
                                    name="location_id" required>
                                <option value="">-- Select Location --</option>
                                @foreach ($locations as $location)
                                    <option value="{{ $location->id }}"
                                        {{ $val('location_id') == $location->id ? 'selected' : '' }}>
                                        {{ $location->location_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('location_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="added_date">Date Added <span class="text-danger">*</span></label>
                            <input id="added_date" type="date"
                                   class="form-control @error('added_date') is-invalid @enderror"
                                   name="added_date" value="{{ $val('added_date', date('Y-m-d')) }}" required>
                            @error('added_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="form-group mb-0">
                    <label for="description">Description</label>
                    <textarea id="description" class="form-control @error('description') is-invalid @enderror"
                              name="description" rows="3">{{ $val('description') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

            </div>
        </div>

        {{-- ================= SPECIFICATION ================= --}}
        <div class="card card-primary">
            <div class="card-header">
                <h4>Specification</h4>
                <div class="card-header-action">
                    <span class="badge badge-info" id="spec-type-badge">{{ $selectedType }}</span>
                </div>
            </div>
            <div class="card-body">

                <div class="spec-panel" data-type="COMPUTER">
                    @include('assets.partials.spec-computer')
                </div>

                <div class="spec-panel" data-type="PERIPHERAL">
                    @include('assets.partials.spec-peripheral')
                </div>

                <div class="spec-panel" data-type="PRINTER">
                    @include('assets.partials.spec-printer')
                </div>

                <div class="spec-panel" data-type="NETWORK_DEVICE">
                    @include('assets.partials.spec-network')
                </div>

                <div class="spec-panel" data-type="OTHER">
                    <p class="text-muted mb-0">
                        This category holds no detailed specification. Pick a category of another
                        type to record hardware, network or software details.
                    </p>
                </div>

            </div>
        </div>

    </div>

    <div class="col-lg-4">

        {{-- ================= LIFECYCLE ================= --}}
        <div class="card card-primary">
            <div class="card-header"><h4>Status</h4></div>
            <div class="card-body">

                <div class="form-group">
                    <label for="status">Status <span class="text-danger">*</span></label>
                    <select id="status" class="form-control @error('status') is-invalid @enderror" name="status" required>
                        @foreach ($statuses as $key => $label)
                            <option value="{{ $key }}" {{ $val('status', 'IN_STORAGE') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="condition">Condition <span class="text-danger">*</span></label>
                    <select id="condition" class="form-control @error('condition') is-invalid @enderror"
                            name="condition" required>
                        @foreach ($conditions as $key => $label)
                            <option value="{{ $key }}" {{ $val('condition', 'GOOD') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('condition')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="employee_id">Assigned To</label>
                    <select id="employee_id" class="form-control @error('employee_id') is-invalid @enderror"
                            name="employee_id">
                        <option value="">-- Not Assigned --</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}"
                                {{ $val('employee_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->name }} ({{ $employee->employee_code }})
                            </option>
                        @endforeach
                    </select>
                    @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="form-text text-muted">
                        For a tracked handover with dates, use <b>Assign</b> on the asset page instead.
                    </small>
                </div>

                <div class="form-group mb-0 spec-panel" data-type="PERIPHERAL">
                    <label for="parent_asset_id">Attached To</label>
                    <select id="parent_asset_id" class="form-control @error('parent_asset_id') is-invalid @enderror"
                            name="parent_asset_id">
                        <option value="">-- Not Attached --</option>
                        @foreach ($computers as $computer)
                            <option value="{{ $computer->id }}"
                                {{ $val('parent_asset_id') == $computer->id ? 'selected' : '' }}>
                                {{ $computer->asset_code }} - {{ $computer->asset_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('parent_asset_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

            </div>
        </div>

        {{-- ================= PROCUREMENT ================= --}}
        <div class="card card-primary">
            <div class="card-header"><h4>Purchase &amp; Warranty</h4></div>
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
                           name="purchase_date"
                           value="{{ $val('purchase_date') ? \Carbon\Carbon::parse($val('purchase_date'))->format('Y-m-d') : '' }}">
                    @error('purchase_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row">
                    <div class="col-6">
                        <div class="form-group mb-0">
                            <label for="warranty_start">Warranty Start</label>
                            <input id="warranty_start" type="date"
                                   class="form-control @error('warranty_start') is-invalid @enderror"
                                   name="warranty_start"
                                   value="{{ $val('warranty_start') ? \Carbon\Carbon::parse($val('warranty_start'))->format('Y-m-d') : '' }}">
                            @error('warranty_start')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group mb-0">
                            <label for="warranty_end">Warranty End</label>
                            <input id="warranty_end" type="date"
                                   class="form-control @error('warranty_end') is-invalid @enderror"
                                   name="warranty_end"
                                   value="{{ $val('warranty_end') ? \Carbon\Carbon::parse($val('warranty_end'))->format('Y-m-d') : '' }}">
                            @error('warranty_end')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- ================= IMAGE ================= --}}
        <div class="card card-primary">
            <div class="card-body">

                <div class="form-group text-center">
                    <img src="{{ $asset && $asset->image ? asset('storage/' . $asset->image) : '' }}" id="preview"
                         class="img-preview img-fluid mb-3 mt-2"
                         style="border-radius: 5px; max-height:260px; object-fit: contain;">
                </div>

                <div class="form-group mb-0">
                    <label for="image">Image (jpeg/jpg/png)</label>
                    <input id="image" type="file" class="form-control @error('image') is-invalid @enderror"
                           name="image" accept="image/png,image/jpeg" onchange="previewImage(event)">
                    @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="form-text text-muted">
                        Max 4MB. Optional.{{ $asset ? ' Leave empty to keep the current image.' : '' }}
                    </small>
                </div>

            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block mb-4">
            <i class="fa fa-save"></i> {{ $asset ? 'Update Asset' : 'Save Asset' }}
        </button>
    </div>
</div>

<script>
    function previewImage(event) {
        const input = event.target;
        const preview = document.getElementById('preview');
        preview.src = (input.files && input.files[0]) ? URL.createObjectURL(input.files[0]) : '';
    }

    // Show only the specification panel that matches the chosen category's type,
    // and disable the hidden panels so they never reach the server.
    (function () {
        const categorySelect = document.getElementById('category_id');
        const panels = document.querySelectorAll('.spec-panel');
        const badge = document.getElementById('spec-type-badge');

        function applyType() {
            const option = categorySelect.options[categorySelect.selectedIndex];
            const type = (option && option.dataset.assetType) ? option.dataset.assetType : 'OTHER';

            panels.forEach(panel => {
                const matches = panel.dataset.type === type;
                panel.style.display = matches ? '' : 'none';
                panel.querySelectorAll('input, select, textarea').forEach(field => {
                    field.disabled = !matches;
                });
            });

            if (badge) {
                badge.textContent = type.replace(/_/g, ' ');
            }
        }

        categorySelect.addEventListener('change', applyType);
        applyType();
    })();
</script>
