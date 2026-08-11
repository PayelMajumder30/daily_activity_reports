@extends('layouts.app')

@section('title', 'Asset Model')

@section('content')

<div class="container-fluid py-4">

    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold">
                <i class="bi bi-cpu"></i>
                Asset Model
            </h2>
            <p class="text-muted">
                Manage all Asset models. 
            </p>
        </div>

        <div class="col-md-4 text-end">
            <h6 class="text-secondary">
                {{ now()->format('d M Y') }}
            </h6>
        </div>
    </div>

    <div class="row">

        <!-- Asset type List -->
        <div class="card shadow border-0">
            
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Asset Model List</h5>

                    <button class="btn btn-primary btn-sm" id="addBtn">
                        <i class="bi bi-plus-circle"></i>
                        Add Asset Model
                    </button>
                </div>

                <div class="card-body">
                    <table class="table table-bordered table-hover mb-0" id="assetMoTable">
                        <thead class="table-dark">
                            <tr>
                                <th width="70">SL</th>
                                <th>Asset type</th>
                                <th>Model Name</th>
                                <th>Manufacturer</th>
                                <th>Status</th>
                                <th width="120">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($assetModels as $model)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ ucwords($model->assetType->name) }}</td>
                                    <td>{{ $model->model_name }}</td>
                                    <td>{{ $model->manufacturer ?? 'NA' }}</td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input model-status" data-id="{{ encryptId($model->id) }}" {{ $model->status ? 'checked' : '' }}>
                                        </div>
                                    </td>
                                    <td>
                                        <button class="btn btn-warning btn-sm editModel" data-id="{{ encryptId($model->id) }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            
        </div>

        <!-- modal -->
        <div class="modal fade" id="assetModelModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">

                    <form id="assetModelForm" action="{{ route('asset-model.store') }}" method="POST">
                        @csrf

                        <div id="methodField"></div>

                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTitle">
                                Add Asset Model
                            </h5>

                            <button type="button" class="btn-close" data-bs-dismiss="modal">      
                            </button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Asset Type</label>

                                <select name="asset_type_id" id="asset_type_id" class="form-select">
                                    <option value="">Select Asset Type</option>

                                    @foreach($assetTypes as $type)
                                        <option value="{{ $type->id }}">
                                            {{ ucwords($type->name) }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-danger" id="asset_type_id_error"></small>
           
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Model Name</label>
                                <input type="text" class="form-control" id="model_name" name="model_name"> 
                                <small class="text-danger" id="model_name_error"></small> 
            
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Manufacturer
                                    <small class="text-muted">(Optional)</small>
                                </label>

                                <input type="text" class="form-control" id="manufacturer" name="manufacturer">      
                            </div>

                        </div>

                        <div class="modal-footer">

                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">       
                                Close
                            </button>

                            <button class="btn btn-primary" id="saveBtn">                                    
                                Save
                            </button>

                        </div>

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

        $(document).ready(function(){
            $('#assetMoTable').DataTable({
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, 'All']
                ],
                ordering: true,
                searching: false,
                responsive: true
            });
        });

        function clearFormErrors() {
            $('.text-danger').text('');
        }

        $(function () {
            let modal = new bootstrap.Modal(
                document.getElementById('assetModelModal')
            );

            const storeUrl = "{{ route('asset-model.store') }}";
            const editUrl = "{{ route('asset-model.edit', ':id') }}";
            const updateUrl = "{{ route('asset-model.update', ':id') }}";

            // add button
            $('#addBtn').click(function () {
                clearFormErrors();
                $('#modalTitle').text('Add Asset Model');
                $('#assetModelForm').attr('action', storeUrl);
                $('#methodField').html('');
                $('#asset_type_id').val('');
                $('#model_name').val('');
                $('#manufacturer').val('');
                $('#saveBtn')
                    .removeClass('btn-warning')
                    .addClass('btn-primary')
                    .text('Save');

                modal.show();

            });

            // edit button
            $(document).on('click','.editModel',function(){

                clearFormErrors();
                let id=$(this).data('id');
                $.get(editUrl.replace(':id',id),function(res){

                    $('#modalTitle').text('Update Asset Model');

                    $('#asset_type_id').val(res.asset_type_id);

                    $('#model_name').val(res.model_name);

                    $('#manufacturer').val(res.manufacturer);

                    $('#assetModelForm')
                        .attr('action',
                            updateUrl.replace(':id',id));

                    $('#methodField')
                        .html('@method("PUT")');

                    $('#saveBtn')
                        .removeClass('btn-primary')
                        .addClass('btn-warning')
                        .text('Update');

                    modal.show();

                });

            });

            // submit modal
            $('#assetModelForm').submit(function(e){

                e.preventDefault();
                $('.text-danger').text('');
                let form=$(this);
                $.ajax({
                    url:form.attr('action'),
                    type:form.find('input[name="_method"]').length
                        ? 'POST'
                        : 'POST',

                    data:form.serialize(),
                    success:function(res){
                        modal.hide();
                        Swal.fire({
                            icon:'success',
                            title:'Success',
                            text:res.message,
                            timer:1500,
                            showConfirmButton:false

                        }).then(function(){

                            location.reload();

                        });
                    },

                    error:function(xhr){
                        if(xhr.status==422){
                            $.each(xhr.responseJSON.errors,function(key,value){

                                $('#'+key+'_error').text(value[0]);

                            });
                        }
                    }
                });
            });
        });


        $(document).on('change','.model-status',function(){
            let checkbox=$(this);
            $.ajax({
                url:"{{ route('asset-model.changeStatus',':id') }}"
                        .replace(':id',checkbox.data('id')),

                type:"POST",
                data:{
                    _token:"{{ csrf_token() }}"
                },

                success:function(res){
                    Swal.fire({
                        icon:'success',
                        title:'Updated',
                        text:res.status
                            ? 'Asset Model Activated'
                            : 'Asset Model Deactivated',

                        timer:1200,
                        showConfirmButton:false

                    });
                },

                error:function(){

                    checkbox.prop(
                        'checked',
                        !checkbox.prop('checked')
                    );

                }

            });

        });
    </script>
    @endpush
@endsection