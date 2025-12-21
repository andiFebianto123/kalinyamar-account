@push('after_scripts')
<script>
    $(function(){
        SIAOPS.setAttribute('invoice_plugin', function(){
            return {
                name: 'invoice_plugin',
                accounts_compact:[],
                eventLoader: async function(){
                    var instance = this;
                    eventEmitter.on("crudTable-filter_invoice_plugin_load", function(data){
                        instance.refresh();
                    });
                },
                filterParameters: function(){
                    if(window.filter_tables){
                        return window.filter_tables;
                    }
                    return {};
                },
                refresh: function(){
                    var instance = this;
                    setTimeout(() => {
                        $("#crudTable-invoice thead tr.filters th").eq(4).children('input').remove();
                        $("#crudTable-invoice thead tr.filters th").eq(6).children('input').remove();
                        $("#crudTable-invoice thead tr.filters th").eq(10).children('input').remove();
                        $("#crudTable-invoice thead tr.filters th").eq(11).children('input').remove();
                        $("#crudTable-invoice thead tr.filters th").eq(12).children('input').remove();
                    }, 400);
                    $.ajax({
                        url: "{{ url($crud->route.'/total') }}",
                        type: 'GET',
                        data: {
                            search: window.filterValues,
                            ...instance.filterParameters()
                        },
                        typeData: 'json',
                        success: function (result) {
                            $('#panel-invoice').html(`
                                <div class="d-flex justify-content-between">
                                    <div class="p-2 bd-highlight"><strong class='fs-6'>{{trans('backpack::crud.voucher.total_exclude_ppn')}} : ${result.total_price_exclude_ppn}</strong></div>
                                    <div class="p-2 bd-highlight"><strong class='fs-6'>{{trans('backpack::crud.voucher.total_include_ppn')}} : ${result.total_price_include_ppn}</strong></div>
                                    <div class="p-2 bd-highlight"></div>
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
                    instance.eventLoader()
                    // instance.refresh();
                }
            }
        });

        SIAOPS.getAttribute('invoice_plugin').load();

    });
</script>
@endpush
