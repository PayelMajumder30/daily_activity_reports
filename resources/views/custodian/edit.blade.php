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

                    {{-- Location--}}
                    <div class="col-md-4 mb-3">
                        <label class="form-label">
                            Location
                            <span class="text-danger">*</span>
                        </label>

                        <select name="location_id" id="location_id" class="form-select @error('location_id') is-invalid @enderror">                                                   
                          
                            <option value="">
                                Select Location
                            </option>

                            @foreach($locations as $location)
                                <option value="{{ $location->id }}"
                                    {{ old('location_id', $custodian->location_id) == $location->id ? 'selected' : '' }}>
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

                        <select
                            name="discipline_id"
                            id="discipline_id"
                            class="form-select @error('discipline_id') is-invalid @enderror"
                        >

                            <option value="">
                                Select Department
                            </option>

                            @foreach($departments as $department)

                                <option
                                    value="{{ $department->id }}"
                                    {{ old(
                                        'discipline_id',
                                        $custodian->discipline_id
                                    ) == $department->id ? 'selected' : '' }}
                                >

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

                        <select
                            name="section_id"
                            id="section_id"
                            class="form-select @error('section_id') is-invalid @enderror"
                            {{ $sections->count() == 0 ? 'disabled' : '' }}
                        >

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
                        <i class="bi bi-arrow-left"></i>

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
    | Department -> Section
    |--------------------------------------------------------------------------
    */

    $('#discipline_id').on('change', function () {

        let departmentId = $(this).val();


        $('#section_id')
            .html('<option value="">Loading...</option>')
            .prop('disabled', true);


        if (!departmentId) {

            $('#section_id')
                .html(
                    '<option value="">Select Department First</option>'
                )
                .prop('disabled', true);

            return;
        }

        let url = "{{ route('custodian.sections', ':id') }}"
            .replace(':id', departmentId);

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


                    $('#section_id')
                        .html(options)
                        .prop('disabled', false);


                } else {

                    $('#section_id')
                        .html(
                            '<option value="">No Section Available</option>'
                        )
                        .prop('disabled', true);

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


});

</script>

@endpush