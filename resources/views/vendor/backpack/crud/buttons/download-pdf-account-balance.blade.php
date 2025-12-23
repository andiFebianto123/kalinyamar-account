<button id="btn-export-pdf-account-balance" class="btn btn-primary">
    <i class="la la-file-download"></i> PDF
</button>

@push('after_scripts')
    {{-- <script>
        $('#btn-export-pdf-po').click(async function (){
            const activeTab = $('#po_tab .card-body .nav-tabs .nav-link.active');
            setLoadingButton("#btn-export-pdf-po", true);

            const {response, errors} = await API_REQUEST("DOWNLOAD", "{{ backpack_url('/vendor/download-po-pdf') }}", {
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
                setLoadingButton("#btn-export-pdf-po", false);
            }else if(response){
                let result = await response;
                setLoadingButton("#btn-export-pdf-po", false);

                const url = window.URL.createObjectURL(result);
                const a = document.createElement('a');
                a.href = url;

                // Nama file default - kamu bisa set manual atau ambil dari response header (opsional)
                a.download = 'document-subkon-po.pdf';
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);

            }
        });
    </script> --}}
@endpush
