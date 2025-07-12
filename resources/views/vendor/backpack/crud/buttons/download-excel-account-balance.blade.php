<button id="btn-export-excel-account-balance" class="btn btn-primary">
    <i class="la la-file-download"></i> Excel
</button>

@push('after_scripts')
    {{-- <script>
        $('#btn-export-excel-po').click(async function (){
            const activeTab = $('#po_tab .card-body .nav-tabs .nav-link.active');
            setLoadingButton("#btn-export-excel-po", true);

            const {response, errors} = await API_REQUEST("DOWNLOAD", "{{ backpack_url('/vendor/download-po') }}", {
                type: activeTab.data('alt-name'),
            });
            if(errors){
                var errorResponse = await errors;
                swal({
                    title: "Error",
                    text: "Internet server error",
                    icon: "error",
                    timer: 4000,
                    buttons: false,
                });
                setLoadingButton("#btn-export-excel-po", false);
            }else if(response){
                let result = await response;
                setLoadingButton("#btn-export-excel-po", false);

                const url = window.URL.createObjectURL(result);
                const a = document.createElement('a');
                a.href = url;

                // Nama file default - kamu bisa set manual atau ambil dari response header (opsional)
                a.download = 'document-subkon-po.xlsx';
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);

            }
        });
    </script> --}}
@endpush
