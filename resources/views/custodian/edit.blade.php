@extends('layouts.app')
@section('title', 'Update Custodian')
@section('content')

<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="row mb-4">

        <div class="col-md-8">

            <h2 class="fw-bold">

                <i class="bi bi-person-workspace"></i>

                Update Custodian

            </h2>

            <p class="text-muted mb-0">
                Update Custodian Details
            </p>

        </div>

        <div class="col-md-4 text-end">
            <h6 class="text-secondary">
                {{ now()->format('d M Y') }}
            </h6>

        </div>

    </div>


    {{-- Card --}}
    <div class="card shadow border-0">

        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-pencil-square"></i>
                Edit Custodian
            </h5>

        </div>


        <div class="card-body">

            <form action="{{ route('custodian.update', encryptId($custodian->id)) }}" method="POST" id="custodianForm">
                @csrf

                @method('PUT')


                <div class="row">


                    {{-- Custodian Name --}}
                    <div class="col-md-3 mb-3">

                        <label class="form-label">
                            Custodian Name
                            <span class="text-danger">*</span>
                        </label>

                        <input type="text" name="custodian_name" value="{{ old('custodian_name', $custodian->custodian_name) }}"    
                            class="form-control @error('custodian_name') is-invalid @enderror" placeholder="Enter custodian name">

                        @error('custodian_name')

                            <small class="text-danger">
                                {{ $message }}
                            </small>

                        @enderror

                    </div>

                    {{-- Employee ID --}}
                    <div class="col-md-3 mb-3">

                        <label class="form-label">

                            Employee ID

                            <span class="text-danger">*</span>

                        </label>

                        <input type="text" name="emp_id" value="{{ old('emp_id', $custodian->emp_id) }}" class="form-control @error('emp_id') is-invalid @enderror"
                            placeholder="Enter 8 digit employee ID" maxlength="8">

                        @error('emp_id')

                            <small class="text-danger">
                                {{ $message }}
                            </small>

                        @enderror

                    </div>

                    {{-- Designation --}}
                    <div class="col-md-3 mb-3">

                        <label class="form-label">
                            Designation
                            <span class="text-danger">*</span>
                        </label>

                        <select name="designation_id" class="form-select @error('designation_id') is-invalid @enderror">
                            <option value="">
                                Select Designation
                            </option>

                            @foreach($designations as $designation)
                                <option value="{{ $designation->id }}" {{ old('designation_id', $custodian->designation_id) == $designation->id ? 'selected' : '' }}>
                                    {{ ucwords($designation->name) }}

                                </option>
                            @endforeach
                        </select>

                        @error('designation_id')

                            <small class="text-danger">
                                {{ $message }}
                            </small>

                        @enderror

                    </div>

                    {{-- Email --}}
                    <div class="col-md-3 mb-3">

                        <label class="form-label">
                            Email <span class="text-danger">*</span>
                        </label>

                        <input type="email" name="email" value="{{ old('email', $custodian->email) }}" class="form-control @error('email') is-invalid @enderror" placeholder="Enter email address">

                        @error('email')
                            <small class="text-danger">
                                {{ $message }}
                            </small>
                        @enderror

                    </div>

                    {{-- Region --}}
                    <div class="col-md-3 mb-3">

                        <label class="form-label">
                            Region
                            <span class="text-danger">*</span>
                        </label>

                        <select name="location_id" id="location_id" class="form-select @error('location_id') is-invalid @enderror">                                                                           
                            <option value="">
                                Select Region
                            </option>

                            @foreach($locations as $location)

                                <option
                                    value="{{ $location->id }}" {{ old('location_id', $custodian->location_id) == $location->id ? 'selected' : '' }}>
                                                                 
                                    {{ ucwords($location->name) }}
                                </option>

                            @endforeach

                        </select>

                        @error('location_id')
                            <small class="text-danger">
                                {{ $message }}
                            </small>
                        @enderror

                    </div>

                    {{-- Station --}}
                    <div class="col-md-3 mb-3">
                        <label class="form-label">
                            Station
                        </label>

                        <select name="station_id"                          
                            id="station_id" class="form-select @error('station_id') is-invalid @enderror" disabled>                                                                          
                            <option value="">
                                Loading stations...
                            </option>
                        </select>

                        @error('station_id')
                            <small class="text-danger">
                                {{ $message }}
                            </small>
                        @enderror

                    </div>

                    {{-- Department --}}
                    <div class="col-md-3 mb-3">

                        <label class="form-label">

                            Department

                            <span class="text-danger">*</span>

                        </label>

                        <select name="discipline_id" id="discipline_id" class="form-select @error('discipline_id') is-invalid @enderror">
                            <option value="">
                                Select Department
                            </option>

                            @foreach($departments as $department)

                                <option
                                    value="{{ $department->id }}"
                                    {{ old(
                                        'discipline_id',
                                        $custodian->discipline_id
                                    ) == $department->id ? 'selected' : '' }}>                              

                                    {{ ucwords($department->name) }}
                                </option>
                            @endforeach

                        </select>

                        @error('discipline_id')

                            <small class="text-danger">
                                {{ $message }}
                            </small>
                        @enderror

                    </div>


                    {{-- Section --}}
                    <div class="col-md-3 mb-3">

                        <label class="form-label">
                            Section
                        </label>

                        <select name="section_id"                           
                            id="section_id" class="form-select @error('section_id') is-invalid @enderror" {{ $sections->count() == 0 ? 'disabled' : '' }}>                                                                           

                            @if($sections->count() > 0)

                                <option value="">
                                    Select Section
                                </option>

                                @foreach($sections as $section)

                                    <option value="{{ $section->id }}" {{ old('section_id', $custodian->section_id) == $section->id ? 'selected' : '' }}>
                                        {{ ucwords($section->section_name) }}

                                    </option>

                                @endforeach

                            @else
                                <option value="">
                                    No Section Available
                                </option>

                            @endif

                        </select>

                        @error('section_id')

                            <small class="text-danger">
                                {{ $message }}
                            </small>

                        @enderror

                    </div>


                    
                    {{-- Phone --}}
                    <!-- <div class="col-md-4 mb-3">

                        <label class="form-label">

                            Phone

                        </label>

                        <input
                            type="text"
                            name="phone"
                            value="{{ old('phone', $custodian->phone) }}"
                            class="form-control @error('phone') is-invalid @enderror"
                            placeholder="Enter 10 digit phone number"
                            maxlength="10"
                        >

                        @error('phone')

                            <small class="text-danger">

                                {{ $message }}

                            </small>

                        @enderror

                    </div> -->

                </div>


                {{-- Buttons --}}
                <div class="mt-3">

                    <button type="submit" class="btn btn-primary">                       
                        <i class="bi bi-check-circle"></i>
                        Update Custodian
                    </button>


                    <a href="{{ route('custodian.index') }}" class="btn btn-secondary ms-2">                       
                        Back
                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection


@push('scripts')

<script>

$(document).ready(function () {

    /*
    |--------------------------------------------------------------------------
    | Region -> Station
    |--------------------------------------------------------------------------
    */

    $('#location_id').on('change', function () {

        let locationId = $(this).val();
        let stationSelect = $('#station_id');

        /*
        |--------------------------------------------------------------------------
        | Reset Station
        |--------------------------------------------------------------------------
        */

        stationSelect
            .empty()
            .append('<option value="">Loading stations...</option>')
            .prop('disabled', true);


        /*
        |--------------------------------------------------------------------------
        | No Region Selected
        |--------------------------------------------------------------------------
        */

        if (!locationId) {

            stationSelect
                .empty()
                .append('<option value="">Select Region First</option>')
                .prop('disabled', true);

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Station API URL
        |--------------------------------------------------------------------------
        */

        let url = "{{ route('location.stations.byLocation', ':id') }}"
            .replace(':id', locationId);


        /*
        |--------------------------------------------------------------------------
        | Load Stations
        |--------------------------------------------------------------------------
        */

        $.ajax({

            url: url,
            type: 'GET',

            success: function (response) {

                stationSelect.empty();


                /*
                |--------------------------------------------------------------------------
                | Stations Found
                |--------------------------------------------------------------------------
                */

                if (response.length > 0) {

                    stationSelect.append(
                        '<option value="">Select Station</option>'
                    );


                    $.each(response, function (index, station) {

                        let stationName = station.station_name;

                        if (station.short_name) {
                            stationName += ' (' + station.short_name + ')';
                        }


                        stationSelect.append(
                            $('<option>', {
                                value: station.id,
                                text: stationName
                            })
                        );

                    });


                    /*
                    |--------------------------------------------------------------------------
                    | Select Existing / Old Station
                    |--------------------------------------------------------------------------
                    */

                    let oldStation =
                        "{{ old('station_id', $custodian->station_id) }}";

                    if (oldStation) {
                        stationSelect.val(oldStation);
                    }


                    stationSelect.prop('disabled', false);

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | No Station Found
                    |--------------------------------------------------------------------------
                    */

                    stationSelect
                        .append(
                            '<option value="">No Station Available</option>'
                        )
                        .prop('disabled', true);

                }

            },

            error: function () {

                stationSelect
                    .empty()
                    .append(
                        '<option value="">Unable to load stations</option>'
                    )
                    .prop('disabled', true);

            }

        });

    });


    /*
    |--------------------------------------------------------------------------
    | Load Existing Region's Stations Automatically
    |--------------------------------------------------------------------------
    */

    let oldLocation =
        "{{ old('location_id', $custodian->location_id) }}";

    if (oldLocation) {

        $('#location_id').val(oldLocation);

        $('#location_id').trigger('change');

    }


    /*
    |--------------------------------------------------------------------------
    | Department -> Section
    |--------------------------------------------------------------------------
    */

    $('#discipline_id').on('change', function () {

        let departmentId = $(this).val();
        let sectionSelect = $('#section_id');


        /*
        |--------------------------------------------------------------------------
        | Reset Section
        |--------------------------------------------------------------------------
        */

        sectionSelect
            .empty()
            .append('<option value="">Loading...</option>')
            .prop('disabled', true);


        /*
        |--------------------------------------------------------------------------
        | No Department
        |--------------------------------------------------------------------------
        */

        if (!departmentId) {

            sectionSelect
                .empty()
                .append(
                    '<option value="">Select Department First</option>'
                )
                .prop('disabled', true);

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Section API
        |--------------------------------------------------------------------------
        */

        let url = "{{ route('custodian.sections', ':id') }}"
            .replace(':id', departmentId);


        $.ajax({

            url: url,
            type: 'GET',

            success: function (response) {

                sectionSelect.empty();


                if (response.length > 0) {

                    sectionSelect.append(
                        '<option value="">Select Section</option>'
                    );


                    $.each(response, function (index, section) {

                        sectionSelect.append(
                            $('<option>', {
                                value: section.id,
                                text: section.section_name
                            })
                        );

                    });


                    /*
                    |--------------------------------------------------------------------------
                    | Restore Existing / Old Section
                    |--------------------------------------------------------------------------
                    */

                    let oldSection =
                        "{{ old('section_id', $custodian->section_id) }}";

                    if (oldSection) {
                        sectionSelect.val(oldSection);
                    }


                    sectionSelect.prop('disabled', false);

                } else {

                    sectionSelect
                        .append(
                            '<option value="">No Section Available</option>'
                        )
                        .prop('disabled', true);

                }

            },

            error: function () {

                sectionSelect
                    .empty()
                    .append(
                        '<option value="">Unable to load sections</option>'
                    )
                    .prop('disabled', true);

            }

        });

    });


    /*
    |--------------------------------------------------------------------------
    | Load Existing Department's Sections Automatically
    |--------------------------------------------------------------------------
    */

    let oldDepartment =
        "{{ old('discipline_id', $custodian->discipline_id) }}";

    if (oldDepartment) {

        $('#discipline_id').val(oldDepartment);

        $('#discipline_id').trigger('change');

    }

});
</script>

@endpush