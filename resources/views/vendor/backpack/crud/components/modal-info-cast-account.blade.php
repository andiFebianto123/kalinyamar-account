
<div>
    <p style="font-size: 15px;">
        <span style="">{{trans('backpack::crud.modal.bank_name')}}</span>: <span class="bank_name fw-bold"></span> |
        <span>{{ trans('backpack::crud.modal.no_account') }} :</span> <span class="no_account fw-bold"></span>
    </p>
    <p class="mb-4">
        <strong><span style="font-size: 20px;">Saldo</span></strong>
        <span class="total_saldo fs-4 fw-bold text-dark"></span>
    </p>

    <div class="d-flex justify-content-end mb-2">
        <button id="btn-export-kas-pdf" class="btn btn-sm btn-primary me-2">
            <i class="la la-file-download"></i> PDF
        </button>
        <button id="btn-export-kas-excel" class="btn btn-sm btn-primary">
            <i class="la la-file-download"></i> Excel
        </button>
    </div>

    <div class="table-responsive">
        <table class="detail-information table info-cast-account">
            <thead>
                <tr>
                <th>{{ trans('backpack::crud.cash_account.field_transaction.date_transaction.label') }}</th>
                <th>{{ trans('backpack::crud.cash_account.field_transaction.nominal.label') }}</th>
                <th>{{ trans('backpack::crud.cash_account.field_transaction.description.label') }}</th>
                <th>{{ trans('backpack::crud.cash_account.field_transaction.kdp.label') }}</th>
                <th>{{ trans('backpack::crud.cash_account.field_transaction.job_name.label') }}</th>
                <th>{{ trans('backpack::crud.cash_account.field_transaction.account.label') }}</th>
                <th>{{ trans('backpack::crud.cash_account.field_transaction.no_invoice.label') }}</th>
                <th>{{ trans('backpack::crud.cash_account.field_transaction.status.label') }}</th>
                <th></th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>


@push('inline_scripts')
    @once
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
                text-align: left;
                white-space: nowrap;
            }
            #modalEdit {
                z-index: 1060;
            }

            #modalDelete {
                z-index: 1060;
            }

            .modal-backdrop.second-backdrop {
                z-index: 1055;
            }
        </style>
    @endonce
@endpush

@push('after_scripts')
    @once
        <div class="modal fade second" id="modal2" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header justify-content-center">
                        <h5 class="modal-title text-center">Modal Kedua</h5>
                        <button type="button" class="btn-close position-absolute end-0 me-3" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        Ini modal kedua, modal pertama tetap terbuka.
                    </div>
                </div>
            </div>
        </div>
        <script>
            if (typeof editEntry != 'function') {
                function editEntry(button){
                    let modal2 = new bootstrap.Modal(document.getElementById('modalEdit'), {
                        backdrop: false
                    });
                    modal2.show();

                    let backdrop = document.createElement('div');
                    backdrop.className = 'modal-backdrop fade show second-backdrop';
                    document.body.appendChild(backdrop);
                    document.getElementById('modalEdit').addEventListener('hidden.bs.modal', function () {
                        backdrop.remove();
                    }, { once: true });

                    var route = $(button).attr('data-route');
                    var title = $(button).attr('data-title-edit');
                    var action = $(button).attr('data-route-action');

                    $('#modalEdit .modal-body').html('loading...');
                    $('#modalEdit #modalTitleCentered').html(title);

                    $.ajax({
                        url: route,
                        type: 'GET',
                        typeData: 'json',
                        success: function (data) {
                            $('#modalEdit .modal-body').html(data.html);
                            $('#modalEdit #form-edit').attr('action', action);
                        },
                        error: function (xhr, status, error) {
                            console.error(xhr);
                            alert('An error occurred while loading the create form.');
                        }
                    });
                }
            }
            if(typeof deleteEntry != 'function') {

                function deleteEntry(button) {

                    let modal2 = new bootstrap.Modal(document.getElementById('modalDelete'), {
                        backdrop: false
                    });
                    modal2.show();

                    let backdrop = document.createElement('div');
                    backdrop.className = 'modal-backdrop fade show second-backdrop';
                    document.body.appendChild(backdrop);
                    document.getElementById('modalDelete').addEventListener('hidden.bs.modal', function () {
                        backdrop.remove();
                    }, { once: true });

                    // ask for confirmation before deleting an item
                    // e.preventDefault();
                    var route = $(button).attr('data-route');
                    var title = $(button).attr('data-title-delete');
                    var body = $(button).attr('data-body');

                    $("#modalDelete #modalDeleteLabel").html(title);
                    $("#modalDelete .modal-body").html(body);

                    $('#btn-delete').off('click').on('click', function(e){

                        var btn = $(this);
                        btn.attr('disabled', true);
                        btn.find('.btn-text').html("Loading...");
                        btn.find('.btn-spinner').removeClass('d-none');

                        $.ajax({
                            url: route,
                            type: 'DELETE',
                            success: function(result) {
                                btn.attr('disabled', false);
                                btn.find('.btn-text').html("{{ trans('backpack::crud.delete') }}");
                                btn.find('.btn-spinner').addClass('d-none');

                                hideModal('modalDelete');

                                if (result == 1) {
                                    // Redraw the table
                                    if (typeof crud != 'undefined' && typeof crud.table != 'undefined') {
                                        // Move to previous page in case of deleting the only item in table
                                        if(crud.table.rows().count() === 1) {
                                        crud.table.page("previous");
                                        }

                                        crud.table.draw(false);
                                    }

                                    // Show a success notification bubble
                                    new Noty({
                                    type: "success",
                                    text: "{!! '<strong>'.trans('backpack::crud.delete_confirmation_title').'</strong><br>'.trans('backpack::crud.delete_confirmation_message') !!}"
                                    }).show();

                                    // Hide the modal, if any
                                    $('.modal').modal('hide');
                                } else {
                                    // if the result is an array, it means
                                    // we have notification bubbles to show
                                    if (result instanceof Object) {
                                    // trigger one or more bubble notifications
                                    Object.entries(result).forEach(function(entry, index) {
                                        var type = entry[0];
                                        if(type != 'events'){
                                            entry[1].forEach(function(message, i) {
                                                // new Noty({
                                                // type: type,
                                                // text: message
                                                // }).show();
                                                swal({
                                                    title: "Success",
                                                    text: message,
                                                    icon: "success",
                                                    timer: 4000,
                                                    buttons: false,
                                                });

                                            });
                                        }
                                    });
                                    if(result.events){
                                        forEachFlexible(result.events, function(eventname, data){
                                            eventEmitter.emit(eventname, data);
                                        });
                                    }
                                    } else {// Show an error alert
                                        swal({
                                        title: "{!! trans('backpack::crud.delete_confirmation_not_title') !!}",
                                        text: "{!! trans('backpack::crud.delete_confirmation_not_message') !!}",
                                        icon: "error",
                                        timer: 4000,
                                        buttons: false,
                                        });
                                    }
                                }
                            },
                            error: function(result) {
                                btn.attr('disabled', false);
                                btn.find('.btn-text').html("{{ trans('backpack::crud.delete') }}");
                                btn.find('btn-spinner').addClass('d-none');
                                // Show an alert with the result
                                swal({
                                title: "{!! trans('backpack::crud.delete_confirmation_not_title') !!}",
                                text: "{!! trans('backpack::crud.delete_confirmation_not_message') !!}",
                                icon: "error",
                                timer: 4000,
                                buttons: false,
                                });
                            }
                        });
                    });

                }
            }
        </script>
        <script>
            SIAOPS.setAttribute("{{$name}}", function(){
                return {
                    dom: $('#{{$name}}'),
                    route: "{{url($crud->route)}}",
                    eventLoader: function(){
                        // event when create success
                        // eventEmitter.on("{{$name}}_create_success", function(data){
                        //     $('#{{$name}} .saldo-str').html(data.new_saldo);
                        // });

                        // event when delete success
                        // eventEmitter.on("{{$name}}_delete_success", function(data){
                        //     $('#{{$name}} .saldo-str').html(data.new_saldo);
                        // });

                        // event when update success
                        // eventEmitter.on("{{$name}}_update_success", function(data){
                        //     $('#{{$name}} .saldo-str').html(data.new_saldo);
                        // });
                    },
                    show: function(){
                        var instance = this;
                        // $('#{{$name}}').modal('show');
                        return this;
                    },
                    loadData: function(id){
                        var instance = this;
                        instance.loadingBdoy();
                        var url = instance.route+'-show?_id='+id;
                        var url_export_pdf = instance.route+'/export-trans-pdf?id='+id;
                        var url_export_excel = instance.route+'/export-trans-excel?id='+id;

                        $('#btn-export-kas-pdf').attr('data-url', url_export_pdf);
                        $('#btn-export-kas-excel').attr('data-url', url_export_excel);
                        $.ajax({
                            url: url,
                            type: 'GET',
                            typeData: 'json',
                            success: function (response) {
                                instance.refreshBody(response);
                            },
                            error: function (xhr, status, error) {
                                console.error(xhr);
                                alert('An error occurred while loading the create form.');
                            }
                        });
                        return this;
                    },
                    loadingBdoy: function(){
                        $("#{{$name}} .modal-title").html('...');
                        $("#{{$name}} .bank_name").html('...');
                        $('#{{$name}} .no_account').html('...');
                        $('#{{$name}} .total_saldo').html('...');

                        var tabel = $('#{{$name}} .detail-information tbody').html('');
                    },
                    refreshBody: function(data){
                        var header = data.result.cast_account;
                        var details = data.result.detail;
                        var balance = data.result.balance;
                        $("#{{$name}} .modal-title").html(header.name);
                        $("#{{$name}} .bank_name").html(header.bank_name);
                        $('#{{$name}} .no_account').html(header.no_account);
                        $('#{{$name}} .total_saldo').html(balance);

                        var tabel = $('#{{$name}} .detail-information tbody').html('');

                        details.forEach((detail) => {

                            var btn = ``;

                            var btn_delete = '';

                            if(detail.is_primary){
                                if(detail.is_transfer == null){
                                    btn += `
                                        <a href="javascript:void(0)"
                                            onclick="editEntry(this)"
                                            data-route="${detail.url_edit}"
                                            data-route-action="${detail.url_update}"
                                            data-title-edit="Ubah Data Transaksi"
                                            bp-button="update" class="btn btn-sm btn-primary">
                                                <i class="la la-pen"></i>
                                        </a>
                                    `;
                                }

                                if(detail.log_payment_id){
                                    btn += `
                                            <a href="javascript:void(0)"
                                                onclick="deleteEntry(this)"
                                                bp-button="delete"
                                                data-route="${detail.url_delete}"
                                                class="btn btn-sm btn-danger"
                                                data-button-type="delete"
                                                data-title-delete="Hapus Item Transaksi"
                                                data-body="Apakah anda yakin ingin menghapus data item transaksi ini ?">
                                                    <i class="la la-trash"></i>
                                                </a>
                                            `;
                                }
                            }else{
                                if(detail.is_transfer == null){
                                      if(detail.kdp_str == '-'){
                                            btn += `
                                                <a href="javascript:void(0)"
                                                    onclick="editEntry(this)"
                                                    data-route="${detail.url_edit}"
                                                    data-route-action="${detail.url_update}"
                                                    data-title-edit="Ubah Data Transaksi"
                                                    bp-button="update" class="btn btn-sm btn-primary">
                                                        <i class="la la-pen"></i>
                                                </a>
                                            `;
                                        btn_delete = `
                                            <a href="javascript:void(0)"
                                                onclick="deleteEntry(this)"
                                                bp-button="delete"
                                                data-route="${detail.url_delete}"
                                                class="btn btn-sm btn-danger"
                                                data-button-type="delete"
                                                data-title-delete="Hapus Item Transaksi"
                                                data-body="Apakah anda yakin ingin menghapus data item transaksi ini ?">
                                                    <i class="la la-trash"></i>
                                                </a>
                                            `;
                                    }
                                }
                            }

                            if(btn_delete != ''){
                                if(detail.log_payment_id){
                                    btn_delete = `
                                    <a href="javascript:void(0)"
                                        onclick="deleteEntry(this)"
                                        bp-button="delete"
                                        data-route="${detail.url_delete}"
                                        class="btn btn-sm btn-danger"
                                        data-button-type="delete"
                                        data-title-delete="Hapus Item transaksi void"
                                        data-body="Apakah anda yakin ingin menghapus data item transaksi ini ?">
                                            <i class="la la-trash"></i>
                                        </a>
                                    `;
                                    btn += btn_delete;
                                }else{
                                    btn += btn_delete;
                                }
                            }

                            var str = `
                                <tr>
                                    <td>${detail.date_transaction_str}</td>
                                    <td>${detail.nominal_transaction_str}</td>
                                    <td>${detail.description_str}</td>
                                    <td>${detail.kdp_str}</td>
                                    <td>${detail.job_name_str}</td>
                                    <td>${detail.account_id_str}</td>
                                    <td>${detail.no_invoice_str}</td>
                                    <td>${detail.status_str}</td>
                                    <td>${btn}</td>
                                </tr>
                            `;
                            tabel.append(str);
                        });

                    },
                    load:function(){

                    }
                }
            });
            SIAOPS.getAttribute("{{$name}}").load();
        </script>
        <script>
            $(function(){
               $('#btn-export-kas-pdf').click(async function (){
                    setLoadingButton("#btn-export-kas-pdf", true);

                    var get_url_export = $(this).attr('data-url');
                    var get_title_export = "Laporan_kas_transaksi_rekening_"+ $("#{{$name}} .modal-title").html();
                    var params_url = MakeParamUrl(window.filter_tables || {});

                    var url_export_with_params = get_url_export + params_url;

                    if(get_url_export == ''){
                        setLoadingButton("#btn-export-kas-pdf", false);
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
                        setLoadingButton("#btn-export-kas-pdf", false);
                    }else if(response){
                        let result = await response;
                        setLoadingButton("#btn-export-kas-pdf", false);

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

                $('#btn-export-kas-excel').click(async function (){
                    setLoadingButton("#btn-export-kas-excel", true);

                    var get_url_export = $(this).attr('data-url');
                    var get_title_export = "Laporan_kas_transaksi_rekening_"+ $("#{{$name}} .modal-title").html();
                    var params_url = MakeParamUrl(window.filter_tables || {});

                    var url_export_with_params = get_url_export + params_url;

                    if(get_url_export == ''){
                        setLoadingButton("#btn-export-kas-excel", false);
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
                        setLoadingButton("#btn-export-kas-excel", false);
                    }else if(response){
                        let result = await response;
                        setLoadingButton("#btn-export-kas-excel", false);

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

            });
        </script>
    @endonce
@endpush


