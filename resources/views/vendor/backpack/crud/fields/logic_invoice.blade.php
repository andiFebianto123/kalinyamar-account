@php
	// if not otherwise specified, the hidden input should take up no space in the form
  $field['wrapper'] = $field['wrapper'] ?? $field['wrapperAttributes'] ?? [];
  $field['wrapper']['class'] = $field['wrapper']['class'] ?? "hidden";
  $set_value = (isset($entry)) ? $entry : null;
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
        SIAOPS.setAttribute('logic_invoice', function(){
            return {
                form_type : "{{ $crud->getActionMethod() }}",
                logicFormulaNoPO: function(){
                    var form = (this.form_type == 'create') ? '#form-create' : '#form-edit';
                    var nominal_exclude_ppn = getInputNumber(form + ' input[name="nominal_exclude_ppn"]');
                    var tax_ppn = getInputNumber(form + ' input[name="tax_ppn"]');
                    var nilai_ppn = (tax_ppn == 0) ? 0 : (nominal_exclude_ppn * (tax_ppn / 100));
                    var total = nominal_exclude_ppn + nilai_ppn;
                    setInputNumber(form + ' #nominal_include_ppn_masked', total);
                },
                load: function(){
                    var instance = this;
                    var form = (this.form_type == 'create') ? '#form-create' : '#form-edit';

                    var entry = {!! json_encode($set_value) !!};
                    if(entry != null){
                        setTimeout(() => {
                            setInputNumber(form + ' #nominal_exclude_ppn_masked', entry.nominal_exclude_ppn || 0);
                            setInputNumber(form+' #dpp_other_masked', entry.price_dpp || 0);
                            $(form+' input[name="tax_ppn"]').val(entry.tax_ppn || 0);
                            setInputNumber(form+' #nominal_include_ppn_masked', entry.price_total_include_ppn || 0);
                        }, 300);
                    }

                    $(form+ ' select[name="client_po_id"]').off('select2:select').on('select2:select', function (e) {
                        var id = e.params.data.id;
                        $.ajax({
                            url: '{!! backpack_url("invoice-client/get-client-po") !!}',
                            method: 'GET',
                            data: {
                                id: id,
                            },
                            success: function(response) {
                                var respon = response.result;
                                setInputNumber(form + ' #nominal_exclude_ppn_masked', respon.job_value || 0);
                                $(form+' input[name="tax_ppn"]').val(respon.tax_ppn);
                                instance.logicFormulaNoPO();
                                $(form+' input[name="kdp"]').val(respon.work_code);
                                $(form+' input[name="client_name"]').val(respon.client.name);
                                $(form+" input[name='po_date']").val(respon.date_invoice);
                            }
                        });
                    });

                    $(form+' input[name="tax_ppn"]').on('keyup', function(){
                        instance.logicFormulaNoPO();
                    });

                }
            }
        });
        SIAOPS.getAttribute('logic_invoice').load();
    </script>
@endpush
