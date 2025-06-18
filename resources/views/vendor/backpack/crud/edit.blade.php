@push('after_scripts') @if (request()->ajax()) @endpush @endif
<div class="row" bp-section="crud-operation-update">
	<div class="{{ $crud->getEditContentClass() }}">
		{{-- Default box --}}

		@include('crud::inc.grouped_errors')

		  <form method="post"
                id="form-edit"
		  		action="{{ url($crud->route.'/'.$entry->getKey()) }}"
				enctype="multipart/form-data"
		  		>
		  {!! csrf_field() !!}
		  {!! method_field('PUT') !!}

		  	@includeWhen($crud->model->translationEnabled(), 'crud::inc.edit_translation_notice')

			{{-- load the view from the application if it exists, otherwise load the one in the package --}}
			@if(view()->exists('vendor.backpack.crud.form_content'))
				@include('vendor.backpack.crud.form_content', ['fields' => $crud->fields(), 'action' => 'edit'])
			@else
				@include('crud::form_content', ['fields' => $crud->fields(), 'action' => 'edit'])
			@endif
			{{-- This makes sure that all field assets are loaded. --}}
			<div class="d-none" id="parentLoadedAssets">{{ json_encode(Basset::loaded()) }}</div>
			{{-- @include('crud::inc.form_save_buttons') --}}
		  </form>
	</div>
</div>
<script>
    $('#btn-submit-edit').unbind('click').on('click', function (e) {
        e.preventDefault();
        var url = $('#form-edit').attr('action');
        var formData = new FormData($('#modalEdit form')[0]);
        normalizeShowMessage('form-edit');
        btnLoader('btn-submit-edit', false);
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            typeData: 'json',
            success: function (data) {
                btnLoader('btn-submit-edit');
                if(data.success){
                    swal({
                        title: "Success",
                        text: "{!! trans('backpack::crud.update_success') !!}",
                        icon: "success",
                        timer: 4000,
                        buttons: false,
                    });
                    $('#modalEdit').modal('hide');
                    hideModal('modalEdit');
                    if(window.crud.table){
                        window.crud.table.ajax.reload();
                    }
                    if(data.events){
                        forEachFlexible(data.events, function(eventname, data){
                            eventEmitter.emit(eventname, data);
                        });
                    }
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
                btnLoader('btn-submit-edit');
                errorShowMessage('form-edit', xhr.responseJSON.errors);
            }
        });
    });
</script>
@if (!request()->ajax()) @endpush @endif


