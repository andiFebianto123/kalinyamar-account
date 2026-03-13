<a href="javascript:void(0)"
        bp-button="show"
        data-route="{{ url($crud->route.'/'.$entry->id.'/detail') }}"
        onclick="showDetailProject(this, event)"
        class="btn btn-sm btn-primary">
		{{ trans('backpack::crud.profit_lost.show_detail') }}
</a>
@push('after_scripts') @if (request()->ajax()) @endpush @endif
<script>
    if (typeof showDetailProject != 'function') {
        function showDetailProject(button, event) {
            var params_url = MakeParamUrl(window.filter_tables || {});
            var tab_active = (SIAOPS.getAttribute('export')) ? SIAOPS.getAttribute('export').tab_active : 'profit_lost';

            var filter_params_url = "";
            if(SIAOPS.getAttribute('SETUP_ALL_FILTER_'+tab_active)){
                filter_params_url = generateDataTableParams(SIAOPS.getAttribute('SETUP_ALL_FILTER_'+tab_active).searchValues);
            }else if(window.filterValues){
                filter_params_url = generateDataTableParams(window.filterValues || {});
            }
            event.preventDefault();
            var route = $(button).attr('data-route');
            route += "?index=1";
            route += params_url + '&'+ filter_params_url;
            window.open(route, '_blank');
        }
    }
</script>
@if (!request()->ajax()) @endpush @endif
