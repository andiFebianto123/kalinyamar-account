@if ($crud->hasAccess('update', $entry))
	@if (!$crud->model->translationEnabled())
        <a href="javascript:void(0)"
            onclick="editEntry(this)"
            data-route="{{ url($crud->route.'/'.$entry->getKey().'/edit') }}"
            data-route-action="{{ url($crud->route.'/'.$entry->getKey()) }}"
            data-bs-toggle="modal"
            data-bs-target="#modalEdit"
            data-title-edit="{{ trans('backpack::crud.project_status.title_model_edit_close') }}"
            bp-button="update" class="btn btn-sm btn-primary">
                <i class="la la-pen"></i>
        </a>
	@else

	{{-- Edit button group --}}
	<div class="btn-group">
	  <a href="{{ url($crud->route.'/'.$entry->getKey().'/edit') }}" class="btn btn-sm btn-link pr-0">
	    <span><i class="la la-edit"></i> {{ trans('backpack::crud.edit') }}</span>
	  </a>
	  <a class="btn btn-sm btn-link dropdown-toggle text-primary pl-1" data-toggle="dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	    <span class="caret"></span>
	  </a>
	  <ul class="dropdown-menu dropdown-menu-right">
  	    <li class="dropdown-header">{{ trans('backpack::crud.edit_translations') }}:</li>
	  	@foreach ($crud->model->getAvailableLocales() as $key => $locale)
		  	<a class="dropdown-item" href="{{ url($crud->route.'/'.$entry->getKey().'/edit') }}?_locale={{ $key }}">{{ $locale }}</a>
	  	@endforeach
	  </ul>
	</div>

	@endif
@endif

@push('after_scripts') @if (request()->ajax()) @endpush @endif
<script>
	if (typeof editEntry != 'function') {
        function editEntry(button){
            var route = $(button).attr('data-route');
            var title = $(button).attr('data-title-edit');
            var action = $(button).attr('data-route-action');

            $('#modalEdit .modal-body').html('loading...');
            $('#modalEdit #modalTitleCentered').html(title);

            $.ajax({
                url: route,
                type: 'GET',
                typeData: 'json',
                success: function (data) {
                    $('#modalEdit .modal-body').html(data.html);
                    $('#modalEdit #form-edit').attr('action', action);
                },
                error: function (xhr, status, error) {
                    console.error(xhr);
                    alert('An error occurred while loading the create form.');
                }
            });
        }
    }
</script>
@if (!request()->ajax()) @endpush @endif
