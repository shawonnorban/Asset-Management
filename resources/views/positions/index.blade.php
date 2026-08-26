@extends('layouts.main')

@section('content')
    <div class="section-header">
        <h1>Positions</h1>
        <div class="ml-auto">
            <a href="{{ route('employees.index') }}" class="btn btn-light mr-2">
                <i class="fa fa-id-card"></i> Employees
            </a>
            <a href="{{ route('positions.create') }}" class="btn btn-primary">
                <i class="fa fa-plus"></i> Add Position
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
                                <th>Position</th>
                                <th width="15%">Employees</th>
                                <th width="20%">Options</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($positions as $position)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $position->name }}</td>
                                    <td>{{ $position->employees_count }}</td>
                                    <td>
                                        <a href="{{ route('positions.edit', $position->id) }}"
                                           class="btn btn-warning btn-sm">Edit</a>

                                        <form id="delete{{ $position->id }}"
                                              action="{{ route('positions.destroy', $position->id) }}"
                                              method="POST" class="d-inline">
                                            @method('delete')
                                            @csrf
                                            <button type="button" class="btn btn-danger btn-sm swal-confirm"
                                                    data-form="delete{{ $position->id }}">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center">No position recorded yet.</td></tr>
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
