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
                    <div class="col-md-4 mb-3">

                        <label class="form-label">
                            Location
                            <span class="text-danger">*</span>
                        </label>

                        <select
                            name="location_id"
                            id="location_id"
                            class="form-select @error('location_id') is-invalid @enderror">

                            <option value="">
                                Select Location
                            </option>

                            @foreach($locations as $location)

                                <option
                                    value="{{ $location->id }}"
                                    {{ old('location_id') == $location->id ? 'selected' : '' }}>

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

                    {{-- Department --}}
                    <div class="col-md-4 mb-3">

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
                    <div class="col-md-4 mb-3">

                        <label class="form-label">
                            Section
                        </label>

                        <select name="section_id" id="section_id" class="form-select @error('section_id') is-invalid @enderror" disabled>
                            <option value="">
                                Select Department First
                            </option>

                        </select>

                        @error('section_id')

                            <small class="text-danger">
                                {{ $message }}
                            </small>
                        @enderror
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
    | Department -> Section
    |--------------------------------------------------------------------------
    */

    $('#discipline_id').on('change', function () {
        let departmentId = $(this).val();

        /*
        |--------------------------------------------------------------------------
        | Loading
        |--------------------------------------------------------------------------
        */

        $('#section_id').html('<option value="">Loading...</option>').prop('disabled', true);                 

        /*
        |--------------------------------------------------------------------------
        | No Department Selected
        |--------------------------------------------------------------------------
        */

        if (!departmentId) {

            $('#section_id')
                .html(
                    '<option value="">Select Department First</option>'
                )
                .prop('disabled', true);

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | AJAX URL
        |--------------------------------------------------------------------------
        */

        let url = "{{ route('custodian.sections', ':id') }}"
            .replace(':id', departmentId);


        /*
        |--------------------------------------------------------------------------
        | AJAX Request
        |--------------------------------------------------------------------------
        */

        $.ajax({
            url: url,
            type: 'GET',
            success: function (response) {
                if (response.length > 0) {
                    let options =
                        '<option value="">Select Section</option>';

                    $.each(
                        response,
                        function (index, section) {

                            options +=
                                '<option value="' +
                                section.id +
                                '">' +
                                section.section_name +
                                '</option>';

                        }
                    );

                    $('#section_id').html(options).prop('disabled', false);                                            

                    /*
                    |--------------------------------------------------------------------------
                    | Restore Old Section After Validation Error
                    |--------------------------------------------------------------------------
                    */

                    let oldSection = "{{ old('section_id') }}";
                        
                    if (oldSection) {
                        $('#section_id').val(oldSection);                           
                    }

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | Department Has No Section
                    |--------------------------------------------------------------------------
                    */

                    $('#section_id').html('<option value="">No Section Available</option>').prop('disabled', true);
                                                                                                  
                }

            },

            error: function () {

                $('#section_id')
                    .html(
                        '<option value="">Unable to load sections</option>'
                    )
                    .prop('disabled', true);
            }

        });

    });


    /*
    |--------------------------------------------------------------------------
    | Load Section Automatically When Validation Fails
    |--------------------------------------------------------------------------
    */

    let oldDepartment =
        "{{ old('discipline_id') }}";

    if (oldDepartment) {
        $('#discipline_id').trigger('change');
    }

});

</script>

@endpush
@endsection