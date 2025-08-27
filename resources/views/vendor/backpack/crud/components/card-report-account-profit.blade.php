<div>
    <div class="d-flex justify-content-between">
        <h5>{{trans('backpack::crud.profit_lost.consolidation_income_statement')}}</h5>
        <div>
            <button id="btn-export-consolidation-pdf" class="btn btn-sm btn-primary">
                <i class="la la-file-download"></i> PDF
            </button>
            <button id="btn-export-consolidation-excel" class="btn btn-sm btn-primary">
                <i class="la la-file-download"></i> Excel
            </button>
        </div>
    </div>
    <div class="table-responsive">
        <table id="table-account-{{$name}}" class="info-cast-account table">
            <thead class="text-left">
                <tr>
                <th style="width: 40%;">{{-- trans('backpack::crud.expense_account.column.code') --}}</th>
                <th style="width: 10%;">{{-- trans('backpack::crud.expense_account.column.name') --}}</th>
                <th style="width: 20%;">{{-- trans('backpack::crud.expense_account.column.balance') --}}</th>
                <th>{{-- trans('backpack::crud.expense_account.column.action') --}}</th>
                </tr>
            </thead>
            <tbody class="text-left">
                <tr>
                    <td><strong>Pendapatan Usaha</strong></td>
                    <td></td>
                    <td><strong id="str_tot_1">Rp. xxx</strong></td>
                </tr>
                <tr>
                    <td>Pendapatan Kontrak</td>
                    <td><div id="str_tot_2">Rp. xxx</div></td>
                    <td></td>
                </tr>
                <tr>
                    <td>Pendapatan Non-Kontrak</td>
                    <td><div id="str_tot_3">Rp. xxx</div></td>
                    <td></td>
                </tr>
                <tr>
                    <td><strong>Beban Usaha</strong></td>
                    <td></td>
                    <td><strong id="str_tot_4">Rp. xxx</strong></td>
                </tr>
                <tr>
                    <td>Beban Proyek (Kontrak)</td>
                    <td><div id="str_tot_5">Rp. xxx</div></td>
                    <td></td>
                </tr>
                <tr>
                    <td>Beban Operasional (Umum)</td>
                    <td><div id="str_tot_6">Rp. xxx</div></td>
                    <td></td>
                </tr>
                <tr>
                    <td><strong>Laba Usaha (Operating Profit)</strong></td>
                    <td></td>
                    <td><strong id="str_tot_7">Rp. xxx</strong></td>
                </tr>
                <tr>
                    <td><strong>Pendapatan/Beban Lain-lain</strong></td>
                    <td></td>
                    <td><strong id="str_tot_8">Rp. xxx</strong></td>
                </tr>
                <tr>
                    <td>Pendapatan Bunga Bank</td>
                    <td><div id="str_tot_9">Rp. xxx</div></td>
                    <td></td>
                </tr>
                <tr>
                    <td><strong>Laba Sebelum Pajak</strong></td>
                    <td></td>
                    <td><strong id="str_tot_10">Rp. xxx</strong></td>
                </tr>
                <tr>
                    <td>Beban Pajak</td>
                    <td><div id="str_tot_11">Rp. xxx</div></td>
                    <td></td>
                </tr>
                <tr>
                    <td><strong>Laba Bersih</strong></td>
                    <td></td>
                    <td><strong id="str_tot_12">Rp. xxx</strong></td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

@push('inline_scripts')
    @once
        <style>
            .saldo-str {
                font-size: 20px;
                font-weight: 700;
                padding-top: 200px;
            }
        </style>
        <style>
            .info-cast-account tbody,
            .info-cast-account td,
            .info-cast-account tfoot,
            .info-cast-account th,
            .info-cast-account thead,
            .info-cast-account tr {
                border-color: transparent;
            }

            .infor-cast-account {
                width: auto;
                table-layout: auto;
            }
            .info-cast-account th, .info-cast-account td{
                /* text-align: left; */
                white-space: nowrap;
            }
            .info-cast-account th:nth-child(1),
            .info-cast-account td:nth-child(1) { width: 15%; }

            .info-cast-account th:nth-child(2),
            .info-cast-account td:nth-child(2) { width: 45%; }

            .info-cast-account th:nth-child(3),
            .info-cast-account td:nth-child(3) { width: 25%; }

            .info-cast-account th:nth-child(4),
            .info-cast-account td:nth-child(4) { width: 15%; }
        </style>
        <style>
            .btn-danger {
                background-color: #e55353 !important;
            }
        </style>
    @endonce
@endpush

@push('after_scripts')
    <script>
        if(SIAOPS.getAttribute('accounts') == null){
            SIAOPS.setAttribute('accounts', function(){
                return {
                    name: 'accounts',
                    accounts_compact:[],
                    eventLoader: async function(){
                        eventEmitter.on("account_create_success", async function(data){
                            if(data.component_name != undefined){
                                await SIAOPS.getAttribute(data.component_name).load();
                            }else{
                                window.location.href = location.href;
                            }
                        });
                    },
                    addAccount: function(instanceAccount){
                        var instance = this;
                        instance.accounts_compact.push(instanceAccount);
                    },
                    load:async function(){
                        this.eventLoader();
                        for (const callAccount of this.accounts_compact) {
                            await callAccount.load();
                        }
                    }
                }
            });
        }

        SIAOPS.setAttribute("{{$name}}", function(){
            return {
                name: "{{$name}}",
                url: "{{$route}}",
                table: "table-account-{{$name}}",
                load: async function(){
                    var instance = this;

                    return new Promise((resolve, reject) => {
                        $.ajax({
                            url: instance.url,
                            type: 'GET',
                            typeData: 'json',
                            success: function (result) {
                                // $('#'+instance.table+' tbody').empty();
                                $('#str_tot_1').html(result.total_acct_1);
                                $('#str_tot_2').html(result.total_acct_2);
                                $('#str_tot_3').html(result.total_acct_3);
                                $('#str_tot_4').html(result.total_acct_4);
                                $('#str_tot_5').html(result.total_acct_5);
                                $('#str_tot_6').html(result.total_acct_6);
                                $('#str_tot_7').html(result.total_acct_7);
                                $('#str_tot_8').html(result.total_acct_8);
                                $('#str_tot_9').html(result.total_acct_9);
                                $('#str_tot_10').html(result.total_acct_10);
                                $('#str_tot_11').html(result.total_acct_11);
                                $('#str_tot_12').html(result.total_acct_12);
                                resolve(result);
                            },
                            error: function (xhr, status, error) {
                                console.error(xhr);
                                reject(xhr);
                                alert('An error occurred while loading the create form.');
                            }
                        });
                    });
                }
            }
        });

        SIAOPS.getAttribute('accounts').addAccount(
            SIAOPS.getAttribute("{{$name}}"));

        eventEmitter.on("{{$name}}_update_success", async function(data){
            // if(data.level == 2){
            window.location.href = location.href;
            // }
            // await SIAOPS.getAttribute("{{$name}}").load();
        });
    </script>
@endpush

@push('after_scripts')
    @once
        <script>
            $(function(){
                SIAOPS.getAttribute('accounts').load();

                $('#btn-export-consolidation-pdf').click(async function(){
                    setLoadingButton("#btn-export-consolidation-pdf", true);
                    var get_url_export = "{{url($crud->route)}}/export-consolidation-pdf?export=1";
                    var get_title_export = "Laporan_laba_rugi_konsolidasi.pdf";
                    var params_url = MakeParamUrl(window.filter_tables || {});

                    var url_export_with_params = get_url_export + params_url;

                    if(get_url_export == ''){
                        setLoadingButton("#btn-export-consolidation-pdf", false);
                        swal({
                            title: "Error",
                            text: "Internet server error",
                            icon: "error",
                            timer: 4000,
                            buttons: false,
                        });
                        return;
                    }

                    const {response, errors} = await API_REQUEST("DOWNLOAD", url_export_with_params);

                    if(errors){
                        var errorResponse = await errors;
                        swal({
                            title: "Error",
                            text: "Internet server error",
                            icon: "error",
                            timer: 4000,
                            buttons: false,
                        });
                        setLoadingButton("#btn-export-consolidation-pdf", false);
                    }else if(response){
                        let result = await response;
                        setLoadingButton("#btn-export-consolidation-pdf", false);

                        const url = window.URL.createObjectURL(result);
                        const a = document.createElement('a');
                        a.href = url;

                        a.download = get_title_export;
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        window.URL.revokeObjectURL(url);
                    }
                });

                $('#btn-export-consolidation-excel').click(async function(){
                    setLoadingButton("#btn-export-consolidation-excel", true);
                    var get_url_export = "{{url($crud->route)}}/export-consolidation-excel?export=1";
                    var get_title_export = "Laporan_laba_rugi_konsolidasi.xlsx";
                    var params_url = MakeParamUrl(window.filter_tables || {});

                    var url_export_with_params = get_url_export + params_url;

                    if(get_url_export == ''){
                        setLoadingButton("#btn-export-consolidation-excel", false);
                        swal({
                            title: "Error",
                            text: "Internet server error",
                            icon: "error",
                            timer: 4000,
                            buttons: false,
                        });
                        return;
                    }

                    const {response, errors} = await API_REQUEST("DOWNLOAD", url_export_with_params);
                    if(errors){
                        var errorResponse = await errors;
                        swal({
                            title: "Error",
                            text: "Internet server error",
                            icon: "error",
                            timer: 4000,
                            buttons: false,
                        });
                        setLoadingButton("#btn-export-consolidation-excel", false);
                    }else if(response){
                        let result = await response;
                        setLoadingButton("#btn-export-consolidation-excel", false);

                        const url = window.URL.createObjectURL(result);
                        const a = document.createElement('a');
                        a.href = url;

                        // Nama file default - kamu bisa set manual atau ambil dari response header (opsional)
                        a.download = get_title_export;
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        window.URL.revokeObjectURL(url);

                    }
                });
            });
        </script>
    @endonce
@endpush
