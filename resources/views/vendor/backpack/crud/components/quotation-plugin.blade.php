@push('after_scripts')
<script>
    $(function(){
        SIAOPS.setAttribute('quotation_plugin', function(){
            return {
                name: 'quotation_plugin',
                accounts_compact:[],
                url_download_quotation_pdf: "{{url($crud_custom->route.'/download/quotation/pdf')}}",
                url_download_quotation_excel: "{{url($crud_custom->route.'/download/quotation/excel')}}",
                url_download_quotation_check_pdf: "{{url($crud_custom->route.'/download/quotation_check/pdf')}}",
                url_download_quotation_check_excel: "{{url($crud_custom->route.'/download/quotation_check/excel')}}",
                eventLoader: async function(){
                    var instance = this;
                    eventEmitter.on("crudTable-quotation_plugin_load", function(data){
                        instance.load();
                    });
                },
                load: function(){
                    var instance = this;
                    instance.eventLoader();
                    $('#panel-quotation').html(`
                        <div class="d-flex justify-content-end">
                            <div class="p-2 bd-highlight">
                                <a href="${instance.url_download_quotation_pdf}">
                                <button id="btn-export-pdf-account-balance" class="btn btn-primary">
                                    <i class="la la-file-download"></i> PDF
                                </button>
                                </a>
                                <a href="${instance.url_download_quotation_excel}">
                                <button id="btn-export-excel-po" class="btn btn-primary">
                                    <i class="la la-file-download"></i> Excel
                                </button>
                                </a>
                            </div>
                        </div>
                    `);
                    $('#panel-quotation_check').html(`
                        <div class="d-flex justify-content-end">
                            <div class="p-2 bd-highlight">
                                <a href="${instance.url_download_quotation_check_pdf}">
                                <button id="btn-export-pdf-account-balance" class="btn btn-primary">
                                    <i class="la la-file-download"></i> PDF
                                </button>
                                </a>
                                <a href="${instance.url_download_quotation_check_excel}">
                                <button id="btn-export-excel-po" class="btn btn-primary">
                                    <i class="la la-file-download"></i> Excel
                                </button>
                                </a>
                            </div>
                        </div>
                    `);
                }
            }
        });
        SIAOPS.getAttribute('quotation_plugin').load();
    });
</script>
@endpush
