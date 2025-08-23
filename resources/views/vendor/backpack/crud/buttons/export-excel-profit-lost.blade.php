<button id="btn-export-excel" class="btn btn-primary">
    <i class="la la-file-download"></i> Excel
</button>

@push('after_scripts')
    <script>

        if(SIAOPS.getAttribute('export') == null){
            SIAOPS.setAttribute('export', function(){
                return {
                    url_pdf: "{{ url($crud->route.'/export-detail-pdf') }}?id={{ $crud->id_profit_lost }}",
                    title_pdf: "Laporan-laba-rugi.pdf",
                    url_excel: "{{ url($crud->route.'/export-detail-excel') }}?id={{ $crud->id_profit_lost }}",
                    title_excel: "Laporan-laba-rugi.xlsx",
                }
            });
        }

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
    </script>
@endpush
