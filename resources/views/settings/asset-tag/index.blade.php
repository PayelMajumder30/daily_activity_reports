@extends('layouts.app')

@section('title', 'Asset Tag')

@section('content')

<div class="container-fluid py-4">

    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold">
                <i class="bi bi-upc-scan"></i>
                Asset Tag
            </h2>
            <p class="text-muted">
                Manage all Asset tags. 
            </p>
        </div>

        <div class="col-md-4 text-end">
            <h6 class="text-secondary">
                {{ now()->format('d M Y') }}
            </h6>
        </div>
    </div>

    <div class="row">

        <!-- Asset tag List -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header">
                    <h5 class="mb-0">Asset tag List</h5>
                </div>

                <div class="card-body">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th width="70">SL</th>
                                <th>Tag Number</th>
                                <th>Status</th>
                                <th width="120">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($assetTags as $assetTag)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>{{ ucwords($assetTag->tag_no) }}</td>

                                <td class="text-center">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input assetTag-status" type="checkbox"                                       
                                            data-id="{{ encryptId($assetTag->id) }}" {{ $assetTag->status ? 'checked' : '' }}>                                       
                                    </div>
                                </td>

                                <td>
                                    <button type="button" class="btn btn-warning btn-sm editAssetTag" data-id="{{ encryptId($assetTag->id) }}">     
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">
                                    No Asset Tag Found
                                </td>
                            </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>
            </div>
        </div>

        <!-- Add / Update -->
        <div class="col-lg-4">

            <div class="card shadow border-0">

                <div class="card-header">
                    <h5 id="formTitle" class="mb-0">
                        Add Asset Tag
                    </h5>
                </div>

                <div class="card-body">

                    <form id="assetTagForm" action="{{ route('asset-tag.store') }}" method="POST">
                        @csrf

                        <div id="methodField"></div>

                        <div class="mb-3">

                            <label class="form-label">
                                Asset tag number
                            </label>

                            <input type="text" class="form-control @error('tag_no') is-invalid @enderror" name="tag_no" id="tag_no">
                            @error('tag_no')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <button class="btn btn-primary" id="submitBtn">
                            <i class="bi bi-check-circle"></i>
                            Save Asset tag
                        </button>

                        <button type="button" class="btn btn-secondary d-none" id="cancelBtn">      
                            Cancel
                        </button>
                    </form>
                </div>
            </div>
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
        </script>
    @endif

    <script>
        $(document).ready(function () {

            function clearFormErrors() {
                $('.text-danger').text('');
                $('.form-control').removeClass('is-invalid');
            }
            // Route templates
            const editUrl = "{{ route('asset-tag.edit', ':id') }}";
            const updateUrl = "{{ route('asset-tag.update', ':id') }}";
            const storeUrl = "{{ route('asset-tag.store') }}";

            $(document).on('click', '.editAssetTag', function () {
                clearFormErrors();
                let id = $(this).data('id');
                $.ajax({
                    url: editUrl.replace(':id', id),
                    type: 'GET',

                    success: function (res) {

                        $('#formTitle').text('Update Asset Tag');

                        $('#tag_no').val(res.tag_no);

                        $('#submitBtn')
                            .removeClass('btn-primary')
                            .addClass('btn-warning')
                            .html('<i class="bi bi-pencil-square"></i> Update Asset Tag');

                        $('#cancelBtn').removeClass('d-none');

                        $('#assetTagForm')
                            .attr('action', updateUrl.replace(':id', id));

                        $('#methodField').html('@method("PUT")');
                    }
                });
            });

            $('#cancelBtn').on('click', function () {
                clearFormErrors();
                $('#assetTagForm').attr('action', storeUrl);
                $('#methodField').html('');
                $('#tag_no').val('');
                $('#formTitle').text('Add Asset tag');

                $('#submitBtn')
                    .removeClass('btn-warning')
                    .addClass('btn-primary')
                    .html('<i class="bi bi-check-circle"></i> Save Asset Tag');

                $(this).addClass('d-none');

            });

        });


        $(document).on('change', '.assetTag-status', function () {
            let checkbox = $(this);
            $.ajax({

                url: "{{ route('asset-tag.changeStatus', ':id') }}"
                        .replace(':id', checkbox.data('id')),

                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}"
                },

                success: function (res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated',
                        text: res.status
                                ? 'Asset Tag Activated'
                                : 'Asset Tag Deactivated',
                        timer: 1200,
                        showConfirmButton: false
                    });

                },

                error: function () {
                    checkbox.prop('checked', !checkbox.prop('checked'));

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Unable to update status.'
                    });

                }
            });
        });
    </script>
    @endpush
@endsection