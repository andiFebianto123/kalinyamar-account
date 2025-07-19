<?php
    $user_id = backpack_user()->id;
    // {{ $entry->getKey() }}, {{ $entry->approval_no_apprv }}, {{ $entry->voucer_edit_id }}
?>
@if ($user_id == $entry->approval_user_id && $entry->approval_status == 'Pending')
    <button
            type="button"
            class="btn btn-sm btn-primary"
            onclick="confirmApprovalOrReject(this)"
            data-route="{{ url($crud->route.'/'.$entry->voucer_edit_id.'/approve') }}"
            data-toggle="tooltip"
            data-bs-toggle="modal"
            data-bs-target="#modalApproval"
            data-no_apprv=" {{ $entry->approval_no_apprv }}"
            data-title-approval="{{ trans('backpack::crud.voucher.confirm.title') }}"
            data-body="{{ trans('backpack::crud.voucher.confirm.confirm_approved_statement') }}"
            title="Approve"
        >
        Approve
    </button>
@endif

@push('after_scripts') @if (request()->ajax()) @endpush @endif
<script>
    function confirmApprovalOrRejectold(id, no_apprv, id_voucher_edit) {
        swal({
            title: "{{trans('backpack::crud.voucher.confirm.caution')}}",
            text: "{{trans('backpack::crud.voucher.confirm.confirm_approved_statement')}}",
            icon: "warning",
            buttons: {
                cancel: "{{trans('backpack::crud.voucher.confirm.cancel')}}",
                reject: {
                    text: "Reject",
                    value: "Rejected",
                    className: "swal-button--danger"
                },
                approve: {
                    text: "Approve",
                    value: "Approved",
                    className: "swal-button--confirm"
                }
            },
            dangerMode: true,
        }).then((value) => {
            if (value === "Approved" || value === "Rejected") {
                $.ajax({
                    url: `{{ url($crud->route) }}/${id_voucher_edit}/approve`, // ganti sesuai route kamu
                    method: 'POST',
                    data: {
                        action: value,
                        no_apprv: no_apprv,
                    },
                    success: function () {
                        swal("{{trans('backpack::crud.voucher.confirm.alert_success')}}", "{{trans('backpack::crud.voucher.confirm.confirm_after_success')}}-"+value, "success").then(() => {
                            eventEmitter.emit('crudTable-voucher_create_success', {});
                            eventEmitter.emit('crudTable-history_edit_voucher_create_success', {});
                        });
                    },
                    error: function () {
                        swal("Gagal!", "Terjadi kesalahan saat memproses.", "error");
                    }
                });
            }
        });
    }
    function confirmApprovalOrReject(button) {
        var route = $(button).attr('data-route');
        var no_apprv = $(button).attr('data-no_apprv');
        var title = $(button).attr('data-title-approval');
        var body = $(button).attr('data-body');

        $("#modalApproval #modalApprovalLabel").html(title);
        $("#modalApproval .modal-body").html(body);

        $('#btn-approve').off('click').on('click', function(e){
            var btn = $(this);
            btn.attr('disabled', true);
            btn.find('.btn-text').html("Loading...");
            btn.find('.btn-spinner').removeClass('d-none');

            $.ajax({
                url: route, // ganti sesuai route kamu
                method: 'POST',
                data: {
                    action: "Approved",
                    no_apprv: no_apprv,
                },
                success: function (response) {
                    btn.attr('disabled', false);
                    btn.find('.btn-text').html("{{ trans('backpack::crud.approve_submit') }}");
                    btn.find('.btn-spinner').addClass('d-none');
                    hideModal('modalApproval');
                    swal("{{trans('backpack::crud.voucher.confirm.alert_success')}}", "{{trans('backpack::crud.voucher.confirm.confirm_after_success')}}", "success").then(() => {
                        if(response.events){
                            forEachFlexible(response.events, function(eventname, data){
                                eventEmitter.emit(eventname, data);
                            });
                        }
                    });
                },
                error: function () {
                    swal("Gagal!", "Terjadi kesalahan saat memproses.", "error");
                    btn.attr('disabled', false);
                    btn.find('.btn-text').html("{{ trans('backpack::crud.approve_submit') }}");
                    btn.find('.btn-spinner').addClass('d-none');
                }
            });
        });
    }
</script>
@if (!request()->ajax()) @endpush @endif
