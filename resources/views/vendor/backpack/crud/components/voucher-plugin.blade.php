@push('inline_scripts')
    @once
        <style>
            #crudTable-voucher_wrapper .dataTables_scrollHead table thead tr th {
                background-color: #FCD72D !important;
            }
        </style>
    @endonce
@endpush

@push('after_scripts')
<script>
    $(function(){
        SIAOPS.setAttribute('voucher_plugin', function(){
            return {
                name: 'voucher_plugin',
                accounts_compact:[],
                eventLoader: async function(){
                    var instance = this;
                    eventEmitter.on("crudTable-voucher_plugin_load", function(data){
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
                            $('#panel-voucher').html(`
                                <div class="d-flex justify-content-between">
                                    <div class="p-2 bd-highlight"><strong>{{trans('backpack::crud.voucher.total_exclude_ppn')}} : Rp${result.total_exclude_ppn}</strong></div>
                                    <div class="p-2 bd-highlight"><strong>{{trans('backpack::crud.voucher.total_include_ppn')}} : Rp${result.total_include_ppn}</strong></div>
                                    <div class="p-2 bd-highlight"><strong>{{trans('backpack::crud.voucher.total_transfer_value')}} : Rp${result.total_nilai_transfer}</strong></div>
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

        SIAOPS.getAttribute('voucher_plugin').load();

    });
</script>
@endpush
