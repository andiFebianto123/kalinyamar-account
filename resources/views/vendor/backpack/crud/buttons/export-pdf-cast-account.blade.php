<button id="btn-export-pdf-cast-account" class="btn btn-primary">
    <i class="la la-file-download"></i> PDF
</button>

@push('after_scripts')
    <script>
        $(function() {
            $('#btn-export-pdf-cast-account').click(async function (){
                setLoadingButton("#btn-export-pdf-cast-account", true);

                var urlParams = new URLSearchParams(window.location.search);
                var filterYear = urlParams.get('filter_year') || (window.filter_tables && window.filter_tables.filter_year) || 'all';
                
                var url_export = "{{ url($crud->route.'/export-pdf') }}?export=1&filter_year=" + filterYear;
                var title_export = "{{ $crud->file_title_export_pdf ?? 'Laporan_rekening_kas.pdf' }}";

                const {response, errors} = await API_REQUEST("DOWNLOAD", url_export);

                if(errors){
                    swal({
                        title: "Error",
                        text: "Gagal mengunduh file PDF",
                        icon: "error",
                        timer: 4000,
                        buttons: false,
                    });
                    setLoadingButton("#btn-export-pdf-cast-account", false);
                } else if(response){
                    let result = await response;
                    setLoadingButton("#btn-export-pdf-cast-account", false);

                    const url = window.URL.createObjectURL(result);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = title_export;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    window.URL.revokeObjectURL(url);
                }
            });
        });
    </script>
@endpush
