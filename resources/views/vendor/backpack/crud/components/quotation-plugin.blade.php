@push('after_scripts')
<script>
    if(SIAOPS.getAttribute('export') == null){
        SIAOPS.setAttribute('export', function(){
            return {
                url_pdf: "",
                title_pdf: "",
                url_excel: "",
                title_excel: "",
            }
        });
    }
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
                actionLoad: function(){
                    $('#btn-export-pdf').click(async function (){
                        // const activeTab = $('#po_tab .card-body .nav-tabs .nav-link.active');
                        setLoadingButton("#btn-export-pdf", true);

                        var get_url_export = SIAOPS.getAttribute('export').url_pdf;
                        var get_title_export = SIAOPS.getAttribute('export').title_pdf;

                        if(get_url_export == ''){
                            setLoadingButton("#btn-export-pdf", false);
                            swal({
                                title: "Error",
                                text: "Internet server error",
                                icon: "error",
                                timer: 4000,
                                buttons: false,
                            });
                            return;
                        }

                        const {response, errors} = await API_REQUEST("DOWNLOAD", get_url_export);

                        if(errors){
                            var errorResponse = await errors;
                            swal({
                                title: "Error",
                                text: "Internet server error",
                                icon: "error",
                                timer: 4000,
                                buttons: false,
                            });
                            setLoadingButton("#btn-export-pdf", false);
                        }else if(response){
                            let result = await response;
                            setLoadingButton("#btn-export-pdf", false);

                            const url = window.URL.createObjectURL(result);
                            const a = document.createElement('a');
                            a.href = url;

                            // Nama file default - kamu bisa set manual atau ambil dari response header (opsional)
                            a.download = get_title_export;
                            document.body.appendChild(a);
                            a.click();
                            a.remove();
                            window.URL.revokeObjectURL(url);

                        }
                    });
                    $('#btn-export-excel').click(async function (){
                        // const activeTab = $('#po_tab .card-body .nav-tabs .nav-link.active');
                        setLoadingButton("#btn-export-excel", true);

                        var get_url_export = SIAOPS.getAttribute('export').url_excel;
                        var get_title_export = SIAOPS.getAttribute('export').title_excel;

                        if(get_url_export == ''){
                            setLoadingButton("#btn-export-excel", false);
                            swal({
                                title: "Error",
                                text: "Internet server error",
                                icon: "error",
                                timer: 4000,
                                buttons: false,
                            });
                            return;
                        }

                        const {response, errors} = await API_REQUEST("DOWNLOAD", get_url_export);
                        if(errors){
                            var errorResponse = await errors;
                            swal({
                                title: "Error",
                                text: "Internet server error",
                                icon: "error",
                                timer: 4000,
                                buttons: false,
                            });
                            setLoadingButton("#btn-export-excel", false);
                        }else if(response){
                            let result = await response;
                            setLoadingButton("#btn-export-excel", false);

                            const url = window.URL.createObjectURL(result);
                            const a = document.createElement('a');
                            a.href = url;

                            // Nama file default - kamu bisa set manual atau ambil dari response header (opsional)
                            a.download = get_title_export;
                            document.body.appendChild(a);
                            a.click();
                            a.remove();
                            window.URL.revokeObjectURL(url);

                        }
                    });
                },
                load: function(){
                    var instance = this;
                    instance.eventLoader();
                    // $('#panel-quotation').html(`
                    //     <div class="d-flex justify-content-end">
                    //         <div class="p-2 bd-highlight">
                    //             <a href="javascript:void(0)">
                    //             <button id="btn-export-pdf" class="btn btn-primary">
                    //                 <i class="la la-file-download"></i> PDF
                    //             </button>
                    //             </a>
                    //             <a href="javascript:void(0)">
                    //             <button id="btn-export-pdf" class="btn btn-primary">
                    //                 <i class="la la-file-download"></i> PDF
                    //             </button>
                    //             </a>
                    //         </div>
                    //     </div>
                    // `);
                    // $('#panel-quotation_check').html(`
                    //     <div class="d-flex justify-content-end">
                    //         <div class="p-2 bd-highlight">
                    //             <a href="javascript:void(0)">
                    //             <button id="btn-export-pdf" class="btn btn-primary">
                    //                 <i class="la la-file-download"></i> PDF
                    //             </button>
                    //             </a>
                    //             <a href="javascript:void(0)">
                    //             <button id="btn-export-excel" class="btn btn-primary">
                    //                 <i class="la la-file-download"></i> Excel
                    //             </button>
                    //             </a>
                    //         </div>
                    //     </div>
                    // `);
                    // instance.actionLoad();
                }
            }
        });
        SIAOPS.getAttribute('quotation_plugin').load();
    });
</script>
@endpush
