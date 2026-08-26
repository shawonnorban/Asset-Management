@extends('layouts.main')

@section('content')
    <div class="section-header">
        <h1>Employees</h1>
        @if (auth()->user()->inRoles(['admin', 'manager']))
            <div class="ml-auto">
                <a href="{{ route('departments.index') }}" class="btn btn-light mr-2">
                    <i class="fa fa-sitemap"></i> Departments
                </a>
                <a href="{{ route('positions.index') }}" class="btn btn-light mr-2">
                    <i class="fa fa-user-tag"></i> Positions
                </a>
                <a href="{{ route('employees.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus"></i> Add Employee
                </a>
            </div>
        @endif
    </div>

    <div class="section-body">
        @if (session()->has('success'))
            <div class="alert alert-success" role="alert">{{ session('success') }}</div>
        @endif
        @if (session()->has('error'))
            <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
        @endif

        <div class="card card-primary">
            <div class="card-body">

                <form method="GET" class="form-row align-items-end mb-3">
                    <div class="col-md-3">
                        <label>Department</label>
                        <select name="department_id" class="form-control">
                            <option value="">-- All Departments --</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}"
                                    {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Location</label>
                        <select name="location_id" class="form-control">
                            <option value="">-- All Locations --</option>
                            @foreach ($locations as $location)
                                <option value="{{ $location->id }}"
                                    {{ request('location_id') == $location->id ? 'selected' : '' }}>
                                    {{ $location->location_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary"><i class="fa fa-filter"></i> Filter</button>
                        <a href="{{ route('employees.index') }}" class="btn btn-light">Reset</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table id="table_id" class="table table-bordered table-hover table-striped table-sm">
                        <thead>
                            <tr>
                                <th width="4%">No</th>
                                <th width="7%">Photo</th>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Position</th>
                                <th>Location</th>
                                <th>Mobile</th>
                                <th>Mail Address</th>
                                <th>Join Date</th>
                                <th width="16%">Options</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($employees as $employee)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="text-center">
                                        @if ($employee->image && Storage::disk('public')->exists($employee->image))
                                            <img src="{{ Storage::url($employee->image) }}" alt="employee photo"
                                                 style="width:48px; height:48px; object-fit:cover; border-radius:50%;">
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td><b>{{ $employee->employee_code }}</b></td>
                                    <td>{{ $employee->name }}</td>
                                    <td>{{ $employee->department->name ?? '-' }}</td>
                                    <td>{{ $employee->position->name ?? '-' }}</td>
                                    <td>{{ $employee->location->location_name ?? '-' }}</td>
                                    <td>{{ $employee->mobile ?? '-' }}</td>
                                    <td>{{ $employee->mail_address ?? '-' }}</td>
                                    <td>{{ optional($employee->join_date)->format('d M Y') ?? '-' }}</td>
                                    <td>
                                        <a href="{{ route('employees.show', $employee->id) }}"
                                           class="btn btn-success btn-sm">Detail</a>

                                        @if (auth()->user()->inRoles(['admin', 'manager']))
                                            <a href="{{ route('employees.edit', $employee->id) }}"
                                               class="btn btn-warning btn-sm">Edit</a>

                                            <form id="delete{{ $employee->id }}"
                                                  action="{{ route('employees.destroy', $employee->id) }}"
                                                  method="POST" class="d-inline">
                                                @method('delete')
                                                @csrf
                                                <button type="button" class="btn btn-danger btn-sm swal-confirm"
                                                        data-form="delete{{ $employee->id }}">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="11" class="text-center">No employee recorded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            $('#table_id').DataTable();
        });
    </script>
@endsection
