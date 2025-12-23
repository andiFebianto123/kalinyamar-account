{{-- @if ($crud->hasAccess('create'))
    <a href="{{ url($crud->route.'/create') }}" class="btn btn-primary" bp-button="create" data-style="zoom-in">
        <i class="la la-plus"></i> <span>{{ trans('backpack::crud.add') }} {{ $crud->entity_name }}</span>
    </a>
@endif --}}

@if ($crud->hasAccess('create'))
    <a href="javascript:void(0)" id="btn-open-create" data-bs-toggle="modal" data-bs-target="#modalCreate" class="btn btn-primary" bp-button="create" data-style="zoom-in">
        <i class="la la-plus"></i>
    </a>
@endif

@push('after_scripts')
    <script>
        $('#btn-open-create').unbind('click').on('click', function (e) {
            e.preventDefault();
            var route = "{{ url($crud->route.'/create') }}";
            OpenCreateFormModal({
                route: route,
                modal: {
                    id: '#modalCreate',
                }
            });
            // var url = "{{ url($crud->route.'/create') }}";
            // $('#modalCreate .modal-body').html('loading...');
            // $.ajax({
            //     url: url,
            //     type: 'GET',
            //     typeData: 'json',
            //     success: function (data) {
            //         $('#modalCreate .modal-body').html(data.html);
            //     },
            //     error: function (xhr, status, error) {
            //         console.error(xhr);
            //         alert('An error occurred while loading the create form.');
            //     }
            // });
        });
    </script>
@endpush
