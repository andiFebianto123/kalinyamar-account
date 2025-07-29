<div class="mt-2">
    <div class="d-flex justify-content-between align-items-start mb-2">
    <div>
      <strong class="fs-6" id="date-invoice">PER: Rabu, 28 Mei 2025</strong><br>
    </div>
    <div class="text-end">
      <strong class="fs-6" id="duration-invoice">Invoice Terlama 642 Hari</strong><br>
      <strong class="fs-6" id="name-invoice">Nama Pekerjaan</strong>
    </div>
  </div>

  {{-- <div class="table-responsive mb-1">
    <table class="table table-borderless">
        <thead class="bg-navy">
            <tr>
                <th colspan="2" style="width:30%;">TOTAL OMSET INCLUDE PPN</th>
                <th style="width:40%;"><strong>2022</strong></th>
                <th style="width:30%;"><strong>28 Mei 2025</strong></th>
            </tr>
        </thead>
        <tbody>
          <tr>
            <td colspan="2">Bedasarkan Faktur keluaran</td>
            <td>Rp.4000.000.000</td>
            <td>Rp.4000.000.000</td>
          </tr>
        </tbody>
    </table>
  </div> --}}

  {{-- <div class="table-responsive mb-1">
    <table class="table table-borderless">
        <thead class="bg-navy">
            <tr>
                <th style="width:30%;"><strong>TOTAL INVOICE FAKTUR</strong></th>
                <th colspan="2" style="width:70%;"><strong>Per 1 Januari 2023 sampai dengan 28 Mei 2025</strong></th>
            </tr>
        </thead>
        <tbody>
          <tr>
            <td class="bg-navy"><strong>2023 - TERBAYAR INCLUDE PPN</strong></td>
            <td>Rp.4000.000.000</td>
          </tr>
        </tbody>
    </table>
  </div> --}}

  {{-- <div class="table-responsive mb-1">
    <table class="table table-borderless">
        <thead class="bg-light-actual">
            <tr>
                <th style="width:4%;"><strong>No.</strong></th>
                <th style="width:26%;"><strong>Estimasi Pembayaran PPN Masa</strong></th>
                <th style="width:40%;"><strong>Oktober 2023</strong></th>
                <th style="width:30%;"><strong>November 2023</strong></th>
            </tr>
        </thead>
        <tbody>
          <tr>
            <td>1.</td>
            <td>Pajak Keluaran (Dipungut Sendiri)</td>
            <td>Rp.4000.000.000</td>
            <td>Rp.4000.000.000</td>
          </tr>
          <tr>
            <td>2.</td>
            <td>Pajak Masukan</td>
            <td>Rp.4000.000.000</td>
            <td>Rp.4000.000.000</td>
          </tr>
          <tr>
            <td></td>
            <td><strong>Total Bayar</strong></td>
            <td><strong>Rp.4000.000.000</strong></td>
            <td><strong>Rp.4000.000.000</strong></td>
          </tr>
          <tr>
            <td></td>
            <td rowspan="2"><strong><i>Nilai dapat berubah jika ada faktur<br>masukan dan faktur keluaran Tambahan</i></strong></td>
            <td><strong class="text-danger">JATUH TEMPO</strong></td>
            <td><strong class="text-danger">JATUH TEMPO</strong></td>
          </tr>
          <tr>
            <td></td>
            <td><strong class="text-danger">25 NOVEMBER 2023</strong></td>
            <td><strong class="text-danger">25 DESEMBER 2023</strong></td>
          </tr>
          <tr>
            <td colspan="2"><strong>STATUS PEMBAYARAN PPN MASA</strong></td>
            <td class="bg-danger">KURANG BAYAR</td>
            <td class="bg-navy">SUDAH BAYAR</td>
          </tr>
        </tbody>
    </table>
  </div> --}}

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
        /* .table-borderless {
            width: auto;
        } */
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
                    eventEmitter.on("crudTable-resume-project_load", function(data){
                        instance.load();
                    });
                },
                load: function(){
                    var instance = this;
                    instance.eventLoader();
                    $.ajax({
                        url: "{{ url($crud->route.'/resume-total') }}",
                        type: 'GET',
                        typeData: 'json',
                        success: function (result) {
                            // console.log(result);

                            $('#date-invoice').html(`PER: ${result.list.tgl_start_invoice || ''}`);
                            $('#duration-invoice').html(`Invoice Terlama ${result.list.invoice_old.duration || ''} Hari`);
                            $('#name-invoice').html(`${result.list.invoice_old.name || ''}`);

                            $('#invoice_1 tbody').html('');
                            // console.log(result.list.invoice_1);
                            forEachFlexible(result.list.invoice_1, function(index, value){
                                $('#invoice_1 tbody').append(`
                                    <tr>
                                        <td>${(index+1)}</td>
                                        <td>${value.setup_client.name}</td>
                                        <td>Rp${value.price_total_include_ppn_str}</td>
                                    <tr>
                                `);
                            });
                            $('#invoice_1 tbody').append(`
                                <tr class="bg-light-actual">
                                    <td></td>
                                    <td></td>
                                    <td><strong>Rp${formatRupiah(result.list.invoice_1_total)}</strong></td>
                                </tr>
                            `);

                            $('#invoice_2 tbody').html('');
                            forEachFlexible(result.list.invoice_2, function(index, value){
                                $('#invoice_2 tbody').append(`
                                    <tr>
                                        <td>${index+1}</td>
                                        <td>${value.setup_client.name}</td>
                                        <td>Rp${value.price_total_include_ppn_str}</td>
                                    <tr>
                                `);
                            });
                            $('#invoice_2 tbody').append(`
                                <tr class="bg-light-actual">
                                    <td></td>
                                    <td></td>
                                    <td><strong>Rp${formatRupiah(result.list.invoice_2_total)}</strong></td>
                                </tr>
                            `);

                            $('#invoice_3 tbody').html('');
                            forEachFlexible(result.list.invoice_3, function(index, value){
                                $('#invoice_3 tbody').append(`
                                    <tr>
                                        <td>${index+1}</td>
                                        <td>${value.setup_client.name}</td>
                                        <td>Rp${value.price_total_include_ppn_str}</td>
                                    <tr>
                                `);
                            });
                            $('#invoice_3 tbody').append(`
                                <tr class="bg-light-actual">
                                    <td></td>
                                    <td></td>
                                    <td><strong>Rp${formatRupiah(result.list.invoice_3_total)}</strong></td>
                                </tr>
                            `);

                            $('#invoice_4 tbody').html('');
                            forEachFlexible(result.list.invoice_4, function(index, value){
                                $('#invoice_4 tbody').append(`
                                    <tr>
                                        <td>${index+1}</td>
                                        <td>${value.setup_client.name}</td>
                                        <td>Rp${value.price_total_include_ppn_str}</td>
                                    <tr>
                                `);
                            });
                            $('#invoice_4 tbody').append(`
                                <tr class="bg-light-actual">
                                    <td></td>
                                    <td></td>
                                    <td><strong>Rp${formatRupiah(result.list.invoice_4_total)}</strong></td>
                                </tr>
                            `);

                            $('#invoice_5 tbody').html('');
                            forEachFlexible(result.list.invoice_5, function(index, value){
                                $('#invoice_5 tbody').append(`
                                    <tr>
                                        <td>${index+1}</td>
                                        <td>${value.setup_client.name}</td>
                                        <td>Rp${value.price_total_include_ppn_str}</td>
                                    <tr>
                                `);
                            });
                            $('#invoice_5 tbody').append(`
                                <tr class="bg-light-actual">
                                    <td></td>
                                    <td></td>
                                    <td><strong>Rp${formatRupiah(result.list.invoice_5_total)}</strong></td>
                                </tr>
                            `);

                            $('#invoice_6 tbody').html('');
                            forEachFlexible(result.list.invoice_6, function(index, value){
                                $('#invoice_6 tbody').append(`
                                    <tr>
                                        <td>${index+1}</td>
                                        <td>${value.setup_client.name}</td>
                                        <td>Rp${value.price_total_include_ppn_str}</td>
                                    <tr>
                                `);
                            });
                            $('#invoice_6 tbody').append(`
                                <tr class="bg-light-actual">
                                    <td></td>
                                    <td></td>
                                    <td><strong>Rp${formatRupiah(result.list.invoice_6_total)}</strong></td>
                                </tr>
                            `);

                            $('#grand_total').html(`Rp${result.grand_total}`);

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
    });
</script>
@endpush
