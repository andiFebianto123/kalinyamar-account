@push('inline_scripts')
    @once
        <style>
            #crudTable-voucher_payment_non_rutin_wrapper .dataTables_scrollHead table thead tr th {
                background-color: #FCD72D !important;
            }

            #crudTable-voucher_payment_plan_non_rutin_wrapper .dataTables_scrollHead table thead tr th {
                background-color: #FCD72D !important;
            }

            #crudTable-voucher_payment_rutin_wrapper .dataTables_scrollHead table thead tr th {
                background-color: #FCD72D !important;
            }

            #crudTable-voucher_payment_plan_rutin_wrapper .dataTables_scrollHead table thead tr th {
                background-color: #FCD72D !important;
            }
        </style>
    @endonce
@endpush

@push('after_scripts')
<script>
    $(function(){
        SIAOPS.setAttribute('voucher_payment_plugin', function(){
            return {
                name: 'voucher_plugin',
                accounts_compact:[],
                eventLoader: async function(){
                    var instance = this;
                    eventEmitter.on("crudTable-voucher_payment_plugin_load", function(data){
                        instance.load();
                    });
                },
                load: function(){
                    var instance = this;
                    instance.eventLoader();
                    $.ajax({
                        url: "{{ url($crud->route.'/total') }}",
                        type: 'GET',
                        typeData: 'json',
                        success: function (result) {
                            $('#panel-voucher_payment_non_rutin').html(`
                                <div class="d-flex justify-content-start">
                                    <div class="p-2 bd-highlight"><strong>{{trans('backpack::crud.voucher_payment.total_payment_value')}} : ${result.voucher_payment_non_rutin_total}</strong></div>
                                </div>
                            `);

                            $('#panel-voucher_payment_plan_non_rutin').html(`
                                <div class="d-flex justify-content-start">
                                    <div class="p-2 bd-highlight"><strong>{{trans('backpack::crud.voucher_payment.total_payment_approve_value')}} : ${result.voucher_payment_plan_non_rutin_total}</strong></div>
                                </div>
                            `);

                            $('#panel-voucher_payment_rutin').html(`
                                <div class="d-flex justify-content-start">
                                    <div class="p-2 bd-highlight"><strong>{{trans('backpack::crud.voucher_payment.total_payment_value')}} : ${result.voucher_payment_rutin_total}</strong></div>
                                </div>
                            `);

                            $('#panel-voucher_payment_plan_rutin').html(`
                                <div class="d-flex justify-content-start">
                                    <div class="p-2 bd-highlight"><strong>{{trans('backpack::crud.voucher_payment.total_payment_approve_value')}} : ${result.voucher_payment_plan_rutin_total}</strong></div>
                                </div>
                            `);
                        },
                        error: function (xhr, status, error) {
                            console.error(xhr);
                            alert('An error occurred while loading the create form.');
                        }
                    });
                }
            }
        });

        SIAOPS.getAttribute('voucher_payment_plugin').load();

    });
</script>
@endpush
