{{-- @push('inline_scripts')
    @once
        <style>
            #crudTable-voucher_wrapper .dataTables_scrollHead table thead tr th {
                background-color: #FCD72D !important;
            }
        </style>
    @endonce
@endpush --}}

@push('after_scripts')
<script>
    $(function(){
        SIAOPS.setAttribute('profit_lost_plugin', function(){
            return {
                name: 'profit_lost_plugin',
                accounts_compact:[],
                eventLoader: async function(){
                    var instance = this;
                    eventEmitter.on("crudTable-filter_profit_lost_plugin_load", function(data){
                        instance.refresh();
                    });
                },
                refresh: function(){
                    var instance = this;
                    var getI = SIAOPS.getAttribute('crudTable-project');
                    var get_param = getI.table.ajax.url();
                    
                    const params = new URLSearchParams(getI.table.ajax.url());

                    var category = params.get('category');

                    $.ajax({
                        url: "{{ url($crud->route.'/total') }}",
                        type: 'GET',
                        data: {
                            search: window.filterValues,
                            category: category,
                        },
                        typeData: 'json',
                        success: function (result) {
                            $('#panel-project').html(`
                                <div class="d-flex justify-content-between">
                                    <div class="p-2 bd-highlight"><strong>{{trans('backpack::crud.voucher.total_exclude_ppn')}} : ${result.total_price_exlude_ppn}</strong></div>
                                    <div class="p-2 bd-highlight"><strong>{{trans('backpack::crud.profit_lost.total_profit_lost')}} : ${result.total_price_prift_lost_finals}</strong></div>
                                    <div class="p-2 bd-highlight"></strong></div>
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
                    // instance.refresh();
                }
            }
        });
        SIAOPS.getAttribute('profit_lost_plugin').load();
    });
</script>
@endpush
