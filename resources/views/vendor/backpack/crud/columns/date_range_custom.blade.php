@php
    // Parsing nama kolom: 'start_date,end_date'
    [$startField, $endField] = explode(',', $column['name']);
    $start = $entry->{$startField};
    $end = $entry->{$endField};
    $column['format'] = $column['format'] ?? backpack_theme_config('default_date_format');

    $startFormatted = $start ? \Carbon\Carbon::parse($start)
    ->locale(App::getLocale())
    ->isoFormat($column['format']) : '-';

    $endFormatted = $end ? \Carbon\Carbon::parse($end)
    ->locale(App::getLocale())
    ->isoFormat($column['format']) : '-';
@endphp


<span>
    @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_start')
        {{$startFormatted}} <i class="la la-arrow-right"></i> {{$endFormatted}}
    @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_end')
</span>
