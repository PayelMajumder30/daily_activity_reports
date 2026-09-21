@extends('layouts.app')

@section('title', 'User location Permission')

@section('content')

<div class="container-fluid py-4">

    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold">
                <i class="bi bi-shield-check"></i>
                User Location Permission
            </h2>
            <p class="text-muted">
                Call Coordinator Location Permission
            </p>
        </div>

        <div class="col-md-4 text-end">
            <h6 class="text-secondary">
                {{ now()->format('d M Y') }}
            </h6>
        </div>
    </div>

    {{-- User Table Card --}}
    <div class="card shadow border-0">

        <div class="card-header d-flex justify-content-between align-items-center">

            <h5 class="mb-0">              
                Location permission
            </h5>

        </div>

        <div class="card-body">
            {{-- Success Message --}}
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i>
                    {{ session('success') }}
                </div>
            @endif

            {{-- Validation Error --}}
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- User Selection --}}
            <form method="GET" action="{{ route('user-location-permission.index') }}">
                <div class="row mb-4">
                    <div class="col md-5">
                        <label class="form-label fw-bold">
                            Call Coordinator
                        </label>

                        <select name="user_id" class="form-select" onchange="this.form.submit()">
                            <option value="">
                                Select Call Coordinator
                            </option>

                            @foreach($users as $user)

                                <option value="{{ $user->id }}" {{ $selectedUser && $selectedUser->id == $user->id ? 'selected' : '' }}>                                   
                                    {{ ucwords($user->name) }}
                                    ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>

            @if($selectedUser)

                <hr>

                <div class="d-flex justify-content-between align-items-center mb-3">

                    <div>

                        <h6 class="mb-1">
                            Permission for:
                            <strong>
                                {{ ucwords($selectedUser->name) }}
                            </strong>
                        </h6>

                        <small class="text-muted">
                            Select the Region and Airport Stations this Call Coordinator can access.
                        </small>

                    </div>

                </div>


                <form method="POST" action="{{ route('user-location-permission.store') }}">
                      
                    @csrf

                    <input type="hidden" name="user_id" value="{{ $selectedUser->id }}">

                    <div class="row">

                        @foreach($locations as $location)

                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border">
                                    <div class="card-header bg-light">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input location-checkbox" id="location_{{ $location->id }}" data-location="{{ $location->id }}">

                                            <label class="form-check-label fw-bold" for="location_{{ $location->id }}">
                                                                                                                                           
                                                {{ ucwords($location->name) }}

                                                @if($location->short_name)
                                                    <span class="text-muted">
                                                        ({{ $location->short_name }})
                                                    </span>
                                                @endif

                                            </label>

                                        </div>

                                    </div>


                                    <div class="card-body">

                                        @forelse($location->airportStation as $station)

                                            <div class="form-check mb-2">

                                                <input type="checkbox" class="form-check-input station-checkbox"                                                                                                  
                                                    name="stations[]" value="{{ $station->id }}" id="station_{{ $station->id }}"                                                   
                                                    data-location="{{ $location->id }}" {{ in_array($station->id, $permissionStationIds) ? 'checked' : '' }}>
                                                                                                    
                                                <label class="form-check-label" for="station_{{ $station->id }}">                                              

                                                    {{ $station->station_name }}

                                                    @if($station->short_name)
                                                        <span class="text-muted">
                                                            ({{ $station->short_name }})
                                                        </span>
                                                    @endif

                                                </label>

                                            </div>

                                        @empty

                                            <div class="text-muted">
                                                No station available.
                                            </div>

                                        @endforelse

                                    </div>

                                </div>

                            </div>

                        @endforeach

                    </div>


                    <div class="d-flex justify-content-end mt-3">

                        <button type="submit" class="btn btn-primary">                           
                            <i class="bi bi-check-circle"></i>
                            Save Permissions
                        </button>

                    </div>

                </form>

            @else

                <div class="alert alert-info">

                    <i class="bi bi-info-circle"></i>

                    Please select a Call Coordinator to configure
                    Location and Station permissions.

                </div>

            @endif
        </div>
    </div>
</div>

@push('scripts')

<script>

    $(document).ready(function () {

        /*
        |--------------------------------------------------------------------------
        | Update Location checkbox based on selected stations
        |--------------------------------------------------------------------------
        */

        function updateLocationCheckbox(locationId) {

            let stations = $('.station-checkbox[data-location="' + locationId + '"]');

            let checkedStations = stations.filter(':checked');

            let locationCheckbox = $('.location-checkbox[data-location="' + locationId + '"]');
                
            if (stations.length === 0) {

                locationCheckbox.prop('checked', false);
                locationCheckbox.prop('indeterminate', false);

                return;
            }


            if (checkedStations.length === stations.length) {

                // All stations checked
                locationCheckbox.prop('checked', true);
                locationCheckbox.prop('indeterminate', false);

            }
            else if (checkedStations.length > 0) {

                // Some stations checked
                locationCheckbox.prop('checked', false);
                locationCheckbox.prop('indeterminate', true);

            }
            else {

                // No station checked
                locationCheckbox.prop('checked', false);
                locationCheckbox.prop('indeterminate', false);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Location checkbox
        |--------------------------------------------------------------------------
        | Checking location -> check all stations
        | Unchecking location -> uncheck all stations
        |--------------------------------------------------------------------------
        */

        $('.location-checkbox').on('change', function () {

            let locationId = $(this).data('location');

            let checked = $(this).is(':checked');

            $('.station-checkbox[data-location="' + locationId + '"]')
                .prop('checked', checked);

            $(this).prop('indeterminate', false);
        });


        /*
        |--------------------------------------------------------------------------
        | Individual station checkbox
        |--------------------------------------------------------------------------
        */

        $('.station-checkbox').on('change', function () {

            let locationId = $(this).data('location');

            updateLocationCheckbox(locationId);
        });


        /*
        |--------------------------------------------------------------------------
        | Initial state
        |--------------------------------------------------------------------------
        */

        $('.location-checkbox').each(function () {

            let locationId = $(this).data('location');

            updateLocationCheckbox(locationId);
        });

    });

</script>

@endpush
@endsection