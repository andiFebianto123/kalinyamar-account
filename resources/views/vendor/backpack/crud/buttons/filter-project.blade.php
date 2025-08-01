<div class="btn-group" id="filterYear">
  <button class="btn btn-primary dropdown-toggle filter-btn" type="button" id="defaultDropdown" data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false">
    <i class="la la-filter"></i>- Pilih Kategori
  </button>
  <ul class="dropdown-menu" aria-labelledby="defaultDropdown">
    @php
        $get_all_year = \App\Http\Helpers\CustomHelper::getOptionProject();
    @endphp
    <li><a class="dropdown-item active" href="javascript:void(0)" data-value="all">{{ trans('backpack::crud.filter.all_category') }}</a></li>
    @foreach ($get_all_year as $year)
        <li><a class="dropdown-item" href="javascript:void(0)" data-value="{{ $year }}">{{ $year }}</a></li>
    @endforeach
  </ul>
</div>

<div class="btn-group" id="filterClient">
  <button class="btn btn-primary dropdown-toggle filter-btn" type="button" id="defaultDropdown" data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false">
    <i class="la la-filter"></i>- Pilih Client
  </button>
  <ul class="dropdown-menu" aria-labelledby="defaultDropdown">
    @php
        $get_all_year = \App\Http\Helpers\CustomHelper::getOptionProjectClient();
    @endphp
    <li><a class="dropdown-item active" href="javascript:void(0)" data-value="all">{{ trans('backpack::crud.filter.all_client') }}</a></li>
    @foreach ($get_all_year as $year)
        <li><a class="dropdown-item" href="javascript:void(0)" data-value="{{ $year['id'] }}">{{ $year['text'] }}</a></li>
    @endforeach
  </ul>
</div>

@push('after_scripts')
    <script>
        if(typeof filterSelectYear != 'function'){
            function filterSelectYear(){
                $('#filterYear .dropdown-menu li').each(function(){
                    $(this).children().click(function(e){
                        e.preventDefault();
                        $('#filterYear .dropdown-menu li a').removeClass('active');
                        let value = $(this).data('value');
                        $(this).addClass('active');
                        var route_project = "{!! url($crud->route.'/search') !!}"+'?tab=project';
                        var route_project_edit = "{!! url($crud->route.'/search') !!}"+'?tab=project_edit';
                        var filter_client = $('#filterClient .dropdown-menu li a.active').data('value');
                        route_project += "&filter_category="+value;
                        route_project += "&filter_client="+filter_client;
                        route_project_edit += "&filter_category="+value;

                        // if(SIAOPS.getAttribute('crudTable-project') != null){
                        //     SIAOPS.getAttribute('crudTable-project').table.ajax.url(route_project).load();
                        // }
                        // if(SIAOPS.getAttribute('crudTable-project_edit') != null){
                        //     SIAOPS.getAttribute('crudTable-project_edit').table.ajax.url(route_project_edit).load();
                        // }

                        if(crud.table){
                            crud.table.ajax.url(route_project).load();
                        }

                        forEachFlexible(SIAOPS.getAllAttributes(), function(key, item){
                            if(key.includes("crudTable")){
                                var filter_category = $('#filterYear .dropdown-menu li a.active').data('value');
                                var filter_client = $('#filterClient .dropdown-menu li a.active').data('value');
                                var url_route = item.route;
                                url_route += "&filter_category="+filter_category;
                                url_route += "&filter_client="+filter_client;
                                item.table.ajax.url(url_route).load();
                            }
                        });

                    });
                });
            }
        }
        filterSelectYear();
        if(typeof filterSelectClient != 'function'){
            function filterSelectClient(){
                $('#filterClient .dropdown-menu li').each(function(){
                    $(this).children().click(function(e){
                        e.preventDefault();
                        $('#filterClient .dropdown-menu li a').removeClass('active');
                        let value = $(this).data('value');
                        $(this).addClass('active');
                        var route_project = "{!! url($crud->route.'/search') !!}"+'?tab=project';
                        var route_project_edit = "{!! url($crud->route.'/search') !!}"+'?tab=project_edit'
                        var filter_category = $('#filterYear .dropdown-menu li a.active').data('value');
                        route_project += "&filter_category="+filter_category;
                        route_project += "&filter_client="+value;
                        route_project_edit += "&filter_category="+value;

                        // if(SIAOPS.getAttribute('crudTable-project') != null){
                        //     SIAOPS.getAttribute('crudTable-project').table.ajax.url(route_project).load();
                        // }
                        // if(SIAOPS.getAttribute('crudTable-project_edit') != null){
                        //     SIAOPS.getAttribute('crudTable-project_edit').table.ajax.url(route_project_edit).load();
                        // }

                        if(crud.table){
                            crud.table.ajax.url(route_project).load();
                        }

                        forEachFlexible(SIAOPS.getAllAttributes(), function(key, item){
                            if(key.includes("crudTable")){
                                var filter_category = $('#filterYear .dropdown-menu li a.active').data('value');
                                var filter_client = $('#filterClient .dropdown-menu li a.active').data('value');
                                var url_route = item.route;
                                url_route += "&filter_category="+filter_category;
                                url_route += "&filter_client="+filter_client;
                                item.table.ajax.url(url_route).load();
                            }
                        });

                    });
                });
            }
        }
        filterSelectClient();
    </script>
@endpush
