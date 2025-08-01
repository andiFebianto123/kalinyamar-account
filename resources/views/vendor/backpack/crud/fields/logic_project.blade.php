@php
	// if not otherwise specified, the hidden input should take up no space in the form
  $field['wrapper'] = $field['wrapper'] ?? $field['wrapperAttributes'] ?? [];
  $field['wrapper']['class'] = $field['wrapper']['class'] ?? "hidden";
  $set_value = (isset($no_po_spk)) ? $no_po_spk : null;
@endphp

{{-- hidden input --}}
@include('crud::fields.inc.wrapper_start')
  <input type="hidden" name="no_type" />
  <input type="hidden" name="actual_price_ppn" />
  <input type="hidden" name="actual_price_total_include_ppn" />
  <input type="hidden" name="actual_duration">
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
                logicFormulaNoPO: function(){
                    var form = (this.form_type == 'create') ? '#form-create' : '#form-edit';
                    var nilai_exclude_ppn = getInputNumber(form+' #price_total_exclude_ppn');
                    var ppn = getInputNumber(form+' select[name="tax_ppn"]');

                    var nilai_ppn = (ppn == 0) ? 0 : (nilai_exclude_ppn * (ppn / 100));
                    var total_with_ppn = nilai_ppn + nilai_exclude_ppn;
                    setInputNumber(form+' #price_ppn_masked', nilai_ppn);
                    setInputNumber(form+' #price_total_include_ppn_masked', total_with_ppn);

                    var start_date = $(form + ' input[name="start_date"]').val();
                    var end_date = $(form + ' input[name="end_date"]').val();

                    const start = new Date(start_date);
                    const end = new Date(end_date);

                    const diffMs = end - start;

                    const totalDays = Math.floor(diffMs / (1000 * 60 * 60 * 24)) + 1;
                    // $(form+ ' input[name="duration"]').val(totalDays);

                    $(form+' input[name="actual_price_ppn"]').val(nilai_ppn);
                    $(form+' input[name="actual_price_total_include_ppn"]').val(total_with_ppn);
                    $(form+' input[name="actual_duration"]').val(totalDays);

                },
                load: function(){
                    var instance = this;
                    var form = (this.form_type == 'create') ? '#form-create' : '#form-edit';

                    function hitungDurasiHari(actualEndDate) {
                        const [day, month, year] = actualEndDate.split('/');

                        const endDate = new Date(year, month - 1, day);

                        const today = new Date();

                        const selisihMs = today - endDate;

                        const durasiHari = Math.floor(selisihMs / (1000 * 60 * 60 * 24));

                        return durasiHari;
                    }

                    @if ($set_value != null)
                    var data_po_spk = {!! json_encode($set_value) !!};
                        console.log(data_po_spk);
                        $(form+' input[name="actual_price_ppn"]').val(data_po_spk.actual_price_ppn);
                        $(form+' input[name="actual_price_total_include_ppn"]').val(data_po_spk.actual_price_total_include_ppn);
                        $(form+' input[name="actual_duration"]').val(data_po_spk.actual_duration);
                        $(form+' input[name="no_type"]').val(data_po_spk.no_type);

                        if(data_po_spk.po_status == 1){
                            $(form+' .no_po_spk').hide();
                            $(form+' .po_date').hide();
                            $(form+' .received_po_date').hide();
                            $(form+' .space').hide();
                        }else if(data_po_spk.po_status == 0){
                            var selectedOption = new Option(data_po_spk.no_po_spk.no_po_spk, data_po_spk.no_po_spk.id, true, true);
                            $(form+ ' select[name="no_po_spk"]').append(selectedOption).trigger('change');
                            $(form+' .no_po_spk').show();
                            $(form+' .po_date').show();
                            $(form+' .received_po_date').show();
                            $(form+' .space').show();
                        }
                    @endif

                    $(form+ ' select[name="no_po_spk"]').off('select2:select').on('select2:select', function (e) {
                        var id = e.params.data.id;

                        var data = e.params.data.data;

                        $(form+ ' #po_date').datepicker('setDate', data.date_po_spk_str);
                        $(form+' input[name="no_type"]').val(data.type);
                    });

                    $(form+' #price_total_exclude_ppn_masked').on('keyup', function(){
                        instance.logicFormulaNoPO();
                    });

                    $(form + ' select[name="tax_ppn"]').off('select2:select').on('select2:select', function (e) {
                        instance.logicFormulaNoPO();
                    });

                    $(form + " #start_date_end_date").on('apply.daterangepicker hide.daterangepicker', function(e, picker){
                        instance.logicFormulaNoPO();
                    });

                    $(form+' input[name="po_status_check"]').change(function() {
                        if ($(this).is(":checked")) {
                            $(form+' .no_po_spk').hide();
                            $(form+' .po_date').hide();
                            $(form+' .received_po_date').hide();
                            $(form+' .space').hide();
                        } else {
                            $(form+' .no_po_spk').show();
                            $(form+' .po_date').show();
                            $(form+' .received_po_date').show();
                            $(form+' .space').show();
                        }
                    })

                    $(form+' #actual_end_date').change(function(){
                        var end_date = $(form+' #actual_end_date').val();
                        $(form+ ' input[name="duration"]').val(hitungDurasiHari(end_date));
                    });


                }
            }
        });
        SIAOPS.getAttribute('logic_asset').load();
    </script>
@endpush
