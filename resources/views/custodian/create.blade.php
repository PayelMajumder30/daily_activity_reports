@extends('layouts.app')

@section('title', 'Create Custodian')

@section('content')

<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="row mb-4">

        <div class="col-md-8">

            <h2 class="fw-bold">
                <i class="bi bi-person-workspace"></i>
                Add Custodian
            </h2>

            <p class="text-muted">
                Create a new custodian
            </p>

        </div>

        <div class="col-md-4 text-end">
            <h6 class="text-secondary">
                {{ now()->format('d M Y') }}
            </h6>
        </div>

    </div>


    {{-- Form Card --}}
    <div class="card shadow border-0">

        <div class="card-header">

            <h5 class="mb-0">
                <i class="bi bi-person-plus"></i>
                Custodian Details
            </h5>

        </div>

        <div class="card-body">
            <form action="{{ route('custodian.store') }}" method="POST">
                @csrf

                <div class="row">

                    {{-- Custodian Name --}}
                    <div class="col-md-3 mb-3">

                        <label class="form-label">
                            Custodian Name
                            <span class="text-danger">*</span>
                        </label>

                        <input type="text" name="custodian_name" value="{{ old('custodian_name') }}"     
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

                        <input type="text" name="emp_id" value="{{ old('emp_id') }}" class="form-control @error('emp_id') is-invalid @enderror" 
                            placeholder="Enter 8 digit employee ID" maxlength="8" inputmode="numeric">

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
                                <option value="{{ $designation->id }}" {{ old('designation_id') == $designation->id ? 'selected' : '' }}>

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

                        <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" placeholder="Enter email address">

                        @error('email')
                            <small class="text-danger">
                                {{ $message }}
                            </small>
                        @enderror

                    </div>

                    {{-- Location--}}
                    <div class="col-md-3 mb-3">

                        <label class="form-label">
                            Region
                            <span class="text-danger">*</span>
                        </label>

                        <select name="location_id" id="location_id" class="form-select">
                            <option value="">Select Region</option>

                            @foreach($locations as $location)
                                <option
                                    value="{{ $location->id }}"
                                    {{ old('location_id', $custodian->location_id ?? '') == $location->id ? 'selected' : '' }}>                             
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

                     {{-- Station--}}
                    <div class="col-md-3 mb-3">
                        <label class="form-label">
                            Station<span class="text-danger">*</span>
                        </label>

                        <select name="station_id" id="station_id" class="form-select" disabled>
                            <option value="">Select Region First</option>
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

                                <option value="{{ $department->id }}" {{ old('discipline_id') == $department->id ? 'selected' : '' }}>

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

                        <select name="section_id" id="section_id" class="form-select @error('section_id') is-invalid @enderror" disabled>
                            <option value="">
                                Select Department First
                            </option>

                        </select>

                    </div>                  

                </div>


                {{-- Buttons --}}
                <div class="mt-3">

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i>
                        Save Custodian
                    </button>

                    <a href="{{ route('custodian.index') }}" class="btn btn-secondary ms-2">
                        Back
                    </a>

                </div>

            </form>

        </div>

    </div>

</div>


@push('scripts')

<script>

    $(document).ready(function () {

        /*
        |--------------------------------------------------------------------------
        | Old values after validation error
        |--------------------------------------------------------------------------
        */

        const oldLocation    = @json(old('location_id'));
        const oldStation     = @json(old('station_id'));
        const oldDepartment  = @json(old('discipline_id'));
        const oldSection     = @json(old('section_id'));


        /*
        |--------------------------------------------------------------------------
        | Department -> Section
        |--------------------------------------------------------------------------
        */

        $('#discipline_id').on('change', function () {

            let departmentId = $(this).val();
            let sectionSelect = $('#section_id');

            // Reset section dropdown
            sectionSelect
                .empty()
                .append('<option value="">Loading sections...</option>')
                .prop('disabled', true);

            // No department selected
            if (!departmentId) {

                sectionSelect
                    .empty()
                    .append('<option value="">Select Department First</option>')
                    .prop('disabled', true);

                return;
            }

            // AJAX URL
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
                        | Restore old section after validation error
                        |--------------------------------------------------------------------------
                        */

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
                        ).prop('disabled', true);
                        
                }

            });

        });


        /*
        |--------------------------------------------------------------------------
        | Location -> Station
        |--------------------------------------------------------------------------
        */

        $('#location_id').on('change', function () {

            let locationId = $(this).val();
            let stationSelect = $('#station_id');

            stationSelect
                .empty()
                .append('<option value="">Loading stations...</option>')
                .prop('disabled', true);

            if (!locationId) {
                stationSelect
                    .empty()
                    .append('<option value="">Select Location First</option>')
                    .prop('disabled', true);

                return;
            }

            let url = "{{ route('location.stations.byLocation', ':id') }}"
                .replace(':id', locationId);

            $.ajax({
                url: url,
                type: 'GET',

                success: function (response) {

                    stationSelect.empty();

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

                        stationSelect.prop('disabled', false);

                    } else {

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
        | Automatically restore Department and Location after validation error
        |--------------------------------------------------------------------------
        */

        if (oldDepartment) {
            $('#discipline_id').val(oldDepartment).trigger('change');
        }

        if (oldLocation) {
            $('#location_id').val(oldLocation).trigger('change');
        }

    });


</script>

@endpush
@endsection