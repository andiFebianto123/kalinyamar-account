<div class="btn-group" id="filterStatusProject">
  <button class="btn btn-primary dropdown-toggle filter-btn" type="button" id="statusProjectDropdown" data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false">
    <i class="la la-filter"></i>- {{ trans('backpack::crud.project.column.project.status_po.label') }}
  </button>
  <ul class="dropdown-menu" aria-labelledby="statusProjectDropdown">
    @php
        $get_all_status = \App\Http\Helpers\CustomHelper::getOptionStatusProject();
    @endphp
    <li><a class="dropdown-item active" href="javascript:void(0)" data-value="all">{{ trans('backpack::crud.filter.all_status_project') }}</a></li>
    @foreach ($get_all_status as $status)
        <li><a class="dropdown-item" href="javascript:void(0)" data-value="{{ $status['name'] }}">{{ $status['name'] }}</a></li>
    @endforeach
  </ul>
</div>

@push('after_scripts')
    <script>
        if(window.filter_tables == undefined){
            window.filter_tables = {};
        }
        if(typeof filterSelectStatusProject != 'function'){
            function filterSelectStatusProject(){
                $('#filterStatusProject .dropdown-menu li').each(function(){
                    $(this).children().click(function(e){
                        e.preventDefault();
                        $('#filterStatusProject .dropdown-menu li a').removeClass('active');
                        let value = $(this).data('value');
                        $(this).addClass('active');

                        window.filter_tables.filter_status_project = value;

                        forEachFlexible(SIAOPS.getAllAttributes(), function(key, item){
                            if(key.includes("crudTable")){
                                var url_route = item.table.ajax.url();

                                // Helper to update query string
                                var url = new URL(url_route.startsWith('http') ? url_route : window.location.origin + (url_route.startsWith('/') ? '' : '/') + url_route);
                                if (value === 'all') {
                                    url.searchParams.delete('filter_status_project');
                                } else {
                                    url.searchParams.set('filter_status_project', value);
                                }
                                
                                var final_url = url_route.startsWith('http') ? url.toString() : url.pathname + url.search;
                                item.table.ajax.url(final_url).load();
                            }
                        });

                        forEachFlexible(eventEmitter.events, function(key, data){
                            if(key.includes("crudTable-filter")){
                                eventEmitter.emit(key, true);
                            }
                        });
                    });
                });
            }
        }
        filterSelectStatusProject();
    </script>
@endpush
