{{-- text input --}}

@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    @include('crud::fields.inc.translatable_icon')

    @if(isset($field['prefix']) || isset($field['suffix'])) <div class="input-group"> @endif
        @if(isset($field['prefix'])) <span class="input-group-text">{!! $field['prefix'] !!}</span> @endif
        <?php
            // dd($field['value']);
        ?>
        <div class="table-responsive">
            <table class="table table-borderless" style="width: 1300px;">
                <thead>
                    <tr>
                        <th>Checklist</th>
                        <th>{{trans('backpack::crud.voucher.column.voucher.no_voucher.label')}}</th>
                        <th>{{trans('backpack::crud.voucher.column.voucher.date_voucher.label')}}</th>
                        <th>{{trans('backpack::crud.voucher.column.voucher.bussines_entity_name.label')}}</th>
                        <th>{{trans('backpack::crud.voucher.column.voucher.bill_date.label')}}</th>
                        <th>{{trans('backpack::crud.voucher.column.voucher.no_po_spk.label')}}</th>
                        <th>{{trans('backpack::crud.voucher.column.voucher.payment_transfer.label_2')}}</th>
                        <th>{{trans('backpack::crud.voucher.column.voucher.due_date.label_2')}}</th>
                        <th>{{trans('backpack::crud.voucher.column.voucher.factur_status.label')}}</th>
                        <th>{{trans('backpack::crud.voucher.column.voucher.payment_type.label')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($field['value'] as $voucher)
                        <tr>
                            <td><input type="checkbox" name="voucher[]" class="form-check-input" value="{{$voucher->id}}"></td>
                            <td>{{$voucher->no_voucher}}</td>
                            <td>{{$voucher->date_voucher_str}}</td>
                            <td>{{$voucher?->subkon?->name}}</td>
                            <td>{{$voucher->bill_date_str}}</td>
                            <td>{{ ($voucher->reference_type == 'App\Models\Spk') ? $voucher->reference->no_spk : $voucher->reference->po_number}}</td>
                            <td>{{$voucher->payment_transfer_str}}</td>
                            <td>{{$voucher->due_date_str}}</td>
                            <td>{{$voucher->factur_status}}</td>
                            <td>{{$voucher->payment_type}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if(isset($field['suffix'])) <span class="input-group-text">{!! $field['suffix'] !!}</span> @endif
    @if(isset($field['prefix']) || isset($field['suffix'])) </div> @endif

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
@include('crud::fields.inc.wrapper_end')
