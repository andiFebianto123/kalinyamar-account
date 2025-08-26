<button id="btn-export-excel" class="btn btn-primary">
    <i class="la la-file-download"></i> Excel
</button>

@push('after_scripts')
    <script>
        if(SIAOPS.getAttribute('export') == null){
            SIAOPS.setAttribute('export', function(){
                return {
                    url_pdf: "{{ url($crud->route.'/export-pdf') }}{{ $crud->param_uri_export ?? '' }}",
                    title_pdf: "{{ $crud->file_title_export_pdf ?? 'report.pdf' }}",
                    url_excel: "{{ url($crud->route.'/export-excel') }}{{ $crud->param_uri_export ?? '' }}",
                    title_excel: "{{ $crud->file_title_export_excel ?? 'report.xlsx' }}",
                }
            });
        }

        $('#btn-export-excel').click(async function (){
            // const activeTab = $('#po_tab .card-body .nav-tabs .nav-link.active');
            setLoadingButton("#btn-export-excel", true);

            var get_url_export = SIAOPS.getAttribute('export').url_excel;
            var get_title_export = SIAOPS.getAttribute('export').title_excel;
            var params_url = MakeParamUrl(window.filter_tables || {});

            var url_export_with_params = get_url_export + params_url;

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

            const {response, errors} = await API_REQUEST("DOWNLOAD", url_export_with_params);
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
