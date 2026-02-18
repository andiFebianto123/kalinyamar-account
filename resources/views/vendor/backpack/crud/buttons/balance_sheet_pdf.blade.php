<button id="btn-export-pdf-custom" class="btn btn-primary">
    <i class="la la-file-pdf"></i> PDF
</button>

@push('after_scripts')
<script>
    $('#btn-export-pdf-custom').click(async function () {
        setLoadingButton("#btn-export-pdf-custom", true);
        
        // Take active query string from browser (year/quarter filter)
        let searchParams = new URLSearchParams(window.location.search);
        searchParams.set('export', '1');
        
        let url = "{!! url($crud->route.'/export-pdf') !!}?" + searchParams.toString();
        
        const {response, errors} = await API_REQUEST("DOWNLOAD", url);
        if(response){
            let result = await response;
            const blobUrl = window.URL.createObjectURL(result);
            const a = document.createElement('a');
            a.href = blobUrl;
            a.download = "{{ $crud->file_title_export_pdf ?? 'Laporan_Neraca.pdf' }}";
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(blobUrl);
        }
        setLoadingButton("#btn-export-pdf-custom", false);
    });
</script>
@endpush
