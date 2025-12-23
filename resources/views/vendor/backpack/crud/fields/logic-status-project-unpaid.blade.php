@php
	// if not otherwise specified, the hidden input should take up no space in the form
  $field['wrapper'] = $field['wrapper'] ?? $field['wrapperAttributes'] ?? [];
  $field['wrapper']['class'] = $field['wrapper']['class'] ?? "hidden";
  $set_value = (isset($no_po_spk)) ? $no_po_spk : null;
@endphp

{{-- hidden input --}}
@include('crud::fields.inc.wrapper_start')
  {{-- <input
  	type="hidden"
    name="{{ $field['name'] }}"
    value="{{ old_empty_or_null($field['name'], '') ??  $field['value'] ?? $field['default'] ?? '' }}"
    @include('crud::fields.inc.attributes')
  	> --}}
@include('crud::fields.inc.wrapper_end')

@push('crud_fields_scripts')
<script>
    SIAOPS.setAttribute('logic_unpaid_project', function(){
        return {
            form_type : "{{ $crud->getActionMethod() }}",
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

                $(form+' #invoice_date').change(function(){
                    var invoice_date = $(form+' #invoice_date').val();
                    var total_day = hitungDurasiHari(invoice_date);
                    $(form+' input[name="total_progress_day"]').val(total_day);
                });

            }
        }
    });
    SIAOPS.getAttribute('logic_unpaid_project').load();
</script>
@endpush
