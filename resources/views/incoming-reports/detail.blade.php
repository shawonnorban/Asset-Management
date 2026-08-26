@extends('layouts.main')

<style>
    td {
        font-size: 16px;
        padding-bottom: 5px;
    }
</style>

@section('content')
<div class="section-header">
    <h1>Report Detail</h1>
    <div class="ml-auto">
        <a href="/incoming-reports" class="btn btn-primary">
            <i class="fa fa-back"></i> Back
        </a>
    </div>
</div>

<div class="section-body">

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card card-primary">
                <div class="card-header">Inventory Reports</div>
                <div class="card-body">

                    <div class="row">
                        <div class="col-lg-4">
                            <label>Asset Name</label>
                            <input class="form-control"
                                   value="{{ $issueReport->asset->asset_name }}" disabled>
                        </div>
                        <div class="col-lg-4">
                            <label>Asset Code</label>
                            <input class="form-control"
                                   value="{{ $issueReport->asset->asset_code }}" disabled>
                        </div>
                        <div class="col-lg-4">
                            <label>Report Status</label>
                            @if ($issueReport->status === 'Pending')
                                <div class="alert alert-warning">Pending</div>
                            @elseif ($issueReport->status === 'In Review')
                                <div class="alert alert-primary">In Review</div>
                            @elseif ($issueReport->status === 'Completed')
                                <div class="alert alert-success">Completed</div>
                            @endif
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-lg-4">
                            <label>Category</label>
                            <input class="form-control"
                                   value="{{ $issueReport->asset->category->category_name }}" disabled>
                        </div>
                        <div class="col-lg-4">
                            <label>Brand</label>
                            <input class="form-control"
                                   value="{{ $issueReport->asset->brand }}" disabled>
                        </div>
                        <div class="col-lg-4">
                            <label>Location</label>
                            <input class="form-control"
                                   value="{{ $issueReport->asset->location->location_name }}" disabled>
                        </div>
                    </div>

                    <hr>

                    <div class="form-group">
                        <label>Report Title</label>
                        <input class="form-control" value="{{ $issueReport->title }}" disabled>
                    </div>

                    <div class="form-group">
                        <label>Report Description</label>
                        <textarea class="form-control" rows="5" disabled>{{ $issueReport->description }}</textarea>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-primary">
                <div class="card-header">Action</div>
                <div class="card-body">

                    @if ($issueReport->status === 'Pending')
                        <form id="perbaikiForm{{ $issueReport->id }}"
                              action="/incoming-reports/detail/{{ $issueReport->id }}/perbaiki"
                              method="POST">
                            @method('PUT')
                            @csrf
                            <button type="button"
                                    class="btn btn-primary btn-block"
                                    onclick="confirmPerbaiki({{ $issueReport->id }})">
                                <i class="fas fa-tools"></i> In Review
                            </button>
                        </form>

                    @elseif ($issueReport->status === 'In Review')
                        <form id="selesaiForm{{ $issueReport->id }}"
                              action="/incoming-reports/detail/{{ $issueReport->id }}/complete"
                              method="POST">
                            @method('PUT')
                            @csrf
                            <div class="form-group">
                                <label>Report Analysis</label>
                                <textarea name="decision_analysis"
                                          class="form-control"
                                          rows="5" required></textarea>
                            </div>
                            <button type="button"
                                    class="btn btn-success btn-block"
                                    onclick="confirmSelesai({{ $issueReport->id }})">
                                <i class="fas fa-check"></i> Completed
                            </button>
                        </form>

                    @elseif ($issueReport->status === 'Completed')
                        @if ($feedback)
                            <label>Admin Analysis</label>
                            <div class="alert alert-light">
                                {{ $feedback->decision_analysis }}
                            </div>
                        @endif

                        @foreach ($feedbackReplies as $reply)
                            <label>User Reply</label>
                            <div class="alert alert-primary">
                                {{ $reply->feedback_reply }}
                            </div>
                        @endforeach
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmPerbaiki(id) {
    Swal.fire({
        title: 'Konfirmasi',
        text: 'Change the status to In Review?',
        icon: 'question',
        showCancelButton: true
    }).then((res) => {
        if (res.isConfirmed) {
            document.getElementById('perbaikiForm' + id).submit();
        }
    });
}

function confirmSelesai(id) {
    Swal.fire({
        title: 'Konfirmasi',
        text: 'Mark this report as completed?',
        icon: 'question',
        showCancelButton: true
    }).then((res) => {
        if (res.isConfirmed) {
            document.getElementById('selesaiForm' + id).submit();
        }
    });
}
</script>
@endsection
