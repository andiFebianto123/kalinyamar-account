@push('inline_scripts')
    @once
        <style>
            .dataTables_wrapper .dataTables_scrollHead table thead tr th {
                background-color: #FCD72D !important;
            }
        </style>
    @endonce
@endpush

@push('after_scripts')
    @once
        <script>
            SIAOPS.setAttribute('export', function(){
                return {
                    url_pdf: "{{ url($crud->route.'/export-pdf') }}",
                    title_pdf: "Project-report.pdf",
                    url_excel: "{{ url($crud->route.'/export-excel') }}",
                    title_excel: "Project-report.xlsx",
                }
            });
        </script>
    @endonce
@endpush
