@if ($crud->hasAccess('delete', $entry))
    <a href="javascript:void(0)"
        onclick="deleteEntry(this)"
        bp-button="delete"
        data-route="{{ url($crud->route.'/'.$entry->getKey().'?type=project') }}"
        class="btn btn-sm btn-danger"
        data-button-type="delete"
        data-bs-toggle="modal"
        data-bs-target="#modalDelete"
        data-title-delete="{{ trans('backpack::crud.delete') }} {{ trans('backpack::crud.profit_lost.project_income_statement') }}"
        data-body="{{ trans('backpack::crud.delete_confirm') }}">
        <i class="la la-trash"></i>
    </a>
@endif

{{-- Button Javascript --}}
@push('after_scripts') @if (request()->ajax()) @endpush @endif
@bassetBlock('backpack/crud/buttons/delete-button-'.app()->getLocale().'.js')
<script>
	if (typeof deleteEntry != 'function') {
	  $("[data-button-type=delete]").unbind('click');

	  function deleteEntry(button) {
		var route = $(button).attr('data-route');
        var title = $(button).attr('data-title-delete');
        var body = $(button).attr('data-body');

        $("#modalDelete #modalDeleteLabel").html(title);
        $("#modalDelete .modal-body").html(body);

        $('#btn-delete').off('click').on('click', function(e){

            var btn = $(this);
            btn.attr('disabled', true);
            btn.find('.btn-text').html("Loading...");
            btn.find('.btn-spinner').removeClass('d-none');

            $.ajax({
			    url: route,
			    type: 'DELETE',
			    success: function(result) {
                    btn.attr('disabled', false);
                    btn.find('.btn-text').html("{{ trans('backpack::crud.delete') }}");
                    btn.find('.btn-spinner').addClass('d-none');

                    hideModal('modalDelete');

                    if (result == 1) {
                        if (typeof crud != 'undefined' && typeof crud.table != 'undefined') {
                            crud.table.draw(false);
                        }

                        new Noty({
                        type: "success",
                        text: "{!! '<strong>'.trans('backpack::crud.delete_confirmation_title').'</strong><br>'.trans('backpack::crud.delete_confirmation_message') !!}"
                        }).show();

                        $('.modal').modal('hide');
                    } else {
                        if (result instanceof Object) {
                            Object.entries(result).forEach(function(entry, index) {
                                var type = entry[0];
                                if(type != 'events'){
                                    entry[1].forEach(function(message, i) {
                                        swal({
                                            title: "Success",
                                            text: message,
                                            icon: "success",
                                            timer: 4000,
                                            buttons: false,
                                        });
                                    });
                                }
                            });
                            if(result.events){
                                forEachFlexible(result.events, function(eventname, data){
                                    eventEmitter.emit(eventname, data);
                                });
                            }
                        } else {
                            swal({
                                title: "{!! trans('backpack::crud.delete_confirmation_not_title') !!}",
                                text: "{!! trans('backpack::crud.delete_confirmation_not_message') !!}",
                                icon: "error",
                                timer: 4000,
                                buttons: false,
                            });
                        }
                    }
                },
                error: function(result) {
                    btn.attr('disabled', false);
                    btn.find('.btn-text').html("{{ trans('backpack::crud.delete') }}");
                    btn.find('btn-spinner').addClass('d-none');
                    swal({
                        title: "{!! trans('backpack::crud.delete_confirmation_not_title') !!}",
                        text: "{!! trans('backpack::crud.delete_confirmation_not_message') !!}",
                        icon: "error",
                        timer: 4000,
                        buttons: false,
                    });
                }
            });
        });
      }
	}
</script>
@endBassetBlock
@if (!request()->ajax()) @endpush @endif
