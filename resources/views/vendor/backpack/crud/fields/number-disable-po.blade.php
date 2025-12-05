{{-- number input --}}

@php
    $getValue = \App\Http\Helpers\CustomHelper::formatRupiah(old_empty_or_null($field['name'], '') ??  $field['value'] ?? $field['default'] ?? 0);
@endphp

@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    @include('crud::fields.inc.translatable_icon')

    @if(isset($field['prefix']) || isset($field['suffix'])) <div class="input-group"> @endif
        @if(isset($field['prefix'])) <span class="input-group-text">{!! $field['prefix'] !!}</span> @endif
        <input
            disabled
        	type="text"
        	name="{{ $field['name'] }}"
            value="{{ $getValue }}"
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
            if(typeof setInputNumber2 == "undefined"){
                function formatIdr(angka){
                    const formatter = new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR'
                    });

                    let hasilFormat = formatter.format(angka);
                    let tanpaRp = hasilFormat.replace('Rp', '').trim();

                    return tanpaRp;
                }
                function setInputNumber2(selected, value){
                    let nominal = formatIdr(value);
                    $(selected).val(nominal).trigger('input');
                }
            }
            function calculationPPN(form_type){
                var job_value = parseFloat($(form_type+' input[name="job_value"]').val());
                var ppn = parseFloat($(form_type+' input[name="tax_ppn"]').val());
                var ppn_value_total = (job_value * (ppn / 100)) + job_value;

                if(isNaN(ppn_value_total)){
                    ppn_value_total = job_value;
                }

                // var maskElement = $(form_type+' input[name="'+name+'"]').mask('{{ $field['mask'] }}', {reverse: true});

                var total = 0;

                if(isNaN(ppn_value_total)){
                    // $(form_type+' input[name="'+name+'"]').val(0);
                }else{
                    // $(form_type+' input[name="'+name+'"]').val(ppn_value_total);
                    total = ppn_value_total;
                }
                //var totalmask = maskElement.masked(total);
                //$(form_type+' input[name="'+name+'"]').val(totalmask);

                setInputNumber2(form_type+' input[name="'+name+'"]', total);
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
