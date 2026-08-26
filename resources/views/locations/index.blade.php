@extends('layouts.main')

@section('content')
    <div class="section-header">
        <h1>Locations</h1>
        <div class="ml-auto">
            <a href="{{ route('locations.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Add Location</a>
        </div>
    </div>

    <div class="section-body">
        @if (session()->has('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif
        @if (session()->has('error'))
            <div class="alert alert-danger" role="alert">
                {{ session('error') }}
            </div>
        @endif

        <div class="row">
            <div class="col">
                <div class="card card-primary">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="table_id" class="table table-bordered table-hover table-striped table-condensed">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Location</th>
                                        <th>Options</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($locations as $location)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            {{-- gunakan field sesuai migrasi/model --}}
                                            <td>{{ $location->location_name }}</td>
                                            <td>
                                                <a href="{{ route('locations.edit', $location->id) }}" class="btn btn-warning">Edit</a>

                                                <form id="delete-form-{{ $location->id }}"
                                                      action="{{ route('locations.destroy', $location->id) }}"
                                                      method="POST" class="d-inline">
                                                    @method('DELETE')
                                                    @csrf

                                                    {{-- tombol bertipe button agar JS swal-confirm bisa mencegah submit --}}
                                                    <button type="button"
                                                            class="btn btn-danger swal-confirm"
                                                            data-form="delete-form-{{ $location->id }}">
                                                        Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Datatables Jquery -->
    <script>
        $(document).ready(function() {
            $('#table_id').DataTable();
        });
    </script>
@endsection
