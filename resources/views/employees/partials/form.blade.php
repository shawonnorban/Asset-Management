@php
    /** @var \App\Models\Employee|null $employee */
    $employee = $employee ?? null;
    $val = fn ($field, $default = null) => old($field, $employee->{$field} ?? $default);
@endphp

<div class="row">
    <div class="col-lg-8">

        {{-- ================= IDENTITY ================= --}}
        <div class="card card-primary">
            <div class="card-header"><h4>Employee</h4></div>
            <div class="card-body">

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="employee_code">Employee Code <span class="text-danger">*</span></label>
                            <input id="employee_code" type="text"
                                   class="form-control @error('employee_code') is-invalid @enderror"
                                   name="employee_code" value="{{ $val('employee_code') }}" maxlength="32" required>
                            @error('employee_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="name">Employee Name <span class="text-danger">*</span></label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                                   name="name" value="{{ $val('name') }}" maxlength="100" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="department_id">Department <span class="text-danger">*</span></label>
                            <select id="department_id" class="form-control @error('department_id') is-invalid @enderror"
                                    name="department_id" required>
                                <option value="">-- Select Department --</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}"
                                        {{ $val('department_id') == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="position_id">Position <span class="text-danger">*</span></label>
                            <select id="position_id" class="form-control @error('position_id') is-invalid @enderror"
                                    name="position_id" required>
                                <option value="">-- Select Position --</option>
                                @foreach ($positions as $position)
                                    <option value="{{ $position->id }}"
                                        {{ $val('position_id') == $position->id ? 'selected' : '' }}>
                                        {{ $position->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('position_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="location_id">Location</label>
                            <select id="location_id" class="form-control @error('location_id') is-invalid @enderror"
                                    name="location_id">
                                <option value="">-- Not Set --</option>
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
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-0">
                            <label for="father_name">Father's Name <span class="text-danger">*</span></label>
                            <input id="father_name" type="text"
                                   class="form-control @error('father_name') is-invalid @enderror"
                                   name="father_name" value="{{ $val('father_name') }}" maxlength="100" required>
                            @error('father_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-0">
                            <label for="mother_name">Mother's Name</label>
                            <input id="mother_name" type="text"
                                   class="form-control @error('mother_name') is-invalid @enderror"
                                   name="mother_name" value="{{ $val('mother_name') }}" maxlength="100">
                            @error('mother_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-0">
                            <label for="nid_number">NID Number</label>
                            <input id="nid_number" type="text"
                                   class="form-control @error('nid_number') is-invalid @enderror"
                                   name="nid_number" value="{{ $val('nid_number') }}" maxlength="30">
                            @error('nid_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- ================= ADDRESS ================= --}}
        <div class="card card-primary">
            <div class="card-header"><h4>Address</h4></div>
            <div class="card-body">

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-0">
                            <label for="present_address">Present Address</label>
                            <textarea id="present_address"
                                      class="form-control @error('present_address') is-invalid @enderror"
                                      name="present_address" rows="3"
                                      maxlength="500">{{ $val('present_address') }}</textarea>
                            @error('present_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-0">
                            <label for="permanent_address">Permanent Address</label>
                            <textarea id="permanent_address"
                                      class="form-control @error('permanent_address') is-invalid @enderror"
                                      name="permanent_address" rows="3"
                                      maxlength="500">{{ $val('permanent_address') }}</textarea>
                            @error('permanent_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <div class="col-lg-4">

        {{-- ================= CONTACT ================= --}}
        <div class="card card-primary">
            <div class="card-header"><h4>Contact</h4></div>
            <div class="card-body">

                <div class="form-group">
                    <label for="mobile">Mobile</label>
                    <input id="mobile" type="text" class="form-control @error('mobile') is-invalid @enderror"
                           name="mobile" value="{{ $val('mobile') }}" placeholder="01XXXXXXXXX" maxlength="20">
                    @error('mobile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="mail_address">Mail Address</label>
                    <input id="mail_address" type="email"
                           class="form-control @error('mail_address') is-invalid @enderror"
                           name="mail_address" value="{{ $val('mail_address') }}"
                           placeholder="name@company.com" maxlength="150">
                    @error('mail_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group mb-0">
                    <label for="join_date">Join Date</label>
                    <input id="join_date" type="date" class="form-control @error('join_date') is-invalid @enderror"
                           name="join_date"
                           value="{{ $val('join_date') ? \Carbon\Carbon::parse($val('join_date'))->format('Y-m-d') : '' }}">
                    @error('join_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

            </div>
        </div>


        {{-- ================= PHOTO ================= --}}
        <div class="card card-primary">
            <div class="card-header"><h4>Photo</h4></div>
            <div class="card-body">

                <div class="form-group text-center">
                    <img src="{{ $employee && $employee->image ? asset('storage/' . $employee->image) : '' }}"
                         id="preview" class="img-fluid mb-3 mt-2"
                         style="border-radius:5px; max-height:220px; object-fit:contain;">
                </div>

                <div class="form-group mb-0">
                    <label for="image">Photo (jpeg/jpg/png)</label>
                    <input id="image" type="file" class="form-control @error('image') is-invalid @enderror"
                           name="image" accept="image/png,image/jpeg" onchange="previewImage(event)">
                    @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="form-text text-muted">
                        Max 4MB. Optional.{{ $employee ? ' Leave empty to keep the current photo.' : '' }}
                    </small>
                </div>

            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block mb-4">
            <i class="fa fa-save"></i> {{ $employee ? 'Update Employee' : 'Save Employee' }}
        </button>
    </div>
</div>

<script>
    function previewImage(event) {
        const input = event.target;
        const preview = document.getElementById('preview');
        preview.src = (input.files && input.files[0]) ? URL.createObjectURL(input.files[0]) : '';
    }
</script>
