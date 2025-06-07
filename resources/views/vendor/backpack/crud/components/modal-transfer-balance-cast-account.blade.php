<div>
    <p style="font-size: 15px;">
        <span style="">{{trans('backpack::crud.modal.bank_name')}}</span>: <span class="bank_name fw-bold"></span> |
        <span>{{ trans('backpack::crud.modal.no_account') }} :</span> <span class="no_account fw-bold"></span>
    </p>
    <p class="mb-4">
        <strong><span style="font-size: 20px;">Saldo</span></strong>
        <span class="total_saldo fs-4 fw-bold text-dark"></span>
    </p>
    <div class="col-md-12">
        <form action="{{ url($crud->route) }}-move-transaction" id="form-transfer-balance-cast-account">
            <div class="row">
                <input type="hidden" name="cast_account_id" class="cast_account_id">
                <input type="hidden" class="balance" name="balance">
                <div class="form-group col-md-6 required" element="div" bp-field-wrapper="true" bp-field-name="nominal_transaction" bp-field-type="mask" bp-section="crud-field">
                    <label>{{trans('backpack::crud.cash_account.field_transfer.nominal_transfer.label')}}</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input
                            type="text"
                            data-alt="nominal_transaction_masked"
                            data-bs-maskoption="{&quot;reverse&quot;:true}"
                            data-init-function="bpFieldInitMaskElement"
                            value="" placeholder="000.000"
                            id="nominal_transfer_masked"
                            class="form-control"
                            data-initialized="true" maxlength="23">
                    </div>
                    <input type="hidden" name="nominal_transfer" id="nominal_transaction" value="">
                </div>
                <script>
                    SIAOPS.loadScript([
                        "{{ asset('packages/jquery-mask-plugin-master/dist/jquery.mask.min.js') }}"
                    ], {
                        async: false, // Load secara synchronous
                        defer: false,
                    });
                    function bpFieldInitMaskElement(element){
                        var $maskedInput = element;
                        var $hiddenInput = $maskedInput.parent().next();
                        var mask_option = $maskedInput.data('bs-maskoption');

                        function getCleanValue(val) {
                            return val.replace(/[^\d]/g, '');
                        }

                        // $maskedInput.unmask();
                        setTimeout(() => {
                            $maskedInput.mask('000.000.000.000.000', mask_option);
                        }, 100);

                        $hiddenInput.val(getCleanValue($maskedInput.val()));

                        $maskedInput.on('input change keyup', function () {
                            let raw = getCleanValue($(this).val());
                            $hiddenInput.val(raw);
                        });
                    }
                    bpFieldInitMaskElement($('#nominal_transfer_masked'));
                </script>
                <div class="col-md-6"></div>
                <div class="form-group col-md-6 required" element="div" bp-field-wrapper="true" bp-field-name="status" bp-field-type="select_from_array" bp-section="crud-field">
                    <label>{{trans("backpack::crud.cash_account.field_transfer.to_account.label")}}</label>
                    <select name="to_account" class="form-control form-select">
                        {{-- <option value="enter">MASUK</option>
                        <option value="out">KELUAR</option> --}}
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>


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
                        return this;
                    },
                    loadData: function(id){
                        var instance = this;
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
                    refreshBody: function(data){
                        var header = data.result.cast_account;
                        $("#{{$name}} .modal-title").html("{{trans('backpack::crud.modal.transfer_balance')}}");
                        $("#{{$name}} .bank_name").html(header.bank_name);
                        $('#{{$name}} .no_account').html(header.no_account);
                        $('#{{$name}} .total_saldo').html(header.total_saldo_str);

                        $('#{{$name}} .cast_account_id').val(header.id);

                        $('#{{$name}} #form-transfer-balance-cast-account .balance').val(header.total_saldo);
                    },
                    createMoveTransfer:function(){
                        var url = $('#form-transfer-balance-cast-account').attr('action');
                        var formData = new FormData($('#form-transfer-balance-cast-account')[0]);
                        normalizeShowMessage('form-transfer-balance-cast-account');
                        setLoadingButton('.btn-transfer-balance', true);
                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            typeData: 'json',
                            success: function (data) {
                                setLoadingButton('.btn-transfer-balance', false);
                                if(data.success){
                                    $('#form-transfer-balance-cast-account')[0].reset();
                                    swal({
                                        title: "Success",
                                        text: "{!! trans('backpack::crud.insert_success') !!}",
                                        icon: "success",
                                        timer: 4000,
                                        buttons: false,
                                    });
                                    // $('#{{$name}}').modal('hide');
                                    $('.btn-close').click();
                                    if(window.crud){
                                        window.crud.table.ajax.reload();
                                    }
                                    if(data.events){
                                        forEachFlexible(data.events, function(eventname, data){
                                            eventEmitter.emit(eventname, data);
                                        });
                                    }
                                }else{
                                    swal({
                                        title: "{!! trans('backpack::crud.delete_confirmation_not_title') !!}",
                                        text: data.error,
                                        icon: "error",
                                        timer: 4000,
                                        buttons: false,
                                    });
                                }
                            },
                            error: function (xhr, status, error) {
                                // console.error(xhr);
                                setLoadingButton('.btn-transfer-balance', false);
                                errorShowMessage('form-transfer-balance-cast-account', xhr.responseJSON.errors);
                            }
                        });
                    },
                    load:function(){
                        var instance = this;
                        const myModalEl = document.getElementById('{{$name}}');
                        myModalEl.addEventListener('show.bs.modal', (event) => {
                           // load select2
                            normalizeShowMessage('form-transfer-balance-cast-account');
                            $.ajax({
                                url: instance.route+'-select-to-account',
                                type: 'GET',
                                typeData: 'json',
                                success: function (response) {
                                    var option = `<option value="" selected="">{{trans("backpack::crud.cash_account.field_transfer.to_account.placeholder")}}</option>`;

                                    response.result.forEach((value) => {
                                        option += `<option value='${value.id}'>${value.name}</option>`;
                                    });
                                    $('#form-transfer-balance-cast-account select[name="to_account"]').html(option);
                                },
                                error: function (xhr, status, error) {
                                    console.error(xhr);
                                    alert('An error occurred while loading the create form.');
                                }
                            });
                        });
                        $('.btn-transfer-balance').off('click').click(function(){
                            instance.createMoveTransfer();
                        });
                    }
                }
            });
            window.addEventListener('load', function () {
                SIAOPS.getAttribute("{{$name}}").load();
            });
        </script>
    @endonce
@endpush
