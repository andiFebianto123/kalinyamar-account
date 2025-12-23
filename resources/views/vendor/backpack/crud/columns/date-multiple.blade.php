{{-- localized date using nesbot carbon --}}
@php
    $column['value'] = $column['value'] ?? data_get($entry, $column['name']);
    $column['escaped'] = $column['escaped'] ?? true;
    $column['prefix'] = $column['prefix'] ?? '';
    $column['suffix'] = $column['suffix'] ?? '';
    $column['format'] = $column['format'] ?? backpack_theme_config('default_date_format');
    $column['text'] = $column['default'] ?? '-';

    if($column['value'] instanceof \Closure) {
        $column['value'] = $column['value']($entry);
    }

    if(!empty($column['value'])) {
        $date_explode = explode(",", $column['value']);
        foreach($date_explode as $key => $date){
            $date_explode[$key] = \Carbon\Carbon::parse($date)
                ->locale(App::getLocale())
                ->isoFormat($column['format']);
        }
        $date = implode(",", $date_explode);

        $column['text'] = $column['prefix'].$date.$column['suffix'];
    }
@endphp

<span data-order="{{ $column['value'] ?? '' }}">
    @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_start')
        @if($column['escaped'])
            {{ $column['text'] }}
        @else
            {!! $column['text'] !!}
        @endif
    @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_end')
</span>
