@extends('layouts.app')

@section('title', 'Asset Replacement Register')

@section('content')

<div class="container-fluid py-4">
    <div class="row mb-4">

        <div class="col-md-8">
            <h2 class="fw-bold">
                <i class="bi bi-arrow-left-right"></i>
                Asset Replacement Register
            </h2>

            <p class="text-muted">
                Asset Replacement History
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
                Search Asset replacement history
            </h5>

            <a href="{{ route('replacement.history.export', request()->query()) }}" class="btn btn-success btn-sm">               
                <i class="bi bi-file-earmark-excel"></i>
                Export to Excel
            </a>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('replacement.history.index') }}">                           

                <div class="row">
                    <div class="col-md-3">
                    <label for="tag_no" class="form-label">Tag No.</label>
                    <input type="text" name="tag_no" id="tag_no" class="form-control" value="{{ request('tag_no') }}" placeholder="Search Tag">         
                </div>

                {{-- Asset Type --}}
                <div class="col-md-3">
                    <label for="asset_type" class="form-label">Asset Type</label>

                    <select name="asset_type" id="asset_type" class="form-select">
                        <option value="">All Asset Types</option>

                        @foreach($assetTypes as $type)
                            <option value="{{ $type->id }}" {{ (string) request('asset_type') === (string) $type->id ? 'selected' : '' }}>                                                        
                                {{ ucwords($type->name) }}
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

                    <a href="{{ route('replacement.history.index') }}" class="btn btn-secondary">                   
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow border-0">

        <div class="card-header d-flex justify-content-between align-items-center">

            <h5 class="mb-0">
                Asset Replacement Register
            </h5>

        </div>

        <div class="card-body">
            {{-- Replacement History Table --}}
            <div class="table-responsive">
                <table id="replacementHistoryTable"
                       class="table table-bordered table-striped table-hover align-middle">

                    <thead class="table-dark">
                        <tr>
                            <th>SL No.</th>
                            <th>Asset Tag No.</th>
                            <th>Asset Type</th>
                            <th>Location </th>
                            <th>Station</th>
                            <th>Old Model</th>
                            <th>Old Serial No.</th>
                            <th>New Model</th>
                            <th>New Serial No.</th>
                            <th>Replacement Date</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($replacementHistories as $history)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    {{ $history->assetInventory->tag_no ?? 'N/A' }}
                                </td>

                                <td>
                                    {{ $history->assetInventory->assetType->name ?? 'N/A' }}
                                </td>

                                <td>
                                    {{ $history->assetInventory->location->name ?? 'N/A' }}                                   
                                </td>
                                <td>{{ $history->assetInventory->station->station_name ?? 'N/A' }}</td>

                                <td>
                                    {{ $history->oldAssetModel->model_name ?? 'N/A' }}
                                </td>

                                <td>
                                    {{ $history->old_serial_no ?: 'N/A' }}
                                </td>
                                
                                <td>
                                    {{ $history->newAssetModel->model_name ?? 'N/A' }}
                                </td>

                                <td>
                                    {{ $history->new_serial_no ?: 'N/A' }}
                                </td>

                                <td>
                                    {{ $history->replaced_at
                                        ? $history->replaced_at->format('d-m-Y')
                                        : 'N/A' }}
                                </td>

                                <td>
                                    {{ $history->remarks ?: 'N/A' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center">
                                    No asset replacement history found.
                                </td>
                            </tr>
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
        $('#replacementHistoryTable').DataTable({
            pageLength: 10,
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, 'All']
            ],
            ordering: true,
            searching: false,
            responsive: true,
            order: [],
            language: {
                emptyTable: "No asset replacement history found."
            }
        });
    });
</script>

@endpush
@endsection