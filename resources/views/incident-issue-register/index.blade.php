@extends('layouts.app')

@section('title', 'Incident Issue Register')

@section('content')

<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold">
                <i class="bi bi-person-workspace"></i>
                Incident issue register
            </h2>

            <p class="text-muted">
                Manage all incident issue registers
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
                        Attend

                        <span class="badge bg-warning">
                            {{ $attendIncidents->count() }}
                        </span>

                    </button>

                </li>

                <li class="nav-item">

                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#closeTab">
                        Close

                        <span class="badge bg-success">
                            {{ $closeIncidents->count() }}
                        </span>

                    </button>

                </li>

            </ul>


            <div class="tab-content mt-3">

                {{-- OPEN --}}

                <div class="tab-pane fade show active"
                     id="openTab">

                    @include(
                        'incident-issue-register.partials.table',
                        [
                            'incidents' => $openIncidents,
                            'status' => 'Open'
                        ]
                    )

                </div>


                {{-- ATTEND --}}

                <div class="tab-pane fade"
                     id="attendTab">

                    @include(
                        'incident-issue-register.partials.table',
                        [
                            'incidents' => $attendIncidents,
                            'status' => 'Attend'
                        ]
                    )

                </div>


                {{-- CLOSE --}}

                <div class="tab-pane fade"
                     id="closeTab">

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

                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                                                
                    </button>

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

                        <textarea id="modal_remarks" class="form-control" rows="5"></textarea>
                                                            
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

</div>

@push('scripts')

<script>

$(document).ready(function () {

    /*
    |--------------------------------------------------------------------------
    | Open Incident Modal
    |--------------------------------------------------------------------------
    */

    $(document).on('click', '.incident-status-btn', function () {

        let id = $(this).data('id');

        let status = $(this).data('status');

        $('#incident_id').val(id);

        $.ajax({

            url: "{{ url('incident_issue_register') }}/" + id + "/details",

            type: "GET",

            success: function (response) {

                if (!response.status) {

                    toastr.error(response.message);

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

                        $('#incidentModalTitle')
                            .text('Attend Incident');

                        $('#incidentActionBtn')
                            .text('Attend Call')
                            .removeClass('btn-success')
                            .addClass('btn-primary')
                            .show();

                        $('#modal_remarks')
                            .prop('readonly', false);

                    }
                    else if (status === 'Attend') {

                        $('#incidentModalTitle')
                            .text('Close Incident');

                        $('#incidentActionBtn')
                            .text('Close Call')
                            .removeClass('btn-primary')
                            .addClass('btn-success')
                            .show();

                        $('#modal_remarks')
                            .prop('readonly', false);

                    }
                    else {

                        $('#incidentModalTitle')
                            .text('Incident Details');

                        $('#incidentActionBtn')
                            .hide();

                        $('#modal_remarks')
                            .prop('readonly', true);

                    }

                @else

                    $('#incidentModalTitle')
                        .text('Incident Details');

                    $('#incidentActionBtn')
                        .hide();

                    $('#modal_remarks')
                        .prop('readonly', true);

                @endif


                let modal = new bootstrap.Modal(
                    document.getElementById('incidentModal')
                );

                modal.show();

            },

            error: function () {

                toastr.error(
                    'Unable to fetch incident details.'
                );

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

        let remarks = $('#modal_remarks').val();

        let status = $('#modal_status').text();

        if (!remarks.trim()) {

            toastr.error(
                'Issue details cannot be empty.'
            );

            return;
        }

        let actionUrl = '';

        if (status === 'Open') {

            actionUrl = "{{ url('incident_issue_register') }}/ " + id + "/attend";           

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

                    toastr.success(response.message);

                    setTimeout(function () {
                        location.reload();
                    }, 800);

                }
                else {

                    toastr.error(response.message);

                }

            },

            error: function (xhr) {

                if (xhr.responseJSON?.message) {

                    toastr.error(
                        xhr.responseJSON.message
                    );

                } else {

                    toastr.error(
                        'Something went wrong.'
                    );

                }

            },

            complete: function () {

                $('#incidentActionBtn').prop('disabled', false);                    

            }

        });

    });

});

</script>

@endpush
@endsection