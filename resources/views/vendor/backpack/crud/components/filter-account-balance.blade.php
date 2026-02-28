<div class="row">
    @php
        $currentYear = date('Y');
        $journalYears = $journal_years ?? [];
        
        // Merge journal years with current year to ensure current year is always an option
        $years = array_unique(array_merge([$currentYear], $journalYears));
        rsort($years);

        $quarters = [
            1 => 'Kuartal 1 (Jan - Mar)',
            2 => 'Kuartal 2 (Apr - Jun)',
            3 => 'Kuartal 3 (Jul - Sep)',
            4 => 'Kuartal 4 (Okt - Des)',
        ];

        // Default to current year ONLY if no year is requested. 
        // If "all" is requested, $selectedYear will be "all".
        $selectedYear = request('filter_year', $currentYear);
        $selectedQuarter = request('filter_quarter');
    @endphp
    <div class="form-group col-sm-2 mb-3" element="div" bp-field-wrapper="true" bp-field-name="filter_year" bp-field-type="select_from_array" bp-section="crud-field">
        <h5>{{trans('backpack::crud.balance_sheet.filters.year.label')}}</h5>
        <select name="filter_year" class="form-control form-select filter-balance-sheet">
            <option value="all" {{ $selectedYear === 'all' ? 'selected' : '' }}>Semua Tahun</option>
            @foreach($years as $year)
                <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-sm-2 mb-3" element="div" bp-field-wrapper="true" bp-field-name="filter_quarter" bp-field-type="select_from_array" bp-section="crud-field">
        <h5>{{trans('backpack::crud.balance_sheet.filters.quarter.label')}}</h5>
        <select name="filter_quarter" class="form-control form-select filter-balance-sheet">
            <option value="">{{trans('backpack::crud.balance_sheet.filters.quarter.placeholder')}}</option>
            @foreach($quarters as $key => $label)
                <option value="{{ $key }}" {{ $selectedQuarter == $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>

@push('after_scripts')
<script>
    $(document).ready(function() {
        $('.filter-balance-sheet').on('change', function() {
            let year = $('select[name="filter_year"]').val();
            let quarter = $('select[name="filter_quarter"]').val();
            
            // Mengambil URL saat ini dan membersihkan &amp; jika ada dalam bentuk string
            let currentUrl = window.location.href.replace(/&amp;/g, '&');
            let url = new URL(currentUrl);
            
            // Bersihkan semua parameter yang mengandung "amp;"
            let newSearchParams = new URLSearchParams();
            url.searchParams.forEach((value, key) => {
                let cleanKey = key.replace(/^amp;/, '');
                newSearchParams.set(cleanKey, value);
            });

            // Set filter baru
            if (year) newSearchParams.set('filter_year', year);
            else newSearchParams.delete('filter_year');
            
            if (quarter) newSearchParams.set('filter_quarter', quarter);
            else newSearchParams.delete('filter_quarter');
            
            // Redirect ke URL yang sudah bersih
            window.location.href = url.origin + url.pathname + '?' + newSearchParams.toString();
        });
    });
</script>
@endpush


