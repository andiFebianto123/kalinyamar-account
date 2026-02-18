<button id="btn-export-excel-custom" class="btn btn-primary">
    <i class="la la-file-excel"></i> Excel
</button>

@push('after_scripts')
<script>
    $('#btn-export-excel-custom').click(async function () {
        setLoadingButton("#btn-export-excel-custom", true);
        
        // Take active query string from browser (year/quarter filter)
        let searchParams = new URLSearchParams(window.location.search);
        searchParams.set('export', '1');
        
        let url = "{!! url($crud->route.'/export-excel') !!}?" + searchParams.toString();
        
        const {response, errors} = await API_REQUEST("DOWNLOAD", url);
        if(response){
            let result = await response;
            const blobUrl = window.URL.createObjectURL(result);
            const a = document.createElement('a');
            a.href = blobUrl;
            a.download = "{{ $crud->file_title_export_excel ?? 'Laporan_Neraca.xlsx' }}";
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(blobUrl);
        }
        setLoadingButton("#btn-export-excel-custom", false);
    });
</script>
@endpush
