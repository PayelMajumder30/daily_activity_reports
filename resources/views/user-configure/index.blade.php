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

    <div class="d-flex justify-content-between mb-3">
        <h4>User Configuration</h4>

        <a href="{{ route('user-configuration.create') }}" class="btn btn-primary">       
           + Add User
        </a>

    </div>

    <table class="table table-bordered">

        <thead>
            <tr>
                <th>SL</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th width="170">Action</th>
            </tr>
        </thead>

        <tbody>

            @foreach($users as $user)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    @if($user->role==0)
                        Management
                    @elseif($user->role==1)
                        Uploader
                    @else
                        Manpower
                    @endif
                </td>

                <td>

                    <a href="{{ route('user-configuration.edit',$user->id) }}" class="btn btn-warning btn-sm">                    
                        Edit
                    </a>

                    <form action="{{ route('user-configuration.destroy',$user) }}" method="POST" style="display:inline">

                        @csrf
                        @method('DELETE')

                        <button class="btn btn-danger btn-sm" onclick="return confirm('Delete this user?')">
                            Delete
                        </button>
                    </form>
                </td>
            </tr>

            @endforeach

        </tbody>

    </table>
</div>

@endsection