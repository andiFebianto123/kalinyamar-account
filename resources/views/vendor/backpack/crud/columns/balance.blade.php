{{-- regular object attribute --}}
@php
    $total_balance = \App\Http\Helpers\CustomHelper::formatRupiahWithCurrency($entry->balance);
@endphp

<span>
    @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_start')
        {{ $total_balance }}
    @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_end')
</span>
