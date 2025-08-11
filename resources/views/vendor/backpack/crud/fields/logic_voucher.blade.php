@php
	// if not otherwise specified, the hidden input should take up no space in the form
  $field['wrapper'] = $field['wrapper'] ?? $field['wrapperAttributes'] ?? [];
  $field['wrapper']['class'] = $field['wrapper']['class'] ?? "hidden";
  $set_value = (isset($no_po_spk)) ? $no_po_spk : null;
@endphp

{{-- hidden input --}}
@include('crud::fields.inc.wrapper_start')
  <input type="hidden" name="no_type" />
  <input type="hidden" name="bussines_entity_name" />
  <input
  	type="hidden"
    name="{{ $field['name'] }}"
    value="{{ old_empty_or_null($field['name'], '') ??  $field['value'] ?? $field['default'] ?? '' }}"
    @include('crud::fields.inc.attributes')
  	>
@include('crud::fields.inc.wrapper_end')

@push('crud_fields_scripts')
    <script>
        SIAOPS.setAttribute('logic_asset', function(){
            return {
                form_type : "{{ $crud->getActionMethod() }}",
                logicFormula: function(){
                    var form = (this.form_type == 'create') ? '#form-create' : '#form-edit';
                    var bill_value = getInputNumber(form+' #bill_value');
                    var ppn = getInputNumber(form+' input[name="tax_ppn"]');

                    var nilai_ppn = (ppn == 0) ? 0 : (bill_value * (ppn / 100));

                    var total = bill_value + nilai_ppn;
                    setInputNumber(form+ ' #total_masked', total);

                    var pph_23 = getInputNumber(form + ' input[name="pph_23"]');
                    var diskon_pph_23 = (pph_23 == 0) ? 0 : bill_value * (pph_23 / 100);
                    setInputNumber(form+' #discount_pph_23_masked', diskon_pph_23);

                    var pph_4 = getInputNumber(form + ' input[name="pph_4"]');
                    var diskon_pph_4 = (pph_4 == 0) ? 0 : bill_value * (pph_4 / 100);
                    setInputNumber(form+' #discount_pph_4_masked', diskon_pph_4);

                    var pph_21 = getInputNumber(form+' input[name="pph_21"]');
                    var diskon_pph_21 = (pph_21 == 0) ? 0 : bill_value * (pph_21 / 100);
                    setInputNumber(form+' #discount_pph_21_masked', diskon_pph_21);

                    var payment_transfer = total - diskon_pph_23 - diskon_pph_4 - diskon_pph_21;
                    setInputNumber(form+' #payment_transfer_masked', payment_transfer);

                },
                load: function(){
                    var instance = this;
                    var form = (this.form_type == 'create') ? '#form-create' : '#form-edit';

                    @if ($set_value != null)
                        var data_po_spk = {!! json_encode($set_value) !!};
                        var selectedOption = new Option(data_po_spk.no_po_spk, data_po_spk.id, true, true);
                        $(form+ ' select[name="no_po_spk"]').append(selectedOption).trigger('change');
                        $(form+ ' input[name="bussines_entity_name"]').val(data_po_spk.name_company);
                        // $(form+ ' input[name="date_po_spk"]').val(data_po_spk.date_po_spk_str);
                        $(form+ ' input[name="bank_name"]').val(data_po_spk.bank_name);
                        $(form+' input[name="no_account"]').val(data_po_spk.bank_account);
                        $(form+' input[name="no_type"]').val(data_po_spk.type);

                        setTimeout(() => {
                             $(form+ ' input[name="date_po_spk"]').val(data_po_spk.date_po_spk_str);
                        }, 500);

                    @endif

                    $(form+ ' select[name="client_po_id"]').off('select2:select').on('select2:select', function (e) {
                        var id = e.params.data.id;
                        $.ajax({
                            url: "{{ url($crud->route) }}/get_client_selected_ajax?id=" + id,
                            type: 'GET',
                            dataType: 'json',
                            success: function (data) {
                                var work_option = new Option(data.work_code, data.id, true, true);
                                $(form+ ' select[name="reference_id"]').append(work_option).trigger('change');
                                $(form+' input[name="job_name"]').val(data.job_name);
                                setInputNumber(form+' #bill_value_masked', data.price_total);
                            }
                        })
                    });

                    $(form+' select[name="reference_id"]').off('select2:select').on('select2:select', function (e) {
                        var id = e.params.data.id;
                        $.ajax({
                            url: "{{ url($crud->route) }}/get_client_selected_ajax?id=" + id,
                            type: 'GET',
                            dataType: 'json',
                            success: function (data) {
                                var po_number_option = new Option(data.po_number, data.id, true, true);
                                $(form+ ' select[name="client_po_id"]').append(po_number_option).trigger('change');
                                $(form+' input[name="job_name"]').val(data.job_name);
                                setInputNumber(form+' #bill_value_masked', data.price_total);
                            }
                        })
                    });

                    $(form+' select[name="factur_status"]').off('select2:select').on('select2:select', function (e) {
                        if(e.params.data.id == 'TIDAK ADA' || e.params.data.id == "AKAN ADA"){
                            $(form+' input[name="no_factur"]').attr('readonly', true);
                            $(form+' #date_factur').attr('disabled', true);
                        }else{
                            $(form+' input[name="no_factur"]').removeAttr('readonly');
                            $(form+' #date_factur').removeAttr('disabled');
                        }
                    });

                    $(form+' #bill_value_masked').on('keyup', function(){
                        instance.logicFormula();
                    });

                    $(form+' input[name="tax_ppn"]').on('keyup', function(){
                        instance.logicFormula();
                    });

                    $(form + ' input[name="pph_23"]').on('keyup', function(){
                        instance.logicFormula();
                    });

                    $(form + ' input[name="pph_4"]').on('keyup', function(){
                        instance.logicFormula();
                    });

                    $(form+' input[name="pph_21"]').on('keyup', function(){
                        instance.logicFormula();
                    });

                }
            }
        });
        SIAOPS.getAttribute('logic_asset').load();
    </script>
@endpush
