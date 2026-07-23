@extends('layouts.app')

@section('title', 'Total Complaints')

@section('content')

<div class="container-fluid py-4">

   <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold">
                <i class="bi bi-file-earmark-text"></i>
                Total Complaints
            </h2>
            <p class="text-muted">
                View, search, and manage complaint records with engineer-wise details, status, and report information.
            </p>
        </div>

        <div class="col-md-4 text-end">
            <h6 class="text-secondary">
                {{ now()->format('d M Y') }}
            </h6>
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
            <form id="uploadForm" action="{{ route('upload.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">
                            Report Date <span class="text-danger">*</span>
                        </label>

                        <input type="text" id="report_date" name="report_date" value="{{old('report_date')}}" 
                            class="form-control datepicker" @error('report_date') is-invalid @enderror autocomplete="off">
                        @error('report_date')
                            <small class="text-danger">
                                {{ $message }}
                            </small>
                        @enderror
                    </div>

                    <div class="col-md-5">

                        <label class="form-label">
                            Excel File <span class="text-danger">*</span>
                        </label>

                        <input type="file" id="excel_file" name="excel_file" class="form-control @error('excel_file') is-invalid @enderror" accept=".xlsx,.xls">
                        @error('excel_file')
                            <small class="text-danger">
                                {{ $message }}
                            </small>
                        @enderror

                    </div>

                    <div class="col-md-3">

                        <label class="form-label d-block">&nbsp;</label>
                        <button id ="uploadBtn" class="btn btn-success w-100" disabled>
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
                        <option value="">All status</option>
                        @foreach($statuses as $status)
                            <option value="{{$status}}">
                                {{ $status}}
                            </option>
                        @endforeach
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
                        <th>Activity Details</th>
                        <th>Type of Activity</th>
                        <th>Asset Tag No</th>
                        <th>Engineer</th>
                        <th>Status</th>
                        <th>Activity Duration</th>
                        <th>Report Date</th>
                    </tr>

                </thead>

                <tbody>
                    @if($complaints->count())
                        @foreach($complaints as $key => $row)
                            <tr>
                                <td>{{ $key+1 }}</td>

                                <td>{{ $row->complaint_title }}</td>

                                <td>{{ !empty($row->type_of_activity) ? $row->type_of_activity : 'NA' }}</td>

                                <td>{{ !empty($row->asset_tag_no) ? $row->asset_tag_no : 'NA'}}</td>

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

@push('scripts')
<script>
    $(document).ready(function(){

        $('#uploadBtn').prop('disabled', true);

        function checkFields(){

            let date = $('#report_date').val().trim();
            let file = $('#excel_file').val();

            if(date !== '' && file !== ''){
                $('#uploadBtn').prop('disabled', false);
            }else{
                $('#uploadBtn').prop('disabled', true);
            }
        }

        $('#report_date').on('change keyup', checkFields);
        $('#excel_file').on('change', checkFields);

        $('#btnSearch').click(function (e) {
            e.preventDefault();
            $.ajax({
                url: "{{ route('complaints.search') }}",
                type: "GET",
                data: {
                    engineer: $('#engineer').val(),
                    status: $('#status').val(),
                    complaint: $('#complaint').val(),
                    date: $('#date').val()
                },

                success: function (response) {
                    let table = $('#complaintTable').DataTable();
                    table.clear();
                    $.each(response, function (index, item) {
                        let badge = '';
                        if(item.status == 'Closed'){
                            badge =
                            '<span class="badge bg-success">Closed</span>';
                        }else{
                            badge =
                            '<span class="badge bg-warning">'+item.status+'</span>';
                        }

                        table.row.add([
                            index + 1,
                            item.complaint_title,
                            item.type_of_activity ?? '-',
                            item.asset_tag_no ?? '-',
                            item.engineer_name,
                            badge,
                            item.resolution_time,
                            item.report_date

                        ]);

                    });

                    table.draw();
                }
            });

        });

        $('#btnReset').click(function () {

            $('#engineer').val('');
            $('#status').val('');
            $('#complaint').val('');
            $('#date').val('');
            location.reload();

        });

        $('#uploadForm').submit(function(){
            $('#uploadBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Uploading...');

        });

    }); 
    
    @if(session('success'))

        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: "{{ session('success') }}",
            confirmButtonText: 'OK'
        });

    @endif

</script>
@endpush
@endsection