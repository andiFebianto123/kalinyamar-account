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
            data-init-function="bpFieldInitMaskElement"
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
        function bpFieldInitMaskElement(element){
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

            $maskedInput.on('input change keyup', function () {
                let raw = getCleanValue($(this).val());
                $hiddenInput.val(raw);
            });
        }
</script>
@endpush
@endif
