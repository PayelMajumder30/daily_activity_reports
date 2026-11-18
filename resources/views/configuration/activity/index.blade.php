@extends('layouts.app')

@section('title', 'Activity Configuration')

@section('content')

<div class="container-fluid py-4">

    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold">
                <i class="bi bi-list-task"></i>
                Activity Type Configuration
            </h2>
            <p class="text-muted">
                Manage all activity types. Add new activities, update existing ones, or delete activities from a single interface.
            </p>
        </div>

        <div class="col-md-4 text-end">
            <h6 class="text-secondary">
                {{ now()->format('d M Y') }}
            </h6>
        </div>
    </div>

    <div class="row">

        <!-- Activity List -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header">
                    <h5 class="mb-0">Type of Activity List</h5>
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
                            @forelse($activitylist as $activity)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>{{ ucwords($activity->title) }}</td>

                                <td class="text-center">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input activity-status" type="checkbox"                                       
                                            data-id="{{ encryptId($activity->id) }}" {{ $activity->status ? 'checked' : '' }}>                                       
                                    </div>
                                </td>

                                <td>
                                    <button type="button" class="btn btn-warning btn-sm editActivity" data-id="{{ encryptId($activity->id) }}">     
                                        <i class="bi bi-pencil-square"></i>
                                    </button>

                                    <!-- <form action="{{ route('activity-configuration.destroy', encryptId($activity->id)) }}" method="POST" class="delete-form d-inline">
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
                                    No Activity Found
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
                        Add Activity
                    </h5>
                </div>

                <div class="card-body">

                    <form id="activityForm" action="{{ route('activity-configuration.store') }}" method="POST">
                        @csrf

                        <div id="methodField"></div>

                        <div class="mb-3">

                            <label class="form-label">
                                Activity Title
                            </label>

                            <input type="text" class="form-control" name="title" id="title">
                        </div>

                        <button class="btn btn-primary" id="submitBtn">
                            <i class="bi bi-check-circle"></i>
                            Save Activity
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

            // Route templates
            const editUrl = "{{ route('activity-configuration.edit', ':id') }}";
            const updateUrl = "{{ route('activity-configuration.update', ':id') }}";
            const storeUrl = "{{ route('activity-configuration.store') }}";

            $('.editActivity').on('click', function () {

                let id = $(this).data('id');

                $.ajax({
                    url: editUrl.replace(':id', id),
                    type: 'GET',

                    success: function (res) {

                        $('#formTitle').text('Update Activity');

                        $('#title').val(res.title);

                        $('#submitBtn')
                            .removeClass('btn-primary')
                            .addClass('btn-warning')
                            .text('Update Activity');

                        $('#cancelBtn').removeClass('d-none');

                        $('#activityForm')
                            .attr('action', updateUrl.replace(':id', id));

                        $('#methodField').html('@method("PUT")');
                    }
                });

            });

            $('#cancelBtn').on('click', function () {

                $('#activityForm').attr('action', storeUrl);

                $('#methodField').html('');

                $('#title').val('');

                $('#formTitle').text('Add Activity');

                $('#submitBtn')
                    .removeClass('btn-warning')
                    .addClass('btn-primary')
                    .text('Save Activity');

                $(this).addClass('d-none');

            });

        });

        $(document).on('submit', '.delete-form', function(e){

            e.preventDefault();

            let form = this;

            Swal.fire({
                title: 'Delete Activity?',
                text: "This record will be permanently deleted.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel'
            }).then((result)=>{

                if(result.isConfirmed){
                    form.submit();
                }

            });
        });

        $(document).on('change', '.activity-status', function () {
            let checkbox = $(this);
            $.ajax({

                url: "{{ route('activity-configuration.changeStatus', ':id') }}"
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
                                ? 'Activity Activated'
                                : 'Activity Deactivated',
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