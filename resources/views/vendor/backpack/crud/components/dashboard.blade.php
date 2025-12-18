<div>
    <div class="row">
        <div class="col-md-6">
            <div class="card2 mb-4">
                <div class="card2-parent-header">
                    <div class="card2-header fs-6">Total Omzet (Exclude PPn)</div>
                </div>
                <div class="card2-body">
                    <div class="sub-header">Per - <span class="date-invoice"></span></div>
                    <div class="amount" id="omzet_all_total">Rp0</div>
                </div>

            </div>
        </div>
        <div class="col-md-6">
            <div class="card2 mb-4">
                <div class="card2-parent-header">
                    <div class="card2-header fs-6">Laba</div>
                </div>
                <div class="card2-body">
                    <div class="sub-header">Per - <span class="date-invoice"></span></div>
                    <div class="amount" id="laba_all_total">Rp0</div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card2 mb-4">
                <div class="card2-parent-header">
                    <div class="card2-header fs-6">
                        Realisasi Pekerjaan
                    </div>
                </div>
                <div class="card2-body">
                    <div class="row">
                        <div class="col">
                            <div class="label fw-bold mb-1">Rutin</div>
                            <div class="item">
                                <div class="icon blue"><i class="la la-file-invoice-dollar fs-4"></i></div>
                                <div>Omzet<br><strong id="rp_rutin_omzet_total">Rp0</strong></div>
                            </div>
                            <div class="item">
                                <div class="icon cyan"><i class="la la-file-invoice-dollar fs-4"></i></div>
                                <div>Biaya<br><strong id="rp_rutin_biaya_total">Rp0</strong></div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="label fw-bold mb-1">Non Rutin</div>
                            <div class="item">
                                <div class="icon orange"><i class="la la-file-invoice-dollar fs-4"></i></div>
                                <div>Omzet<br><strong id="rp_non_rutin_omzet_total">Rp0</strong></div>
                            </div>
                            <div class="item">
                                <div class="icon pink"><i class="la la-file-invoice-dollar fs-4"></i></div>
                                <div>Biaya<br><strong id="rp_non_rutin_biaya_total">Rp0</strong></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card2">
                <div class="card2-parent-header">
                    <div class="card2-header fs-6">Laba</div>
                </div>
                <div class="card2-body">
                    <div class="row">
                        <div class="col">
                            <div class="btn mb-3">
                                <button class="btn btn-primary btn-sm" id="btn-rutin">Rutin</button>
                            </div>
                            <div class="item">
                                <div class="icon blue"><i class="la la-file-invoice-dollar fs-4"></i></div>
                                <div><strong id="laba_rutin_total">Rp0</strong></div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="btn mb-3">
                                <button class="btn btn-primary btn-sm" id="btn-non-rutin">Non Rutin</button>
                            </div>
                            <div class="item">
                                <div class="icon orange"><i class="la la-file-invoice-dollar fs-4"></i></div>
                                <div><strong id="laba_non_rutin_total">Rp0</strong></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12 mb-4">
            <div class="card2">
                <div class="card2-parent-header">
                    <div class="card2-header fs-6">Monitoring Biaya Pekerjaan Berjalan (Non Rutin)</div>
                </div>
                <div class="card2-body">
                    <div class="fs-5 fw-bold text-center mb-3">
                       Per - <span class="date-invoice"></span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <thead class="bg-light-actual">
                                <tr>
                                    <th>Total Nilai Pekerjaan</th>
                                    <th>Total Biaya Pekerjaan</th>
                                    <th>Laba Berjalan</th>
                                    <th>Jumlah Pekerjaan</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- @foreach ($data_monitoring as $item) --}}
                                    <th>Rp{{ $data_monitoring['total_job_value'] }}</th>
                                    <th>Rp{{ $data_monitoring['total_transfer'] }}</th>
                                    <th>Rp{{ $data_monitoring['total_profit_lost'] }}</th>
                                    <th>{{ $data_monitoring['total_job'] }}</th>
                                {{-- @endforeach --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12 mb-4">
            <div class="card2">
                <div class="card2-parent-header">
                    <div class="card2-header fs-6">Monitoring Proyek</div>
                </div>
                <div class="card2-body">
                    <div class="label mb-2 fw-bold fs-6">Status Proyek</div>
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <div class="status-box red">
                                <div class="status-title fs-6">UNPAID</div>
                                <div class="status-value" id="Unpaid_total">Rp0</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="status-box yellow">
                                <div class="status-title fs-6">TERTUNDA</div>
                                <div class="status-value" id="Tertunda_total">Rp0</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="status-box blue">
                                <div class="status-title fs-6">BELUM SELESAI</div>
                                <div class="status-value" id="Belum_Selesai_total">Rp0</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="status-box blue">
                                <div class="status-title fs-6">RETENSI</div>
                                <div class="status-value" id="Retensi_total">Rp0</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="status-box yellow">
                                <div class="status-title fs-6">BELUM ADA PO</div>
                                <div class="status-value" id="Belum_ada_PO_total">Rp0</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="status-box green">
                                <div class="status-title fs-6">CLOSE</div>
                                <div class="status-value" id="Close">Rp0</div>
                            </div>
                        </div>
                    </div>
                    <div class="label mb-2 fw-bold fs-6">Status Penawaran</div>
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <div class="status-box yellow">
                                <div class="status-title fs-6">HPS</div>
                                <div class="status-value" id="HPS_total">Rp0</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="status-box blue">
                                <div class="status-title fs-6">QUOTATION</div>
                                <div class="status-value" id="Quotation_total">Rp0</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="status-box green">
                                <div class="status-title fs-6">CLOSE</div>
                                <div class="status-value" id="Close_total">Rp0</div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="label fw-bold mb-1">Rutin</div>
                            <div class="item">
                                <div class="icon blue"><i class="la la-file-invoice-dollar fs-4"></i></div>
                                <div>Unpaid<br><strong id="Unpaid_rutin_total">Rp0</strong></div>
                            </div>
                            <div class="item">
                                <div class="icon cyan"><i class="la la-file-invoice-dollar fs-4"></i></div>
                                <div>Tertunda<br><strong id="Tertunda_rutin_total">Rp0</strong></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="label fw-bold mb-1">Non Rutin</div>
                            <div class="item">
                                <div class="icon orange"><i class="la la-file-invoice-dollar fs-4"></i></div>
                                <div>Unpaid<br><strong id="Unpaid_non_rutin_total">Rp0</strong></div>
                            </div>
                            <div class="item">
                                <div class="icon pink"><i class="la la-file-invoice-dollar fs-4"></i></div>
                                <div>Tertunda<br><strong id="Tertunda_non_rutin_total">Rp0</strong></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('inline_scripts')
<style>
    .card2 {
      background: white;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      overflow: hidden;
      /* padding: 20px; */
    }

    .card2-parent-header {
        padding-top:15px;
        border-bottom: 1px solid rgba(197, 197, 197, 0.705);
    }

    .card2-header {
      border-left: 3px solid #005792;
      font-weight: bold;
      padding-top:7px;
      padding-bottom: 7px;
      margin-bottom: 15px;
      padding-left: 16px;
    }

    .card2-body {
        padding: 15px;
    }

    .sub-header {
      background: #f1f3f5;
      padding: 5px 10px;
      font-size: 0.9rem;
      color: #555;
      margin-bottom: 10px;
    }

    .amount {
      font-size: 1.8rem;
      font-weight: bold;
      color: #000;
      padding-top: 12px;
      padding-bottom: 16px;
      text-align: center;
    }

    .icon {
      width: 40px !important;
      height: 40px !important;
      border-radius: 8px;
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .icon.blue { background: #1e6091; }
    .icon.cyan { background: #48cae4; }
    .icon.orange { background: #f9a825; }
    .icon.pink { background: #f06292; }

    .item {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
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

    .status-box {
      border-radius: 10px;
      padding: 20px;
      color: white;
      font-weight: bold;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
      min-width: 180px;
      margin-bottom: 12px;
    }

    .status-value {
      font-weight: normal;
      font-size: 16px;
      margin-top: 20px;
    }
    .red { background-color: #d9534f; }
    .yellow { background-color: #f0ad4e; color: black; }
    .blue { background-color: #195381; }
    .green { background-color: #28a745; }

</style>
@endpush

@push('after_scripts')
<script>
    $(function(){
        SIAOPS.setAttribute('dashboard', function(){
            return {
                name: 'dashboard',
                accounts_compact:[],
                eventLoader: async function(){
                    var instance = this;
                    // eventEmitter.on("crudTable-voucher_plugin_load", function(data){
                    //     instance.load();
                    // });
                },
                load: function(){
                    var instance = this;
                    instance.eventLoader();
                    $.ajax({
                        url: "{{ url($crud->route.'/get-chart') }}",
                        type: 'GET',
                        typeData: 'json',
                        success: function (result) {
                            // console.log(result);
                            var date_invoice = $('.date-invoice');
                            date_invoice.each(function(){
                                $(this).html(result.first_invoice.invoice_first_date);
                            });
                            $('#rp_rutin_omzet_total').html('Rp'+result.total_job_realisasion.total_omzet_rutin);
                            $('#rp_non_rutin_omzet_total').html('Rp'+result.total_job_realisasion.total_omzet_non_rutin);
                            $('#rp_rutin_biaya_total').html('Rp'+result.total_job_realisasion.total_biaya_rutin);
                            $('#rp_non_rutin_biaya_total').html('Rp'+result.total_job_realisasion.total_biaya_non_rutin);

                            $('#laba_all_total').html('Rp'+result.total_job_realisasion.total_all_laba);

                            // $('#laba_rutin_total').html('Rp'+result.total_laba_category.total_laba_rutin);
                            // $('#laba_non_rutin_total').html('Rp'+result.total_laba_category.total_laba_non_rutin);

                            $('#laba_rutin_total').html('Rp'+result.total_job_realisasion.total_laba_rutin);
                            $('#laba_non_rutin_total').html('Rp'+result.total_job_realisasion.total_laba_non_rutin);

                            // $('#omzet_all_total').html('Rp'+result.total_omzet_all.total_omzet);
                            $('#omzet_all_total').html('Rp'+result.total_job_realisasion.total_all_omzet);

                            var total_project = result.total_projects.list_projects;
                            $('#Unpaid_total').html('Rp'+total_project.UNPAID);
                            $('#Tertunda_total').html('Rp'+total_project.TERTUNDA);
                            $('#Belum_Selesai_total').html('Rp'+total_project.BELUM_SELESAI);
                            $('#Retensi_total').html('Rp'+total_project.RETENSI);
                            $('#Belum_ada_PO_total').html('Rp'+total_project.BELUM_ADA_PO);
                            $('#Close').html('Rp'+total_project.CLOSE);

                            $('#Unpaid_rutin_total').html('Rp'+result.total_projects.total_unpaid_rutin);
                            $('#Unpaid_non_rutin_total').html('Rp'+result.total_projects.total_unpaid_non_rutin);
                            $('#Tertunda_rutin_total').html('Rp'+result.total_projects.total_tertunda_rutin);
                            $('#Tertunda_non_rutin_total').html('Rp'+result.total_projects.total_tertunda_non_rutin);

                            var quotation = result.total_quotations.list_quotations;
                            $('#HPS_total').html('Rp'+quotation.HPS);
                            $('#Quotation_total').html('Rp'+quotation.QUOTATION);
                            $('#Close_total').html('Rp'+quotation.CLOSE);
                        },
                        error: function (xhr, status, error) {
                            console.error(xhr);
                            alert('An error occurred while loading the create form.');
                        }
                    });
                }
            }
        });

        SIAOPS.getAttribute('dashboard').load();

        $('#btn-rutin').click(function(){
            $('#modalInfoLabaRutin').modal('show');
        });
        $('#btn-non-rutin').click(function(){
            $('#modalInfoLabaNonRutin').modal('show');
        });
        $('.modal .btn-close').click(function(){
            $('#modalInfoLabaRutin').modal('hide');
            $('#modalInfoLabaNonRutin').modal('hide');
        });
    });
</script>
@endpush

@push('after_scripts')
<div class="modal fade" id="modalInfoLabaRutin" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header justify-content-center">
                <h5 class="modal-title text-center w-100" id="modalTitleCentered">Tabel Pekerjaan Rutin</h5>
                <button type="button" class="btn-close position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-borderless" style="width: 800px;">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>KDP</th>
                                <th>Nama Pekerjaan</th>
                                <th>Nilai Invoice</th>
                                <th>Biaya</th>
                                <th>Laba</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data_laba['data_laba_rutin'] as $key => $laba)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $laba->work_code }}</td>
                                    <td>{{ $laba->job_name }}</td>
                                    <td>Rp{{ \App\Http\Helpers\CustomHelper::formatRupiah($laba->invoice_price_job_exlude_ppn) }}</td>
                                    <td>Rp{{ \App\Http\Helpers\CustomHelper::formatRupiah($laba->price_total_str) }}</td>
                                    <td>Rp{{ \App\Http\Helpers\CustomHelper::formatRupiah($laba->total_laba) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modalInfoLabaNonRutin" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header justify-content-center">
                <h5 class="modal-title text-center w-100" id="modalTitleCentered">Tabel Pekerjaan Non Rutin</h5>
                <button type="button" class="btn-close position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-borderless" style="width: 800px;">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>KDP</th>
                                <th>Nama Pekerjaan</th>
                                <th>Nilai Invoice</th>
                                <th>Biaya</th>
                                <th>Laba</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data_laba['data_laba_non_rutin'] as $key => $laba)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $laba->kdp }}</td>
                                    <td>{{ $laba->job_name }}</td>
                                    <td>Rp{{ \App\Http\Helpers\CustomHelper::formatRupiah($laba->invoice_price_job_exlude_ppn) }}</td>
                                    <td>Rp{{ \App\Http\Helpers\CustomHelper::formatRupiah($laba->price_total_str) }}</td>
                                    <td>Rp{{ \App\Http\Helpers\CustomHelper::formatRupiah($laba->total_laba) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endpush
