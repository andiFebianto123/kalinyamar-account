
@push('after_scripts') @if (request()->ajax()) @endpush @endif
<div class="row" bp-section="crud-operation-create">
	<div class="{{ $crud->getCreateContentClass() }}">
		{{-- Default box --}}

		@include('crud::inc.grouped_errors')

		  <form method="post"
                id="form-create"
		  		action="{{ url($crud->route) }}"
				enctype="multipart/form-data"
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
    $('#btn-submit-create').unbind('click').on('click', async function (e) {
        e.preventDefault();
        var url = $('#form-create').attr('action');

        var lazySubmit = await new Promise((resolve, reject) => {
            setTimeout(() => {
                resolve(1)
            }, 100);
        });

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
                    // if(data.events){
                    //     data.events.forEach((listener) => {
                    //         eventEmitter.emit(listener+'_error', data.error);
                    //     });
                    // }
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
