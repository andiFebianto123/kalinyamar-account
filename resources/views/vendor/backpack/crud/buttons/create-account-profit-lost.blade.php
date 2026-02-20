@if ($crud->hasAccess('create'))
    <div class="btn-group" id="createOptionProfitLost">
    <button class="btn btn-primary dropdown-toggle filter-btn" type="button" id="defaultDropdown" data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false">
        <i class="la la-plus"></i>
    </button>
    <ul class="dropdown-menu" aria-labelledby="defaultDropdown">
        <li>
            <a class="dropdown-item"
                id="choose-consolidation"
                href="javascript:void(0)"
                data-bs-toggle="modal"
                data-bs-target="#modalCreate"
                data-title="{{trans('backpack::crud.profit_lost.title_modal_create_consolidation')}}"
                data-value="konsolidasi">{{ trans('backpack::crud.profit_lost.choose_create.consolidation_account') }}</a></li>
        <li>
            <a class="dropdown-item"
                id="choose-project"
                href="javascript:void(0)"
                data-bs-toggle="modal"
                data-bs-target="#modalCreate"
                data-value="proyek">{{ trans('backpack::crud.profit_lost.choose_create.project_account') }}</a></li>
    </ul>
    </div>

@push('after_scripts')
    <script>
        $('#choose-consolidation').click(function(e){
            var elem = $(this);
            e.preventDefault();
            var route = "{{ url($crud->route.'/create') }}";
            OpenCreateFormModal({
                route: route,
                modal: {
                    id: '#modalCreate',
                    title: elem.data('title'),
                    // action: route
                }
            });
        })
        $('#choose-project').click(function(e){
            e.preventDefault();
            var route = "{{ url($crud->route.'/create') }}?type=project";
            var storeRoute = "{{ url($crud->route.'/store-project') }}?type=project";
            OpenCreateFormModal({
                route: route,
                modal: {
                    id: '#modalCreate',
                    title: "{{ trans('backpack::crud.profit_lost.title_modal_create_project') }}",
                    action: storeRoute
                }
            });
        });
    </script>
@endpush
@endif



