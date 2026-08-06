@php
	// if not otherwise specified, the hidden input should take up no space in the form
  $field['wrapper'] = $field['wrapper'] ?? $field['wrapperAttributes'] ?? [];
  $field['wrapper']['class'] = $field['wrapper']['class'] ?? "hidden";
@endphp

{{-- hidden input --}}
@include('crud::fields.inc.wrapper_start')
@include('crud::fields.inc.wrapper_end')

@push('crud_fields_scripts')
<script>
    SIAOPS.setAttribute('logic_cast_loan', function(){
        return {
            form_type : "{{ $crud->getActionMethod() }}",
            load: function(){

                var instance = this;
                var form = (this.form_type == 'create') ? '#form-create' : '#form-edit';

                $(form+' #balance_information').hide();

                $(form+' select[name="loan_transaction_flag_id"]').change(function(){
                    var val = $(form+' select[name="loan_transaction_flag_id"]').val();
                    if (!val) {
                        $(form+' #balance_information').hide();
                        return;
                    }
                    $.ajax({
                        url: "{{ url('admin/cash-flow/cast-account-loan/get-loan-balance') }}",
                        type: "GET",
                        data: {
                            loan_transaction_flag_id: val
                        },
                        success: function(response){
                            if(response.status == false){
                                $(form+' #balance_information').hide();
                            }else{
                                $(form+' #balance_information').show();
                                $(form+' .total_saldo').html(response.remaining_balance);
                            }
                        }
                    });
                });

                @if (isset($entry) && $entry->reference)
                    var data_entry = {!! json_encode($entry) !!};
                    if (data_entry.reference && data_entry.reference.id) {
                        var flagText = data_entry.reference.kode || data_entry.reference.code || data_entry.reference.id;
                        var selectedOption = new Option(flagText, data_entry.reference.id, true, true);
                        $(form+' select[name="loan_transaction_flag_id"]').append(selectedOption).trigger('change');
                    }
                @endif

            }
        }
    });
    SIAOPS.getAttribute('logic_cast_loan').load();
</script>
@endpush
