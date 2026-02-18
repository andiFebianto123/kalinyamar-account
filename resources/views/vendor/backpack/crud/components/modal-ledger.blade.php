<div class="d-flex justify-content-end mb-2">
    <button id="btn-export-ledger-pdf" class="btn btn-sm btn-primary me-2">
        <i class="la la-file-download"></i> PDF
    </button>
    <button id="btn-export-ledger-excel" class="btn btn-sm btn-primary">
        <i class="la la-file-download"></i> Excel
    </button>
</div>
<div class="table-responsive">
    <table id="tableLedger" class="table table-bordered table-striped w-100">
        <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Keterangan</th>
                    <th>{{ trans('backpack::crud.cash_account.field_transaction.status.enter') }}</th>
                    <th>{{ trans('backpack::crud.cash_account.field_transaction.status.out') }}</th>
                    <th>Saldo Komulatif</th>
                </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

@push('after_styles')
    {{-- DataTables CSS --}}
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
@endpush

@push('after_scripts')
    {{-- DataTables JS --}}
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        if (typeof viewLedger != 'function') {
            var currentAccountId = null;
            var currentAccountTitle = '';

            function viewLedger(id, title) {
                currentAccountId = id;
                currentAccountTitle = title;
                $('#modal_ledger .modal-title').text('Buku Besar: ' + title);
                $('#modal_ledger').modal('show');
                
                // Safe check for DataTables
                if (typeof $.fn.dataTable !== 'undefined' && $.fn.dataTable.isDataTable('#tableLedger')) {
                    $('#tableLedger').DataTable().destroy();
                }
                
                if (typeof $.fn.DataTable !== 'undefined') {
                    $('#tableLedger').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: "{{ url($crud->route . '-ledger') }}",
                            data: function(d) {
                                d._id = id;
                                d.filter_year = new URLSearchParams(window.location.search).get('filter_year');
                                d.filter_quarter = new URLSearchParams(window.location.search).get('filter_quarter');
                                return d;
                            }
                        },
                        columns: [
                            { data: 'date', name: 'date' },
                            { data: 'description', name: 'description' },
                            { data: 'debit', name: 'debit', className: 'text-end' },
                            { data: 'credit', name: 'credit', className: 'text-end' },
                            { data: 'balance', name: 'balance', className: 'text-end' }
                        ],
                        order: [[0, 'asc']],
                        pageLength: 25,
                        searching: true,
                        lengthChange: false,
                        dom: "<'row'<'col-sm-12 col-md-6'><'col-sm-12 col-md-6 text-end'f>>" +
                             "<'row'<'col-sm-12'tr>>" +
                             "<'row mt-2'<'col-sm-12 col-md-6'p><'col-sm-12 col-md-6 text-end'i>>",
                        language: {
                            emptyTable: "{{ trans('backpack::crud.emptyTable') }}",
                            info: "{{ trans('backpack::crud.info') }}",
                            infoEmpty: "{{ trans('backpack::crud.infoEmpty') }}",
                            infoFiltered: "{{ trans('backpack::crud.infoFiltered') }}",
                            infoPostFix: "{{ trans('backpack::crud.infoPostFix') }}",
                            thousands: "{{ trans('backpack::crud.thousands') }}",
                            lengthMenu: "{{ trans('backpack::crud.lengthMenu') }}",
                            loadingRecords: "{{ trans('backpack::crud.loadingRecords') }}",
                            processing: "{{ trans('backpack::crud.processing') }}",
                            search: "{{ trans('backpack::crud.search') }}",
                            zeroRecords: "{{ trans('backpack::crud.zeroRecords') }}",
                            paginate: {
                                first: "{{ trans('backpack::crud.paginate.first') }}",
                                last: "{{ trans('backpack::crud.paginate.last') }}",
                                next: "{{ trans('backpack::crud.paginate.next') }}",
                                previous: "{{ trans('backpack::crud.paginate.previous') }}"
                            },
                            aria: {
                                sortAscending: "{{ trans('backpack::crud.aria.sortAscending') }}",
                                sortDescending: "{{ trans('backpack::crud.aria.sortDescending') }}"
                            }
                        }
                    });
                } else {
                    console.error('DataTables library not loaded correctly.');
                }
            }

            $(function(){
                $('#btn-export-ledger-pdf').click(async function (){
                    if(!currentAccountId) return;
                    setLoadingButton("#btn-export-ledger-pdf", true);

                    var params = new URLSearchParams(window.location.search);
                    var filter_year = params.get('filter_year') || '';
                    var filter_quarter = params.get('filter_quarter') || '';
                    var url = "{{ url($crud->route . '-ledger-pdf') }}?id=" + currentAccountId + "&filter_year=" + filter_year + "&filter_quarter=" + filter_quarter;
                    var title = "Buku_Besar_" + currentAccountTitle.replace(/ /g, "_") + ".pdf";

                    const {response, errors} = await API_REQUEST("DOWNLOAD", url);

                    if(errors){
                        swal({
                            title: "Error",
                            text: "Terjadi kesalahan saat mengunduh PDF",
                            icon: "error",
                            timer: 4000,
                            buttons: false,
                        });
                        setLoadingButton("#btn-export-ledger-pdf", false);
                    } else if(response){
                        let result = await response;
                        setLoadingButton("#btn-export-ledger-pdf", false);

                        const url_blob = window.URL.createObjectURL(result);
                        const a = document.createElement('a');
                        a.href = url_blob;
                        a.download = title;
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        window.URL.revokeObjectURL(url_blob);
                    }
                });

                $('#btn-export-ledger-excel').click(async function (){
                    if(!currentAccountId) return;
                    setLoadingButton("#btn-export-ledger-excel", true);

                    var params = new URLSearchParams(window.location.search);
                    var filter_year = params.get('filter_year') || '';
                    var filter_quarter = params.get('filter_quarter') || '';
                    var url = "{{ url($crud->route . '-ledger-excel') }}?id=" + currentAccountId + "&filter_year=" + filter_year + "&filter_quarter=" + filter_quarter;
                    var title = "Buku_Besar_" + currentAccountTitle.replace(/ /g, "_") + ".xlsx";

                    const {response, errors} = await API_REQUEST("DOWNLOAD", url);

                    if(errors){
                        swal({
                            title: "Error",
                            text: "Terjadi kesalahan saat mengunduh Excel",
                            icon: "error",
                            timer: 4000,
                            buttons: false,
                        });
                        setLoadingButton("#btn-export-ledger-excel", false);
                    } else if(response){
                        let result = await response;
                        setLoadingButton("#btn-export-ledger-excel", false);

                        const url_blob = window.URL.createObjectURL(result);
                        const a = document.createElement('a');
                        a.href = url_blob;
                        a.download = title;
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        window.URL.revokeObjectURL(url_blob);
                    }
                });
            });
        }
    </script>
@endpush
