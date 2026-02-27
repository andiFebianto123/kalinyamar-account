{{-- closure function column type --}}
@php
    $column['value'] = $column['value'] ?? $column['function'];
    $column['escaped'] = $column['escaped'] ?? true;
    $column['prefix'] = $column['prefix'] ?? '';
    $column['suffix'] = $column['suffix'] ?? '';
    $column['text'] = $column['default'] ?? '-';
    $column['row_number'] = $rowNumber ?? null;
    $column['width_box'] = $column['width_box'] ?? '200px';

    if($column['value'] instanceof \Closure) {
        $column['value'] = $column['value']($entry, $column['row_number']);
    }

    if(!empty($column['value'])) {
        $column['text'] = $column['prefix'].$column['value'].$column['suffix'];
    }
@endphp

<span>
    @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_start')
        <div style="white-space: normal; word-wrap: break-word; width: {{ $column['width_box'] }};">
            @if($column['escaped'])
                {{ $column['text'] }}
            @else
                {!! $column['text'] !!}
            @endif
        </div>
    @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_end')
</span>
