{{-- @push('inline_scripts')

@endpush --}}

@push('after_scripts')
<script>
    $(function(){
        SIAOPS.setAttribute('purchase_order_plugin', function(){
            return {
                name: 'purchase_order_plugin',
                accounts_compact:[],
                eventLoader: async function(){
                    var instance = this;
                    eventEmitter.on("crudTable-filter-purchase_order_plugin_load", function(data){
                        instance.refresh();
                    });
                },
                refresh: function(){
                    var instance = this;
                    var params_url = MakeParamUrl(window.filter_tables || {});
                    var url = "{{url($crud->route.'/total')}}?permalink=1"+params_url;
                    $.ajax({
                        url: url,
                        type: 'GET',
                        typeData: 'json',
                        success: function (result) {
                            $('#str_list_open').html(`{{trans('backpack::crud.po.tab.title_total_incl_ppn')}}: ${result.total_open}`);
                            $('#str_list_close').html(`{{trans('backpack::crud.po.tab.title_total_incl_ppn')}}: ${result.total_closed}`);

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
        SIAOPS.getAttribute('purchase_order_plugin').load();
    });
</script>
@endpush
