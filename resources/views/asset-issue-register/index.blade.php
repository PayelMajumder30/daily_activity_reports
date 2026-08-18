@extends('layouts.app')
@section('title', 'Asset Issued Register')
@section('content')

<div class="container-fluid py-4">


    {{-- Header --}}

    <div class="row mb-4">

        <div class="col-md-8">

            <h2 class="fw-bold">
                <i class="bi bi-box-arrow-right"></i>
                Asset Issue Register
            </h2>

            <p class="text-muted">
                Manage issued assets and issue history
            </p>

        </div>

    </div>


    {{-- Search Card --}}

    <div class="card shadow border-0 mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-search"></i>
                Search Issued Assets
            </h5>

            <a href="{{ route('asset-issue-register.create') }}" class="btn btn-primary btn-sm">               
                <i class="bi bi-plus-circle"></i>
                Issue Asset
            </a>

        </div>

        <div class="card-body">

            <form method="GET" action="{{ route('asset-issue-register.index') }}">
                <div class="row">
                    {{-- Custodian --}}
                    <div class="col-md-3">
                        <label class="form-label">
                            Custodian Name
                        </label>
                        <input type="text" name="custodian_name" value="{{ request('custodian_name') }}" class="form-control" placeholder="Search custodian">
                    </div>


                    {{-- Employee ID --}}

                    <div class="col-md-2">

                        <label class="form-label">
                            Employee ID
                        </label>

                        <input type="text" name="emp_id" value="{{ request('emp_id') }}" class="form-control" placeholder="Employee ID">                   
                    </div>


                    {{-- Tag --}}

                    <div class="col-md-2">

                        <label class="form-label">
                            Asset Tag
                        </label>

                        <input type="text" name="tag_no" value="{{ request('tag_no') }}" class="form-control" placeholder="Tag No">
                    </div>


                    {{-- Status --}}

                    <div class="col-md-2">

                        <label class="form-label">
                            Status
                        </label>

                        <select name="issue_status" class="form-select">                                                      

                            <option value="">
                                All
                            </option>

                            <option
                                value="Issued"
                                {{ request('issue_status') == 'Issued' ? 'selected' : '' }}>

                                Issued

                            </option>

                            <option
                                value="Returned"
                                {{ request('issue_status') == 'Returned' ? 'selected' : '' }}>

                                Returned

                            </option>

                        </select>

                    </div>


                    <div class="col-md-3 d-flex align-items-end">

                        <button type="submit" class="btn btn-primary">                                                     
                            <i class="bi bi-search"></i>
                            Search

                        </button>

                        <a href="{{ route('asset-issue-register.index') }}" class="btn btn-secondary ms-2">                                                      
                            Reset
                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>


    {{-- Table --}}

    <div class="card shadow border-0">
        <div class="card-header">
            <h5 class="mb-0">
                Asset Issue Details
            </h5>

        </div>


        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="assetIssueTable">
                                     
                    <thead class="table-dark">

                        <tr>
                            <th>SL</th>
                            <th>Tag No.</th>
                            <th>Asset Type</th>
                            <th>Asset Model</th>
                            <th>Custodian</th>
                            <th>Employee ID</th>
                            <th>Department</th>
                            <th>User Type</th>
                            <th>Issued Date</th>
                            <th>Returned Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>

                    </thead>


                    <tbody>
                        @foreach($issueRegisters as $issue)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                                                   
                                <td>
                                    <strong>
                                        {{ $issue->assetInventory->tag_no ?? 'N/A' }}
                                    </strong>
                                </td>


                                <td>
                                    {{ ucwords(
                                        $issue->assetInventory
                                            ->assetModel
                                            ->assetType
                                            ->name ?? 'N/A'
                                    ) }}

                                </td>

                                <td>
                                    {{ $issue->assetInventory
                                        ->assetModel
                                        ->model_name ?? 'N/A' }}

                                </td>

                                <td>
                                    {{ ucwords(
                                        $issue->custodian
                                            ->custodian_name ?? 'N/A'
                                    ) }}
                                </td>

                                <td>{{ $issue->custodian->emp_id ?? 'N/A' }} </td>

                                <td>
                                    {{ ucwords(
                                        $issue->custodian
                                            ->discipline
                                            ->name ?? 'N/A'
                                    ) }}
                                </td>

                                <td>{{ ucfirst($issue->user_type) }}</td>
                                                                  
                                <td>
                                    {{ $issue->issued_date
                                        ? $issue->issued_date->format('d-m-Y')
                                        : 'N/A' }}
                                </td>

                                <td>
                                    {{ $issue->returned_date
                                        ? $issue->returned_date->format('d-m-Y')
                                        : '-' }}
                                </td>

                                <td>
                                    @if($issue->issue_status === 'Issued')
                                        <span class="badge bg-success">
                                            Issued
                                        </span>
                                    @else

                                        <span class="badge bg-secondary">
                                            Returned
                                        </span>

                                    @endif

                                </td>

                                <td>
                                    <a href="{{ route('asset-issue-register.edit',encryptId($issue->id)) }}" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    @if($issue->issue_status === 'Issued')
                                        <button type="button" class="btn btn-sm btn-success return-asset" data-id="{{ encryptId($issue->id) }}">                                                                                                                                
                                            <i class="bi bi-arrow-return-left"></i>
                                        </button>
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

<script>

    $(document).on('click', '.return-asset', function () {

        let button = $(this);
        let id = button.data('id');

        Swal.fire({
            title: 'Return Asset?',
            text: 'This asset will become available for another custodian.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Return',
            cancelButtonText: 'Cancel'

        }).then((result) => {

            if (!result.isConfirmed) {
                return;
            }

            $.ajax({

                url: "{{ route('asset-issue-register.return', ':id') }}".replace(':id', id),                      
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}"
                },

                success: function (response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Returned',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false

                    }).then(() => {
                        location.reload();
                    });
                },


                error: function (xhr) {
                    let message =
                        xhr.responseJSON?.message
                        ?? 'Unable to return asset.';

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: message
                    });

                }
            });

        });

    });

</script>

@endpush
@endsection