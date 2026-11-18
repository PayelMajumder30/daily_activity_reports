@extends('layouts.app')

@section('title', 'Status Configuration')

@section('content')

<div class="container-fluid py-4">

    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold">
                <i class="bi bi-check2-circle"></i>
                Status Configuration
            </h2>
            <p class="text-muted">
                Manage all status. Add new status, update existing ones, or delete status from a single interface.
            </p>
        </div>

        <div class="col-md-4 text-end">
            <h6 class="text-secondary">
                {{ now()->format('d M Y') }}
            </h6>
        </div>
    </div>

    <div class="row">

        <!-- Status List -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header">
                    <h5 class="mb-0">Status List</h5>
                </div>

                <div class="card-body">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th width="70">SL</th>
                                <th>Title</th>
                                <th>Status</th>
                                <th width="120">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($statuslist as $statususe)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ ucwords($statususe->title) }}</td>
                                <td class="text-center">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input statususe-status" type="checkbox"                                       
                                            data-id="{{ encryptId($statususe->id) }}" {{ $statususe->status ? 'checked' : '' }}>                                       
                                    </div>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-warning btn-sm editStatus" data-id="{{ encryptId($statususe->id) }}">           
                                        <i class="bi bi-pencil-square"></i>
                                    </button>

                                    <!-- <form action="{{ route('status-configuration.destroy', encryptId($statususe->id)) }}" method="POST" class="delete-form d-inline"> 
                                        @csrf
                                        @method('DELETE')

                                        <button class="btn btn-danger btn-sm">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form> -->
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">
                                    No Status Found
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
                        Add Status
                    </h5>
                </div>

                <div class="card-body">

                    <form id="statusForm" action="{{ route('status-configuration.store') }}" method="POST">
                        @csrf

                        <div id="methodField"></div>

                        <div class="mb-3">

                            <label class="form-label">
                                Status Title
                            </label>

                            <input type="text" class="form-control"  name="title" id="title">
                        </div>

                        <button class="btn btn-primary" id="submitBtn">
                            <i class="bi bi-check-circle"></i>
                            Save Status
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
   
    {{-- Success Message --}}
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

            // Route templates
            const editUrl = "{{ route('status-configuration.edit', ':id') }}";
            const updateUrl = "{{ route('status-configuration.update', ':id') }}";
            const storeUrl = "{{ route('status-configuration.store') }}";

            // Edit
            $('.editStatus').on('click', function () {

                let id = $(this).data('id');

                $.ajax({
                    url: editUrl.replace(':id', id),
                    type: 'GET',

                    success:function(res){

                        $('#formTitle').text('Update Status');

                        $('#title').val(res.title);

                        $('#submitBtn')
                            .removeClass('btn-primary')
                            .addClass('btn-warning')
                            .html('<i class="bi bi-pencil-square"></i> Update Status');

                        $('#cancelBtn').removeClass('d-none');

                        $('#statusForm')
                            .attr('action', updateUrl.replace(':id', id));

                        $('#methodField').html('@method("PUT")');

                    }

                });

            });

            // Cancel
            $('#cancelBtn').click(function(){

                $('#statusForm').attr('action', storeUrl);

                $('#methodField').html('');

                $('#title').val('');

                $('#formTitle').text('Add Status');

                $('#submitBtn')
                    .removeClass('btn-warning')
                    .addClass('btn-primary')
                    .html('<i class="bi bi-check-circle"></i> Save Status');

                $(this).addClass('d-none');

            });

            // Delete Confirmation
            $(document).on('submit','.delete-form',function(e){

                e.preventDefault();

                let form=this;

                Swal.fire({

                    title:'Delete Status?',
                    text:'This record will be permanently deleted.',
                    icon:'warning',
                    showCancelButton:true,
                    confirmButtonColor:'#d33',
                    cancelButtonColor:'#6c757d',
                    confirmButtonText:'Yes, Delete',
                    cancelButtonText:'Cancel'

                }).then((result)=>{

                    if(result.isConfirmed){
                        form.submit();
                    }

                });

            });
        });

        $(document).on('change', '.statususe-status', function () {
            let checkbox = $(this);
            $.ajax({

                url: "{{ route('status-configuration.statusChange', ':id') }}"
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
                                ? 'Status Activated'
                                : 'Status Deactivated',
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