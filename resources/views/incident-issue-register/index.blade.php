@extends('layouts.app')

@section('title', 'Incident Issue Register')

@section('content')

<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold">
                <i class="bi bi-person-workspace"></i>
                Incident register
            </h2>

            <p class="text-muted">
                Manage all incident registers
            </p>

        </div>

        <div class="col-md-4 text-end">

            <h6 class="text-secondary">
                {{ now()->format('d M Y') }}
            </h6>

        </div>

    </div>


    <div class="card">

        <div class="card-header d-flex justify-content-between align-items-center">

            <h5 class="mb-0">
                Incident Issue Register
            </h5>

            @if(auth()->user()->role != 2)

                <a href="{{ route('incident_issue_register.create') }}"
                   class="btn btn-primary">

                    <i class="fas fa-plus"></i>
                    Add Incident

                </a>

            @endif

        </div>

        <div class="card-body">

            {{-- Tabs --}}

            <ul class="nav nav-tabs" id="incidentTabs">

                <li class="nav-item">

                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#openTab">                                                   
                        Open

                        <span class="badge bg-danger">
                            {{ $openIncidents->count() }}
                        </span>

                    </button>

                </li>

                <li class="nav-item">

                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#attendTab">                                                    
                        Attended

                        <span class="badge bg-warning">
                            {{ $attendIncidents->count() }}
                        </span>

                    </button>

                </li>

                <li class="nav-item">

                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#closeTab">
                        Closed

                        <span class="badge bg-success">
                            {{ $closeIncidents->count() }}
                        </span>

                    </button>

                </li>

            </ul>


            <div class="tab-content mt-3">

                {{-- OPEN --}}

                <div class="tab-pane fade show active" id="openTab">                    
                    @include(
                        'incident-issue-register.partials.table',
                        [
                            'incidents' => $openIncidents,
                            'status' => 'Open'
                        ]
                    )

                </div>


                {{-- ATTEND --}}

                <div class="tab-pane fade" id="attendTab">                    

                    @include(
                        'incident-issue-register.partials.table',
                        [
                            'incidents' => $attendIncidents,
                            'status' => 'Attend'
                        ]
                    )

                </div>


                {{-- CLOSE --}}

                <div class="tab-pane fade" id="closeTab">                    

                    @include(
                        'incident-issue-register.partials.table',
                        [
                            'incidents' => $closeIncidents,
                            'status' => 'Close'
                        ]
                    )

                </div>

            </div>

        </div>

    </div>


    {{-- Incident Modal --}}

    <div class="modal fade" id="incidentModal" tabindex="-1">        

        <div class="modal-dialog modal-lg modal-dialog-centered">

            <div class="modal-content">
                <div class="modal-header">

                    <h5 class="modal-title" id="incidentModalTitle">                      
                        Incident Details
                    </h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>                                                                 

                </div>

                <div class="modal-body">

                    <input type="hidden" id="incident_id">
                        
                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <strong>Call ID:</strong>
                            <div id="modal_call_id">-</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <strong>Status:</strong>
                            <div id="modal_status">-</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <strong>Asset Model:</strong>
                            <div id="modal_asset_model">-</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <strong>Tag No:</strong>
                            <div id="modal_tag_no">-</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <strong>Serial No:</strong>
                            <div id="modal_serial_no">-</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <strong>Custodian:</strong>
                            <div id="modal_custodian">-</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <strong>Issue Category:</strong>
                            <div id="modal_category">-</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <strong>Call Generated:</strong>
                            <div id="modal_generated">-</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <strong>Call Attended:</strong>
                            <div id="modal_attended">-</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <strong>Call Closed:</strong>
                            <div id="modal_closed">-</div>
                        </div>

                    </div>

                    <hr>

                    <div class="mb-3">

                        <label class="form-label">
                            Issue Details / Remarks
                        </label>

                        <textarea id="modal_remarks" class="form-control" rows="5" readonly></textarea>
                                                            
                    </div>
                    
                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">                                       
                        Cancel
                    </button>

                    @if(auth()->user()->role == 2)

                        <button type="button" class="btn btn-primary" id="incidentActionBtn">                                                       
                            Save
                        </button>

                    @endif

                </div>

            </div>

        </div>

    </div>

    {{-- Change Assigned Engineer Modal --}}

    <div class="modal fade" id="changeEngineerModal" tabindex="-1"
        aria-labelledby="changeEngineerModalLabel" aria-hidden="true">

        <div class="modal-dialog modal-md modal-dialog-centered">

            <div class="modal-content">

                <div class="modal-header">

                    <h5 class="modal-title" id="changeEngineerModalLabel">
                        Change Assigned Engineer
                    </h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">                                                                        
                    </button>

                </div>

                <div class="modal-body">

                    <input type="hidden" id="change_engineer_incident_id">
                    <div class="mb-3">
                        <label class="form-label">
                            Current Engineer
                        </label>
                        <input type="text" class="form-control" id="current_engineer_name" readonly>                                                                           
                    </div>


                    <div class="mb-3">

                        <label for="new_engineer_id" class="form-label">
                            Select New Engineer <span class="text-danger">*</span>
                        </label>

                        <select class="form-select" id="new_engineer_id">
                                
                            <option value="">
                                Select Engineer
                            </option>

                            @foreach($engineers as $engineer)

                                <option value="{{ $engineer->id }}">
                                    {{ $engineer->name }}
                                    @if(!empty($engineer->emp_id))
                                        - {{ $engineer->emp_id }}
                                    @endif
                                </option>

                            @endforeach

                        </select>

                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">                                                    
                        Cancel
                    </button>

                    <button type="button" class="btn btn-primary" id="changeEngineerBtn">                                              
                        Save
                    </button>
                </div>

            </div>

        </div>

    </div>
</div>

@push('scripts')

<script>

    /*
    |--------------------------------------------------------------------------
    | Auto Refresh Incident Issue Register
    |--------------------------------------------------------------------------
    | Refresh the page every 60 seconds so newly assigned incidents
    | are automatically visible to the engineer.
    |--------------------------------------------------------------------------
    */

    setInterval(function(){
        location.reload();
    }, 60000);

    $(document).on('click', '.incident-status-btn', function () {

        let id = $(this).data('id');
        let status = $(this).data('status');

        $('#incident_id').val(id);

        $.ajax({

            url: "{{ url('incident_issue_register') }}/" + id + "/details",
            type: "GET",

            success: function (response) {

                if (!response.status) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message,
                        confirmButtonText: 'OK'
                    });

                    return;
                }

                let data = response.data;

                $('#modal_call_id').text(data.call_id);
                $('#modal_status').text(data.status);
                $('#modal_asset_model').text(data.asset_model);
                $('#modal_tag_no').text(data.tag_no);
                $('#modal_serial_no').text(data.serial_no);
                $('#modal_custodian').text(data.custodian);
                $('#modal_category').text(data.category);

                $('#modal_generated').text(
                    data.call_generated_at ?? '-'
                );

                $('#modal_attended').text(
                    data.call_attended_at ?? '-'
                );

                $('#modal_closed').text(
                    data.call_closed_at ?? '-'
                );

                $('#modal_remarks').val(
                    data.remarks ?? ''
                );


                /*
                |--------------------------------------------------------------------------
                | Engineer Action
                |--------------------------------------------------------------------------
                */

                @if(auth()->user()->role == 2)

                    if (status === 'Open') {

                        $('#incidentModalTitle').text('Attend Incident');                            

                        $('#incidentActionBtn')
                            .text('Attend Call')
                            .removeClass('btn-success')
                            .addClass('btn-primary')
                            .show();

                        $('#modal_remarks').prop('readonly', false);                           

                    }

                    else if (status === 'Attend') {

                        $('#incidentModalTitle').text('Close Incident');                            

                        $('#incidentActionBtn')
                            .text('Close Call')
                            .removeClass('btn-primary')
                            .addClass('btn-success')
                            .show();

                        $('#modal_remarks').prop('readonly', false);
                            

                    }

                    else {
                        $('#incidentModalTitle').text('Incident Details');                          
                        $('#incidentActionBtn').hide();                          
                        $('#modal_remarks').prop('readonly', true);                           
                    }

                @else
                    $('#incidentModalTitle').text('Incident Details');                      
                    $('#incidentActionBtn').hide();                       
                    $('#modal_remarks').prop('readonly', true);                       
                @endif


                let modal = new bootstrap.Modal(
                    document.getElementById('incidentModal')
                );

                modal.show();
            },

            error: function () {

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Unable to fetch incident details.',
                    confirmButtonText: 'OK'
                });

            }

        });

    });


    /*
    |--------------------------------------------------------------------------
    | Attend / Close
    |--------------------------------------------------------------------------
    */

    $('#incidentActionBtn').on('click', function () {

        let id = $('#incident_id').val();
        let remarks = $('#modal_remarks').val().trim();
        let status = $('#modal_status').text().trim();

        if (!remarks) {

            Swal.fire({
                icon: 'warning',
                title: 'Issue Details Required',
                text: 'Issue details cannot be empty.',
                confirmButtonText: 'OK'
            });

            return;
        }


        let actionUrl = '';

        if (status === 'Open') {
            actionUrl = "{{ url('incident_issue_register') }}/" + id + "/attend";                                              
        }

        else if (status === 'Attend') {
            actionUrl = "{{ url('incident_issue_register') }}/" + id + "/close";                                          
        }

        else {
            return;
        }


        $.ajax({
            url: actionUrl,
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                remarks: remarks
            },


            beforeSend: function () {
                $('#incidentActionBtn')
                    .prop('disabled', true)
                    .text('Saving...');
            },


            success: function (response) {

                if (response.status) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        confirmButtonText: 'OK'
                    }).then(function () {
                        location.reload();
                    });

                }

                else {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message ?? 'Unable to update incident.',
                        confirmButtonText: 'OK'
                    });

                }

            },


            error: function (xhr) {

                let message = 'Something went wrong.';

                if (xhr.responseJSON?.message) {
                    message = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message,
                    confirmButtonText: 'OK'
                });
            },

            complete: function () {
                $('#incidentActionBtn').prop('disabled', false);                 
            }

        });

    });

    /*
    |--------------------------------------------------------------------------
    | Open Change Engineer Modal
    |--------------------------------------------------------------------------
    */

    $(document).on('click', '.assigned-engineer-btn', function () {

        let incidentId = $(this).data('id');
        let engineerId = $(this).data('engineer-id');
        let engineerName = $(this).data('engineer-name');

        $('#change_engineer_incident_id').val(incidentId);

        $('#current_engineer_name').val(engineerName);

        /*
        |--------------------------------------------------------------------------
        | Reset dropdown
        |--------------------------------------------------------------------------
        */

        $('#new_engineer_id').val('');

        /*
        |--------------------------------------------------------------------------
        | Hide current engineer from dropdown
        |--------------------------------------------------------------------------
        */

        $('#new_engineer_id option').show();
        $('#new_engineer_id option[value="' + engineerId + '"]').hide();

        /*
        |--------------------------------------------------------------------------
        | Open modal
        |--------------------------------------------------------------------------
        */

        let modal = new bootstrap.Modal(
            document.getElementById('changeEngineerModal')
        );

        modal.show();

    });

    /*
    |--------------------------------------------------------------------------
    | Save Changed Engineer
    |--------------------------------------------------------------------------
    */

    $('#changeEngineerBtn').on('click', function () {

        let incidentId = $('#change_engineer_incident_id').val();
        let newEngineerId = $('#new_engineer_id').val();

        if (!newEngineerId) {

            Swal.fire({
                icon: 'warning',
                title: 'Engineer Required',
                text: 'Please select an engineer.',
                confirmButtonText: 'OK'
            });

            return;
        }

        let button = $('#changeEngineerBtn');

        $.ajax({

            url: "{{ url('incident_issue_register') }}/" + incidentId + "/change-engineer",                         
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                support_user_id: newEngineerId
            },


            beforeSend: function () {
                button.prop('disabled', true).text('Saving...');                                   
            },


            success: function (response) {

                if (response.status) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        confirmButtonText: 'OK'
                    }).then(function () {
                        location.reload();
                    });

                } else {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message ?? 'Unable to change engineer.',
                        confirmButtonText: 'OK'
                    });
                }
            },


            error: function (xhr) {

                let message = 'Something went wrong.';

                if (xhr.responseJSON?.message) {
                    message = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message,
                    confirmButtonText: 'OK'
                });

            },

            complete: function () {
                button.prop('disabled', false).text('Save');                                
            }

        });

    });

</script>

@endpush
@endsection