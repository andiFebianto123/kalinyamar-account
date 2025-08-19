@php
	// if not otherwise specified, the hidden input should take up no space in the form
  $field['wrapper'] = $field['wrapper'] ?? $field['wrapperAttributes'] ?? [];
  $field['wrapper']['class'] = $field['wrapper']['class'] ?? "hidden";
  $set_value = (isset($no_po_spk)) ? $no_po_spk : null;
  $settings = \App\Models\Setting::first();
  $entry_value = $crud?->entry;
@endphp

{{-- hidden input --}}
@include('crud::fields.inc.wrapper_start')
  <input
  	type="hidden"
    name="{{ $field['name'] }}"
    value="{{ old_empty_or_null($field['name'], '') ??  $field['value'] ?? $field['default'] ?? '' }}"
    @include('crud::fields.inc.attributes')
  	>
@include('crud::fields.inc.wrapper_end')

@push('crud_fields_scripts')
    <script>
        SIAOPS.setAttribute('logic_client_po', function(){
            return {
                form_type : "{{ $crud->getActionMethod() }}",
                logicFormula: function(){
                    var form = (this.form_type == 'create') ? '#form-create' : '#form-edit';
                    var nilai_pekerjaan = getInputNumber(form+' #job_value');
                    var ppn = getInputNumber(form+' input[name="tax_ppn"]');

                    var nilai_ppn = (ppn == 0) ? 0 : (nilai_pekerjaan * (ppn / 100));
                    var total = nilai_pekerjaan + nilai_ppn;
                    setInputNumber(form+' #job_value_include_ppn_masked', total);

                    var total_biaya =  getInputNumber(form+' #price_total');
                    var laba_rugi_po = nilai_pekerjaan - total_biaya;
                    setInputNumber(form+' #profit_and_loss_masked', laba_rugi_po);

                    var beban_umum = getInputNumber(form+' #load_general_value');
                    var laba_rugi_akhir = laba_rugi_po - beban_umum;
                    setInputNumber(form+' #profit_and_lost_final_masked', laba_rugi_akhir);

                },
                load: function(){
                    var instance = this;
                    var form = (this.form_type == 'create') ? '#form-create' : '#form-edit';
                    var settings = {!! json_encode($settings) !!};
                    var entry = {!! json_encode($entry_value) !!};

                    if(entry){
                        // console.log(entry);
                        if(entry.status == null || entry.status == 'ADA PO'){
                            $(form+' input[name="po_number"]').removeAttr('disabled');
                        }else{
                            $(form+' input[name="po_number"]').attr('disabled', true);
                        }
                        $(form+' select[name="status"]').on('select2:select', function (e) {
                            var data = $(this).val();
                            if(data == 'TANPA PO'){
                                $(form+' input[name="po_number"]').attr('disabled', true);
                            }else{
                                $(form+' input[name="po_number"]').removeAttr('disabled');
                            }
                        });
                    }else{
                        $(form+' select[name="status"]').on('select2:select', function (e) {
                            var data = $(this).val();
                            if(data == 'TANPA PO'){
                                var kdp = "UMUM-";
                                $(form+' input[name="po_number"]').attr('disabled', true);
                                $(form+' input[name="work_code"]').val(kdp);
                            }else{
                                $(form+' input[name="po_number"]').removeAttr('disabled');
                                $(form+' input[name="work_code"]').val(settings.work_code_prefix);
                            }
                        });
                    }



                    $(form+' #job_value_masked').on('keyup', function(){
                        instance.logicFormula();
                    });
                    $(form+' input[name="tax_ppn"]').on('keyup', function(){
                        instance.logicFormula();
                    });
                    $(form+' #price_total_masked').on('keyup', function(){
                        instance.logicFormula();
                    });
                    $(form+' #load_general_value_masked').on('keyup', function(){
                        instance.logicFormula();
                    });
                }
            }
        });
        SIAOPS.getAttribute('logic_client_po').load();
    </script>
@endpush
