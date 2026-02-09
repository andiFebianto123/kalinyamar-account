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
                    eventEmitter.on("crudTable-filter_voucher_plugin_load", function(data){
                        instance.refresh();
                    });
                },
                refresh: function(){
                    var instance = this;
                    $.ajax({
                        url: "{{ url($crud->route.'/total') }}",
                        type: 'GET',
                        data: {
                            ...window.filter_tables,
                            search: window.filterValues,
                        },
                        typeData: 'json',
                        success: function (result) {
                            $('#panel-voucher').html(`
                                <div class="d-flex justify-content-between">
                                    <div class="p-2 bd-highlight"><strong>{{trans('backpack::crud.voucher.total_exclude_ppn')}} : ${result.total_exclude_ppn}</strong></div>
                                    <div class="p-2 bd-highlight"><strong>{{trans('backpack::crud.voucher.total_include_ppn')}} : ${result.total_include_ppn}</strong></div>
                                    <div class="p-2 bd-highlight"><strong>{{trans('backpack::crud.voucher.total_transfer_value')}} : ${result.total_nilai_transfer}</strong></div>
                                </div>
                            `);
                        },
                        error: function (xhr, status, error) {
                            console.error(xhr);
                            alert('An error occurred while loading the create form.');
                        }
                    });
                },
                load: function(){
                    var instance = this;
                    instance.eventLoader();
                    instance.refresh();
                }
            }
        });

        SIAOPS.getAttribute('voucher_plugin').load();

    });
</script>
@endpush
