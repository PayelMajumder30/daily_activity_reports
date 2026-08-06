@extends('layouts.app')

@section('title', 'Designation')

@section('content')

<div class="container-fluid py-4">

    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold">
                <i class="bi bi-person-badge"></i>
                Designation
            </h2>
            <p class="text-muted">
                Manage all Designations. 
            </p>
        </div>

        <div class="col-md-4 text-end">
            <h6 class="text-secondary">
                {{ now()->format('d M Y') }}
            </h6>
        </div>
    </div>

    <div class="row">

        <!-- Designation List -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header">
                    <h5 class="mb-0">Designation List</h5>
                </div>

                <div class="card-body">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th width="70">SL</th>
                                <th>Name</th>
                                <th>Status</th>
                                <th width="120">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($designations as $designation)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>{{ ucwords($designation->name) }}</td>

                                <td class="text-center">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input designation-status" type="checkbox"                                       
                                            data-id="{{ encryptId($designation->id) }}" {{ $designation->status ? 'checked' : '' }}>                                       
                                    </div>
                                </td>

                                <td>
                                    <button type="button" class="btn btn-warning btn-sm editDesignation" data-id="{{ encryptId($designation->id) }}">     
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">
                                    No Designation Found
                                </td>
                            </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>
            </div>
        </div>

        <!-- Add / Update -->
        <div class="col-lg-4">

            <div class="card shadow border-0">

                <div class="card-header">
                    <h5 id="formTitle" class="mb-0">
                        Add Designation
                    </h5>
                </div>

                <div class="card-body">

                    <form id="designationForm" action="{{ route('designation.store') }}" method="POST">
                        @csrf

                        <div id="methodField"></div>

                        <div class="mb-3">

                            <label class="form-label">
                                Designation name
                            </label>

                            <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" id="name">
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <button class="btn btn-primary" id="submitBtn">
                            <i class="bi bi-check-circle"></i>
                            Save Designation
                        </button>

                        <button type="button" class="btn btn-secondary d-none" id="cancelBtn">      
                            Cancel
                        </button>
                    </form>
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

            function clearFormErrors() {
                $('.text-danger').text('');
                $('.form-control').removeClass('is-invalid');
            }
            // Route templates
            const editUrl = "{{ route('designation.edit', ':id') }}";
            const updateUrl = "{{ route('designation.update', ':id') }}";
            const storeUrl = "{{ route('designation.store') }}";

            $('.editDesignation').on('click', function () {

                clearFormErrors();
                let id = $(this).data('id');

                $.ajax({
                    url: editUrl.replace(':id', id),
                    type: 'GET',

                    success: function (res) {

                        $('#formTitle').text('Update Designation');

                        $('#name').val(res.name);

                        $('#submitBtn')
                            .removeClass('btn-primary')
                            .addClass('btn-warning')
                            .text('Update Designation');

                        $('#cancelBtn').removeClass('d-none');

                        $('#designationForm')
                            .attr('action', updateUrl.replace(':id', id));

                        $('#methodField').html('@method("PUT")');
                    }
                });

            });

            $('#cancelBtn').on('click', function () {

                clearFormErrors();
                $('#designationForm').attr('action', storeUrl);

                $('#methodField').html('');

                $('#name').val('');

                $('#formTitle').text('Add Designation');

                $('#submitBtn')
                    .removeClass('btn-warning')
                    .addClass('btn-primary')
                    .text('Save Designation');

                $(this).addClass('d-none');

            });

        });

        // $(document).on('submit', '.delete-form', function(e){

        //     e.preventDefault();

        //     let form = this;

        //     Swal.fire({
        //         title: 'Delete Activity?',
        //         text: "This record will be permanently deleted.",
        //         icon: 'warning',
        //         showCancelButton: true,
        //         confirmButtonColor: '#d33',
        //         cancelButtonColor: '#6c757d',
        //         confirmButtonText: 'Yes, Delete',
        //         cancelButtonText: 'Cancel'
        //     }).then((result)=>{

        //         if(result.isConfirmed){
        //             form.submit();
        //         }

        //     });
        // });

        $(document).on('change', '.designation-status', function () {
            let checkbox = $(this);
            $.ajax({

                url: "{{ route('designation.changeStatus', ':id') }}"
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
                                ? 'Designation Activated'
                                : 'Designation Deactivated',
                        timer: 1200,
                        showConfirmButton: false
                    });

                },

                error: function () {
                    checkbox.prop('checked', !checkbox.prop('checked'));

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Unable to update status.'
                    });

                }
            });
        });
    </script>
    @endpush
@endsection