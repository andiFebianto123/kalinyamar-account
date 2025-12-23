@php
	// if not otherwise specified, the hidden input should take up no space in the form
  $field['wrapper'] = $field['wrapper'] ?? $field['wrapperAttributes'] ?? [];
  $field['wrapper']['class'] = $field['wrapper']['class'] ?? "hidden";
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
        SIAOPS.setAttribute('logic_account_transaction', function(){
            return {
                form_type : "{{ $crud->getActionMethod() }}",
                logicFormula: function(){
                    var form = (this.form_type == 'create') ? '#form-create' : '#form-edit';
                },
                load: function(){
                    var instance = this;
                    var form = (this.form_type == 'create') ? '#form-create' : '#form-edit';
                    $(form+' select[name="kdp"]').off('select2:select').on('select2:select', function (e) {
                        var id = e.params.data.id; // id invoice
                        $.ajax({
                            url: "{{ url($crud->route) }}/get-invoice?id=" + id,
                            type: 'GET',
                            dataType: 'json',
                            success: function (data) {
                                var id = data.id;
                                var invoice_number = data.invoice_number;
                                var job_name = data.job_name;
                                var selectedOption = new Option(invoice_number, id, true, true);
                                setInputNumber(form + ' #nominal_transaction_masked', data.price_total);
                                $(form+' select[name="no_invoice"]').append(selectedOption).trigger('change');
                                $(form+' input[name="job_name"]').val(job_name);
                            }
                        })
                    });

                    $(form+' select[name="no_invoice"]').off('select2:select').on('select2:select', function (e) {
                        var id = e.params.data.id; // id invoice
                        $.ajax({
                            url: "{{ url($crud->route) }}/get-invoice?id=" + id,
                            type: 'GET',
                            dataType: 'json',
                            success: function (data) {
                                var id = data.id;
                                var invoice_number = data.kdp;
                                var job_name = data.job_name;
                                var selectedOption = new Option(invoice_number, id, true, true);
                                setInputNumber(form + ' #nominal_transaction_masked', data.price_total);
                                $(form+' select[name="kdp"]').append(selectedOption).trigger('change');
                                $(form+' input[name="job_name"]').val(job_name);
                            }
                        })
                    });
                }
            }
        });
        SIAOPS.getAttribute('logic_account_transaction').load();
    </script>
@endpush
