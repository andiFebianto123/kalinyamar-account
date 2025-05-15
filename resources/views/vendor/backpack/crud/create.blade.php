
@push('after_scripts') @if (request()->ajax()) @endpush @endif
<div class="row" bp-section="crud-operation-create">
	<div class="{{ $crud->getCreateContentClass() }}">
		{{-- Default box --}}

		@include('crud::inc.grouped_errors')

		  <form method="post"
                id="form-create"
		  		action="{{ url($crud->route) }}"
				@if ($crud->hasUploadFields('create'))
				enctype="multipart/form-data"
				@endif
		  		>
			  {!! csrf_field() !!}
		      {{-- load the view from the application if it exists, otherwise load the one in the package --}}
		      @if(view()->exists('vendor.backpack.crud.form_content'))
		      	@include('vendor.backpack.crud.form_content', [ 'fields' => $crud->fields(), 'action' => 'create' ])
		      @else
		      	@include('crud::form_content', [ 'fields' => $crud->fields(), 'action' => 'create' ])
		      @endif
                {{-- This makes sure that all field assets are loaded. --}}
                <div class="d-none" id="parentLoadedAssets">{{ json_encode(Basset::loaded()) }}</div>
	          {{-- @include('crud::inc.form_save_buttons') --}}
		  </form>
	</div>
</div>
<script>
    $('#btn-submit-create').unbind('click').on('click', function (e) {
        e.preventDefault();
        var url = "{{ url($crud->route) }}";
        var formData = new FormData($('#modalCreate form')[0]);
        normalizeShowMessage('form-create');
        btnLoader('btn-submit-create', false);
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            typeData: 'json',
            success: function (data) {
                btnLoader('btn-submit-create');
                if(data.success){
                    swal({
                        title: "Success",
                        text: "{!! trans('backpack::crud.insert_success') !!}",
                        icon: "success",
                        timer: 4000,
                        buttons: false,
                    });
                    $('#modalCreate').modal('hide');
                    hideModal('modalCreate');
                    window.crud.table.ajax.reload();
                }else{
                    swal({
                        title: "{!! trans('backpack::crud.delete_confirmation_not_title') !!}",
                        text: data.error,
                        icon: "error",
                        timer: 4000,
                        buttons: false,
                    });
                }
                // $('#modalCreate .modal-body').html(data.html);
            },
            error: function (xhr, status, error) {
                // console.error(xhr);
                btnLoader('btn-submit-create');
                errorShowMessage('form-create', xhr.responseJSON.errors);
            }
        });
    });
</script>
@if (!request()->ajax()) @endpush @endif
