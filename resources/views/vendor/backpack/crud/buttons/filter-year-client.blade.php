<div class="btn-group" id="filterYear">
  <button class="btn btn-primary dropdown-toggle filter-btn" type="button" id="defaultDropdown" data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false">
    <i class="la la-filter"></i>
  </button>
  <ul class="dropdown-menu" aria-labelledby="defaultDropdown">
    @php
        $get_all_year = \App\Http\Helpers\CustomHelper::getYearOptionsClient();
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
                        var route = "{!! url($crud->route.'/search') !!}";
                        route += "?filter_year="+value;
                        crud.table.ajax.url(route).load();
                        window.filter_tables.filter_year = value;
                    });
                });
            }
        }
        filterSelectYear();
    </script>
@endpush
