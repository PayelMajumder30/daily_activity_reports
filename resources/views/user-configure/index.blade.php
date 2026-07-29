@extends('layouts.app')

@section('title', 'User Configuration')

@section('content')

<div class="container-fluid py-4">

    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold">
                <i class="bi bi-file-earmark-text"></i>
                Total Users
            </h2>
            <p class="text-muted">
                View all registered users, create new accounts, edit user details, and manage user roles from a single interface.
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
                <i class="bi bi-people"></i>
                User Details
            </h5>

            <a href="{{ route('user-configuration.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i>
                Add User
            </a>

        </div>

        <div class="card-body">

            <table class="table table-bordered table-hover">

                <thead class="table-dark">
                    <tr>
                        <th width="70">SL</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th width="150">Role</th>
                        <th width="140" class="text-center">Action</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($users as $user)

                        <tr>

                            <td>{{ $loop->iteration }}</td>

                            <td>{{ ucwords($user->name) }}</td>

                            <td>{{ $user->email }}</td>

                            <td>
                                @if($user->role == 0)
                                    Management
                                @elseif($user->role == 1)
                                  Uploader
                                @else
                                    Manpower
                                @endif
                            </td>

                            <td class="text-center">

                                <a href="{{ route('user-configuration.edit', encryptId($user->id)) }}"
                                class="btn btn-warning btn-sm"
                                title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>

                                <form action="{{ route('user-configuration.destroy', encryptId($user->id)) }}"  method="POST" class="delete-form d-inline" style="display:inline">
                                   
                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-danger btn-sm" title="Delete">         
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                No Users Found
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

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

             // delete user confirmation
        $(document).on('submit', '.delete-form', function(e) {
            e.preventDefault();

            let form = this;
            Swal.fire({
                title: 'Are you sure?',
                text: 'You want to delete this user.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if(result.isConfirmed) {
                    form.submit();
                }
            });
        });
    </script>
    @endif

   
@endpush
@endsection