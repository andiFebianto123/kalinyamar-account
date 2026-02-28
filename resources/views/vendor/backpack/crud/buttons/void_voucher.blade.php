@if ($crud->hasAccess('void', $entry) && $entry->payment_log_id)
    <a href="javascript:void(0)" 
        onclick="voidVoucherPayment(this)" 
        data-route="{{ url(config('backpack.base.route_prefix').'/fa/voucher/void-payment/'.$entry->getKey()) }}" 
        class="btn btn-sm btn-warning" 
        title="{{ trans('backpack::crud.void_payment.title') }}"
        style="color: white; font-weight: bold;">
        <i class="la la-undo"></i> {{ trans('backpack::crud.void_payment.void') }}
    </a>
@endif

@push('after_scripts') @if (request()->ajax()) @endpush @endif
<script>
    if (typeof voidVoucherPayment != 'function') {
        function voidVoucherPayment(button) {
            var route = $(button).attr('data-route');
            
            swal({
                title: "{{ trans('backpack::crud.void_payment.confirm_title') }}",
                text: "{{ trans('backpack::crud.void_payment.confirm_text') }}",
                icon: "warning",
                buttons: {
                    cancel: {
                        text: "{{ trans('backpack::crud.cancel') }}",
                        value: null,
                        visible: true,
                        className: "btn btn-secondary",
                        closeModal: true,
                    },
                    confirm: {
                        text: "{{ trans('backpack::crud.void_payment.button_confirm') }}",
                        value: true,
                        visible: true,
                        className: "btn btn-warning",
                        closeModal: false
                    }
                },
                dangerMode: true,
            }).then((value) => {
                if (value) {
                    $.ajax({
                        url: route,
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(result) {
                            if (result.success) {
                                swal({
                                    title: "{{ trans('backpack::crud.void_payment.success_title') }}",
                                    text: result.message || "{{ trans('backpack::crud.void_payment.success_text') }}",
                                    icon: "success",
                                    timer: 2000,
                                    buttons: false
                                });

                                if(result.events){
                                    forEachFlexible(result.events, function(eventname, data){
                                        eventEmitter.emit(eventname, data);
                                    });
                                }
                                
                                // Refresh datatables
                                if (typeof crud != 'undefined' && typeof crud.table != 'undefined') {
                                    crud.table.draw(false);
                                }
                            } else {
                                swal("{{ trans('backpack::crud.void_payment.error_title') }}", result.message || "{{ trans('backpack::crud.void_payment.error_text') }}", "error");
                            }
                        },
                        error: function(xhr) {
                            var response = xhr.responseJSON;
                            swal("{{ trans('backpack::crud.error_request') }}", response?.message || "{{ trans('backpack::crud.void_payment.error_request') }}", "error");
                        }
                    });
                }
            });
        }
    }
</script>
@if (!request()->ajax()) @endpush @endif
