@extends('layouts.app')

@section('title', 'Outstation Transfer Register')

@section('content')

<div class="container-fluid py-4">
    <div class="row mb-4">

        <div class="col-md-8">
            <h2 class="fw-bold">
                <i class="bi bi-arrow-left-right"></i>
                Outstation Transfer Register
            </h2>

            <p class="text-muted">
                Outstation Transfer History
            </p>
        </div>

        <div class="col-md-4 text-end">
            <h6 class="text-secondary">
                {{ now()->format('d M Y') }}
            </h6>
        </div>
    </div>
  
    <div class="card shadow border-0 mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-search"></i>
                Search Asset outstation history
            </h5>

            <a href="{{ route('outstation.register.export', request()->query()) }}"
                class="btn btn-success btn-sm">
                    <i class="bi bi-file-earmark-excel"></i>
                    Export to Excel
            </a>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('outstation.history.index') }}">             
                <div class="row">
                    {{-- Tag No --}}
                    <div class="col-md-3">
                        <label>Tag No.</label>
                        <input type="text" name="tag_no" class="form-control" value="{{ request('tag_no') }}"placeholder="Search Tag">      
                    </div>


                    {{-- Serial Number --}}
                    <div class="col-md-3">
                        <label>Serial No.</label>
                        <input type="text" name="serial_no" class="form-control" value="{{ request('serial_no') }}" placeholder="Search Serial">     
                    </div>

                    {{-- Asset Type --}}
                    <div class="col-md-3">
                        <label>Asset Type</label>

                        <select name="asset_type" class="form-select">                           
                            <option value="">
                                All Asset Types
                            </option>

                            @foreach($assetTypes as $type)

                                <option value="{{ $type->id }}"
                                    {{ request('asset_type') == $type->id ? 'selected' : '' }}>
                                    {{ ucwords($type->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>


                    {{-- Asset Model --}}
                    <div class="col-md-3">
                        <label>Asset Model</label>

                        <select name="asset_model" id="asset_model" class="form-select">                           
                            <option value="">
                                All Asset Models
                            </option>

                            @foreach($assetModels as $assetModel)
                                <option value="{{ $assetModel->id }}"
                                    {{ request('asset_model') == $assetModel->id ? 'selected' : '' }}>
                                    {{ ucwords($assetModel->model_name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>


                </div>

                <div class="mt-3">

                    <button type="submit" class="btn btn-primary">                        
                        <i class="bi bi-search"></i>
                        Search
                    </button>

                    <a href="{{ route('outstation.history.index') }}" class="btn btn-secondary">                   
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow border-0">

        <div class="card-header d-flex justify-content-between align-items-center">

            <h5 class="mb-0">
                Asset Outstation Transfer Register
            </h5>

        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0" id="outstationHistoryTable">                   

                    <thead class="table-dark">
                        <tr>
                            <th>SL No.</th>
                            <th>Asset Type</th>
                            <th>Asset Model</th>
                            <th>Asset Serial No.</th>
                            <th>Asset Tag No.</th>                         
                            <th>From Region</th>
                            <th>From Airport/Station</th>
                            <th>To Region</th>
                            <th>To Airport/Station</th>
                            <th>Outstation Date</th>
                            <!-- <th>Recorded By</th> -->                          
                            
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($histories as $history)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    {{ $history->assetInventory->assetType->name ?? 'N/A' }}
                                </td>
                                <td>
                                    {{ $history->assetInventory->assetModel->model_name ?? 'N/A' }}
                                </td>
                                <td>{{ $history->assetInventory->serial_no ?? 'N/A' }}</td>
                                <td>
                                    {{ $history->assetInventory->tag_no ?? 'N/A' }}
                                </td>                              

                                <td>
                                    {{ $history->fromLocation->name ?? 'N/A' }}
                                </td>

                                <td>
                                    {{ $history->fromStation->station_name ?? 'N/A' }}
                                </td>

                                <td>
                                    {{ $history->toLocation->name ?? 'N/A' }}
                                </td>

                                <td>
                                    {{ $history->toStation->station_name ?? 'N/A' }}
                                </td>

                                <td>
                                    {{ $history->outstation_date
                                        ? $history->outstation_date->format('d-m-Y')
                                        : 'N/A' }}
                                </td>

                                <!-- <td>
                                    {{ $history->createdBy->name ?? 'N/A' }}
                                </td> -->

                            
                            </tr>
                        @empty
                          {{-- Keep tbody empty; DataTables will display its empty message --}}
                        @endforelse
                    </tbody>

                </table>
            </div>

        </div>
    </div>

</div>

    @push('scripts')
    <script>

        $(document).ready(function () {
            $('#outstationHistoryTable').DataTable({
                pageLength: 10,

                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, 'All']
                ],

                ordering: true,
                searching: false,
                responsive: true,

                language: {
                    emptyTable: "No outstation history records found.",
                    zeroRecords: "No matching records found."
                }
            });

            $('#asset_model').select2({
                placeholder: 'All Asset Models',
                allowClear: true,
                width: '100%'
            });
        });
    </script>

    @endpush
    @endsection