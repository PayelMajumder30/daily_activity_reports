@extends('layouts.app')

@section('title', 'Create Asset Retention')

@section('content')

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold">
                <i class="bi bi-box-arrow-right"></i>
                Retain Asset
            </h2>
            <p class="text-muted">Retained an available asset to a custodian</p>
        </div>
        <div class="col-md-4 text-end">
            <h6 class="text-secondary">{{ now()->format('d M Y') }}</h6>
        </div>
    </div>

    <div class="card shadow border-0">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-person-workspace"></i> Retain Asset
            </h5>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('asset-retained.store') }}" id="assetIssueForm">
                @csrf

                {{-- CUSTODIAN & USER DETAILS --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Custodian <span class="text-danger">*</span></label>
                        <select name="custodian_id" id="custodian_id" class="form-select @error('custodian_id') is-invalid @enderror">
                            <option value="">Select Custodian</option>
                            @foreach($custodians as $custodian)
                                <option 
                                    value="{{ $custodian->id }}" 
                                    data-emp-id="{{ $custodian->emp_id }}"
                                    data-designation="{{ $custodian->designation->name ?? '' }}"
                                    data-department="{{ $custodian->discipline->name ?? '' }}"
                                    data-section="{{ $custodian->section->name ?? '' }}"
                                    {{ old('custodian_id') == $custodian->id ? 'selected' : '' }}>
                                    {{ ucwords($custodian->custodian_name) }} - {{ $custodian->emp_id }}
                                </option>
                            @endforeach
                        </select>
                        @error('custodian_id')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Employee ID</label>
                        <input type="text" id="custodian_emp_id" class="form-control" readonly>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Designation</label>
                        <input type="text" id="custodian_designation" class="form-control" readonly>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Department</label>
                        <input type="text" id="custodian_department" class="form-control" readonly>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Section</label>
                        <input type="text" id="custodian_section" class="form-control" readonly>
                    </div>

                    {{-- FIXED: USER TYPE FIELD --}}
                    <!-- <div class="col-md-3 mb-3">
                        <label class="form-label">User Type <span class="text-danger">*</span></label>
                        <div class="pt-2">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="user_type" id="user_self" value="self" {{ old('user_type', 'self') == 'self' ? 'checked' : '' }}>
                                <label class="form-check-label" for="user_self">Self</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="user_type" id="user_multi" value="multiuser" {{ old('user_type') == 'multiuser' ? 'checked' : '' }}>
                                <label class="form-check-label" for="user_multi">Multi-User</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="user_type" id="user_operator" value="operator" {{ old('user_type') == 'operator' ? 'checked' : '' }}>
                                <label class="form-check-label" for="user_operator">Operator</label>
                            </div>
                        </div>
                        @error('user_type')
                            <small class="text-danger d-block">{{ $message }}</small>
                        @enderror
                    </div> -->

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Retained Date <span class="text-danger">*</span></label>
                        <input type="date" name="retained_date" value="{{ old('retained_date', now()->format('Y-m-d')) }}" 
                            class="form-control @error('retained_date') is-invalid @enderror">
                        @error('retained_date')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- <div class="col-md-12 mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="Optional remarks">{{ old('remarks') }}</textarea>
                    </div> -->
                </div>

                {{-- SELECTED ASSETS TABLE --}}
                <div class="card border mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Selected Assets</h6>
                        <button type="button" class="btn btn-primary btn-sm" id="addAssetBtn">
                            <i class="bi bi-plus-circle"></i> Add Asset
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="selectedAssetsTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="60">SL</th>
                                        <th>Asset Tag</th>
                                        <th>Asset Type</th>
                                        <th>Asset Model</th>
                                        <th>From Station</th>
                                        <th width="80">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="selectedAssetsBody">
                                    <tr id="noAssetRow">
                                        <td colspan="6" class="text-center text-muted">No assets selected.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        @error('assets')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary" id="issueAssetBtn">
                        <i class="bi bi-check-circle"></i> Retain Asset
                    </button>
                    <a href="{{ route('asset-issue-register.index') }}" class="btn btn-secondary ms-2">Back</a>
                </div>
            </form>

            {{-- ADD ASSETS MODAL --}}
            <div class="modal fade" id="addAssetModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Select Assets to Retain</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div id="modalAssetContainer"></div>
                            <div class="mt-3">
                                <button type="button" class="btn btn-outline-success btn-sm" id="addMoreModalRow">
                                    <i class="bi bi-plus-lg"></i> Add More Option
                                </button>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" id="confirmAddAsset">Add to Table</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Hidden Sources --}}
            <div style="display: none;">
                <select id="assetOptionSource">
                    <option value="">Select Asset Tag</option>
                </select>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function () {
        // 1. Initialize Custodian Select2
        $('#custodian_id').select2({
            placeholder: 'Select Custodian',
            allowClear: true,
            width: '100%'
        });

        const custodianAssetsUrl = "{{ route('asset-retained.custodian-assets', ':id') }}";

        // 2. Custodian Change Event
        $('#custodian_id').on('change', function () {
            let selectedOption = $(this).find('option:selected');
            let custodianId = $(this).val();

            $('#custodian_emp_id').val(selectedOption.attr('data-emp-id') || '');
            $('#custodian_designation').val(selectedOption.attr('data-designation') || '');
            $('#custodian_department').val(selectedOption.attr('data-department') || '');
            $('#custodian_section').val(selectedOption.attr('data-section') || '');

            let assetSource = $('#assetOptionSource');
            assetSource.html('<option value="">Select Asset Tag</option>');

            $('#selectedAssetsBody').html(`
                <tr id="noAssetRow">
                    <td colspan="6" class="text-center text-muted">No assets selected.</td>
                </tr>
            `);

            if (!custodianId) return;

            $.ajax({
                url: custodianAssetsUrl.replace(':id', custodianId),
                type: 'GET',
                success: function (response) {
                    if (!response || response.length === 0) {
                        Swal.fire('Notice', 'No available assets found in inventory.', 'info');
                        return;
                    }

                    response.forEach(function (asset) {
                        assetSource.append(`
                            <option 
                                value="${asset.id}" 
                                data-type="${asset.asset_type}" 
                                data-model="${asset.asset_model}" 
                                data-station-id="${asset.from_station_id}" 
                                data-station-name="${asset.from_station}">
                                ${asset.tag_no}
                            </option>
                        `);
                    });
                },
                error: function () {
                    Swal.fire('Error', 'Unable to load available assets.', 'error');
                }
            });
        });

        // 3. Open Modal Action
        $('#addAssetBtn').on('click', function () {
            if (!$('#custodian_id').val()) {
                Swal.fire('Warning', 'Please select a custodian first.', 'warning');
                return;
            }

            $('#modalAssetContainer').empty();
            addAssetRowToModal();
            $('#addAssetModal').modal('show');
        });

        // Helper: Get all currently selected assets (Main Table + Modal)
        function getSelectedAssetIds() {
            let ids = [];
            $('input[name*="[asset_inventory_id]"]').each(function() {
                if ($(this).val()) ids.push($(this).val());
            });
            $('.asset-select').each(function() {
                if ($(this).val()) ids.push($(this).val());
            });
            return ids;
        }

        // 4. Add Dynamic Row Inside Modal with Filtering
        function addAssetRowToModal() {
            let selectedIds = getSelectedAssetIds();
            let $sourceOptions = $('#assetOptionSource option').clone();

            // Filter out already selected options
            $sourceOptions.each(function() {
                let val = $(this).val();
                if (val && selectedIds.includes(val)) {
                    $(this).remove();
                }
            });

            if ($sourceOptions.length <= 1) {
                Swal.fire('Notice', 'All available assets have already been added.', 'info');
                return;
            }

            let row = $(`
                <div class="row asset-row g-3 mb-3 pb-3 border-bottom align-items-end">
                    <div class="col-md-10">
                        <label class="form-label">Asset Tag <span class="text-danger">*</span></label>
                        <select class="form-select asset-select"></select>
                    </div>
                    <div class="col-md-2 text-end">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-modal-row">
                            <i class="bi bi-trash"></i> Remove
                        </button>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Asset Type</label>
                        <input type="text" class="form-control asset-type" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Asset Model</label>
                        <input type="text" class="form-control asset-model" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">From Station</label>
                        <input type="text" class="form-control from-station" readonly>
                    </div>
                </div>
            `);

            row.find('.asset-select').append($sourceOptions);
            $('#modalAssetContainer').append(row);

            row.find('.asset-select').select2({
                dropdownParent: $('#addAssetModal'),
                width: '100%'
            });
        }

        $('#addMoreModalRow').on('click', function () {
            addAssetRowToModal();
        });

        $(document).on('click', '.remove-modal-row', function () {
            if ($('#modalAssetContainer .asset-row').length > 1) {
                $(this).closest('.asset-row').remove();
            } else {
                Swal.fire('Warning', 'At least one asset row must remain.', 'warning');
            }
        });

        // 5. Populate Details when Asset Tag is Chosen
        $(document).on('change', '.asset-select', function () {
            let container = $(this).closest('.asset-row');
            let selectedOption = $(this).find('option:selected');
            let assetId = $(this).val();

            if (!assetId) {
                container.find('.asset-type, .asset-model, .from-station').val('');
                return;
            }

            container.find('.asset-type').val(selectedOption.attr('data-type') || 'N/A');
            container.find('.asset-model').val(selectedOption.attr('data-model') || 'N/A');
            container.find('.from-station').val(selectedOption.attr('data-station-name') || 'N/A');
        });

        // 6. Transfer Modal Selection to Main Summary Table
        $('#confirmAddAsset').on('click', function () {
            let isValid = true;
            let selectedAssetsInModal = [];

            $('.asset-row').each(function() {
                let assetId = $(this).find('.asset-select').val();
                if (!assetId) {
                    isValid = false;
                } else {
                    selectedAssetsInModal.push(assetId);
                }
            });

            if (!isValid) {
                Swal.fire('Warning', 'Please select an Asset Tag for all modal rows.', 'warning');
                return;
            }

            $('.asset-row').each(function() {
                let assetId = $(this).find('.asset-select').val();
                let assetTag = $(this).find('.asset-select option:selected').text().trim();
                let assetType = $(this).find('.asset-type').val();
                let assetModel = $(this).find('.asset-model').val();
                let fromStation = $(this).find('.from-station').val();

                let alreadyExists = false;
                $('input[name*="[asset_inventory_id]"]').each(function() {
                    if ($(this).val() == assetId) alreadyExists = true;
                });

                if (!alreadyExists) {
                    $('#noAssetRow').remove();
                    let rowCount = $('#selectedAssetsBody tr').length;

                    let tableRow = `
                        <tr>
                            <td>${rowCount + 1}</td>
                            <td>
                                ${assetTag}
                                <input type="hidden" name="assets[${rowCount}][asset_inventory_id]" value="${assetId}">
                            </td>
                            <td>${assetType}</td>
                            <td>${assetModel}</td>
                            <td>${fromStation}</td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm remove-row-btn">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    $('#selectedAssetsBody').append(tableRow);
                }
            });

            reindexTable();
            $('#addAssetModal').modal('hide');
        });

        // 7. Remove Main Table Row & Re-index
        $(document).on('click', '.remove-row-btn', function () {
            $(this).closest('tr').remove();
            if ($('#selectedAssetsBody tr').length === 0) {
                $('#selectedAssetsBody').html(`
                    <tr id="noAssetRow">
                        <td colspan="6" class="text-center text-muted">No assets selected.</td>
                    </tr>
                `);
            } else {
                reindexTable();
            }
        });

        function reindexTable() {
            $('#selectedAssetsBody tr').each(function(index) {
                if ($(this).attr('id') !== 'noAssetRow') {
                    $(this).find('td:first').text(index + 1);
                    $(this).find('input[name*="[asset_inventory_id]"]').attr('name', `assets[${index}][asset_inventory_id]`);
                }
            });
        }
    });
</script>
@endpush

@endsection