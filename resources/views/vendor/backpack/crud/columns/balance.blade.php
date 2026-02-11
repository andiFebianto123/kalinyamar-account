{{-- regular object attribute --}}
@php
    $set_value = $entry;
    $code = $set_value->code_;

    $total_balance = 0;

    if($code == '303'){
        // kusus laba tahun berjalan
        $total_balance = \App\Http\Helpers\CustomHelper::balanceAccount($code);
    }else{
        $sum_accounts = \App\Models\Account::selectRaw("
            (SUM(journal_entries.debit) - SUM(journal_entries.credit)) as balance
        ")
        ->leftJoin('journal_entries', 'journal_entries.account_id', '=', 'accounts.id')
        ->where('accounts.code', 'LIKE', "".$code."%")
        ->first();

        $total_balance = $sum_accounts->balance;
    }

    
    $total_balance = \App\Http\Helpers\CustomHelper::formatRupiahWithCurrency($total_balance);
@endphp

<span>
    @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_start')
        {{ $total_balance }}
    @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_end')
</span>
