<div class="btn-group" id="filterYear">
  <button class="btn btn-primary dropdown-toggle filter-btn" type="button" id="defaultDropdown" data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false">
    <i class="la la-filter"></i>
  </button>
  <ul class="dropdown-menu" aria-labelledby="defaultDropdown">
    @php
        $get_all_year = \App\Http\Helpers\CustomHelper::getYearOptions();
    @endphp
    <li><a class="dropdown-item active" href="javascript:void(0)" data-value="all">{{ trans('backpack::crud.filter.all_year') }}</a></li>
    @foreach ($get_all_year as $year)
        <li><a class="dropdown-item" href="javascript:void(0)" data-value="{{ $year }}">{{ $year }}</a></li>
    @endforeach
  </ul>
</div>

@push('after_scripts')
    <script>
        if(window.filter_tables == undefined){
            window.filter_tables = {};
        }
        if(typeof filterSelectYear != 'function'){
            function filterSelectYear(){
                $('#filterYear .dropdown-menu li').each(function(){
                    $(this).children().click(function(e){
                        e.preventDefault();
                        $('#filterYear .dropdown-menu li a').removeClass('active');
                        let value = $(this).data('value');
                        $(this).addClass('active');
                        // var route = "{!! url($crud->route.'/search') !!}";
                        // route += "?filter_year="+value;
                        // crud.table.ajax.url(route).load();
                        forEachFlexible(SIAOPS.getAllAttributes(), function(key, item){
                            if(key.includes("crudTable")){
                                var url_route = item.route;
                                url_route += "&filter_year="+value;
                                item.table.ajax.url(url_route).load();
                                window.filter_tables.filter_year = value;
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
        filterSelectYear();
    </script>
@endpush
