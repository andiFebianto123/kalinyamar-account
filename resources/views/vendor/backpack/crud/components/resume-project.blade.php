<div class="mt-2">

  <div class="fs-6"><strong>KATEGORI TAGIHAN</strong></div>
  <div><strong>1. TAGIHAN YANG BELUM DIBAYAR</strong></div>

  <div class="table-responsive mt-2">
    <table id="invoice_1" class="table table-borderless">
        <thead class="bg-light-actual">
            <tr>
                <th style="width:4%;"><strong>No.</strong></th>
                <th style="width:26%;"><strong>Nama Perusahaan</strong></th>
                <th style="width:70%;"><strong>Nominal Include PPn</strong></th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
  </div>

  <div class="mt-2"><strong>2. TAGIHAN TERTUNDA (RUTIN)</strong></div>
  <div class="table-responsive mt-2">
    <table id="invoice_2" class="table table-borderless">
        <thead class="bg-light-actual">
            <tr>
                <th style="width:4%;"><strong>No.</strong></th>
                <th style="width:26%;"><strong>Nama Perusahaan</strong></th>
                <th style="width:70%;"><strong>Nominal Include PPn</strong></th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
  </div>

  <div class="mt-2"><strong>3. TAGIHAN TERTUNDA (PROJECT)</strong></div>
  <div class="table-responsive mt-2">
    <table id="invoice_3" class="table table-borderless">
        <thead class="bg-light-actual">
            <tr>
                <th style="width:4%;"><strong>No.</strong></th>
                <th style="width:26%;"><strong>Nama Perusahaan</strong></th>
                <th style="width:70%;"><strong>Nominal Include PPn</strong></th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
  </div>

  <div class="mt-2"><strong>4. TAGIHAN RETENSI</strong></div>
  <div class="table-responsive mt-2">
    <table id="invoice_4" class="table table-borderless">
        <thead class="bg-light-actual">
            <tr>
                <th style="width:4%;"><strong>No.</strong></th>
                <th style="width:26%;"><strong>Nama Perusahaan</strong></th>
                <th style="width:70%;"><strong>Nominal Include PPn</strong></th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
  </div>

  <div class="mt-2"><strong>5. PEKERJAAN YANG BELUM SELESAI (RUTIN)</strong></div>
  <div class="table-responsive mt-2">
    <table id="invoice_5" class="table table-borderless">
        <thead class="bg-light-actual">
            <tr>
                <th style="width:4%;"><strong>No.</strong></th>
                <th style="width:26%;"><strong>Nama Perusahaan</strong></th>
                <th style="width:70%;"><strong>Nominal Include PPn</strong></th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
  </div>

  <div class="mt-2"><strong>6. PEKERJAAN YANG BELUM SELESAI (PROJECT)</strong></div>
  <div class="table-responsive mt-2">
    <table id="invoice_6" class="table table-borderless">
        <thead class="bg-light-actual">
            <tr>
                <th style="width:4%;"><strong>No.</strong></th>
                <th style="width:26%;"><strong>Nama Perusahaan</strong></th>
                <th style="width:70%;"><strong>Nominal Include PPn</strong></th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
  </div>

  <div class="table-responsive">
    <table class="table table-borderless">
        <thead class="bg-navy">
            <tr>
                <th colspan="2" style="width:30%;"><strong>GRAND TOTAL</strong></th>
                <th style="width:70%;"><strong id="grand_total">Rp0</strong></th>
            </tr>
        </thead>
    </table>
  </div>
</div>

@push('inline_scripts')
    <style>
        .bg-navy {
            background-color: #003e6b !important;
            color: white;
            --bs-table-color: #fff;
            --bs-table-bg: #003e6b;
            --bs-table-border-color: #373b3e;
            --bs-table-striped-bg: #2c3034;
            --bs-table-striped-color: #fff;
            --bs-table-active-bg: #373b3e;
            --bs-table-active-color: #fff;
            --bs-table-hover-bg: #323539;
            --bs-table-hover-color: #fff;
            border-color: #003e6b !important;
        }
        .bg-gray-actual{
            background-color: #bebebe;
            color: rgb(0, 0, 0) !important;
            --bs-table-color: #fff;
            --bs-table-bg: #bebebe;
            --bs-table-border-color: #bebebe;
            --bs-table-striped-bg: #bebebe;
            --bs-table-striped-color: #fff;
            --bs-table-active-bg: #bebebe;
            --bs-table-active-color: #fff;
            --bs-table-hover-bg: #bebebe;
            --bs-table-hover-color: #fff;
            border-color: #bebebe !important;
        }
        .bg-light-actual {
            background-color: #ededed !important;
            --bs-table-color: black;
            --bs-table-bg: #ededed;
            --bs-table-border-color: #ededed;
            --bs-table-striped-bg: #ededed;
            --bs-table-striped-color: #ededed;
            --bs-table-active-bg: #030303;
            --bs-table-active-color: #ededed;
            --bs-table-hover-bg: #ededed;
            --bs-table-hover-color: #e9ecef;

            color: black;
            border-color: #ededed !important;
        }
        .bg-danger {
            --bs-table-color: #fff;
            --bs-table-bg: #dc3545;
            --bs-table-border-color: #dc3545;
            --bs-table-striped-bg: #eccccf;
            --bs-table-striped-color: #fff;
            --bs-table-active-bg: #dc3545;
            --bs-table-active-color: #fff;
            --bs-table-hover-bg: #dc3545;
            --bs-table-hover-color: #fff;
            color: var(--bs-table-color);
            border-color: var(--bs-table-border-color);
        }
        .bg-status-red {
            background-color: #dc3545;
            color: white;
        }
        .bg-status-blue {
            background-color: #0d6efd;
            color: white;
        }
        .due-date {
            color: red;
            font-weight: bold;
            font-size: 12px;
        }
        .section-title {
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
            font-size: 14px;
        }
    </style>
    <style>
        .dataTables_wrapper .dataTables_scrollHead table thead tr th {
            background-color: #FCD72D !important;
        }
    </style>
@endpush

@push('after_scripts')
<script>
    $(function(){
        SIAOPS.setAttribute('resume-project', function(){
            return {
                name: 'resume-project',
                accounts_compact:[],
                eventLoader: async function(){
                    var instance = this;
                    eventEmitter.on("crudTable-filter-resume-project_load", function(data){
                        instance.load();
                    });
                },
                load: function(){
                    var instance = this;
                    var urlResume = new URL("{{ url($crud->route.'/resume-total') }}");
                    if(window.filter_tables){
                        if(window.filter_tables.filter_year && window.filter_tables.filter_year != 'all'){
                            urlResume.searchParams.set('filter_year', window.filter_tables.filter_year);
                        }
                        if(window.filter_tables.filter_category && window.filter_tables.filter_category != 'all'){
                            urlResume.searchParams.set('filter_category', window.filter_tables.filter_category);
                        }
                        if(window.filter_tables.filter_client && window.filter_tables.filter_client != 'all'){
                            urlResume.searchParams.set('filter_client', window.filter_tables.filter_client);
                        }
                    }
                    $.ajax({
                        url: urlResume.toString(),
                        type: 'GET',
                        typeData: 'json',
                        success: function (result) {
                            // console.log(result);

                            // $('#date-invoice').html(`PER: ${result.list.tgl_start_invoice || ''}`);
                            // $('#duration-invoice').html(`Invoice Terlama ${result.list.invoice_old.duration || ''} Hari`);
                            // $('#name-invoice').html(`${result.list.invoice_old.name || ''}`);

                            $('#invoice_1 tbody').html('');
                            // console.log(result.list.invoice_1);
                            forEachFlexible(result.list.invoice_1, function(index, value){
                                $('#invoice_1 tbody').append(`
                                    <tr>
                                        <td>${(index+1)}</td>
                                        <td>${value.client_name_str}</td>
                                        <td>${value.price_total_include_ppn_str}</td>
                                    <tr>
                                `);
                            });
                            $('#invoice_1 tbody').append(`
                                <tr class="bg-light-actual">
                                    <td></td>
                                    <td></td>
                                    <td><strong>${result.list.invoice_1_total_str}</strong></td>
                                </tr>
                            `);

                            $('#invoice_2 tbody').html('');
                            forEachFlexible(result.list.invoice_2, function(index, value){
                                $('#invoice_2 tbody').append(`
                                    <tr>
                                        <td>${index+1}</td>
                                        <td>${value.client_name_str}</td>
                                        <td>${value.price_total_include_ppn_str}</td>
                                    <tr>
                                `);
                            });
                            $('#invoice_2 tbody').append(`
                                <tr class="bg-light-actual">
                                    <td></td>
                                    <td></td>
                                    <td><strong>${result.list.invoice_2_total_str}</strong></td>
                                </tr>
                            `);

                            $('#invoice_3 tbody').html('');
                            forEachFlexible(result.list.invoice_3, function(index, value){
                                $('#invoice_3 tbody').append(`
                                    <tr>
                                        <td>${index+1}</td>
                                        <td>${value.client_name_str}</td>
                                        <td>${value.price_total_include_ppn_str}</td>
                                    <tr>
                                `);
                            });
                            $('#invoice_3 tbody').append(`
                                <tr class="bg-light-actual">
                                    <td></td>
                                    <td></td>
                                    <td><strong>${result.list.invoice_3_total_str}</strong></td>
                                </tr>
                            `);

                            $('#invoice_4 tbody').html('');
                            forEachFlexible(result.list.invoice_4, function(index, value){
                                $('#invoice_4 tbody').append(`
                                    <tr>
                                        <td>${index+1}</td>
                                        <td>${value.client_name_str}</td>
                                        <td>${value.price_total_include_ppn_str}</td>
                                    <tr>
                                `);
                            });
                            $('#invoice_4 tbody').append(`
                                <tr class="bg-light-actual">
                                    <td></td>
                                    <td></td>
                                    <td><strong>${result.list.invoice_4_total_str}</strong></td>
                                </tr>
                            `);

                            $('#invoice_5 tbody').html('');
                            forEachFlexible(result.list.invoice_5, function(index, value){
                                $('#invoice_5 tbody').append(`
                                    <tr>
                                        <td>${index+1}</td>
                                        <td>${value.client_name_str}</td>
                                        <td>${value.price_total_include_ppn_str}</td>
                                    <tr>
                                `);
                            });
                            $('#invoice_5 tbody').append(`
                                <tr class="bg-light-actual">
                                    <td></td>
                                    <td></td>
                                    <td><strong>${result.list.invoice_5_total_str}</strong></td>
                                </tr>
                            `);

                            $('#invoice_6 tbody').html('');
                            forEachFlexible(result.list.invoice_6, function(index, value){
                                $('#invoice_6 tbody').append(`
                                    <tr>
                                        <td>${index+1}</td>
                                        <td>${value.client_name_str}</td>
                                        <td>${value.price_total_include_ppn_str}</td>
                                    <tr>
                                `);
                            });
                            $('#invoice_6 tbody').append(`
                                <tr class="bg-light-actual">
                                    <td></td>
                                    <td></td>
                                    <td><strong>${result.list.invoice_6_total_str}</strong></td>
                                </tr>
                            `);

                            $('#grand_total').html(`${result.grand_total}`);

                        },
                        error: function (xhr, status, error) {
                            console.error(xhr);
                            alert('An error occurred while loading the create form.');
                        }
                    });
                }
            }
        });
        SIAOPS.getAttribute('resume-project').load();
        SIAOPS.getAttribute('resume-project').eventLoader();
    });
</script>
@endpush
