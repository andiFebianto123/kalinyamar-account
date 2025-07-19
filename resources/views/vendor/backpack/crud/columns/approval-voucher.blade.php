{{-- regular object attribute --}}
@php

    $get_table = get_class($entry);
    $user_id = backpack_user()->id;
    $set_status = $entry->approval_status;

    if($set_status == null){
        $voucer_edit_last = \App\Models\VoucherEdit::where('voucher_id', $entry->id)->orderBy('id', 'DESC')->first();
        $approvals = \App\Models\Approval::where('model_type', \App\Models\VoucherEdit::class)
        ->where('model_id', $voucer_edit_last->id)
        ->orderBy('no_apprv', 'DESC')->first();
        $set_status = $approvals->status ?? 'NONE';
    }

    $set_status = strtoupper($set_status);


    $column['value'] = $column['value'] ?? data_get($entry, $column['name']);
    $column['escaped'] = $column['escaped'] ?? true;
    $column['limit'] = $column['limit'] ?? 32;
    $column['prefix'] = $column['prefix'] ?? '';
    $column['suffix'] = $column['suffix'] ?? '';
    $column['text'] = $column['default'] ?? '-';

    if($column['value'] instanceof \Closure) {
        $column['value'] = $column['value']($entry);
    }

    if(is_array($column['value'])) {
        $column['value'] = json_encode($column['value']);
    }

    if(!empty($column['value'])) {
        $column['text'] = $column['prefix'].Str::limit($column['value'], $column['limit'], '…').$column['suffix'];
    }
@endphp



<div class="text-center mt-2 status-{{$entry->id}}">
    @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_start')
        <strong>
            @if ($get_table == 'App\Models\VoucherEdit')
                @if ($set_status == 'PENDING')
                    {{ trans('backpack::crud.voucher.waiting_approval') }}
                @elseif($set_status == 'APPROVED')
                    {{ trans('backpack::crud.voucher.approved_approval') }}
                @else
                     {{ trans('backpack::crud.voucher.reject_approval') }}
                @endif
            @else
                {{  $set_status }}
            @endif
        </strong>
    @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_end')
</div>
@if ($set_status == 'PENDING' || $set_status == 'REJECTED')
    <script>
        $('.status-{{$entry->id}}').parent().addClass('bg-danger');
    </script>
@elseif ($set_status == 'APPROVED')
    <script>
        $('.status-{{$entry->id}}').parent().addClass('bg-success');
    </script>
@endif

