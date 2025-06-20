@php
	// if not otherwise specified, the hidden input should take up no space in the form
  $field['wrapper'] = $field['wrapper'] ?? $field['wrapperAttributes'] ?? [];
  $field['wrapper']['class'] = $field['wrapper']['class'] ?? "hidden";
@endphp

{{-- hidden input --}}
@include('crud::fields.inc.wrapper_start')
    <p class="mb-1">
        <strong><span style="font-size: 20px;">{{trans('backpack::crud.cash_account_loan.field.balance_information.label')}}</span></strong>
        <span class="total_saldo fs-4 fw-bold text-dark">{{$field['value']}}</span>
    </p>
    {{-- <h5>{{trans('backpack::crud.cash_account_loan.field.balance_information.placeholder')}}</h5> --}}
@include('crud::fields.inc.wrapper_end')
