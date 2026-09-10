@extends('layouts.app')

@section('title', 'Update User Configuration')

@section('content')

<div class="container-fluid py-4">

    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold">
                <i class="bi bi-file-earmark-text"></i>
                Update Users
            </h2>
            <p class="text-muted">
                Update a user account by entering the user's details and selecting the appropriate access role.
            </p>
        </div>

        <div class="col-md-4 text-end">
            <h6 class="text-secondary">
                {{ now()->format('d M Y') }}
            </h6>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">

            <h4>Update User</h4>

        </div>

        <div class="card-body">

            <form action="{{ route('user-configuration.update', encryptId($user->id)) }}" method="POST">

                @csrf
                @method('PUT')

                {{-- Row 1 --}}
                <div class="row">

                    {{-- Name --}}
                    <div class="col-md-6 mb-3">

                        <label>
                            Name <span class="text-danger">*</span>
                        </label>

                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}">
                        @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    {{-- Email --}}
                    <div class="col-md-6 mb-3">

                        <label>
                            Email <span class="text-danger">*</span>
                        </label>

                        <input type="email" name="email"                           
                            class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}">                          
                        @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>


                {{-- Row 2 --}}
                <div class="row">

                    {{-- Region --}}
                    <div class="col-md-6 mb-3">

                        <label>
                            Region <span class="text-danger">*</span>
                        </label>

                        <select name="location_id"  id="location_id" class="form-select @error('location_id') is-invalid @enderror">                                                              
                            <option value="">Select Region</option>

                            @foreach($locations as $location)
                                <option value="{{ $location->id }}"
                                    {{ old('location_id', $user->location_id) == $location->id ? 'selected' : '' }}>

                                    {{ $location->name }}
                                </option>

                            @endforeach

                        </select>

                        @error('location_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    {{-- Station --}}
                    <div class="col-md-6 mb-3">

                        <label>
                            Airport / Station <span class="text-danger">*</span>
                        </label>

                        <select name="station_id" id="station_id" class="form-select @error('station_id') is-invalid @enderror">                                                        
                            <option value="">Select Station</option>

                        </select>

                        @error('station_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>


                {{-- Row 3 --}}
                <div class="row">

                    {{-- Password --}}
                    <div class="col-md-6 mb-3">

                        <label>Password</label>

                        <div class="input-group">
                            <input type="password" id="password" name="password" 
                                class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">                                                                                       
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">                                                                    
                                <i class="bi bi-eye"></i>
                            </button>

                        </div>

                        <small class="text-muted">
                            Leave blank to keep the existing password.
                        </small>

                        @error('password')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    {{-- Role --}}
                    <div class="col-md-6 mb-3">

                        <label>
                            Role <span class="text-danger">*</span>
                        </label>

                        <select name="role" class="form-select @error('role') is-invalid @enderror">                               
                            <option value="">Select Role</option>

                            <option value="0"
                                {{ old('role', $user->role) == 0 ? 'selected' : '' }}>
                                Management
                            </option>

                            <option value="1"
                                {{ old('role', $user->role) == 1 ? 'selected' : '' }}>
                                Uploader
                            </option>

                            <option value="2"
                                {{ old('role', $user->role) == 2 ? 'selected' : '' }}>
                                Engineer
                            </option>

                        </select>

                        @error('role')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>
                </div>


                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i>
                    Update User
                </button>

                <a href="{{ route('user-configuration.index') }}" class="btn btn-secondary">          
                    Back
                </a>

            </form>

        </div>
       
    </div>
    

</div>

@push('scripts')
<script>
    $(document).ready(function () {

        let oldLocationId = "{{ old('location_id', $user->location_id) }}";
        let oldStationId = "{{ old('station_id', $user->station_id) }}";


        function loadStations(locationId, selectedStationId = '') {
            let stationDropdown = $('#station_id');

            stationDropdown.html(
                '<option value="">Loading...</option>'
            );

            if (!locationId) {

                stationDropdown.html(
                    '<option value="">Select Station</option>'
                );

                return;
            }

            let url = "{{ route('user-configuration.stations', ':locationId') }}".replace(':locationId', locationId);                      

            $.ajax({

                url: url,
                type: "GET",

                success: function (stations) {

                    stationDropdown.empty();
                    stationDropdown.append(
                        '<option value="">Select Station</option>'
                    );


                    if (stations.length > 0) {

                        $.each(stations, function (key, station) {

                            let selected = '';

                            if (station.id == selectedStationId) {
                                selected = 'selected';
                            }

                            stationDropdown.append(
                                '<option value="' + station.id + '" ' +
                                selected + '>' +
                                station.station_name +
                                '</option>'
                            );

                        });

                    } else {

                        stationDropdown.append(
                            '<option value="">No station available</option>'
                        );

                    }

                },

                error: function (xhr) {

                    console.log(xhr.responseText);
                    stationDropdown.html(
                        '<option value="">Unable to load stations</option>'
                    );

                }

            });

        }


        // Load existing stations when edit page opens
        if (oldLocationId) {

            loadStations(
                oldLocationId,
                oldStationId
            );

        }

        // When region changes
        $('#location_id').on('change', function () {
            let locationId = $(this).val();

            // New region means no old station should remain selected
            loadStations(locationId, '');

        });


        // Password show/hide
        $('#togglePassword').click(function () {

            let password = $('#password');
            let icon = $(this).find('i');

            if (password.attr('type') === 'password') {
                password.attr('type', 'text');
                icon.removeClass('bi-eye').addClass('bi-eye-slash');                    

            } else {
                password.attr('type', 'password');
                icon.removeClass('bi-eye-slash').addClass('bi-eye');                   

            }

        });

    });
</script>
@endpush
@endsection