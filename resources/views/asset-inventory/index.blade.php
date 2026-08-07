@extends('layouts.app')

@section('title', 'Asset Inventory')

@section('content')

<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-md-8">
            <h3 class="fw-bold">
                Asset Inventory
            </h3>

            <p class="text-muted">
                Manage all inventory assets.
            </p>
        </div>

        <div class="col-md-4 text-end">
            <a href="{{ route('asset-inventory.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add Inventory
            </a>
        </div>
    </div>

    <div class="card-shadow">
        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>SL</th>
                        <th>Tag</th>
                        <th>PO NO</th>
                        <th>Asset Type</th>
                        <th>Asset Model</th>
                        <th>Serial No.</th>
                        <th>Location</th>
                        <th>Installation</th>
                        <th>Warranty end</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($inventories as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->tag_no }}</td>
                        <td>{{ $item->po_number }}</td>
                        <td>{{ $item->assetType->name }}</td>
                        <td>{{ $item->assetModel->model_name }}</td>
                        <td>{{ $item->serial_no }}</td>
                        <td>{{ $item->location->name }}</td>
                        <td>{{ $item->installation_date }}</td>
                        <td>{{ $item->warranty_end }}</td>
                        <td>
                            <span class="badge bg-success">{{ $item->asset_status }}</span>
                        </td>
                    </tr>                       
                    @empty
                    <tr>
                        <td colspan="10" class="text-center">No Inventory Found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div> 
</div>

@endsection