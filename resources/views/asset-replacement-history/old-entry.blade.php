
@extends('layouts.app')
@section('title', 'Old replacement')
@section('content')

<div class="container-fluid py-4">


    <div class="row mb-4">

        <div class="col-md-8">

            <h2 class="fw-bold">
                <i class="bi bi-clock-history"></i>
                Old Asset Replacement Entry
            </h2>

            <p class="text-muted">
                Search Existing Asset
            </p>

        </div>

        <div class="col-md-4 text-end">

            <h6 class="text-secondary">
                {{ now()->format('d M Y') }}
            </h6>

        </div>

    </div>

    <div class="card shadow border-0">
        {{-- Search Existing Asset --}}
        <div class="card mb-4">

            <div class="card-header">
                <h5 class="mb-0">Search Existing Asset</h5>
            </div>

            <div class="card-body">

                <form method="GET" action="{{ route('asset-replacement.old-entry') }}">
                    
                    <div class="row align-items-end">

                        <div class="col-md-5 mb-3">
                            <label class="form-label">Asset Model</label>

                            <select name="asset_model_id" class="form-select" id="search_asset_model">
                                                                      
                                <option value="">All Models</option>

                                @foreach($assetModels as $model)

                                    <option value="{{ $model->id }}"
                                        {{ request('asset_model_id') == $model->id ? 'selected' : '' }}>

                                        {{ $model->model_name }}

                                    </option>

                                @endforeach

                            </select>
                        </div>

                        <div class="col-md-5 mb-3">
                            <label class="form-label">Serial Number</label>

                            <input type="text" name="serial_no" class="form-control" placeholder="Enter serial number" value="{{ request('serial_no') }}">                                                                              
                        </div>

                        <div class="col-md-2 mb-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Search
                            </button>

                            <a href="{{ route('asset-replacement.old-entry') }}" class="btn btn-secondary">                   
                                Reset
                            </a>
                        </div>

                    </div>

                </form>

            </div>
        </div>


        {{-- Search Results --}}
        @if(request()->filled('asset_model_id') || request()->filled('serial_no'))

            <div class="card mb-4">

                <div class="card-header bg-light">
                    <h5 class="mb-0">Search Results</h5>
                </div>

                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table table-bordered table-striped align-middle">

                            <thead class="table-dark">
                                <tr>
                                    <th>SL No.</th>
                                    <th>Asset Tag</th>
                                    <th>Asset Type</th>
                                    <th>Current Model</th>
                                    <th>Current Serial No.</th>
                                    <th>Location</th>
                                    <th>Station</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse($assets as $asset)

                                    <tr>
                                        <td>{{ $loop->iteration }}</td>

                                        <td>{{ $asset->tag_no }}</td>

                                        <td>{{ ucwords($asset->assetType->name ?? 'N/A') }}</td>

                                        <td>{{ $asset->assetModel->model_name ?? 'N/A' }}</td>

                                        <td>{{ $asset->serial_no ?: 'N/A' }}</td>

                                        <td>{{ $asset->location->name ?? 'N/A' }}</td>

                                        <td>{{ $asset->station->station_name ?? 'N/A' }}</td>

                                        <td>
                                            <a href="{{ route('asset-replacement.old-entry', ['asset_model_id' => request('asset_model_id'),                                          
                                                'serial_no' => request('serial_no'), 'selected_asset_id' => $asset->id]) }}#historical-replacement-card" class="btn btn-sm btn-success">
                                                <i class="bi bi-check-circle"></i>
                                                Select
                                            </a>
                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="8" class="text-center text-danger">
                                            No matching assets found.
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>
            </div>

        @endif


        {{-- Selected Asset and Historical Replacement Form --}}
        @if($selectedAsset)
    
            <div class="card mb-4" id="historical-replacement-card" style="scroll-margin-top: 80px;">

                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-arrow-repeat"></i>
                        Replacement Details
                    </h5>
                </div>

                <div class="card-body">

                    {{-- Current Asset Details --}}
                    <div class="card bg-light border-0 mb-4">

                        <div class="card-body">

                            <h6 class="mb-3">Selected Asset - Current Details</h6>

                            <div class="row">

                                <div class="col-md-4 mb-2">
                                    <strong>Asset Tag:</strong>
                                    {{ $selectedAsset->tag_no }}
                                </div>

                                <div class="col-md-4 mb-2">
                                    <strong>Asset Type:</strong>
                                    {{ ucwords($selectedAsset->assetType->name ?? 'N/A') }}
                                </div>

                                <div class="col-md-4 mb-2">
                                    <strong>Current Model:</strong>
                                    {{ $selectedAsset->assetModel->model_name ?? 'N/A' }}
                                </div>

                                <div class="col-md-4 mb-2">
                                    <strong>Current Serial No.:</strong>
                                    {{ $selectedAsset->serial_no ?: 'N/A' }}
                                </div>

                                <div class="col-md-4 mb-2">
                                    <strong>Location:</strong>
                                    {{ $selectedAsset->location->name ?? 'N/A' }}
                                </div>

                                <div class="col-md-4 mb-2">
                                    <strong>Station:</strong>
                                    {{ $selectedAsset->station->station_name ?? 'N/A' }}
                                </div>

                            </div>

                        </div>
                    </div>


                    {{-- Replacement Entry Form --}}
                    <form method="POST" action="{{ route('asset-replacement.old-entry.store') }}">                   
                        @csrf

                        <input type="hidden" name="asset_inventory_id" value="{{ $selectedAsset->id }}">

                        <h6 class="mb-3">Enter Previous Asset Details</h6>

                        <div class="row">

                            {{-- Old Asset Model --}}
                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Old Asset Model <span class="text-danger">*</span>
                                </label>

                                <select name="old_asset_model_id" id="old_asset_model_id" class="form-select" required>
                                    <option value="">Select Old Asset Model</option>

                                    @foreach($oldAssetModels as $model)

                                        <option value="{{ $model->id }}" {{ old('old_asset_model_id') == $model->id ? 'selected' : '' }}>                                            
                                            {{ $model->model_name }}
                                        </option>

                                    @endforeach

                                </select>

                            </div>


                            {{-- Old Serial Number --}}
                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Old Serial Number <span class="text-danger">*</span>
                                </label>

                                <input type="text" name="old_serial_no" class="form-control" placeholder="Enter old serial number"
                                    value="{{ old('old_serial_no') }}" maxlength="50" required>         

                            </div>


                            {{-- Replacement Date --}}
                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Actual Replacement Date
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="date" name="replaced_at" id ="replaced_at" class="form-control" value="form-control" value="{{ now()->format('Y-m-d') }}">
    
                            </div>


                            {{-- Remarks --}}
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" class="form-control" rows="3" placeholder="Enter historical replacement remarks">{{ old('remarks') }}</textarea>
                            </div>

                        </div>


                        <div class="text-end">

                            <a href="{{ route('asset-replacement.old-entry') }}" class="btn btn-secondary">                           
                                Cancel
                            </a>

                            <button type="submit" class="btn btn-success" id="saveOldReplacementBtn">
                                <i class="bi bi-save"></i>
                                Save Replacement

                            </button>

                        </div>

                    </form>

                </div>
            </div>

        @endif
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function () {

        $('#search_asset_model').select2({
            placeholder: 'Select Asset Model',
            allowClear: true,
            width: '100%'
        });

    });

    
    $(document).ready(function () {

        // Success message
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: @json(session('success')),
                confirmButtonText: 'OK'
            });
        @endif

        // Validation error messages
        @if($errors->any())
            const errors = @json($errors->all());

            Swal.fire({
                icon: 'error',
                title: 'Validation Failed',
                text: errors.join('\n'),
                confirmButtonText: 'OK'
            });
        @endif

        @if($selectedAsset)
            const targetCard = document.getElementById('historical-replacement-card');
            if (targetCard) {
                targetCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        @endif

        $('#old_asset_model_id').select2({
            placeholder: 'Select Old Asset Model',
            allowClear: true,
            width: '100%'
        });
        
        $('#saveOldReplacementBtn').on('click', function (e) {

            e.preventDefault();

            const form = $(this).closest('form');

            Swal.fire({
                title: 'Confirm Historical Replacement?',
                text: 'Are you sure you want to save this replacement entry?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Save',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#198754'
            }).then(function (result) {

                if (result.isConfirmed) {
                    form[0].submit();
                }

            });

        });

    });

</script>
@endpush
@endsection
