@extends('layouts.app')

@section('title', 'Upload File')

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
    
    {{-- Upload Card --}}

    <div class="card shadow border-0 mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="bi bi-cloud-upload"></i>
                Upload Daily Complaint Report
            </h5>
        </div>

        <div class="card-body">
            <form id="uploadForm" action="{{ route('uploader.preview') }}" method="POST" enctype="multipart/form-data">
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

{{-- Upload preview Card --}}

    @if($preview->count())

        <div class="card shadow">

            <div class="card-header">

                <h5>Preview</h5>

            </div>

            <div class="card-body">

                <table class="table table-bordered" id="previewTable">

                    <thead>

                        <tr>

                            <th>SL</th>

                            <th>Complaint</th>

                            <th>Engineer</th>

                            <th>Status</th>

                            <th>Resolution</th>

                            <th>Action</th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach($preview as $key=>$row)

                        <tr id="row_{{ $row->id }}">
                            <td>{{ $loop->iteration }}</td>

                            <td id="complaint_title_{{ $row->id }}">
                                {{ $row->complaint_title }}
                            </td>

                            <td id="engineer_name_{{ $row->id }}">
                                {{ $row->engineer_name }}
                            </td>

                            <td id="status_{{ $row->id }}">
                                {{ $row->status }}
                            </td>

                            <td id="resolution_time_{{ $row->id }}">
                                {{ $row->resolution_time }}
                            </td>

                            <td>
                                <button
                                    class="btn btn-sm btn-warning btnEdit"
                                    data-id="{{ $row->id }}">
                                    Edit
                                </button>
                            </td>
                        </tr>

                        @endforeach

                    </tbody>

                </table>

                <form action="{{ route('uploader.save') }}" method="POST">
                    
                    @csrf
                    <input type="hidden" name="upload_id" value="{{ $uploadId }}" name="upload_id">

                    <button class="btn btn-success">

                        Save All

                    </button>

                </form>

            </div>

        </div>

    @endif
</div>

    @push('scripts')
    <script>
        $('#uploadForm').submit(function(e){

            e.preventDefault();

            let formData=new FormData(this);

            $.ajax({

                url:"{{ route('uploader.preview') }}",
                type:"POST",
                data:formData,
                processData:false,
                contentType:false,

                success:function(res){

                    $('#previewTable tbody').html('');

                    $.each(res.rows,function(i,row){

                        $('#previewTable tbody').append(`

                        <tr>

                            <td>${i+1}</td>

                            <td id="complaint_title_${row.id}">
                                ${row.complaint_title}
                            </td>

                            <td id="engineer_name_${row.id}">
                                ${row.engineer_name}
                            </td>

                            <td id="status_${row.id}">
                                ${row.status}
                            </td>

                            <td id="resolution_time_${row.id}">
                                ${row.resolution_time ?? ''}
                            </td>

                            <td>
                                <button
                                    type="button"
                                    class="btn btn-warning btnEdit"
                                    data-id="${row.id}">
                                    Edit
                                </button>
                            </td>

                        </tr>

                        `);

                    });

                    $('#upload_id').val(res.upload_id);

                }

            });

        });

        $(document).on('click', '.btnEdit', function () {

            let id = $(this).data('id');

            let title = $('#complaint_title_' + id).text().trim();
            let engineer = $('#engineer_name_' + id).text().trim();
            let status = $('#status_' + id).text().trim();
            let time = $('#resolution_time_' + id).text().trim();

            $('#complaint_title_' + id).html(
                `<input type="text" class="form-control" value="${title}">`
            );

            $('#engineer_name_' + id).html(
                `<input type="text" class="form-control" value="${engineer}">`
            );

            $('#status_' + id).html(
                `<input type="text" class="form-control" value="${status}">`
            );

            $('#resolution_time_' + id).html(
                `<input type="text" class="form-control" value="${time}">`
            );

            $(this)
                .removeClass('btn-warning btnEdit')
                .addClass('btn-success btnSaveRow')
                .text('Save');
        });
    </script>

@endsection