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

    <div class="card shadow border-0 mb-4">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-list-ul"></i>
                Issue Register
            </h5>

            <a href="{{ route('issue-register.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i>
                Add Issue Register
            </a>
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
                            <th>Section</th>
                            <th>User Type</th>
                            <th>Operator Name</th>
                            <th>Asset Tag</th>
                            <th>Status</th>
                        </tr>

                    </thead>

                    <tbody>
                        @foreach($issueRegisters as $row)
                            <tr>
                                <td>
                                    {{ $loop->iteration }}
                                </td>

                                <td>
                                    {{ ucwords($row->custodian_name) }}
                                </td>

                                <td>
                                    {{ ucwords($row->designation->name ?? 'N/A') }}
                                </td>

                                <td>
                                    {{ ucwords($row->discipline->name ?? 'N/A') }}
                                </td>

                                <td>
                                    {{ ucwords($row->deptSection->section_name ?? 'N/A') }}
                                </td>

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

                                <td>
                                    {{ $row->operator_name ?? 'N/A' }}
                                </td>

                                <td>
                                    {{ $row->assetInventory->tag_no ?? 'N/A' }}
                                </td>

                                <td>

                                    @if($row->status)
                                        <span class="badge bg-success">
                                            Active
                                        </span>
                                    @else

                                        <span class="badge bg-danger">
                                            Inactive
                                        </span>

                                    @endif

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

    });

</script>

@endpush

@endsection