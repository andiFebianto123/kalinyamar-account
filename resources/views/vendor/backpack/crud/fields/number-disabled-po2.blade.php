@php
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
            value="{{ old($field['name']) ?? $field['value'] ?? '' }}"
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
    <script>
        $(function(){
            function calculationPPN(form_type){
                var job_value = parseFloat($(form_type+' input[name="job_value"]').val());
                var ppn = parseFloat($(form_type+' input[name="tax_ppn"]').val());
                var ppn_value_total = (job_value * (ppn / 100)) + job_value;

                if(isNaN(ppn_value_total)){
                    ppn_value_total = job_value;
                }

                if(isNaN(ppn_value_total)){
                    $(form_type+' input[name="'+name+'"]').val(0);
                }else{
                    $(form_type+' input[name="'+name+'"]').val(ppn_value_total);
                }

                $(form_type+' input[data-alt="{{ $field['attributes']['id'] }}"]').val($(form_type+' input[name="'+name+'"]').val());
                $(form_type+' input[data-alt="{{ $field['attributes']['id'] }}"]').mask();

            }

            var form_type = "{{ $crud->getActionMethod() }}";
            var name = "{{ $field['name'] }}";
            if(form_type == 'create'){
                $('#form-create input[data-alt="job_value_masked"], #form-create input[name="tax_ppn"]').on('keyup', function(){
                    calculationPPN('#form-create');
                });

            }else{
                $('#form-edit input[data-alt="job_value_masked"], #form-edit input[name="tax_ppn"]').off('keyup').on('keyup', function(){
                    calculationPPN('#form-edit');
                });
            }
        });
    </script>
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
