<div>
    <div class="row">
        <div class="col-md-3">
            <div id="list-account-setting" class="list-group">
                <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" href="#list-item-1">
                    Pengaturan Merk
                    <i class="la la-angle-right"></i>
                </a>
                <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" href="#list-item-2">
                    Pengaturan Sistem
                    <i class="la la-angle-right"></i>
                </a>
                <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" href="#list-item-3">
                    Pengaturan Perusahaan
                    <i class="la la-angle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-md-6" data-bs-spy="scroll" data-bs-target="#list-account-setting" data-bs-offset="0" class="scrollspy-example" tabindex="0">
            <div class="card2 mb-4" id="list-item-1">
                <div class="card2-parent-header">
                    <div class="card2-header fs-6">Pengaturan Merk</div>
                </div>
                <div class="card2-body">
                    <form action="{{ url($crud->route.'/updated') }}" id="form-update-personal" method="POST" enctype="multipart/form-data">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="card2">
                                <div class="card2-parent-header">
                                    <div class="card2-header fs-6">Logo Dark</div>
                                </div>
                                <div class="card2-body">
                                    <img src="{{ asset("public/kp-logo.png") }}" width="200" class="sidebar-brand-full">
                                    <div class="mb-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <button type="button" class="btn btn-primary" onclick="document.getElementById('upload-input').click()"><i class="la la-upload"></i> Upload File Here</button>
                                            <span id="file-name" class=""></span>
                                        </div>
                                        <input type="file" id="upload-input" class="d-none" onchange="updateFileName(this)">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="submit" id="btn-update-personal" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                    </form>
                </div>
            </div>
            <div class="card2 mb-4" id="list-item-2">
                <div class="card2-parent-header">
                    <div class="card2-header fs-6">Pengaturan Sistem</div>
                </div>
                <div class="card2-body">
                    <form action="#" method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="mataUang" class="form-label">Mata Uang</label>
                                    <input type="text" class="form-control" id="mataUang" value="IDR">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Posisi Simbol Mata Uang</label>
                                    <div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="posisiSimbol" id="posisiPre" value="pre" checked>
                                            <label class="form-check-label" for="posisiPre">Pre</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="posisiSimbol" id="posisiPost" value="post">
                                            <label class="form-check-label" for="posisiPost">Post</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="poPrefix" class="form-label">PO Prefix</label>
                                    <input type="text" class="form-control" id="poPrefix" value="PO-">
                                </div>
                                <div class="mb-3">
                                    <label for="kodeKerjaPrefix" class="form-label">Kode Kerja Prefix</label>
                                    <input type="text" class="form-control" id="kodeKerjaPrefix" value="KJ">
                                </div>
                                <div class="mb-3">
                                    <label for="fakturPrefix" class="form-label">Faktur Prefix</label>
                                    <input type="text" class="form-control" id="fakturPrefix" value="FA">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="simbolMataUang" class="form-label">Simbol Mata Uang</label>
                                    <input type="text" class="form-control" id="simbolMataUang" value="Rp">
                                </div>
                                <div class="mb-3">
                                    <label for="formatAngkaDesimal" class="form-label">Format Angka Desimal</label>
                                    <input type="number" class="form-control" id="formatAngkaDesimal" value="2">
                                </div>
                                <div class="mb-3">
                                    <label for="spkPrefix" class="form-label">SPK Prefix</label>
                                    <input type="text" class="form-control" id="spkPrefix" value="SPK">
                                </div>
                                <div class="mb-3">
                                    <label for="voucherPrefix" class="form-label">Voucher Prefix</label>
                                    <input type="text" class="form-control" id="voucherPrefix" value="VCH">
                                </div>
                                <div class="mb-3">
                                    <label for="invoicePrefix" class="form-label">Invoice Prefix</label>
                                    <input type="text" class="form-control" id="invoicePrefix" value="INV">
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
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

    .list-group-item.active {
        z-index: 2;
        background-color: #145388 !important;
        border-color: #145388 !important;
    }

    </style>
@endpush

@push('after_scripts')
<script>
    function updateFileName(input) {
        const fileName = input.files[0] ? input.files[0].name : 'Belum ada file dipilih';
        document.getElementById('file-name').textContent = fileName;
    }
</script>
<script>
    $('#btn-update-personal').on('click', async function (e) {
        e.preventDefault();
        var url = $('#form-update-personal').attr('action');

        var lazySubmit = await new Promise((resolve, reject) => {
            setTimeout(() => {
                resolve(1)
            }, 100);
        });

        var formData = new FormData($('#form-update-personal')[0]);
        normalizeShowMessage('form-update-personal');
        btnLoader('btn-update-personal', false);
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            typeData: 'json',
            success: function (data) {
                btnLoader('btn-update-personal');
                if(data.success){
                    swal({
                        title: "Success",
                        text: "{!! trans('backpack::crud.update_success') !!}",
                        icon: "success",
                        timer: 4000,
                        buttons: false,
                    });
                    if(window.crud.table){
                        window.crud.table.ajax.reload();
                    }
                    if(data.events){
                        forEachFlexible(data.events, function(eventname, data){
                            eventEmitter.emit(eventname, data);
                        });
                    }
                }else{
                    swal({
                        title: "{!! trans('backpack::crud.ajax_error_title') !!}",
                        text: data.error,
                        icon: "error",
                        timer: 4000,
                        buttons: false,
                    });
                }
            },
            error: function (xhr, status, error) {
                // console.error(xhr);
                btnLoader('btn-update-personal');
                errorShowMessage('form-update-personal', xhr.responseJSON.errors);
            }
        });
    });
</script>
@endpush
