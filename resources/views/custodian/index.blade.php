@extends('layouts.app')

@section('title', 'Custodian List')

@section('content')

<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="row mb-4">

        <div class="col-md-8">

            <h2 class="fw-bold">

                <i class="bi bi-person-workspace"></i>

                Custodians

            </h2>

            <p class="text-muted">
                Manage registered custodians
            </p>

        </div>

        <div class="col-md-4 text-end">

            <h6 class="text-secondary">
                {{ now()->format('d M Y') }}
            </h6>

        </div>

    </div>


    {{-- Search Card --}}
    <div class="card shadow border-0 mb-4">

        <div class="card-header d-flex justify-content-between align-items-center">

            <h5 class="mb-0">
                <i class="bi bi-search"></i>
                Search Custodians
            </h5>


            <a href="{{ route('custodian.create') }}" class="btn btn-primary btn-sm">               
                <i class="bi bi-plus-circle"></i>
                Add Custodian
            </a>

        </div>


        <div class="card-body">

            <form method="GET" action="{{ route('custodian.index') }}">

                <div class="row">

                    {{-- Custodian Name --}}
                    <div class="col-md-3">

                        <label class="form-label">
                            Custodian Name
                        </label>

                        <input type="text" name="custodian_name" value="{{ request('custodian_name') }}"  class="form-control" placeholder="Search name">
                    </div>


                    {{-- Employee ID --}}
                    <div class="col-md-3">

                        <label class="form-label">
                            Employee ID
                        </label>

                        <input type="text" name="emp_id" value="{{ request('emp_id') }}" class="form-control" placeholder="Search employee ID">
                    </div>


                    {{-- Department --}}
                    <div class="col-md-3">

                        <label class="form-label">
                            Department
                        </label>

                        <select name="discipline_id" class="form-select">

                            <option value="">
                                All Departments
                            </option>

                            @foreach($departments as $department)

                                <option value="{{ $department->id }}" {{ request('discipline_id') == $department->id ? 'selected' : '' }}>
                                    {{ ucwords($department->name) }}

                                </option>

                            @endforeach

                        </select>

                    </div>

                       {{-- Designation --}}
                    <div class="col-md-3">

                        <label class="form-label">
                            Designation
                        </label>

                        <select name="designation_id" class="form-select">

                            <option value="">
                                All Designations
                            </option>

                            @foreach($designations as $designation)

                                <option value="{{ $designation->id }}" {{ request('designation_id') == $designation->id ? 'selected' : '' }}>
                                    {{ ucwords($designation->name) }}

                                </option>

                            @endforeach

                        </select>

                    </div>

                </div>


                <div class="mt-3">

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i>
                        Search
                    </button>


                    <a href="{{ route('custodian.index') }}" class="btn btn-secondary">
                        Reset
                    </a>

                </div>

            </form>

        </div>

    </div>


    {{-- Custodian Table --}}
    <div class="card shadow border-0">

        <div class="card-header">

            <h5 class="mb-0">
                <i class="bi bi-list"></i>
                Custodian List
            </h5>

        </div>


        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-striped align-middle" id="custodianTable">

                    <thead class="table-dark">

                        <tr>

                            <th>SL</th>

                            <th>Custodian Name</th>

                            <th>Employee ID</th>

                            <th>Designation</th>

                            <th>Department</th>

                            <th>Section</th>

                            <th>Status</th>

                            <th width="120">Action</th>

                        </tr>

                    </thead>


                    <tbody>

                        @foreach($custodians as $custodian)

                            <tr>

                                <td>
                                    {{ $loop->iteration }}
                                </td>

                                <td>
                                    <strong>
                                        {{ ucwords($custodian->custodian_name) }}
                                    </strong>
                                </td>

                                <td>
                                    {{ $custodian->emp_id }}
                                </td>

                                <td>
                                    {{ $custodian->designation ? ucwords($custodian->designation->name) : '-' }}                                                                              
                                </td>

                                <td>
                                    {{ $custodian->discipline ? ucwords($custodian->discipline->name): '-' }}                                                                              
                                </td>

                                <td>
                                    {{ $custodian->section ? ucwords($custodian->section->section_name): 'N/A' }}                                                                              
                                </td>

                                <td>
                                   <div class="form-check form-switch">
                                        <input class="form-check-input custodian-status" type="checkbox"                                       
                                            data-id="{{ encryptId($custodian->id) }}" {{ $custodian->status ? 'checked' : '' }}>                                       
                                    </div>

                                </td>

                                <td>                                  
                                    <a href="{{ route('custodian.edit', encryptId($custodian->id)) }}"
                                        class="btn btn-warning btn-sm" title="Edit">                                       
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                  
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

@push('scripts')

{{-- Success Message --}}
@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: @json(session('success')),
        timer: 2000,
        showConfirmButton: false
    });
</script>
@endif

<script>
    $(document).ready(function () {

        /*
        |--------------------------------------------------------------------------
        | Custodian DataTable
        |--------------------------------------------------------------------------
        */

        $('#custodianTable').DataTable({

            columnDefs: [
                {
                    targets: 0,
                    orderable: false
                }
            ],

            order: [[0, 'asc']],

            pageLength: 10,

            searching: false,

            language: {
                emptyTable: "No custodians Found"
            }

        });


        /*
        |--------------------------------------------------------------------------
        | Custodian Status
        |--------------------------------------------------------------------------
        */

        $(document).on('change', '.custodian-status', function () {

            let checkbox = $(this);

            $.ajax({

                url: "{{ route('custodian.changeStatus', ':id') }}"
                        .replace(':id', checkbox.data('id')),

                type: "POST",

                data: {
                    _token: "{{ csrf_token() }}"
                },

                success: function (res) {

                    Swal.fire({

                        icon: 'success',

                        title: 'Updated',

                        text: res.status
                            ? 'Custodian Activated'
                            : 'Custodian Deactivated',

                        timer: 1200,

                        showConfirmButton: false

                    });

                },

                error: function () {

                    // Restore checkbox if update fails
                    checkbox.prop(
                        'checked',
                        !checkbox.prop('checked')
                    );


                    Swal.fire({

                        icon: 'error',

                        title: 'Error',

                        text: 'Unable to update status.'

                    });

                }

            });

        });

    });
</script>

@endpush
@endsection