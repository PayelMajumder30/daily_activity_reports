@extends('layouts.app')

@section('title', 'Create Asset Issued Register')

@section('content')

<div class="container-fluid py-4">

    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold">
                <i class="bi bi-file-earmark-text"></i>
                Add Issue Register
            </h2>
            <p class="text-muted">
                Create new Issue Register
            </p>
        </div>

        <div class="col-md-4 text-end">
            <h6 class="text-secondary">
                {{ now()->format('d M Y') }}
            </h6>
        </div>
    </div>

    <div class="card shadow border-0">

        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-plus-circle"></i>
                Add Issue Register
            </h5>
        </div>

        <div class="card-body">

            <form action="{{ route('issue-register.store') }}" method="POST" id="issueRegisterForm">
                @csrf
                <div class="row">

                    {{-- Custodian --}}
                    <div class="col-md-4 mb-3">

                        <label class="form-label">
                            Custodian Name
                            <span class="text-danger">*</span>
                        </label>

                        <input type="text" name="custodian_name" value="{{ old('custodian_name') }}" class="form-control @error('custodian_name') is-invalid @enderror"
                            placeholder="Enter custodian name">

                        @error('custodian_name')
                            <small class="text-danger">
                                {{ $message }}
                            </small>
                        @enderror

                    </div>

                    {{-- Designation --}}
                    <div class="col-md-4 mb-3">

                        <label class="form-label">
                            Designation
                            <span class="text-danger">*</span>
                        </label>

                        <select
                            name="designation_id"
                            class="form-select @error('designation_id') is-invalid @enderror">

                            <option value="">
                                Select Designation
                            </option>

                            @foreach($designations as $designation)

                                <option
                                    value="{{ $designation->id }}"
                                    {{ old('designation_id') == $designation->id ? 'selected' : '' }}>
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
                                <option value="{{ encryptId($department->id) }}" data-id="{{ $department->id }}" 
                                    {{ old('discipline_id') == encryptId($department->id) ? 'selected' : '' }}>                  
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

                        <select name="section_id" id="section_id" class="form-select" disabled>
                            <option value="">
                                Select Department First
                            </option>

                        </select>

                    </div>

                    {{-- employee Id --}}
                    <div class="col-md-3 mb-3">

                        <label class="form-label">
                            Employee id 
                            <span class="text-danger">*</span>
                        </label>

                         <input type="number" name="emp_id" value="{{ old('emp_id') }}" class="form-control @error('emp_id') is-invalid @enderror"
                            placeholder="Enter employee id">

                        @error('emp_id')
                            <small class="text-danger">
                                {{ $message }}
                            </small>
                        @enderror

                    </div>

                    {{-- User Type --}}
                    <div class="col-md-3 mb-3">

                        <label class="form-label">
                            User Type
                            <span class="text-danger">*</span>
                        </label>

                        <select name="user_type" id="user_type" class="form-select">
                            <option value="">Select User Type</option>
                            <option value="self">Self</option>
                            <option value="multiuser">Multiuser</option>
                            <option value="operator">Operator</option>
                        </select>

                        @error('user_type')
                            <small class="text-danger">
                                {{ $message }}
                            </small>
                        @enderror

                    </div>

                    {{-- Operator Name --}}
                    <div id="operatorField" class="col-md-4 mb-3 d-none">
    <label class="form-label">
        Operator Name
        <span class="text-danger">*</span>
    </label>

    <input
        type="text"
        name="operator_name"
        id="operator_name"
        value="{{ old('operator_name') }}"
        class="form-control @error('operator_name') is-invalid @enderror"
        placeholder="Enter operator name"
    >

    @error('operator_name')
        <small class="text-danger">
            {{ $message }}
        </small>
    @enderror
</div>

                    {{-- Asset Tag --}}
                    <div class="col-md-3 mb-3">

                        <label class="form-label">
                            Asset Tag
                            <span class="text-danger">*</span>
                        </label>

                        <select name="asset_inventory_id" id="asset_inventory_id" class="form-select @error('asset_inventory_id') is-invalid @enderror">
                            <option value="">
                                Select Asset Tag
                            </option>

                            @foreach($assets as $asset)
                                <option
                                    value="{{ $asset->id }}"
                                    {{ old('asset_inventory_id') == $asset->id ? 'selected' : '' }}>

                                    {{ $asset->tag_no }}
                                </option>
                            @endforeach

                        </select>

                        @error('asset_inventory_id')
                            <small class="text-danger">
                                {{ $message }}
                            </small>
                        @enderror
                    </div>
                    
                </div>

                <div class="mt-3">

                    <button type="submit"class="btn btn-primary">
                        <i class="bi bi-check-circle"></i>
                        Save Issue Register
                    </button>

                    <a href="{{ route('issue-register.index') }}" class="btn btn-secondary ms-2">
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
        | Asset Tag - Searchable Single Select
        |--------------------------------------------------------------------------
        */
        $('#asset_inventory_id').select2({
            placeholder: 'Select Asset Tag',
            allowClear: true,
            width: '100%'
        });
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
                    .html('<option value="">Select Department First</option>')
                    .prop('disabled', true);

                return;
            }

            let url = "{{ route('issue-register.sections', ':id') }}"
                .replace(':id', departmentId);

            $.ajax({

                url: url,
                type: 'GET',

                success: function (response) {

                    if (response.length > 0) {

                        let options =
                            '<option value="">Select Section</option>';

                        $.each(response, function (index, section) {

                            options +=
                                '<option value="' +
                                section.id +
                                '">' +
                                section.section_name +
                                '</option>';

                        });

                        $('#section_id')
                            .html(options)
                            .prop('disabled', false);

                    } else {

                        /*
                        | Department has no section
                        */

                        $('#section_id')
                            .html('<option value="">No Section Available</option>')
                            .prop('disabled', true);
                    }
                },

                error: function () {
                    $('#section_id')
                        .html('<option value="">Unable to load sections</option>').prop('disabled', true);                     
                }
            });
        });

        /*
        |--------------------------------------------------------------------------
        | User Type -> Operator Name
        |--------------------------------------------------------------------------
        */
        function toggleOperatorField() {

            let userType = $('#user_type').val();
            if (userType === 'operator') {
                $('#operatorField')
                    .removeClass('d-none')
                    .hide()
                    .slideDown();

                $('#operator_name')
                    .prop('required', true);

            } else {
                $('#operatorField')
                    .slideUp(function () {
                        $(this).addClass('d-none');
                    });

                $('#operator_name')
                    .prop('required', false)
                    .val('');
            }
        }

        $('#user_type').on('change', function () {
            toggleOperatorField();
        });

        toggleOperatorField();

        $('#user_type').on('change', function () {
            toggleOperatorField();
        });

        // Check on page load
        toggleOperatorField();

    });

</script>

@endpush

@endsection