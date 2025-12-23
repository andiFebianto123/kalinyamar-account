@if ($crud->hasAccess('show', $entry))
	@if (!$crud->model->translationEnabled())

	{{-- Single edit button --}}
	{{-- <a href="{{ url($crud->route.'/'.$entry->getKey().'/show') }}" bp-button="show" class="btn btn-sm btn-link">
		<i class="la la-eye"></i> <span>{{ trans('backpack::crud.preview') }}</span>
	</a> --}}
    <a href="javascript:void(0)"
        onclick="showEntry({{ $entry->getKey() }})"
        bp-button="show"
        data-bs-toggle="modal"
        data-bs-target="#modalShow"
        class="btn btn-sm btn-dark">
		<i class="la la-eye"></i>
	</a>

	@else

	{{-- show button group --}}
	<div class="btn-group">
	  <a href="{{ url($crud->route.'/'.$entry->getKey().'/show') }}" class="btn btn-sm btn-link pr-0">
	  	<span><i class="la la-eye"></i> {{ trans('backpack::crud.preview') }}</span>
	  </a>
	  <a class="btn btn-sm btn-link dropdown-toggle text-primary pl-1" data-toggle="dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	    <span class="caret"></span>
	  </a>
	  <ul class="dropdown-menu dropdown-menu-right">
  	    <li class="dropdown-header">{{ trans('backpack::crud.preview') }}:</li>
	  	@foreach ($crud->model->getAvailableLocales() as $key => $locale)
		  	<a class="dropdown-item" href="{{ url($crud->route.'/'.$entry->getKey().'/show') }}?_locale={{ $key }}">{{ $locale }}</a>
	  	@endforeach
	  </ul>
	</div>

	@endif
@endif

@push('after_scripts') @if (request()->ajax()) @endpush @endif
<script>
	if (typeof showEntry != 'function') {
        function showEntry(id){
            var url = "{{ url($crud->route) }}"+"/"+id+"/show";
            $('#modalShow .modal-body').html('loading...');
            $.ajax({
                url: url,
                type: 'GET',
                typeData: 'json',
                success: function (data) {
                    $('#modalShow .modal-body').html(data.html);
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
