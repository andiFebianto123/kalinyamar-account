<div class="btn-group" id="filterCategory">
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
        if(window.filter_tables == undefined){
            window.filter_tables = {};
        }
        if(typeof filterSelectCategory != 'function'){
            function filterSelectCategory(){
                $('#filterCategory .dropdown-menu li').each(function(){
                    $(this).children().click(function(e){
                        e.preventDefault();
                        $('#filterCategory .dropdown-menu li a').removeClass('active');
                        let value = $(this).data('value');
                        $(this).addClass('active');

                        var filter_client = $('#filterClient .dropdown-menu li a.active').data('value');
                        
                        window.filter_tables.filter_category = value;
                        window.filter_tables.filter_client = filter_client;

                        // if(typeof SIAOPS !== 'undefined' && SIAOPS.getAttribute('resume-project')){
                        //     SIAOPS.getAttribute('resume-project').load();
                        // }

                        forEachFlexible(SIAOPS.getAllAttributes(), function(key, item){
                            if(key.includes("crudTable")){
                                var url_active = item.table.ajax.url();
                                var url = new URL(url_active.startsWith('http') ? url_active : window.location.origin + (url_active.startsWith('/') ? '' : '/') + url_active);
                                
                                if (value === 'all') {
                                    url.searchParams.delete('filter_category');
                                } else {
                                    url.searchParams.set('filter_category', value);
                                }

                                if (filter_client === 'all') {
                                    url.searchParams.delete('filter_client');
                                } else {
                                    url.searchParams.set('filter_client', filter_client);
                                }
                                
                                var final_url = url_active.startsWith('http') ? url.toString() : url.pathname + url.search;
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
        filterSelectCategory();

        if(typeof filterSelectClient != 'function'){
            function filterSelectClient(){
                $('#filterClient .dropdown-menu li').each(function(){
                    $(this).children().click(function(e){
                        e.preventDefault();
                        $('#filterClient .dropdown-menu li a').removeClass('active');
                        let value = $(this).data('value');
                        $(this).addClass('active');

                        var filter_category = $('#filterCategory .dropdown-menu li a.active').data('value');

                        window.filter_tables.filter_category = filter_category;
                        window.filter_tables.filter_client = value;

                        // if(typeof SIAOPS !== 'undefined' && SIAOPS.getAttribute('resume-project')){
                        //     SIAOPS.getAttribute('resume-project').load();
                        // }

                        forEachFlexible(SIAOPS.getAllAttributes(), function(key, item){
                            if(key.includes("crudTable")){
                                var url_active = item.table.ajax.url();
                                var url = new URL(url_active.startsWith('http') ? url_active : window.location.origin + (url_active.startsWith('/') ? '' : '/') + url_active);
                                
                                if (filter_category === 'all') {
                                    url.searchParams.delete('filter_category');
                                } else {
                                    url.searchParams.set('filter_category', filter_category);
                                }

                                if (value === 'all') {
                                    url.searchParams.delete('filter_client');
                                } else {
                                    url.searchParams.set('filter_client', value);
                                }
                                
                                var final_url = url_active.startsWith('http') ? url.toString() : url.pathname + url.search;
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
        filterSelectClient();
    </script>
@endpush
