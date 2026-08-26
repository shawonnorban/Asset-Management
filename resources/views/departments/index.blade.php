@extends('layouts.main')

@section('content')
    <div class="section-header">
        <h1>Departments</h1>
        <div class="ml-auto">
            <a href="{{ route('employees.index') }}" class="btn btn-light mr-2">
                <i class="fa fa-id-card"></i> Employees
            </a>
            <a href="{{ route('departments.create') }}" class="btn btn-primary">
                <i class="fa fa-plus"></i> Add Department
            </a>
        </div>
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
                <div class="table-responsive">
                    <table id="table_id" class="table table-bordered table-hover table-striped table-sm">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Department</th>
                                <th width="15%">Employees</th>
                                <th width="20%">Options</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($departments as $department)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $department->name }}</td>
                                    <td>{{ $department->employees_count }}</td>
                                    <td>
                                        <a href="{{ route('departments.edit', $department->id) }}"
                                           class="btn btn-warning btn-sm">Edit</a>

                                        <form id="delete{{ $department->id }}"
                                              action="{{ route('departments.destroy', $department->id) }}"
                                              method="POST" class="d-inline">
                                            @method('delete')
                                            @csrf
                                            <button type="button" class="btn btn-danger btn-sm swal-confirm"
                                                    data-form="delete{{ $department->id }}">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center">No department recorded yet.</td></tr>
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
