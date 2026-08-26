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
            <a href="/review-reports" class="btn btn-primary">
                <i class="fa fa-back"></i> Back
            </a>
        </div>
    </div>

    <div class="section-body">
        @if (session()->has('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <div class="card card-primary">
                    <div class="card-header">
                        Inventory Reports
                    </div>
                    <div class="card-body">

                        <div class="row">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>Asset Name</label>
                                    <input type="text" class="form-control"
                                           value="{{ optional($issueReport->asset)->asset_name }}"
                                           disabled>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>Asset Code</label>
                                    <input type="text" class="form-control"
                                           value="{{ optional($issueReport->asset)->asset_code }}"
                                           disabled>
                                </div>
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
                                <div class="form-group">
                                    <label>Category</label>
                                    <input type="text" class="form-control"
                                           value="{{ optional(optional($issueReport->asset)->category)->category_name }}"
                                           disabled>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>Brand</label>
                                    <input type="text" class="form-control"
                                           value="{{ optional($issueReport->asset)->brand }}"
                                           disabled>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>Location</label>
                                    <input type="text" class="form-control"
                                           value="{{ optional(optional($issueReport->asset)->location)->location_name }}"
                                           disabled>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="form-group">
                            <label>Report Title</label>
                            <input type="text" class="form-control"
                                   value="{{ $issueReport->title }}" disabled>
                        </div>

                        <div class="form-group">
                            <label>Report Description</label>
                            <textarea class="form-control" rows="6" disabled>{{ $issueReport->description }}</textarea>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card card-primary">
                    <div class="card-header">
                        Repair Analysis & Feedback
                    </div>
                    <div class="card-body">

                        {{-- ANALISIS ADMIN --}}
                        @if ($feedback)
                            <label>Admin Analysis</label>
                            <div class="alert alert-light">
                                {{ $feedback->decision_analysis }}
                            </div>
                        @endif

                        {{-- USER REPLY --}}
                        @if ($feedbackReply)
                            <label>User Reply</label>
                            <div class="alert alert-primary">
                                {{ $feedbackReply->feedback_reply }}
                            </div>
                        @endif

                        <hr>

                        {{-- REPLY FORM (when there is no reply yet) --}}
                        @if ($feedback && !$feedbackReply)
                            <form action="/review-reports/detail/{{ $issueReport->id }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label>Give Feedback</label>
                                    <textarea class="form-control"
                                              name="feedback_replies"
                                              rows="5"
                                              required></textarea>
                                    @error('feedback_replies')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit"
                                        class="btn btn-success float-right">
                                    Send Reply
                                </button>
                            </form>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
