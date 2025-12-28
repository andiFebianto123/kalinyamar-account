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
                    $.ajax({
                        url: "{{ url('admin/cash-flow/cast-account-loan/get-loan-balance') }}",
                        type: "GET",
                        data: {
                            loan_transaction_flag_id: $(form+' select[name="loan_transaction_flag_id"]').val()
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

            }
        }
    });
    SIAOPS.getAttribute('logic_cast_loan').load();
</script>
@endpush
