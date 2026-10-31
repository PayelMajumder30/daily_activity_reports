@extends('layouts.app')

@section('title', 'Category Title')

@section('content')

<div class="container-fluid py-4">

    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold">
                <i class="bi bi-grid"></i>
                Category Title
            </h2>
            <p class="text-muted">
                Manage all Category issue titles. 
            </p>
        </div>

        <div class="col-md-4 text-end">
            <h6 class="text-secondary">
                {{ now()->format('d M Y') }}
            </h6>
        </div>
    </div>

    <div class="row">

        <!-- Category title List -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header">
                    <h5 class="mb-0">Category Title List</h5>
                </div>

                <div class="card-body">
                    <table class="table table-bordered table-hover mb-0" id="categoryTable">
                        <thead class="table-dark">
                            <tr>
                                <th width="70">SL</th>
                                <th>Title</th>
                                <th>Status</th>
                                <th width="120">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($categories as $category)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>

                                    <td>
                                        {{ ucwords($category->title) }}
                                    </td>

                                    <td class="text-center">
                                        <div class="form-check form-switch">
                                            <input
                                                class="form-check-input category-status"
                                                type="checkbox"
                                                data-id="{{ encryptId($category->id) }}"
                                                {{ $category->status ? 'checked' : '' }}>
                                        </div>
                                    </td>

                                    <td>
                                        <button type="button" class="btn btn-warning btn-sm editCategory" data-id="{{ encryptId($category->id) }}">                                  
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
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
                        Add Category Title
                    </h5>
                </div>

                <div class="card-body">

                    <form id="categoryForm" action="{{ route('issue_category.store') }}" method="POST">
                        @csrf

                        <div id="methodField"></div>

                        <div class="mb-3">
                            <label class="form-label">
                                Category Title
                            </label>

                            <input
                                type="text"
                                name="title"
                                id="title"
                                class="form-control @error('title') is-invalid @enderror"
                                value="{{ old('title') }}"
                            >

                            @error('title')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="bi bi-check-circle"></i>
                            Save Category
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
                $('#categoryTable').DataTable({
                    pageLength: 10,
                    lengthMenu: [
                        [10, 25, 50, 100, -1],
                        [10, 25, 50, 100, 'All']
                    ],
                    ordering: true,
                    searching: false,
                    responsive: true,
                    
                    language: {
                        emptyTable: "No Category Found"
                    }
                });

                function clearFormErrors() {
                    $('.text-danger').text('');
                    $('.form-control').removeClass('is-invalid');
                }
                // Route templates
                const editUrl = "{{ route('issue_category.edit', ':id') }}";
                const updateUrl = "{{ route('issue_category.update', ':id') }}";
                const storeUrl = "{{ route('issue_category.store') }}";

                $('.editCategory').on('click', function () {

                    clearFormErrors();
                    let id = $(this).data('id');

                    $.ajax({
                        url: editUrl.replace(':id', id),
                        type: 'GET',

                        success: function (res) {

                            $('#formTitle').text('Update Category');

                            $('#title').val(res.title);

                            $('#submitBtn')
                                .removeClass('btn-primary')
                                .addClass('btn-warning')
                                .text('Update Category');

                            $('#cancelBtn').removeClass('d-none');

                            $('#categoryForm').attr(
                                'action',
                                updateUrl.replace(':id', id)
                            );

                            $('#methodField').html('@method("PUT")');
                        }
                    });

                });

                $('#cancelBtn').on('click', function () {

                    clearFormErrors();
                    $('#categoryForm').attr('action', storeUrl);

                    $('#methodField').html('');

                    $('#title').val('');

                    $('#formTitle').text('Add Category');

                    $('#submitBtn')
                        .removeClass('btn-warning')
                        .addClass('btn-primary')
                        .text('Save Category');

                    $(this).addClass('d-none');

                });

            });


            $(document).on('change', '.category-status', function () {
                let checkbox = $(this);
                $.ajax({

                    url: "{{ route('issue_category.changeStatus', ':id') }}"
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
                                    ? 'Category Activated'
                                    : 'Category Deactivated',
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