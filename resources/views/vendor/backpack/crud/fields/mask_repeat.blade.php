{{-- text input --}}

@php
    $get_value = old_empty_or_null($field['name'], '') ??  $field['value'] ?? $field['default'] ?? '';
    $get_value = preg_replace('/\.00$/', '', $get_value);
    $hidden_input_id = $field['name'];
@endphp

@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    @include('crud::fields.inc.translatable_icon')

    @if(isset($field['prefix']) || isset($field['suffix'])) <div class="input-group"> @endif
        @if(isset($field['prefix'])) <span class="input-group-text">{!! $field['prefix'] !!}</span> @endif
        <input
            type="text"
            data-bs-maskoption="{{ json_encode($field['mask_options'] ?? []) }}"
            value="{{ $get_value }}"
            data-init-function="bpFieldInitMaskElement"
            @include('crud::fields.inc.attributes')
        >
        @if(isset($field['suffix'])) <span class="input-group-text">{!! $field['suffix'] !!}</span> @endif
    @if(isset($field['prefix']) || isset($field['suffix'])) </div> @endif
    <input type="hidden" name="{{ $field['name'] }}" id="{{ $hidden_input_id }}">

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
@include('crud::fields.inc.wrapper_end')

@push('crud_fields_scripts')
<script src="{{ asset('packages/jquery-mask-plugin-master/dist/jquery.mask.min.js') }}"></script>
<script>
        function bpFieldInitMaskElement(element){
            var $maskedInput = $(element);
            var $hiddenInput = $maskedInput.parent().next();
            var mask_option = $maskedInput.data('bs-maskoption');

            let value = $hiddenInput.val();
            let clean = parseInt(Number(value));

            function getCleanValue(val) {
                return val.replace(/[^\d]/g, '');
            }

            // // console.log($maskedInput.val());

            // // $maskedInput.unmask();
            setTimeout(() => {
                $maskedInput.mask('{{ $field['mask'] }}', mask_option);
            }, 100);

            $hiddenInput.val(clean);
            $maskedInput.val(formatRupiah(clean));

            $maskedInput.on('input change keyup', function () {
                let raw = getCleanValue($(this).val());
                $hiddenInput.val(raw);
            });
        }
</script>
@endpush
