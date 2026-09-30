@extends('layouts.app')

@section('title', 'Region-Airport/Station')

@section('content')

<div class="container-fluid py-4">

    <div class="row mb-4">

        <div class="col-md-8">
            <h2 class="fw-bold">
                <i class="bi bi-list-ul"></i>
                {{ ucwords($location->name) }} Stations
            </h2>

            <p class="text-muted">
                Manage stations under {{ ucwords($location->name) }}.
            </p>
        </div>

    </div>


    <div class="row">

        {{-- Station List --}}

        <div class="col-lg-8 mb-4">

            <div class="card shadow border-0">

                <div class="card-header d-flex justify-content-between align-items-center">

                    <h5 class="mb-0">
                        Airport/Station List ({{ucwords($location->name)}})
                    </h5>
                    <a href="{{ route('location.index') }}" class="btn btn-secondary">                                  
                        Back
                    </a>
                </div>

                <div class="card-body">

                    <table class="table table-bordered table-hover mb-0" id="stationTable">
                        <thead class="table-dark">
                            <tr>
                                <th width="70">SL</th>
                                <th>Station Name</th>
                                <th>Code name</th>
                                <th>Status</th>
                                <th width="100">Action</th>
                            </tr>

                        </thead>

                        <tbody>

                            @foreach($stations as $station)
                                <tr>
                                    <td>
                                        {{ $loop->iteration }}
                                    </td>

                                    <td>
                                        {{ ucwords($station->station_name) }}
                                    </td>

                                    <td>{{ $station->short_name ?? 'N/A' }}</td>

                                    <td class="text-center">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input station-status" type="checkbox" data-id="{{ encryptId($station->id) }}" {{ $station->status ? 'checked' : '' }}>
                                        </div>
                                    </td>
                                    <td> 
                                        <button type="button" class="btn btn-warning btn-sm editStation" data-id="{{ encryptId($station->id) }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    </td>
                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


       
        {{-- Add / Update Station --}}
        <div class="col-lg-4">

            <div class="card shadow border-0">

                <div class="card-header">
                    <h5 class="mb-0" id="stationFormTitle">
                        Add Station
                    </h5>
                </div>

                <div class="card-body">

                    <form id="stationForm" action="{{ route('location.stations.store', encryptId($location->id)) }}" method="POST">

                        @csrf
                        <div id="stationMethodField"></div>
                        {{-- Sation Name --}}

                        <div class="mb-3">
                            <label class="form-label">
                                Station Name
                            </label>

                            <input type="text" name="station_name" id="station_name"                                                        
                                class="form-control" placeholder="Enter station name" value="{{ old('station_name') }}" required>                                                                                 
                            @error('station_name')
                                <small class="text-danger">
                                    {{ $message }}
                                </small>
                            @enderror
                        </div>

                        {{-- Station Short Name --}}
                        <div class="mb-3">
                            <label class="form-label">
                                Station Short Name
                            </label>

                            <input type="text" name="short_name" id="station_short_name"                               
                                 class="form-control" placeholder="Enter station short name" value="{{ old('short_name') }}" maxlength="50">                                                                                                                       

                            @error('short_name')
                                <small class="text-danger">
                                    {{ $message }}
                                </small>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary" id="stationSubmitBtn">
                            <i class="bi bi-check-circle"></i>
                            Save Station
                        </button>


                        <button type="button" class="btn btn-secondary d-none" id="stationCancelBtn">
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
            text: @json(session('success')),
            timer: 2000,
            showConfirmButton: false
        });
    </script>
@endif

<script>
     $(document).ready(function () {
            // ==========================
            // DataTable
            // ==========================

            $('#stationTable').DataTable({
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, 'All']
                ],
                ordering: true,
                searching: false,
                responsive: true,
                language: {
                    emptyTable: "No Station Found"
                }
            });

            function clearFormErrors() {
                $('.text-danger').text('');
                $('.form-control').removeClass('is-invalid');
            }
            // ==========================
            // Route URLs
            // ==========================

            const editUrl = "{{ route('location.stations.edit', ':id') }}";               
            const updateUrl = "{{ route('location.stations.update', ':id') }}";               
            const storeUrl =
                "{{ route(
                    'location.stations.store',
                    encryptId($location->id)
                ) }}";

            // ==========================
            // Edit Station
            // ==========================

            $(document).on('click', '.editStation', function () {

                clearFormErrors();

                let id = $(this).data('id');

                $.ajax({
                    url: editUrl.replace(':id', id),
                    type: 'GET',

                    success: function (res) {

                        // Change title
                        $('#stationFormTitle').text('Update Station');

                        // Fill station name
                        $('#station_name').val(res.station_name);

                        // Fill station short name
                        $('#station_short_name').val(res.short_name);

                        // Change button
                        $('#stationSubmitBtn')
                            .removeClass('btn-primary')
                            .addClass('btn-warning')
                            .html(
                                '<i class="bi bi-pencil-square"></i> Update Station'
                            );

                        // Show cancel
                        $('#stationCancelBtn').removeClass('d-none');

                        // Change form action
                        $('#stationForm').attr(
                            'action',
                            updateUrl.replace(':id', id)
                        );

                        // Add PUT method
                        $('#stationMethodField').html('@method("PUT")');
                    },

                    error: function () {

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Unable to load station.'
                        });

                    }
                });
            });


            // ==========================
            // Cancel Edit
            // ==========================

            $('#stationCancelBtn').on('click', function () {

                clearFormErrors();

                $('#stationForm').attr('action', storeUrl);

                $('#stationMethodField').html('');

                $('#station_name').val('');
                $('#station_short_name').val('');

                $('#stationFormTitle').text('Add Station');

                $('#stationSubmitBtn')
                    .removeClass('btn-warning')
                    .addClass('btn-primary')
                    .html(
                        '<i class="bi bi-check-circle"></i> Save Station'
                    );

                $(this).addClass('d-none');
            });


            // ==========================
            // Station Status
            // ==========================

            $(document).on('change', '.station-status',
                               
                function () {
                    let checkbox = $(this);
                    let id = checkbox.data('id');

                    $.ajax({

                        url:"{{ route('location.stations.status', ':id') }}".replace(':id', id),                                          
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}"                             
                        },

                        success: function (res) {

                            Swal.fire({
                                icon: 'success',
                                title: 'Updated',
                                text: res.status
                                    ? 'Station Activated'
                                    : 'Station Deactivated',
                                timer: 1200,
                                showConfirmButton: false
                            });
                        },

                        error: function () {
                            // Revert checkbox
                            checkbox.prop(
                                'checked',
                                !checkbox.prop('checked')
                            );

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Unable to update station status.'                                   
                            });

                        }
                    });
                }
            );
        });

</script>
@endpush
@endsection