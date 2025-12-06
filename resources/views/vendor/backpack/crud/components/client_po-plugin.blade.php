@push('after_scripts')
<script>
    $(function(){
        SIAOPS.setAttribute('client_po_plugin', function(){
            return {
                name: 'client_po_plugin',
                accounts_compact:[],
                eventLoader: async function(){
                    var instance = this;
                    eventEmitter.on("crudTable-filter_client_po_plugin_load", function(data){
                        instance.refresh();
                    });
                },
                filterParameters: function(){
                    var getI = SIAOPS.getAttribute('crudTable-client_po');
                    var get_url = getI.table.ajax.url();
                    const params = new URL(get_url).searchParams;
                    const obj = Object.fromEntries(params.entries());
                    return obj;
                },
                refresh: function(){
                    var instance = this;
                    $.ajax({
                        url: "{{ url($crud->route.'/total') }}",
                        type: 'GET',
                        data: {
                            search: window.filterValues,
                            ...instance.filterParameters()
                        },
                        typeData: 'json',
                        success: function (result) {
                            $('#panel-client_po').html(`
                                <div class="d-flex justify-content-between">
                                    <div class="p-2 bd-highlight"><strong class='fs-6'>{{trans('backpack::crud.client_po.count_exclude_ppn')}} : ${result.total_job_value}</strong></div>
                                    <div class="p-2 bd-highlight"><strong class='fs-6'>{{trans('backpack::crud.client_po.count_include_ppn')}} : ${result.total_job_value_ppn}</strong></div>
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
                    setTimeout(() => {
                        $("#crudTable-client_po thead tr.filters th").eq(13).children('input').remove();
                    }, 400);
                }
            }
        });

        SIAOPS.getAttribute('client_po_plugin').load();

    });
</script>
@endpush
