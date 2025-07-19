@if ($crud->hasAccess('update', $entry))
	@if (!$crud->model->translationEnabled())
        @if ($entry->type == 'category_project')
            {{-- Single edit button --}}
            <a href="javascript:void(0)"
                onclick="editEntry(this)"
                data-route="{{ url($crud->route.'/'.$entry->getKey().'/edit?_type=category_project&edit=1') }}"
                data-route-action="{{ url($crud->route.'/'.$entry->getKey().'?_type=category_project&edit=1') }}"
                data-bs-toggle="modal"
                data-bs-target="#modalEdit"
                data-title-edit="{{ trans('backpack::crud.project_system_setup.card.setup_category_project_title_edit') }}"
                bp-button="update" class="btn btn-sm btn-primary">
                    <i class="la la-pen"></i>
            </a>
        @elseif($entry->type == 'status_project')
            {{-- Single edit button --}}
            <a href="javascript:void(0)"
                onclick="editEntry(this)"
                data-route="{{ url($crud->route.'/'.$entry->getKey().'/edit?_type=status_project&edit=1') }}"
                data-route-action="{{ url($crud->route.'/'.$entry->getKey().'?_type=status_project&edit=1') }}"
                data-bs-toggle="modal"
                data-bs-target="#modalEdit"
                data-title-edit="{{ trans('backpack::crud.project_system_setup.card.setup_status_project_title_edit') }}"
                bp-button="update" class="btn btn-sm btn-primary">
                    <i class="la la-pen"></i>
            </a>
        @elseif($entry->type == 'status_offering')
            {{-- Single edit button --}}
            <a href="javascript:void(0)"
                onclick="editEntry(this)"
                data-route="{{ url($crud->route.'/'.$entry->getKey().'/edit?_type=status_offering&edit=1') }}"
                data-route-action="{{ url($crud->route.'/'.$entry->getKey().'?_type=status_offering&edit=1') }}"
                data-bs-toggle="modal"
                data-bs-target="#modalEdit"
                data-title-edit="{{ trans('backpack::crud.project_system_setup.card.setup_status_offering_title_edit') }}"
                bp-button="update" class="btn btn-sm btn-primary">
                    <i class="la la-pen"></i>
            </a>
        @elseif($entry->type == 'client')
            {{-- Single edit button --}}
            <a href="javascript:void(0)"
                onclick="editEntry(this)"
                data-route="{{ url($crud->route.'/'.$entry->getKey().'/edit?_type=client&edit=1') }}"
                data-route-action="{{ url($crud->route.'/'.$entry->getKey().'?_type=client&edit=1') }}"
                data-bs-toggle="modal"
                data-bs-target="#modalEdit"
                data-title-edit="{{ trans('backpack::crud.project_system_setup.card.setup_client_title_edit') }}"
                bp-button="update" class="btn btn-sm btn-primary">
                    <i class="la la-pen"></i>
            </a>
        @elseif($entry->type == 'ppn')
            {{-- Single edit button --}}
            <a href="javascript:void(0)"
                onclick="editEntry(this)"
                data-route="{{ url($crud->route.'/'.$entry->getKey().'/edit?_type=ppn&edit=1') }}"
                data-route-action="{{ url($crud->route.'/'.$entry->getKey().'?_type=ppn&edit=1') }}"
                data-bs-toggle="modal"
                data-bs-target="#modalEdit"
                data-title-edit="{{ trans('backpack::crud.project_system_setup.card.setup_ppn_title_edit') }}"
                bp-button="update" class="btn btn-sm btn-primary">
                    <i class="la la-pen"></i>
            </a>
        @endif
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
