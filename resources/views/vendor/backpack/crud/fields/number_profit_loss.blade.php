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
            function formatRupiah(angka, prefix = '') {
                let isNegative = false;
                if (typeof angka === 'number' && angka < 0) {
                    isNegative = true;
                    angka = Math.abs(angka);
                }

                const numberString = angka.toString().replace(/[^,\d]/g, '');
                const split = numberString.split(',');
                let sisa = split[0].length % 3;
                let rupiah = split[0].substr(0, sisa);
                const ribuan = split[0].substr(sisa).match(/\d{3}/g);

                if (ribuan) {
                    const separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }

                rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;

                return (prefix ? prefix + ' ' : '') + (isNegative ? '-' : '') + rupiah;
            }

            function calculationProfitLoss(form_type){
                var job_value = parseFloat($(form_type+' input[name="job_value"]').val());
                var price_total = parseFloat($(form_type+' input[name="price_total"]').val());

                job_value = isNaN(job_value) ? 0 : job_value;
                price_total = isNaN(price_total) ? 0 : price_total;

                var profit_loss = job_value - price_total;

                var total = profit_loss;

                if(isNaN(profit_loss)){
                    // $(form_type+' input[name="'+name+'"]').val(0);
                }else{
                    // $(form_type+' input[name="'+name+'"]').val(ppn_value_total);
                    total = profit_loss;
                }
                total = formatRupiah(total);
                // var totalmask = maskElement.masked(total);
                $(form_type+' input[name="'+name+'"]').val(total).trigger('input');

            }

            var form_type = "{{ $crud->getActionMethod() }}";
            var name = "{{ $field['name'] }}";
            if(form_type == 'create'){
                $('#form-create input[data-alt="job_value_masked"], #form-create input[data-alt="price_total_masked"]').on('keyup', function(){
                    // calculationPPN('#form-create');
                    calculationProfitLoss('#form-create');
                });

            }else{
                $('#form-edit input[data-alt="job_value_masked"], #form-edit input[data-alt="price_total_masked"]').off('keyup').on('keyup', function(){
                    calculationProfitLoss('#form-edit');
                });
            }
        });
    </script>
@include('crud::fields.inc.wrapper_end')
