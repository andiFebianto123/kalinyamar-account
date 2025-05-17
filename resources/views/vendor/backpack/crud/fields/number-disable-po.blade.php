{{-- number input --}}

@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    @include('crud::fields.inc.translatable_icon')

    @if(isset($field['prefix']) || isset($field['suffix'])) <div class="input-group"> @endif
        @if(isset($field['prefix'])) <span class="input-group-text">{!! $field['prefix'] !!}</span> @endif
        <input
            disabled
        	type="number"
        	name="{{ $field['name'] }}"
            value="{{ old_empty_or_null($field['name'], '') ??  $field['value'] ?? $field['default'] ?? '' }}"
            @include('crud::fields.inc.attributes')
        	>
        @if(isset($field['suffix'])) <span class="input-group-text">{!! $field['suffix'] !!}</span> @endif

    @if(isset($field['prefix']) || isset($field['suffix'])) </div> @endif

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
                    $(form_type+' input[name="'+name+'"]').val(0);
                }else{
                    $(form_type+' input[name="'+name+'"]').val(ppn_value_total);
                }
            }

            var form_type = "{{ $crud->getActionMethod() }}";
            var name = "{{ $field['name'] }}";
            if(form_type == 'create'){
                $('#form-create input[name="job_value"], #form-create input[name="tax_ppn"]').off('keyup').on('keyup', function(){
                    calculationPPN('#form-create');
                });

            }else{
                $('#form-edit input[name="job_value"], #form-edit input[name="tax_ppn"]').off('keyup').on('keyup', function(){
                    calculationPPN('#form-edit');
                    console.log('edit');
                });
            }
        });
    </script>
@include('crud::fields.inc.wrapper_end')
