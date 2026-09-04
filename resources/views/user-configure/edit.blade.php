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

            <a href="{{ route('user-configuration.index') }}" class="btn btn-secondary">
                Back
            </a>

        </div>

        <div class="card-body">
            <form action="{{ route('user-configuration.update', encryptId($user->id)) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label>Name <span class="text-danger">*</span></label>

                        <input type="text"
                            name="name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $user->name) }}">

                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Email <span class="text-danger">*</span></label>

                        <input type="email"
                            name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', $user->email) }}">

                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label>Password</label>

                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">

                        <small class="text-muted">
                            Leave blank to keep the existing password.
                        </small>

                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Role <span class="text-danger">*</span></label>

                        <select name="role"
                                class="form-select @error('role') is-invalid @enderror">

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
                                Manpower
                            </option>

                        </select>

                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                <button type="submit" class="btn btn-primary">
                    Update User
                </button>

                <a href="{{ route('user-configuration.index') }}" class="btn btn-secondary">
                    Cancel
                </a>

            </form>
        </div>
       
    </div>
    

</div>

@endsection