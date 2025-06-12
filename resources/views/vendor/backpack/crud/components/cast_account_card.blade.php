<div>
    @php
        // dd($access->where('id', 4)->first(), $access);
    @endphp
    <div><h5>{{ $detail->name }}</h5></div>
    <div class="d-flex justify-content-between">
        <div class="left-buttons d-flex gap-2">
            {{ trans('backpack::crud.card.cast_account_card.name_bank') }} : <b>{{ $detail->bank_name }}</b> | {{ trans("backpack::crud.card.cast_account_card.no_rekening") }} : <b>{{$detail->no_account}}</b>
        </div>
        <div class="right-buttons d-flex gap-2">
            <button class="btn btn-sm btn-secondary"
                id="btn-{{$name}}-info"
                data-bs-toggle="modal"
                data-bs-target="#modal_info_cast_account"
                ><i class="la la-eye"></i></button>
            @if ($access->where('id', 3)->first())
                <button
                    id="btn-{{$name}}-transfer-balance"
                    data-bs-toggle="modal"
                    data-bs-target="#modal_transfer_balance"
                    class="btn btn-sm btn-primary">
                    <i class="la la-exchange-alt"></i></button>
            @endif
            <button
                id="btn-{{$name}}-add"
                data-route="{{url($crud->route.'/create?_id='.$detail->id)}}"
                data-bs-toggle="modal"
                data-bs-target="#modalCreate"
                data-title="{{ trans('backpack::crud.add').' '.trans('backpack::crud.cash_account.title_modal_create_transaction') }}"
                class="btn btn-sm btn-primary"><i class="la la-plus"></i></button>
            <button
                id="btn-{{$name}}"
                {{-- onclick="deleteEntryCardAccount(this)" --}}
                data-route="{{ url($crud->route.'/'.$detail->id) }}"
                data-name="{{ $detail->name }}"
                data-namecard="{{$name}}"
                data-button-type="delete"
                data-bs-toggle="modal"
                data-bs-target="#modalDelete"
                class="btn btn-sm btn-danger">
                <i class="la la-trash"></i>
            </button>
        </div>
    </div>
    <div>
        <strong>{{ trans('backpack::crud.card.cast_account_card.balance') }} : </strong><span class="saldo-str">Rp{!! \App\Http\Helpers\CustomHelper::formatRupiah($detail->total_saldo) !!}</span>
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
    @endonce
@endpush

@push('after_scripts')
    <script>
        SIAOPS.setAttribute("{{$name}}", function(){
            return {
                id: "{{$detail->id}}",
                btnDelete:$("#btn-{{$name}}"),
                route: "{{url($crud->route)}}",
                btnAdd: $("#btn-{{$name}}-add"),
                deleteEntryCardAccount: function(button) {
                    // ask for confirmation before deleting an item
                    // e.preventDefault();
                    var route = $(button).attr('data-route');
                    var title = $(button).data('name');

                    var nameCard = $(button).data('namecard');

                    var textBody = "{{ trans('backpack::crud.delete_confirm_2') }} "+title+' ?';

                    $('#modalDelete .modal-body').html(textBody);

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
                                    $('#'+nameCard).hide();
                                } else {
                                    // if the result is an array, it means
                                    // we have notification bubbles to show
                                    if (result instanceof Object) {
                                    // trigger one or more bubble notifications
                                    Object.entries(result).forEach(function(entry, index) {
                                        var type = entry[0];
                                        entry[1].forEach(function(message, i) {
                                            new Noty({
                                            type: type,
                                            text: message
                                            }).show();
                                        });
                                    });
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

                },
                eventLoader: function(){
                    // event when create success

                    eventEmitter.on("cast_account_store_success", function(){
                        window.location.href = location.href;
                    });

                    eventEmitter.on("{{$name}}_create_success", function(data){
                        $('#{{$name}} .saldo-str').html(data.new_saldo);
                    });

                    // event when delete success
                    // eventEmitter.on("{{$name}}_delete_success", function(data){
                    //     $('#{{$name}} .saldo-str').html(data.new_saldo);
                    // });

                    // event when update success
                    // eventEmitter.on("{{$name}}_update_success", function(data){
                    //     $('#{{$name}} .saldo-str').html(data.new_saldo);
                    // });
                },
                load:function(){
                    var instance = this;

                    // load event for component
                    instance.eventLoader();

                    $(instance.btnDelete).off('click').click(function(e){
                        e.preventDefault();
                        instance.deleteEntryCardAccount(this);
                    });

                    $(instance.btnAdd).off('click').click(function(e){
                        e.preventDefault();
                        var url =  $(this).data('route');
                        $('#modalCreate .modal-body').html('loading...');
                        $('#modalCreate .modal-title').html($(this).data('title'));
                        let routeAddTransaction = instance.route+'-transaction';
                        $.ajax({
                            url: url,
                            type: 'GET',
                            typeData: 'json',
                            success: function (data) {
                                $('#modalCreate .modal-body').html(data.html);
                                $('#modalCreate #form-create').attr('action', routeAddTransaction);
                            },
                            error: function (xhr, status, error) {
                                console.error(xhr);
                                alert('An error occurred while loading the create form.');
                            }
                        });
                    });

                    $("#btn-{{$name}}-info").off('click').click(function(e){
                        SIAOPS.getAttribute('modal_info_cast_account')
                        .loadData(instance.id);
                    });

                    $("#btn-{{$name}}-transfer-balance").off('click').click(function(e){
                        SIAOPS.getAttribute('modal_transfer_balance')
                        .loadData(instance.id);
                    });

                }
            }
        });
        window.addEventListener('load', function () {
            SIAOPS.getAttribute("{{$name}}").load();
        });
    </script>
@endpush
