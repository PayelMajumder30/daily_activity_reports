@extends('layouts.app')
@section('title', 'Asset Issued Register')
@section('content')

<div class="container-fluid py-4">

 <div class="row mb-4">

        <div class="col-md-8">
            <h2 class="fw-bold">
                <i class="bi bi-box-seam"></i>
                Asset Issue Register
            </h2>

            <p class="text-muted">
                Manage all Asset issue registers.
            </p>
        </div>
        
    </div>

    
    {{-- ==========================
        Search Card
    ========================== --}}

    <div class="card shadow border-0 mb-4">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-search"></i>
                Search Asset Inventory
            </h5>
            <a href="{{ route('issue-register.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i>
                Add Issue Register
            </a>
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('issue-register.index') }}">             
                <div class="row g-3">
                    {{-- Custodian Name --}}
                    <div class="col-md-4">
                        <label>Custodian Name</label>
                        <input type="text" name="custodian_name" class="form-control" value="{{ request('custodian_name') }}"placeholder="Search Custodian name">      
                    </div>

                    {{-- Employee Id --}}
                    <div class="col-md-4">
                        <label>Employee Id</label>
                        <input type="text" name="emp_id" class="form-control" value="{{ request('emp_id') }}" placeholder="Search Employee id">                               
                    </div>

                    {{-- tag no. --}}
                    <div class="col-md-4">
                        <label>Asset Tag</label>
                        <input type="text" name="tag_no" class="form-control" value="{{ request('tag_no') }}" placeholder="Search Asset Tag">     
                    </div>
                  
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">                        
                        <i class="bi bi-search"></i>
                        Search
                    </button>

                    <a href="{{ route('issue-register.index') }}" class="btn btn-secondary">                   
                        Reset
                    </a>
                </div>
            </form>
        </div>

    </div>

    <div class="card shadow border-0 mb-4">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-list-ul"></i>
                Issue Register
            </h5>

           
        </div>


        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="issueRegisterTable">
                    
                    <thead class="table-dark">
                        <tr>
                            <th>SL</th>
                            <th>Custodian</th>
                            <th>Designation</th>
                            <th>Department</th>
                            <th>Employee Id</th>
                            <th>Section</th>
                            <th>User Type</th>
                            <th>Operator Name</th>
                            <th>Asset Tag</th>
                            <!-- <th>Status</th> -->
                             <th>View details</th>
                        </tr>

                    </thead>

                    <tbody>
                        @foreach($issueRegisters as $row)
                            <tr>
                                <td>{{ $loop->iteration }}</td>                                                                    
                                <td>{{ ucwords($row->custodian_name) }}</td>                                                                   
                                <td>{{ ucwords($row->designation->name ?? 'N/A') }}</td>                                                                   
                                <td>{{ ucwords($row->discipline->name ?? 'N/A') }}</td>                                                                    
                                <td>{{ ucwords($row->emp_id ?? 'N/A') }}</td>                                                                   
                                <td>{{ ucwords($row->deptSection->section_name ?? 'N/A') }}</td>                                                                    
                                <td>
                                    @if($row->user_type === 'self')

                                        <span class="badge bg-primary">
                                            Self
                                        </span>

                                    @elseif($row->user_type === 'operator')
                                        <span class="badge bg-warning text-dark">
                                            Operator
                                        </span>
                                    @else

                                        <span class="badge bg-info">
                                            Multiuser
                                        </span>

                                    @endif

                                </td>
                                <td>{{ $row->operator_name ?? 'N/A' }}</td>                                                                    
                                <td>{{ $row->assetInventory->tag_no ?? 'N/A' }}</td>                                                                    

                                <!-- <td>

                                    @if($row->status)
                                        <span class="badge bg-success">
                                            Active
                                        </span>
                                    @else

                                        <span class="badge bg-danger">
                                            Inactive
                                        </span>

                                    @endif

                                </td> -->
                                <td>
                                    <a href="javascript:void(0)" class="btn btn-sm btn-info view-issued-assets" data-emp-id="{{ $row->emp_id }}" title="View Issued Assets">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>

                        @endforeach

                    </tbody>

                </table>

                {{-- Employee Issued Assets Modal --}}
               
                <div class="modal fade" id="employeeAssetsModal" tabindex="-1" aria-labelledby="employeeAssetsModalLabel" aria-hidden="true">

                    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">

                        <div class="modal-content">
                            <div class="modal-header">

                                <h5 class="modal-title" id="employeeAssetsModalLabel">
                                    <i class="bi bi-person-badge"></i>
                                    Employee Issued Assets

                                </h5>

                                <button type="button" class="btn-close" data-bs-dismiss="modal">
                                                                               
                                </button>

                            </div>

                            <div class="modal-body">

                                {{-- Employee Information --}}
                                <div class="card border-0 bg-light mb-4">

                                    <div class="card-body">

                                        <h6 class="fw-bold mb-3">
                                            <i class="bi bi-person"></i>
                                            Employee Details
                                        </h6>

                                        <div class="row">

                                            <div class="col-md-4 mb-3">

                                                <label class="text-muted small">
                                                    Custodian Name
                                                </label>

                                                <div id="modal_custodian_name"
                                                    class="fw-semibold">
                                                </div>

                                            </div>


                                            <div class="col-md-4 mb-3">

                                                <label class="text-muted small">
                                                    Employee ID
                                                </label>

                                                <div id="modal_emp_id"
                                                    class="fw-semibold">
                                                </div>

                                            </div>


                                            <div class="col-md-4 mb-3">

                                                <label class="text-muted small">
                                                    Designation
                                                </label>

                                                <div id="modal_designation" class="fw-semibold">
                                                    
                                                </div>

                                            </div>


                                            <div class="col-md-4 mb-3">

                                                <label class="text-muted small">
                                                    Department
                                                </label>

                                                <div id="modal_department" class="fw-semibold">                                                   
                                                </div>

                                            </div>

                                            <div class="col-md-4 mb-3">

                                                <label class="text-muted small">
                                                    Section
                                                </label>
 
                                                <div id="modal_section" class="fw-semibold">                                                   
                                                </div>

                                            </div>


                                            <div class="col-md-4 mb-3">

                                                <label class="text-muted small">
                                                    User Type
                                                </label>

                                                <div id="modal_user_type" class="fw-semibold">
                                                    
                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                </div>


                                {{-- Assets --}}
                                <div>

                                    <h6 class="fw-bold mb-3">

                                        <i class="bi bi-box-seam"></i>
                                        Issued Assets

                                    </h6>

                                    <div class="table-responsive">

                                        <table class="table table-bordered table-hover align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Asset Tag</th>
                                                    <th>Asset Model</th>
                                                    <th>Serial No.</th>
                                                    <th>Location</th>
                                                    <th>PO Number</th>
                                                    <th>Installation Date</th>
                                                    <th>Warranty End</th>
                                                    <th>Asset Status</th>
                                                </tr>

                                            </thead>

                                            <tbody id="employeeAssetsTableBody">

                                            </tbody>

                                        </table>

                                    </div>

                                </div>

                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    Close
                                </button>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


@push('scripts')

@if(session('success'))

<script>

    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: "{{ session('success') }}",
        timer: 2000,
        showConfirmButton: false
    });

</script>

@endif

<script>

    $(document).ready(function () {

        function ucwords(str) {
            return str
                    .toLowerCase()
                    .replace(/\b\w/g, function (char) {
                        return char.toUpperCase();
                    });
        }
        $('#issueRegisterTable').DataTable({
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
                emptyTable: "No Issued register Found"
            }
        });

        /*
        |--------------------------------------------------------------------------
        | View Employee Issued Assets
        |--------------------------------------------------------------------------
        */

        $(document).on('click', '.view-issued-assets', function () {
            let empId = $(this).data('emp-id');

            /*
            |--------------------------------------------------------------------------
            | Clear previous data
            |--------------------------------------------------------------------------
            */

            $('#modal_custodian_name').text('');
            $('#modal_emp_id').text('');
            $('#modal_designation').text('');
            $('#modal_department').text('');
            $('#modal_section').text('');
            $('#modal_user_type').text('');

            $('#employeeAssetsTableBody').html(`
                <tr>
                    <td colspan="9" class="text-center">
                        <div class="spinner-border spinner-border-sm me-2"></div>
                        Loading assets...
                    </td>
                </tr>
            `);


            /*
            |--------------------------------------------------------------------------
            | Open Modal
            |--------------------------------------------------------------------------
            */

            $('#employeeAssetsModal').modal('show');

            /*
            |--------------------------------------------------------------------------
            | Fetch Employee Assets
            |--------------------------------------------------------------------------
            */

            let url = "{{ route('issue-register.employee-assets', ':emp_id') }}"
                .replace(':emp_id', empId);

            $.ajax({
                url: url,
                type: 'GET',
                success: function (response) {
                    if (!response.status) {
                        $('#employeeAssetsTableBody').html(`
                            <tr>
                                <td colspan="9" class="text-center text-danger">                                       
                                    ${response.message}

                                </td>
                            </tr>
                        `);

                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Employee Details
                    |--------------------------------------------------------------------------
                    */

                    $('#modal_custodian_name').text(response.employee.custodian_name || '-');                        
                    $('#modal_emp_id').text(response.employee.emp_id || '-');                        
                    $('#modal_designation').text(response.employee.designation ? ucwords(response.employee.designation) : '-');                        
                    $('#modal_department').text(response.employee.department || '-');                       
                    $('#modal_section').text(response.employee.section || '-');                       
                    $('#modal_user_type').text(response.employee.user_type || '-');
                        
                    /*
                    |--------------------------------------------------------------------------
                    | Assets
                    |--------------------------------------------------------------------------
                    */

                    let rows = '';
                    if (response.assets.length === 0) {
                        rows = `
                            <tr>
                                <td colspan="9" class="text-center text-muted">                                       
                                    No assets found.
                                </td>
                            </tr>
                        `;

                    } else {

                        $.each(response.assets, function (index, asset) {
                            rows += `
                                <tr>
                                    <td>${index + 1}</td>                                                                           
                                    <td>
                                        <strong>
                                            ${asset.tag_no || '-'}
                                        </strong>
                                    </td>
                                    <td>${asset.asset_model || '-'}</td>                                                                            
                                    <td>${asset.serial_no || '-'}</td>                                                                            
                                    <td>${asset.location || '-'}</td>                                                                       
                                    <td>${asset.po_number || '-'}</td>                                                                            
                                    <td>${asset.installation_date || '-'}</td>                                                                            
                                    <td>${asset.warranty_end || '-'}</td>                                                                        
                                    <td>${asset.asset_status || '-'}</td>                                                                           
                                </tr>

                            `;
                        });

                    }

                    $('#employeeAssetsTableBody').html(rows);
                        
                },

                error: function (xhr) {
                    console.log(xhr);
                    $('#employeeAssetsTableBody').html(`
                        <tr>
                            <td colspan="9" class="text-center text-danger">                                    
                                Unable to load employee assets.
                            </td>

                        </tr>
                    `);
                }
            });

        });

    });

</script>

@endpush

@endsection