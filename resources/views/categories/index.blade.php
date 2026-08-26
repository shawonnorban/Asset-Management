@extends('layouts.main')

@section('content')
    <div class="section-header">
        <h1>Categories</h1>
        <div class="ml-auto">
            <a href="/categories/create" class="btn btn-primary"><i class="fa fa-plus"></i> Add Category</a>
        </div>
    </div>

    <div class="section-body">
        @if (session()->has('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
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
                                        <th>Category</th>
                                        <th>Asset Type</th>
                                        <th>Assets</th>
                                        <th>Options</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($categories as $category)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $category->category_name }}</td>
                                            <td>{{ $assetTypes[$category->asset_type] ?? $category->asset_type }}</td>
                                            <td>{{ $category->assets_count }}</td>
                                            <td>
                                                <a href="/categories/{{ $category->id }}/edit"
                                                    class="btn btn-warning btn-sm">Edit</a>

                                                <form id="form{{ $category->id }}" 
                                                      action="/categories/{{ $category->id }}"
                                                      method="POST" class="d-inline">
                                                    @method('delete')
                                                    @csrf
                                                    <button type="button" 
                                                            class="btn btn-danger btn-sm swal-confirm" 
                                                            data-form="form{{ $category->id }}">
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
