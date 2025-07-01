@php
    $get_value = old($field['name']) ?? $field['value'] ?? '';
    $get_value = preg_replace('/\.00$/', '', $get_value);
    $field['type'] = $field['type'] ?? 'text';
    $field['attributes']['id'] = $field['attributes']['id'] ?? $field['name'] . '_masked';
    $hidden_input_id = $field['name']; // hidden input pakai name asli
@endphp

@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    {{-- Input visible yang di-mask --}}
    @include('crud::fields.inc.translatable_icon')
    @if(isset($field['prefix']) || isset($field['suffix'])) <div class="input-group"> @endif
        @if(isset($field['prefix'])) <span class="input-group-text">{!! $field['prefix'] !!}</span> @endif
        <input
            type="text"
            data-alt="{{ $field['attributes']['id'] }}"
            data-bs-maskoption="{{ json_encode($field['mask_options'] ?? []) }}"
            data-init-function="bpFieldInitMaskElementDppOtherInvoiceClient"
            value="{{ $get_value }}"
            @include('crud::fields.inc.attributes')
        >
    @if(isset($field['suffix'])) <span class="input-group-text">{!! $field['suffix'] !!}</span> @endif
    @if(isset($field['prefix']) || isset($field['suffix'])) </div> @endif
    {{-- Input hidden untuk dikirim ke server --}}
    <input type="hidden" name="{{ $field['name'] }}" id="{{ $hidden_input_id }}">

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
@include('crud::fields.inc.wrapper_end')

@if ($crud->fieldTypeNotLoaded($field))
    @php
        $crud->markFieldTypeAsLoaded($field);
    @endphp

@push('crud_fields_scripts')
<script src="{{ asset('packages/jquery-mask-plugin-master/dist/jquery.mask.min.js') }}"></script>
<script>
        function bpFieldInitMaskElementDppOtherInvoiceClient(element){
            var $maskedInput = $(element);
            var $hiddenInput = $maskedInput.parent().next();
            var mask_option = $maskedInput.data('bs-maskoption');

            function getCleanValue(val) {
                return val.replace(/[^\d]/g, '');
            }

            // $maskedInput.unmask();
            setTimeout(() => {
                $maskedInput.mask('{{ $field['mask'] }}', mask_option);
            }, 100);

            $hiddenInput.val(getCleanValue($maskedInput.val()));

            function updateNominalIncludePpn(obj){
                var form_type = "{{ $crud->getActionMethod() }}";
                if(form_type == 'create'){
                    var raw = getCleanValue($maskedInput.val());
                    var ppn = ($('#form-create input[name="tax_ppn"]').val() > 0) ? $('#form-create input[name="tax_ppn"]').val() : 0;
                    var exclude_ppn = ($('#form-create input[name="nominal_exclude_ppn"]').val() > 0) ? $('#form-create input[name="nominal_exclude_ppn"]').val() : 0;

                    exclude_ppn = parseInt(getCleanValue(exclude_ppn));
                    raw = parseInt(raw);
                    ppn = parseInt(ppn);

                    // var nominal_with_dpp = exclude_ppn + raw;
                    var nominal_with_ppn = (exclude_ppn * (ppn / 100)) + exclude_ppn;

                    $('#form-create input[name="nominal_include_ppn"]').val(formatRupiah(nominal_with_ppn));
                }else{
                    var raw = getCleanValue($maskedInput.val());
                    var ppn = ($('#form-edit input[name="tax_ppn"]').val() > 0) ? $('#form-edit input[name="tax_ppn"]').val() : 0;
                    var exclude_ppn = ($('#form-edit input[name="nominal_exclude_ppn"]').val() > 0) ? $('#form-edit input[name="nominal_exclude_ppn"]').val() : 0;

                    exclude_ppn = parseInt(getCleanValue(exclude_ppn));
                    raw = parseInt(raw);
                    ppn = parseInt(ppn);

                    // var nominal_with_dpp = exclude_ppn + raw;
                    var nominal_with_ppn = (exclude_ppn * (ppn / 100)) + exclude_ppn;

                    $('#form-edit input[name="nominal_include_ppn"]').val(formatRupiah(nominal_with_ppn));
                }
            }

            var form_type = "{{ $crud->getActionMethod() }}";

            $maskedInput.on('input change keyup', function () {
                let raw = getCleanValue($(this).val());
                $hiddenInput.val(raw);
                updateNominalIncludePpn($(this));
            });

            if(form_type == 'create'){
                $('#form-create input[name="tax_ppn"]').on('keyup', function () {
                    updateNominalIncludePpn($(this));
                });
            }else{
                $('#form-edit input[name="tax_ppn"]').on('keyup', function () {
                    updateNominalIncludePpn($(this));
                });
            }
        }
</script>
@endpush
@endif
