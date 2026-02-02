@if ($crud->hasAccess('delete', $entry))
    <a href="javascript:void(0)" 
        onclick="voidEntry(this)" 
        data-route="{{ url($crud->route.'/'.$entry->getKey()) }}" 
        class="btn btn-sm btn-warning" 
        title="Void Transaksi"
        style="color: white; font-weight: bold;">
        <i class="la la-undo"></i> VOID
    </a>
@endif

@push('after_scripts') @if (request()->ajax()) @endpush @endif
<script>
    if (typeof voidEntry != 'function') {
        function voidEntry(button) {
            var route = $(button).attr('data-route');
            
            swal({
                title: "Konfirmasi Void",
                text: "Apakah Anda yakin ingin membatalkan (Void) transaksi ini? Jurnal dan histori transaksi terkait akan dihapus/dibalikkan.",
                icon: "warning",
                buttons: {
                    cancel: {
                        text: "Batal",
                        value: null,
                        visible: true,
                        className: "btn btn-secondary",
                        closeModal: true,
                    },
                    confirm: {
                        text: "Ya, Void!",
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
                            if (result.success || result == 1 || (result instanceof Object && result.success != false)) {
                                swal({
                                    title: "Berhasil!",
                                    text: "Transaksi telah berhasil di-Void.",
                                    icon: "success",
                                    timer: 2000,
                                    buttons: false
                                });
                                
                                // Refresh datatables
                                if (typeof crud != 'undefined' && typeof crud.table != 'undefined') {
                                    crud.table.draw(false);
                                }
                                
                                // Trigger events jika ada
                                if (result.events) {
                                    Object.entries(result.events).forEach(([eventName, data]) => {
                                        if (typeof eventEmitter !== 'undefined') {
                                            eventEmitter.emit(eventName, data);
                                        }
                                    });
                                }
                            } else {
                                swal("Gagal!", result.message || "Terjadi kesalahan saat melakukan Void.", "error");
                            }
                        },
                        error: function(xhr) {
                            var response = xhr.responseJSON;
                            swal("Error!", response?.message || "Tidak dapat memproses permintaan Void.", "error");
                        }
                    });
                }
            });
        }
    }
</script>
@if (!request()->ajax()) @endpush @endif
