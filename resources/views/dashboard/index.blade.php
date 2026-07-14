@extends('layouts.app')

@section('title', 'Complaint Dashboard')

@section('content')

<div class="container-fluid py-4">

    {{-- Page Heading --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold">
                <i class="bi bi-speedometer2"></i>
                Complaint Management Dashboard
            </h2>
            <p class="text-muted">
                Upload daily complaint reports and search complaint details.
            </p>
        </div>

        <div class="col-md-4 text-end">
            <h6 class="text-secondary">
                {{ now()->format('d M Y') }}
            </h6>
        </div>
    </div>

    {{-- Statistics --}}
    <div class="row">
        <div class="col-lg-3 mb-3">
            <div class="card shadow border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6>Total Complaints</h6>
                            <h3>{{ $totalComplaints }}</h3>
                        </div>
                        <div>
                            <i class="bi bi-file-earmark-text fs-1 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 mb-3">
            <div class="card shadow border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6>Open</h6>
                            <h3>{{ $openComplaints }}</h3>
                        </div>
                        <i class="bi bi-hourglass-split fs-1 text-warning"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 mb-3">
            <div class="card shadow border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6>Closed</h6>
                            <h3>{{ $closedComplaints }}</h3>
                        </div>
                        <i class="bi bi-check-circle fs-1 text-success"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 mb-3">
            <div class="card shadow border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6>Engineers</h6>
                            <h3>{{ $totalEngineers }}</h3>
                        </div>
                        <i class="bi bi-people fs-1 text-danger"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- Upload Card --}}

    <div class="card shadow border-0 mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="bi bi-cloud-upload"></i>
                Upload Daily Complaint Report
            </h5>
        </div>

        <div class="card-body">
            <form action="{{ route('upload.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">
                            Report Date
                        </label>

                        <input type="text" name="report_date" class="form-control datepicker" required>
                    </div>

                    <div class="col-md-5">

                        <label class="form-label">
                            Excel File
                        </label>

                        <input type="file" name="excel_file" class="form-control" accept=".xlsx,.xls" required>

                    </div>

                    <div class="col-md-3">

                        <label class="form-label d-block">&nbsp;</label>
                        <button class="btn btn-success w-100">
                            <i class="bi bi-upload"></i>
                            Upload
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>



    {{-- Search Card --}}

    <div class="card shadow border-0 mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-search"></i>
                Search Complaint
            </h5>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label>
                        Engineer
                    </label>

                    <select id="engineer" class="form-select">
                        <option value="">
                            All Engineers
                        </option>

                        @foreach($engineers as $eng)
                            <option value="{{ $eng->engineer_name }}">
                                {{ $eng->engineer_name }}
                            </option>

                        @endforeach

                    </select>

                </div>

                <div class="col-md-3">
                    <label>Date</label>
                    <input type="text" id="date" class="form-control datepicker">
                </div>

                <div class="col-md-3">

                    <label>Status</label>

                    <select id="status" class="form-select">
                        <option value="">All</option>
                        <option>Open</option>
                        <option>Closed</option>
                    </select>

                </div>

                <div class="col-md-3">
                    <label>Complaint</label>
                    <input type="text" id="complaint" class="form-control" placeholder="Search Complaint">
                </div>

            </div>

            <div class="mt-3">

                <button class="btn btn-primary" id="btnSearch">
                    <i class="bi bi-search"></i>
                    Search
                </button>

                <button class="btn btn-secondary" id="btnReset">

                    Reset

                </button>
            </div>
        </div>
    </div>


    {{-- Complaint Table --}}
    @if ($errors->any())

    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)

                <li>{{ $error }}</li>

            @endforeach

        </ul>
    </div>

    @endif
    <div class="card shadow border-0">
        <div class="card-header">
            <h5 class="mb-0">
                Complaint Details
            </h5>
        </div>

        <div class="card-body">
            <table class="table table-bordered table-hover"
                   id="complaintTable">
                <thead class="table-dark">

                <tr>

                    <th>SL</th>

                    <th>Complaint</th>

                    <th>Engineer</th>

                    <th>Status</th>

                    <th>Resolution Time</th>

                    <th>Report Date</th>

                </tr>

                </thead>

                <tbody>
                    @if($complaints->count())
                        @foreach($complaints as $key => $row)
                            <tr>
                                <td>{{ $key+1 }}</td>

                                <td>{{ $row->complaint_title }}</td>

                                <td>{{ $row->engineer_name }}</td>

                                <td>
                                    @if($row->status == 'Closed')
                                        <span class="badge bg-success">Closed</span>
                                    @else
                                        <span class="badge bg-warning">{{ $row->status }}</span>
                                    @endif
                                </td>

                                <td>{{ $row->resolution_time }}</td>

                                <td>
                                    {{ optional($row->upload?->report_date)->format('d-m-Y') ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    @endif

                </tbody>
            </table>
            @if($complaints->isEmpty())
                <div class="alert alert-info text-center mt-3">
                    No Complaint Found
                </div>
            @endif
        </div>
    </div>

</div>

@endsection