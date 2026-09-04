@extends('layouts.app')

@section('title', 'Location')

@section('content')

<div class="container-fluid py-4">

    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold">
                <i class="bi bi-geo-alt"></i>
                Region
            </h2>
            <p class="text-muted">
                Manage all Regions. 
            </p>
        </div>

        <div class="col-md-4 text-end">
            <h6 class="text-secondary">
                {{ now()->format('d M Y') }}
            </h6>
        </div>
    </div>

    <div class="row">

        <!-- Location List -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header">
                    <h5 class="mb-0">Region List</h5>
                </div>

                <div class="card-body">
                    <table class="table table-bordered table-hover mb-0" id="locationTable">
                        <thead class="table-dark">
                            <tr>
                                <th width="70">SL</th>
                                <th>Name</th>
                                <th>Airport/Station</th>
                                <th>Short Name</th>
                                <th>Status</th>
                                <th width="120">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($locations as $location)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>{{ ucwords($location->name) }}</td>
                                 <td>
                                    <a href="{{ route('location.stations.index', encryptId($location->id)) }}" class="btn btn-info btn-sm" title="station">                                        
                                        <i class="bi bi-list-ul"></i>                                       
                                    </a>
                                </td>
                                <td>{{ $location->short_name }}</td>

                                <td class="text-center">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input location-status" type="checkbox"                                       
                                            data-id="{{ encryptId($location->id) }}" {{ $location->status ? 'checked' : '' }}>                                       
                                    </div>
                                </td>

                                <td>
                                    <button type="button" class="btn btn-warning btn-sm editLocation" data-id="{{ encryptId($location->id) }}">     
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    No Location Found
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
                        Add Region
                    </h5>
                </div>

                <div class="card-body">

                    <form id="locationForm" action="{{ route('location.store') }}" method="POST">
                        @csrf

                        <div id="methodField"></div>

                        <div class="mb-3">

                            <label class="form-label">
                                Location name
                            </label>

                            <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" id="name">
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Location short name
                            </label>
                            <input type="text" class="form-control @error('short_name') is-invalid @enderror" name="short_name" id="short_name">
                            @error('short_name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <button class="btn btn-primary" id="submitBtn">
                            <i class="bi bi-check-circle"></i>
                            Save Location
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

            $('#locationTable').DataTable({
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, 'All']
                ],
                ordering: true,
                searching: false,
                responsive: true
            });

            function clearFormErrors() {
                $('.text-danger').text('');
                $('.form-control').removeClass('is-invalid');

                $('#name').val('');
                $('#short_name').val('');
            }
            // Route templates
            const editUrl = "{{ route('location.edit', ':id') }}";
            const updateUrl = "{{ route('location.update', ':id') }}";
            const storeUrl = "{{ route('location.store') }}";

            $(document).on('click', '.editLocation', function () {

                clearFormErrors();
                let id = $(this).data('id');
                $.ajax({
                    url: editUrl.replace(':id', id),
                    type: 'GET',

                    success: function (res) {

                        $('#formTitle').text('Update Location');

                        $('#name').val(res.name);
                        $('#short_name').val(res.short_name);

                        $('#submitBtn')
                            .removeClass('btn-primary')
                            .addClass('btn-warning')
                            .html('<i class="bi bi-pencil-square"></i> Update Location');

                        $('#cancelBtn').removeClass('d-none');

                        $('#locationForm')
                            .attr('action', updateUrl.replace(':id', id));

                        $('#methodField').html('@method("PUT")');
                    }
                });
            });

            $('#cancelBtn').on('click', function () {

                clearFormErrors();

                $('#locationForm').attr('action', storeUrl);
                $('#methodField').html('');
                $('#name').val('');
                $('#short_name').val('');
                $('#formTitle').text('Add Location');
                $('#submitBtn')
                    .removeClass('btn-warning')
                    .addClass('btn-primary')
                    .html('<i class="bi bi-check-circle"></i> Save Location');

                $(this).addClass('d-none');

            });
        });


        $(document).on('change', '.location-status', function () {
            let checkbox = $(this);
            $.ajax({

                url: "{{ route('location.changeStatus', ':id') }}"
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
                                ? 'Location Activated'
                                : 'Location Deactivated',
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