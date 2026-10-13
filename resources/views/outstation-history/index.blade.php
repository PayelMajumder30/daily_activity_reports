@extends('layouts.app')

@section('title', 'Outstation Transfer Register')

@section('content')

<div class="container-fluid py-4">
    <div class="row mb-4">

        <div class="col-md-8">
            <h2 class="fw-bold">
                <i class="bi bi-box-seam"></i>
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

       
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle"
                    id="outstationHistoryTable">

                    <thead class="table-dark">
                        <tr>
                            <th>SL No.</th>
                            <th>Asset Tag No.</th>
                            <th>Asset Type</th>
                            <th>Asset Model</th>
                            <th>From Region</th>
                            <th>From Airport/Station</th>
                            <th>To Region</th>
                            <th>To Airport/Station</th>
                            <th>Outstation Date</th>
                            <th>Recorded By</th>
                            <th>Recorded At</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($histories as $history)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    {{ $history->assetInventory->tag_no ?? 'N/A' }}
                                </td>

                                <td>
                                    {{ $history->assetInventory->assetType->name ?? 'N/A' }}
                                </td>

                                <td>
                                    {{ $history->assetInventory->assetModel->model_name ?? 'N/A' }}
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

                                <td>
                                    {{ $history->createdBy->name ?? 'N/A' }}
                                </td>

                                <td>
                                    {{ $history->created_at
                                        ? $history->created_at->format('d-m-Y h:i A')
                                        : 'N/A' }}
                                </td>

                                <td>
                                    @if($history->asset_inventory_id)
                                        <a href="{{ route(
                                            'asset-inventory.outstation.history',
                                            $history->asset_inventory_id
                                        ) }}"
                                        class="btn btn-sm btn-info"
                                        title="View Asset Outstation History">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center">
                                    No outstation history records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

        </div>

    </div>

    @push('scripts')

    <script>
        $(document).ready(function () {
            $('#outstationHistoryTable').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                responsive: true,
                order: [[8, 'desc']]
            });
        });
    </script>

    @endpush
    @endsection