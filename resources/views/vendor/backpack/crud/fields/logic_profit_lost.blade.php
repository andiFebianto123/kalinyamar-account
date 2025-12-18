@php
	// if not otherwise specified, the hidden input should take up no space in the form
  $field['wrapper'] = $field['wrapper'] ?? $field['wrapperAttributes'] ?? [];
  $field['wrapper']['class'] = $field['wrapper']['class'] ?? "hidden";
  $set_value = (isset($entry)) ? $entry : null;
@endphp

{{-- hidden input --}}
@include('crud::fields.inc.wrapper_start')
  <input type="hidden" name="id_client_po" />
  <input type="hidden" name="voucher_id" />
  <input
  	type="hidden"
    name="{{ $field['name'] }}"
    value="{{ old_empty_or_null($field['name'], '') ??  $field['value'] ?? $field['default'] ?? '' }}"
    @include('crud::fields.inc.attributes')
  	>
@include('crud::fields.inc.wrapper_end')

@push('crud_fields_scripts')
    <script>
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
        SIAOPS.setAttribute('logic_asset', function(){
            return {
                data: {},
                form_type : "{{ $crud->getActionMethod() }}",
                logicFormula: function(data){
                    var form = (this.form_type == 'create') ? '#form-create' : '#form-edit';
                    var price_voucher = getInputNumber(form+' input[name="price_voucher"]');
                    var price_small_cash = getInputNumber(form+' input[name="price_small_cash"]');
                    var price_excl_ppn_po = data.price_excl_ppn_po;

                    var price_after_year = getInputNumber(form+' input[name="price_after_year"]');

                    setInputNumber(form+ ' #price_voucher_masked', price_voucher);
                    // setInputNumber(form+ ' #price_small_cash_masked', price_small_cash);

                    var total_price = price_after_year + price_voucher + price_small_cash;

                    console.log(total_price, price_excl_ppn_po);

                    setInputNumber2(form+ ' input[name="price_total"]', total_price);

                    var laba_rugi = price_excl_ppn_po - total_price;
                    setInputNumber2(form+ ' input[name="price_profit_lost_po"]', laba_rugi);

                    var general_price = getInputNumber(form+' input[name="price_general"]');
                    var laba_rugi_akhir = laba_rugi - general_price;

                    setInputNumber2(form+ ' input[name="price_prift_lost_final"]', laba_rugi_akhir);
                },
                load: function(){
                    var instance = this;
                    var form = (this.form_type == 'create') ? '#form-create' : '#form-edit';

                    @if ($set_value != null)
                        var data_profit_lost = {!! json_encode($set_value) !!};
                        // console.log(data_profit_lost);
                        // var po_number_text = `${data_po_spk.po_number} (${data_po_spk.type})`;
                        // var work_code_text = `${data_po_spk.work_code} (${data_po_spk.type})`;

                        // var selectedOption = new Option(po_number_text, data_po_spk.id, true, true);
                        // // $(form+ ' select[name="client_po_id"]').val(null).trigger('change');
                        // $(form+ ' select[name="client_po_id"]').append(selectedOption).trigger('change');

                        // var selectedOptionw = new Option(work_code_text, data_po_spk.id, true, true);
                        // // $(form+' select[name="reference_id"]').val(null).trigger('change');
                        // $(form+' select[name="reference_id"]').append(selectedOptionw).trigger('change');

                        // setTimeout(() => {
                        //      $(form+ ' input[name="date_po_spk"]').val(data_po_spk.date_po_spk_str);
                        // }, 500);

                        $.ajax({
                            url: "{{ url($crud->route) }}/get_client_selected_ajax?id=" + data_profit_lost.client_po.id,
                            type: 'GET',
                            dataType: 'json',
                            success: function (data) {
                                instance.data = data;
                                setInputNumber(form+ ' #price_voucher_masked', data.price_voucher);
                                // setInputNumber(form+ ' #price_small_cash_masked', data.price_small_cash);
                                instance.logicFormula(data);
                            }
                        });

                    @endif

        

                    $(form+ ' select[name="work_code"]').off('select2:select').on('select2:select', function (e) {
                        var result = e.params.data;
                        var id = result.id;
                        $(form+' input[name="po_number"]').val(result.po_number);
                        $(form+' input[name="id_client_po"]').val(id);
                        $(form+' input[name="voucher_id"]').val(result.voucher_id);
                        // setInputNumber(form+ ' #total_project_masked', e.params.data.data.reference.price_total);
                        $.ajax({
                            url: "{{ url($crud->route) }}/get_client_selected_ajax?id=" + id,
                            type: 'GET',
                            dataType: 'json',
                            success: function (data) {
                                instance.data = data;
                                setInputNumber(form+ ' #price_voucher_masked', data.price_voucher);
                                // setInputNumber(form+ ' #price_small_cash_masked', data.price_small_cash);
                                instance.logicFormula(data);
                            }
                        })
                    });

                    $(form+' #price_after_year_masked').on('keyup', function(){
                        instance.logicFormula(instance.data);
                    });

                    $(form+' #price_general_masked').on('keyup', function(){
                        instance.logicFormula(instance.data);
                    });

                    $(form+' #price_voucher_masked').on('keyup', function(){
                        instance.logicFormula(instance.data);
                    });

                    $(form+' #price_small_cash_masked').on('keyup', function(){
                        instance.logicFormula(instance.data);
                    });

                    $(form+' #price_total_masked').on('keyup', function(){
                        instance.logicFormula(instance.data);
                    });


                }
            }
        });
        SIAOPS.getAttribute('logic_asset').load();
    </script>
@endpush
