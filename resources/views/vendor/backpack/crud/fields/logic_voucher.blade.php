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
                        console.log(data_po_spk);
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

                    $(form+ ' select[name="no_po_spk"]').off('select2:select').on('select2:select', function (e) {
                        var id = e.params.data.id;

                        var data = e.params.data.data;

                        $(form+ ' input[name="bussines_entity_name"]').val(data.name_company);
                        $(form+ ' input[name="date_po_spk"]').val(data.date_po_spk_str);
                        $(form+ ' input[name="bank_name"]').val(data.bank_name);
                        $(form+' input[name="no_account"]').val(data.bank_account);
                        $(form+' input[name="no_type"]').val(data.type);


                        // $.ajax({
                        //     url: '{!! backpack_url("invoice-client/get-client-po") !!}',
                        //     method: 'GET',
                        //     data: {
                        //         id: id,
                        //     },
                        //     success: function(response) {
                        //         var respon = response.result;
                        //         if(form_type == 'create'){
                        //             $('#form-create input[name="po_date"]').val(respon.date_invoice);
                        //             $('#form-create input[name="client_name"]').val(respon.client_name);
                        //             $('#form-create input[name="nominal_exclude_ppn"]').val(respon.job_value);
                        //             $('#form-create input[name="dpp_other"]').val('');
                        //             $('#form-create #dpp_other_masked').val('');
                        //             $('#form-create input[name="tax_ppn"]').val('');
                        //             $('#form-create input[name="nominal_include_ppn"]').val('');
                        //         }else{
                        //             $('#form-edit input[name="po_date"]').val(respon.date_invoice);
                        //             $('#form-edit input[name="client_name"]').val(respon.client_name);
                        //             $('#form-edit input[name="nominal_exclude_ppn"]').val(respon.job_value);
                        //             $('#form-edit input[name="dpp_other"]').val('');
                        //             $('#form-edit #dpp_other_masked').val('');
                        //             $('#form-edit input[name="tax_ppn"]').val('');
                        //             $('#form-edit input[name="nominal_include_ppn"]').val('');
                        //         }
                        //     }
                        // });
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
