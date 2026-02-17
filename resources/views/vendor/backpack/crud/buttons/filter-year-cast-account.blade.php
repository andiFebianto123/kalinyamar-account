<div class="btn-group" id="filterYearCastAccount">
  <button class="btn btn-primary dropdown-toggle filter-btn" type="button" id="dropdownFilterYear" data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false">
    <i class="la la-filter"></i>
  </button>
  <ul class="dropdown-menu" aria-labelledby="dropdownFilterYear">
    @php
        $get_all_year = $year_options ?? \App\Http\Helpers\CustomHelper::getYearOptions('account_transactions', 'date_transaction');
    @endphp
    <li><a class="dropdown-item {{ !request('filter_year') || request('filter_year') == 'all' ? 'active' : '' }}" href="javascript:void(0)" data-value="all">
        {{ trans('backpack::crud.filter.all_year') }}
    </a></li>
    @foreach ($get_all_year as $year)
        <li><a class="dropdown-item {{ request('filter_year') == $year ? 'active' : '' }}" href="javascript:void(0)" data-value="{{ $year }}">
            {{ $year }}
        </a></li>
    @endforeach
  </ul>
</div>

@push('after_scripts')
    <script>
        if(window.filter_tables == undefined){
            window.filter_tables = {};
        }

        $(function() {
            $('#filterYearCastAccount .dropdown-item').click(function(e) {
                e.preventDefault();
                let value = $(this).data('value');
                
                // Update global state
                window.filter_tables.filter_year = value;

                // Build new URL
                let url = new URL(window.location.href);
                if (value === 'all') {
                    url.searchParams.delete('filter_year');
                } else {
                    url.searchParams.set('filter_year', value);
                }

                // Redirect to update card balances via PHP
                window.location.href = url.toString();
            });
        });
    </script>
@endpush
