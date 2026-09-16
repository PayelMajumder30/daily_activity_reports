@extends('layouts.app')

@section('title', 'Upload Complaints')

@section('content')

<div class="container-fluid py-4">

    {{-- Page Heading --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold">
                <i class="bi bi-cloud-upload"></i>
                Upload Complaint
            </h2>
            <p class="text-muted">
                Upload daily complaint reports.
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

            <form id="uploadForm" method="POST" enctype="multipart/form-data" autocomplete="off">
                @csrf

                <div class="row">

                    <div class="col-md-4">
                        <label>Report Date</label>
                        <input type="text" name="report_date" class="form-control datepicker" required>                       
                    </div>

                    <div class="col-md-5">
                        <label>Excel File</label>
                        <input type="file" name="excel_file" class="form-control" accept=".xlsx,.xls"  required>      
                    </div>

                    <div class="col-md-3">
                        <label class="d-block">&nbsp;</label>

                        <div class="d-flex gap-2">

                            <button type="submit" id="btnUpload" class="btn btn-success flex-fill" disabled>    
                                <i class="bi bi-upload"></i>
                                Upload
                            </button>

                            <a href="{{ route('uploader.downloadTemplate') }}" class="btn btn-warning flex-fill" title="Download Sample Excel File">                           
                                <i class="bi bi-file-earmark-excel"></i>
                                Sample Excel
                            </a>

                        </div>
                    </div>

                </div>

            </form>

            <input type="hidden" id="upload_id" value="{{ $uploadId }}">

        </div>
    </div>

    {{-- Preview Card --}}
    <div class="card shadow border-0 {{ $preview->count() ? '' : 'd-none' }}" id="previewCard">
        
        <div class="alert alert-info mb-3">
            <div class="row">
                <div class="col-md-6">
                    <strong>Report Date :</strong>
                    {{ optional($upload)->report_date?->format('d-m-Y') }}
                </div>

                <div class="col-md-6 text-end">
                    <strong>File :</strong>
                    {{ $upload->file_name ?? 'N/A' }}
                </div>
            </div>
        </div>
        
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">Preview Data</h5>
        </div>

        <div class="card-body">

            <table class="table table-bordered" id="previewTable">
  
                <thead>

                    <tr>
                        <th width="5%">SL</th>
                        <th>Activity Details</th>
                        <th>Type of Activity</th>
                        <th>Asset Tag No</th>
                        <th>Engineer</th>
                        <th>Status</th>
                        <th>Activity Duration</th>
                        <th width="12%">Action</th>
                    </tr>

                </thead>

               <tbody>

                    @foreach($preview as $row)
                    <tr id="row_{{ $row->id }}">

                        <td>{{ $loop->iteration }}</td>

                        <td id="complaint_{{ $row->id }}">
                            {{ $row->complaint_title }}
                        </td>

                        <td id="activity_{{ $row->id }}">
                            {{ $row->type_of_activity ?? 'NA' }}
                        </td>

                        <td id="asset_{{ $row->id }}">
                            {{ $row->asset_tag_no ?? 'NA' }}
                        </td>

                        <td id="engineer_{{ $row->id }}">
                            {{ $row->engineer_name }}
                        </td>

                        <td id="status_{{ $row->id }}">
                            {{ $row->status }}
                        </td>

                        <td id="time_{{ $row->id }}">
                            {{ $row->resolution_time }}
                        </td>

                        <td>
                            <button class="btn btn-warning btnEdit"
                                    data-id="{{ $row->id }}">
                                Edit
                            </button>
                        </td>

                    </tr>
                    @endforeach

                </tbody>

            </table>
 
            <div class="d-flex gap-2 mt-3">
                <button type="button" id="btnFinalSave" class="btn btn-success">             
                    <i class="bi bi-save"></i>Final Save
                </button>

                <button type="button" id="btnDeleteUpload" class="btn btn-danger">
                    <i class="bi bi-trash"></i>Delete Upload
                </button>
            </div>

        </div>

    </div>

</div>

@push('scripts')

    <script>
       
        function checkUploadForm() {
            let reportDate = $('input[name="report_date"]').val();
            let excelFile  = $('input[name="excel_file"]').val();

            if(reportDate !== '' && excelFile !== ''){
                $('#btnUpload').prop('disabled', false);
            } else{
                $('#btnUpload').prop('disabled', true);
            }
        }

        $(document).on('change keyup', 'input[name="report_date"], input[name="excel_file"]',
            function(){
                checkUploadForm();
            }
        )
        // Upload AJAX
        $('#uploadForm').submit(function(e){

            e.preventDefault();

            $('#btnUpload')
            .prop('disabled', true)
            .html('<i class="bi bi-arrow-repeat"></i> Uploading...');

            // 1. Show your loading indicator here (this is likely already happening somewhere)
            // $('#loadingModal').modal('show'); 
       
            let formData=new FormData(this);

            $.ajax({

                url:"{{ route('uploader.preview') }}",
                type:"POST",
                data:formData,
                processData:false,
                contentType:false,
                dataType: "json",

                success:function(res){
                    // --- ADD THIS LINE TO HIDE THE MODAL ---
                    $('#loadingModal').modal('hide'); 
                    // If it is a custom overlay instead of a modal, use: 
                    // $('#loadingOverlay').addClass('d-none');

                    Swal.fire({

                        icon:'success',
                        title:'Upload Successful',
                        text:'Preview generated successfully.',
                        timer:1500,
                        showConfirmButton:false

                    });

                    $('#btnUpload')
                    .prop('disabled', false)
                    .html('<i class="bi bi-upload"></i> Upload');

                    console.log(res);
                    $('#upload_id').val(res.upload_id);

                    $('#previewCard').removeClass('d-none');

                    $('#previewTable tbody').html('');

                    $.each(res.rows,function(i,row){

                        $('#previewTable tbody').append(`

                            <tr id="row_${row.id}">

                                <td>${i+1}</td>

                                <td id="complaint_${row.id}">
                                    ${row.complaint_title}
                                </td>

                                <td id="activity_${row.id}">
                                    ${row.type_of_activity ?? 'NA'}
                                </td>

                                <td id="asset_${row.id}">
                                    ${row.asset_tag_no ?? 'NA'}
                                </td>

                                <td id="engineer_${row.id}">
                                    ${row.engineer_name}
                                </td>

                                <td id="status_${row.id}">
                                    ${row.status}
                                </td>

                                <td id="time_${row.id}">
                                    ${row.resolution_time || ''}
                                </td>

                                <td>
                                    <button class="btn btn-warning btnEdit" data-id="${row.id}"> 
                                        Edit
                                    </button>
                                </td>

                            </tr>

                        `);
                    });

                },
                error:function(xhr){

                    $('#loadingModal').modal('hide');

                    console.log(xhr.responseText);

                    $('#btnUpload')
                        .prop('disabled', false)
                        .html('<i class="bi bi-upload"></i> Upload');

                    let message = 'This report date or file already exist.';

                    if (xhr.status === 422 && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Upload Failed',
                        text: message
                    });

                }

            });

        });

        // Edit Button
        $(document).on('click','.btnEdit',function(){

            let id=$(this).data('id');

            $('#complaint_'+id).html(
                '<input class="form-control" value="'+$('#complaint_'+id).text().trim()+'">'
            );

            $('#activity_'+id).html(
                '<input class="form-control" value="'+$('#activity_'+id).text().trim()+'">'
            );

            $('#asset_'+id).html(
                '<input class="form-control" value="'+$('#asset_'+id).text().trim()+'">'
            );

            $('#engineer_'+id).html(
                '<input class="form-control" value="'+$('#engineer_'+id).text().trim()+'">'
            );

            $('#status_'+id).html(
                '<input class="form-control" value="'+$('#status_'+id).text().trim()+'">'
            );

            $('#time_'+id).html(
                '<input class="form-control" value="'+$('#time_'+id).text().trim()+'">'
            );

            $(this).removeClass('btn-warning btnEdit').addClass('btn-success btnSave').text('Save');
        });

        // Save Row AJAX
        $(document).on('click', '.btnSave', function () {

            let button = $(this);
            let id = button.data('id');
            let updateUrl = "{{ route('uploader.update', ['id' => '__ID__']) }}";

            $.ajax({
                url: updateUrl.replace('__ID__', id),
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    complaint_title: $('#complaint_' + id + ' input').val(),
                    type_of_activity: $('#activity_' + id + ' input').val(),
                    asset_tag_no: $('#asset_' + id + ' input').val(),
                    engineer_name: $('#engineer_' + id + ' input').val(),
                    status: $('#status_' + id + ' input').val(),
                    resolution_time: $('#time_' + id + ' input').val()

                },

                success: function () {

                    let title = $('#complaint_' + id + ' input').val();
                    let activity = $('#activity_' + id + ' input').val();
                    let asset = $('#asset_' + id + ' input').val();
                    let engineer = $('#engineer_' + id + ' input').val();
                    let status = $('#status_' + id + ' input').val();
                    let time = $('#time_' + id + ' input').val();

                    $('#complaint_' + id).text(title);
                    $('#activity_' + id).text(activity || 'NA');
                    $('#asset_' + id).text(asset || 'NA');
                    $('#engineer_' + id).text(engineer);
                    $('#status_' + id).text(status);
                    $('#time_' + id).text(time);

                    button
                        .removeClass('btn-success btnSave')
                        .addClass('btn-warning btnEdit')
                        .text('Edit');

                    Swal.fire({

                        icon:'success',
                        title:'Updated',
                        text:'Row updated successfully.',
                        timer:1200,
                        showConfirmButton:false

                    });

                }

            });

        });

        // Final Save AJAX
        $('#btnFinalSave').click(function(){

            let btn = $(this);

            btn.prop('disabled', true)
            .html('<i class="bi bi-arrow-repeat"></i> Saving...');

            $.ajax({
                url: "{{ route('uploader.save') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    upload_id: $('#upload_id').val()
                },

                success: function(response){

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message
                    }).then(function(){
                        location.reload();
                    });

                },

                error: function(xhr){

                    btn.prop('disabled', false)
                    .html('<i class="bi bi-save"></i> Final Save');

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON.message
                    });

                }

            });

        });

        // delete temporary uploaded file
        $('#btnDeleteUpload').click(function () {
            let uploadId = $('#upload_id').val();
            let btn = $(this);

            Swal.fire({
                title: 'Delete Uploaded Data?',
                text: 'This uploaded Excel preview will be removed.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {

                if(result.isConfirmed){

                    // Disable button while deleting
                    btn.prop('disabled', true).html('<i class="bi bi-arrow-repeat"></i> Deleting...');

                    $.ajax({
                        url: "{{ route('uploader.delete', '__ID__') }}".replace('__ID__', uploadId),
                        type: "DELETE",
                        data:{
                            _token:"{{ csrf_token() }}"
                        },

                        success:function(res){

                            Swal.fire({
                                icon:'success',
                                title:'Deleted',
                                text:res.message
                            });

                            $('#previewCard').addClass('d-none');
                            $('#previewTable tbody').html('');
                            $('#uploadForm')[0].reset();
                            $('#upload_id').val('');
                            $('#btnUpload').prop('disabled', true).html('<i class="bi bi-upload"></i> Upload');

                            //restore delete button
                            btn.prop('disabled', false).html('<i class="bi bi-trash"></i> Delete Upload');
         
                        },

                        error:function(xhr){

                            btn.prop('disabled', false).html('<i class="bi bi-trash"></i> Delete Upload');
                            Swal.fire({
                                icon:'error',
                                title:'Error',
                                text:xhr.responseJSON.message
                            });

                        }

                    });

                }

            });

        });
    </script>
@endpush

@endsection