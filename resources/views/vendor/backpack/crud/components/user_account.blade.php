<div>
    <div class="row">
        <div class="col-md-3">
            <div id="list-account-setting" class="list-group">
                <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" href="#list-item-1">
                    Personal Info
                    <i class="la la-angle-right"></i>
                </a>
                <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" href="#list-item-2">
                    Ganti Password
                    <i class="la la-angle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-md-6" data-bs-spy="scroll" data-bs-target="#list-account-setting" data-bs-offset="0" class="scrollspy-example" tabindex="0">
            <div class="card2 mb-4" id="list-item-1">
                <div class="card2-parent-header">
                    <div class="card2-header fs-6">Personal Info</div>
                </div>
                <div class="card2-body">
                    <form action="{{ url($crud->route.'/updated') }}" id="form-update-personal" method="POST" enctype="multipart/form-data">
                    <div class="row mb-3">
                        <div class="col-md-6 form-group">
                            <label class="form-label">Nama</label>
                            <input type="text" class="form-control" name="name" value="{{ $user_name }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="{{ $email }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label d-block">Upload File</label>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-primary" onclick="document.getElementById('upload-input').click()"><i class="la la-upload"></i> Upload File Here</button>
                            <span id="file-name" class=""></span>
                        </div>
                        <input type="file" id="upload-input" name="profile_photo" class="d-none" onchange="updateFileName(this)">
                        <div class="mt-1">Please upload a valid image file. Size of image should not be more than 2MB.</div>
                    </div>
                    <div class="text-end">
                        <button type="submit" id="btn-update-personal" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                    </form>
                </div>
            </div>
            <div class="card2 mb-4" id="list-item-2">
                <div class="card2-parent-header">
                    <div class="card2-header fs-6">Ganti Password</div>
                </div>
                <div class="card2-body">
                    <form action="{{ url($crud->route.'/updated_password') }}" id="form-update-password" method="POST" enctype="multipart/form-data">
                    <div class="row mb-3">
                        <div class="col-md-6 form-group">
                            <label class="form-label">Password Lama</label>
                            <input type="password" class="form-control" name="old_password" placeholder="Masukkan password lama">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">Password Baru</label>
                            <input type="password" class="form-control" name="new_password" placeholder="Masukkan password baru">
                        </div>
                    </div>
                    <div class="mb-3 form-group">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" class="form-control" name="new_password_confirmation" placeholder="Masukkan password">
                    </div>
                    <div class="text-end">
                        <button type="submit" id="btn-change-password" class="btn btn-primary">Ganti Password</button>
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
                    window.location.href = location.href;
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
<script>
    $('#btn-change-password').on('click', async function (e) {
        e.preventDefault();
        var url = $('#form-update-password').attr('action');

        var lazySubmit = await new Promise((resolve, reject) => {
            setTimeout(() => {
                resolve(1)
            }, 100);
        });

        var formData = new FormData($('#form-update-password')[0]);
        normalizeShowMessage('form-update-password');
        btnLoader('btn-change-password', false);
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            typeData: 'json',
            success: function (data) {
                btnLoader('btn-change-password');
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
                btnLoader('btn-change-password');
                errorShowMessage('form-update-password', xhr.responseJSON.errors);
            }
        });
    });
</script>
@endpush
