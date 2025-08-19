@php
	// if not otherwise specified, the hidden input should take up no space in the form
  $field['wrapper'] = $field['wrapper'] ?? $field['wrapperAttributes'] ?? [];
  $field['wrapper']['class'] = $field['wrapper']['class'] ?? "hidden";
  $set_value = (isset($entry)) ? $entry : null;
@endphp

{{-- hidden input --}}
@include('crud::fields.inc.wrapper_start')
  <input type="hidden" name="type" />
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
        SIAOPS.setAttribute('logic_asset', function(){
            return {
                form_type : "{{ $crud->getActionMethod() }}",
                logicFormula: function(){
                    var form = (this.form_type == 'create') ? '#form-create' : '#form-edit';
                    // var bill_value = getInputNumber(form+' #bill_value');
                    // var ppn = getInputNumber(form+' input[name="tax_ppn"]');

                    // var nilai_ppn = (ppn == 0) ? 0 : (bill_value * (ppn / 100));

                    // var total = bill_value + nilai_ppn;
                    // setInputNumber(form+ ' #total_masked', total);



                },
                load: function(){
                    var instance = this;
                    var form = (this.form_type == 'create') ? '#form-create' : '#form-edit';

                    @if ($set_value != null)
                        // var data_po_spk = {!! json_encode($set_value) !!};
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

                    @endif

                    $(form+ ' select[name="client_po_id"]').off('select2:select').on('select2:select', function (e) {
                        var id = e.params.data.id;
                        console.log(e.params.data);
                        $(form+' input[name="job_code"]').val(e.params.data.work_code);
                        $(form+' input[name="voucher_id"]').val(e.params.data.voucher_id);
                        setInputNumber(form+ ' #total_project_masked', e.params.data.data.reference.price_total);
                        setInputNumber(form+' #price_profit_lost_project_masked', e.params.data.data.reference.profit_and_lost_final);
                        // $.ajax({
                        //     url: "{{ url($crud->route) }}/get_client_selected_ajax?id=" + id + "&type=" + type,
                        //     type: 'GET',
                        //     dataType: 'json',
                        //     success: function (data) {

                        //         var work_code_text = `${data.work_code} (${data.type})`;

                        //         var work_option = new Option(work_code_text, data.id, true, true);
                        //         $(form+ ' select[name="reference_id"]').append(work_option).trigger('change');
                        //         // job_code

                        //     }
                        // });
                    });
                }
            }
        });
        SIAOPS.getAttribute('logic_asset').load();
    </script>
@endpush
