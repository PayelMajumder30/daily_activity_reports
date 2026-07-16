@extends('layouts.app')

@section('title', 'Complaint Dashboard')

@section('content')

<div class="container-fluid py-4">
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
</div>

@endsection