@extends('layouts.app')

@section('title', 'Audit Trail')

@section('content')

<div class="container-fluid py-4">
 
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold">
                <i class="bi bi-clock-history"></i>
                Audit Trail
            </h2>
            <p class="text-muted">
                View complete history of system activities performed by users.
            </p>
        </div>

        <div class="col-md-4 text-end">
             <h6 class="text-secondary">
                {{ now()->format('d M Y') }}
            </h6>
        </div>
    </div>


    <!-- Search Card -->
    <div class="card shadow border-0 mb-4">

        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-search"></i>
                Search Audit Logs
            </h5>
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('audit.trail') }}">
                <div class="row">

                    <div class="col-md-2">

                        <label>User</label>

                        <select name="user" class="form-select">

                            <option value="">All Users</option>

                            @foreach($users as $user)

                                <option value="{{ $user->id }}"
                                    {{ request('user')==$user->id?'selected':'' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    
                    <div class="col-md-2">
                        <label>From Date</label>
                        <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control datepicker" >
                        
                    </div>

                    <div class="col-md-2">
                        <label>To Date</label>
                        <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control datepicker">
                    </div>

                    <div class="col-md-2">
                        <label>Module</label>
                        <select name="module" class="form-select">
                            <option value="">All Modules</option>
                            @foreach($modules as $module)
                                <option value="{{ $module }}"
                                    {{ request('module') == $module ? 'selected' : '' }}>
                                    {{ $module }}
                                </option>

                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">

                        <label>Action</label>
                        <select name="action" class="form-select">
                            <option value="">All Actions</option>
                            @foreach($actions as $action)
                                <option value="{{ $action }}"
                                    {{ request('action') == $action ? 'selected' : '' }}>
                                    {{ $action }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">

                        <label>&nbsp;</label>

                        <div>
                            <button class="btn btn-primary">
                                <i class="bi bi-search"></i>
                                Search
                            </button>

                            <a href="{{ route('audit.trail') }}" class="btn btn-secondary">
                                Reset
                            </a>
                        </div>

                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- Table -->

    <div class="card shadow border-0">

        <div class="card-header d-flex justify-content-between">

            <h5 class="mb-0">
                Audit Log Details
            </h5>

            <span class="badge bg-primary">
                Total Logs : {{ $logs->total() }}
            </span>

        </div>

        <!-- table -->
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th width="60">SL</th>
                            <th>User</th>
                            <th>Module</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>

                    <tbody>
                    @forelse($logs as $key=>$log)
                        <tr>
                            <td>
                                {{ $logs->firstItem()+$key }}
                            </td>

                            <td>
                                {{ ucwords($log->user->name ?? 'System') }}
                            </td>

                            <td>
                                {{ $log->module }}                              
                            </td>

                            <td>
                                {{ $log->action }}                              
                            </td>

                            <td>
                                {{ $log->description }}
                            </td>

                            <td>
                                {{ $log->created_at->format('d-m-Y') }}
                                <br>
                                <small class="text-muted">
                                    {{ $log->created_at->format('h:i:s A') }}
                                </small>
                            </td>
                        </tr>
                    @empty

                        <tr>
                            <td colspan="6" class="text-center">
                                No Audit Records Found
                            </td>
                        </tr>
                    @endforelse
                    </tbody>

                </table>
            </div>

        </div>

        <div class="card-footer">

            {{ $logs->withQueryString()->links('pagination::bootstrap-5') }}

        </div>

    </div>

</div>
</div>

@push('scripts')
<script>

</script>
@endpush
@endsection