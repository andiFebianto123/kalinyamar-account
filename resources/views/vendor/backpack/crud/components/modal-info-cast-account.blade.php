{{-- <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#rekeningModal">
  Lihat Rekening
</button> --}}

<div>
    <p style="font-size: 15px;">
        <span style="">{{trans('backpack::crud.modal.bank_name')}}</span>: <span class="bank_name fw-bold"></span> |
        <span>{{ trans('backpack::crud.modal.no_account') }} :</span> <span class="no_account fw-bold"></span>
    </p>
    <p class="mb-4">
        <strong><span style="font-size: 20px;">Saldo</span></strong>
        <span class="total_saldo fs-4 fw-bold text-dark"></span>
    </p>

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
                </tr>
            </thead>
            <tbody>
                {{-- <tr>
                <td>3 Feb 2025</td>
                <td>Rp3.000.000</td>
                <td>PO001</td>
                <td>INV33/55/55</td>
                <td>Masuk</td>
                </tr> --}}
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
        </style>
    @endonce
@endpush

@push('after_scripts')
    @once
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
                        $.ajax({
                            url: instance.route+'-show?_id='+id,
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
                        $("#{{$name}} .modal-title").html(header.name);
                        $("#{{$name}} .bank_name").html(header.bank_name);
                        $('#{{$name}} .no_account').html(header.no_account);
                        $('#{{$name}} .total_saldo').html(header.total_saldo_str);

                        var tabel = $('#{{$name}} .detail-information tbody').html('');

                        details.forEach((detail) => {
                            tabel.append(`
                                <tr>
                                    <td>${detail.date_transaction_str}</td>
                                    <td>${detail.nominal_transaction_str}</td>
                                    <td>${detail.description_str}</td>
                                    <td>${detail.kdp_str}</td>
                                    <td>${detail.job_name_str}</td>
                                    <td>${detail.account_id_str}</td>
                                    <td>${detail.no_invoice_str}</td>
                                    <td>${detail.status_str}</td>
                                </tr>
                            `);
                        });

                    },
                    load:function(){

                    }
                }
            });
            SIAOPS.getAttribute("{{$name}}").load();
        </script>
    @endonce
@endpush


