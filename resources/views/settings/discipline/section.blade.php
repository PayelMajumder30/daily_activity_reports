@extends('layouts.app')

@section('title', 'Department-Section')

@section('content')

<div class="container-fluid py-4">

    <div class="row mb-4">

        <div class="col-md-8">
            <h2 class="fw-bold">
                <i class="bi bi-list-ul"></i>
                {{ ucwords($department->name) }} Sections
            </h2>

            <p class="text-muted">
                Manage sections under {{ ucwords($department->name) }}.
            </p>
        </div>

    </div>


    <div class="row">

        {{-- Section List --}}

        <div class="col-lg-8 mb-4">

            <div class="card shadow border-0">

                <div class="card-header d-flex justify-content-between align-items-center">

                    <h5 class="mb-0">
                        Section List ({{ucwords($department->name)}})
                    </h5>
                    <a href="{{ route('discipline.index') }}" class="btn btn-secondary">                                  
                        Back
                    </a>
                </div>

                <div class="card-body">

                    <table class="table table-bordered table-hover mb-0" id="sectionTable">
                        <thead class="table-dark">
                            <tr>
                                <th width="70">SL</th>
                                <th>Section Name</th>
                                <th>Status</th>
                                <th width="100">Action</th>
                            </tr>

                        </thead>

                        <tbody>

                            @foreach($sections as $section)
                                <tr>
                                    <td>
                                        {{ $loop->iteration }}
                                    </td>

                                    <td>
                                        {{ ucwords($section->section_name) }}
                                    </td>

                                    <td class="text-center">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input section-status" type="checkbox" data-id="{{ encryptId($section->id) }}" {{ $section->status ? 'checked' : '' }}>
                                        </div>
                                    </td>
                                    <td> 
                                        <button type="button" class="btn btn-warning btn-sm editSection" data-id="{{ encryptId($section->id) }}">
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


       
        {{-- Add / Update Section --}}
        <div class="col-lg-4">

            <div class="card shadow border-0">

                <div class="card-header">
                    <h5 class="mb-0" id="sectionFormTitle">
                        Add Section
                    </h5>
                </div>

                <div class="card-body">

                    <form id="sectionForm" action="{{ route('discipline.sections.store', encryptId($department->id)) }}" method="POST">

                        @csrf
                        <div id="sectionMethodField"></div>
                        {{-- Section Name --}}

                        <div class="mb-3">

                            <label class="form-label">
                                Section Name
                            </label>
 
                            <input type="text" name="section_name" id="section_name" class="form-control" placeholder="Enter section name" value="{{ old('section_name') }}" required>
                            @error('section_name')

                                <small class="text-danger">
                                    {{ $message }}
                                </small>

                            @enderror

                        </div>


                        <button type="submit" class="btn btn-primary" id="sectionSubmitBtn">
                            <i class="bi bi-check-circle"></i>
                            Save Section
                        </button>


                        <button type="button" class="btn btn-secondary d-none" id="sectionCancelBtn">
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
            text: @json(session('success')),
            timer: 2000,
            showConfirmButton: false
        });
    </script>
@endif

<script>
     $(document).ready(function () {
            // ==========================
            // DataTable
            // ==========================

            $('#sectionTable').DataTable({
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, 'All']
                ],
                ordering: true,
                searching: false,
                responsive: true,
                language: {
                    emptyTable: "No Section Found"
                }
            });

            function clearFormErrors() {
                $('.text-danger').text('');
                $('.form-control').removeClass('is-invalid');
            }
            // ==========================
            // Route URLs
            // ==========================

            const editUrl = "{{ route('discipline.sections.edit', ':id') }}";               
            const updateUrl = "{{ route('discipline.sections.update', ':id') }}";               
            const storeUrl =
                "{{ route(
                    'discipline.sections.store',
                    encryptId($department->id)
                ) }}";

            // ==========================
            // Edit Section
            // ==========================

            $(document).on('click','.editSection', function () {

                clearFormErrors();
                    let id = $(this).data('id');
                    $.ajax({
                        url: editUrl.replace(':id', id),
                        type: 'GET',
                        success: function (res) {

                            // Change title
                            $('#sectionFormTitle').text('Update Section');
                                
                            // Fill section name
                            $('#section_name').val(res.section_name);

                            // Change button
                            $('#sectionSubmitBtn').removeClass('btn-primary').addClass('btn-warning')
                                .html(
                                    '<i class="bi bi-pencil-square"></i> ' +
                                    'Update Section'
                                );

                            // Show cancel
                            $('#sectionCancelBtn').removeClass('d-none');
                                
                            // Change form action
                            $('#sectionForm')
                                .attr(
                                    'action',
                                    updateUrl.replace(':id', id)
                                );

                            // Add PUT method
                            $('#sectionMethodField').html('@method("PUT")');                                
                        },

                        error: function () {

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Unable to load section.'
                            });
                        }
                    });
                }
            );


            // ==========================
            // Cancel Edit
            // ==========================

            $('#sectionCancelBtn').on('click',               
                function () {

                    clearFormErrors();
                    $('#sectionForm').attr('action', storeUrl);
                    $('#sectionMethodField').html('');
                    $('#section_name').val('');
                    $('#sectionFormTitle').text('Add Section');
                        
                    $('#sectionSubmitBtn').removeClass('btn-warning').addClass('btn-primary')
                        .html(
                            '<i class="bi bi-check-circle"></i> ' +
                            'Save Section'
                        );

                    $(this).addClass('d-none');                        

                }
            );


            // ==========================
            // Section Status
            // ==========================

            $(document).on('change', '.section-status',
                               
                function () {
                    let checkbox = $(this);
                    let id = checkbox.data('id');

                    $.ajax({

                        url:
                            "{{ route(
                                'discipline.sections.status',
                                ':id'
                            ) }}".replace(':id', id),
                            
                        type: 'POST',

                        data: {

                            _token:
                                "{{ csrf_token() }}"

                        },

                        success: function (res) {

                            Swal.fire({

                                icon: 'success',

                                title: 'Updated',

                                text: res.status
                                    ? 'Section Activated'
                                    : 'Section Deactivated',

                                timer: 1200,

                                showConfirmButton: false

                            });

                        },

                        error: function () {

                            // Revert checkbox
                            checkbox.prop(
                                'checked',
                                !checkbox.prop('checked')
                            );

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Unable to update section status.'                                   
                            });

                        }
                    });
                }
            );
        });

</script>
@endpush
@endsection