@if ($crud->hasAccess('update', $entry))
	@if (!$crud->model->translationEnabled())

	{{-- Single edit button --}}
	<a href="javascript:void(0)"
    onclick="editEntry({{ $entry->getKey() }})"
    data-bs-toggle="modal"
    data-bs-target="#modalEdit"
    bp-button="update" class="btn btn-sm btn-link">
		<i class="la la-edit"></i> <span>{{ trans('backpack::crud.edit') }}</span>
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
        function editEntry(id){
            var url = "{{ url($crud->route) }}"+"/"+id+"/edit";
            $('#modalEdit .modal-body').html('loading...');
            $.ajax({
                url: url,
                type: 'GET',
                typeData: 'json',
                success: function (data) {
                    $('#modalEdit .modal-body').html(data.html);
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
