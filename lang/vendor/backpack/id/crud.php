<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Backpack Crud Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used by the CRUD interface.
    | You are free to change them to anything
    | you want to customize your views to better match your application.
    |
    */

    // Forms
    'save_action_save_and_new' => 'Simpan dan buat baru',
    'save_action_save_and_edit' => 'Simpan dan lanjutkan perubahan',
    'save_action_save_and_back' => 'Simpan dan kembali',
    'save_action_save_and_preview' => 'Simpan dan lihat',
    'save_action_changed_notification' => 'Perilaku default setelah penyimpanan diubah.',

    // Create form
    'add' => 'Tambah',
    'back_to_all' => 'Kembali ke semua ',
    'cancel' => 'Batal',
    'add_a_new' => 'Tambahkan yang baru ',

    // Edit form
    'edit' => 'Edit',
    'save' => 'Simpan',

    // Translatable models
    'edit_translations' => 'Terjemahan',
    'language' => 'Bahasa',

    // CRUD table view
    'all' => 'Semua ',
    'in_the_database' => 'di database',
    'list' => 'Daftar',
    'reset' => 'Set ulang',
    'actions' => 'Aksi',
    'preview' => 'Detail',
    'delete' => 'Hapus',
    'admin' => 'Admin',
    'details_row' => 'Ini adalah baris rincian. Ubah sesuka Anda.',
    'details_row_loading_error' => 'Terjadi kesalahan saat memuat detail. Silakan coba lagi.',
    'clone' => 'Duplikat',
    'clone_success' => '<strong>Masukan telah diduplikat</strong><br>Masukan baru telah ditambahkan, dengan informasi yang sama.',
    'clone_failure' => '<strong>Proses duplikat gagal</strong><br>Masukan baru tidak dapat dibuat. Silakan coba lagi.',

    // Confirmation messages and bubbles
    'delete_confirm' => 'Anda yakin ingin menghapus item ini?',
    'delete_confirm_2' => 'Apakah anda yakin ingin menghapus data',
    'delete_confirmation_title' => 'Item Dihapus',
    'delete_confirmation_message' => 'Item telah berhasil dihapus.',
    'delete_confirmation_not_title' => 'TIDAK dihapus',
    'delete_confirmation_not_message' => 'Terjadi kesalahan. Item Anda mungkin belum dihapus.',
    'delete_confirmation_not_deleted_title' => 'Tidak dihapus',
    'delete_confirmation_not_deleted_message' => 'Tidak ada yang terjadi. Item Anda aman.',

    // Bulk actions
    'bulk_no_entries_selected_title' => 'Tidak ada masukan yang dipilih',
    'bulk_no_entries_selected_message' => 'Silakan pilih satu atau lebih untuk melakukan tindakan massal pada mereka.',

    // Bulk confirmation
    'bulk_delete_are_you_sure' => 'Anda yakin ingin menghapus :number item ini?',
    'bulk_delete_sucess_title' => 'Item dihapus',
    'bulk_delete_sucess_message' => ' item telah dihapus',
    'bulk_delete_error_title' => 'Penghapusan gagal',
    'bulk_delete_error_message' => 'Satu atau lebih item tidak dapat dihapus',

    // Ajax errors
    'ajax_error_title' => 'Terjadi kesalahan',
    'ajax_error_text' => 'Terjadi kesalahan saat memuat halaman. Harap segarkan halaman.',

    // DataTables translation
    'emptyTable' => 'Tak ada data yang tersedia pada tabel ini',
    'info' => 'Menampilkan _START_ hingga _END_ dari _TOTAL_ masukan',
    'infoEmpty' => 'Tidak ada masukan',
    'infoFiltered' => '(difilter dari _MAX_ jumlah masukan)',
    'infoPostFix' => '.',
    'thousands' => ',',
    'lengthMenu' => '_MENU_ masukan per halaman',
    'loadingRecords' => 'Memuat...',
    'processing' => 'Memproses...',
    'search' => 'Cari',
    'zeroRecords' => 'Tidak ada data yang cocok ditemukan',
    'paginate' => [
        'first' => 'Pertama',
        'last' => 'Terakhir',
        'next' => 'Selanjutnya',
        'previous' => 'Sebelumnya',
    ],
    'aria' => [
        'sortAscending' => ': aktifkan untuk mengurutkan kolom naik',
        'sortDescending' => ': aktifkan untuk mengurutkan kolom turun',
    ],
    'export' => [
        'export' => 'Ekspor',
        'copy' => 'Salin',
        'excel' => 'Excel',
        'csv' => 'CSV',
        'pdf' => 'PDF',
        'print' => 'Cetak',
        'column_visibility' => 'Visibilitas kolom',
    ],

    // global crud - errors
    'unauthorized_access' => 'Akses tidak sah - Anda tidak memiliki izin yang diperlukan untuk melihat halaman ini.',
    'please_fix' => 'Harap perbaiki yang berikut ini:',

    // global crud - success / error notification bubbles
    'insert_success' => 'Item berhasil ditambahkan.',
    'update_success' => 'Item berhasil diubah.',

    // CRUD reorder view
    'reorder' => 'Susun ulang',
    'reorder_text' => 'Gunakan seret & lepas untuk menyusun ulang.',
    'reorder_success_title' => 'Selesai',
    'reorder_success_message' => 'Susunan Anda telah disimpan.',
    'reorder_error_title' => 'Terjadi kesalahan',
    'reorder_error_message' => 'Susunan Anda belum tersimpan',

    // CRUD yes/no
    'yes' => 'Ya',
    'no' => 'Tidak',

    // CRUD filters navbar view
    'filters' => 'Filter',
    'toggle_filters' => 'Alihkan filter',
    'remove_filters' => 'Hapus filter',

    // Fields
    'browse_uploads' => 'Jelajahi unggahan',
    'select_all' => 'Pilih Semua',
    'select_files' => 'Pilih file',
    'select_file' => 'Pilih file',
    'clear' => 'Bersihkan',
    'page_link' => 'Tautan halaman',
    'page_link_placeholder' => 'http://contoh.com/halaman-yang-anda-inginkan',
    'internal_link' => 'Tautan internal',
    'internal_link_placeholder' => 'Slug internal. Cth: \'admin/page\' (tanpa tanda kutip) untuk \':url\'',
    'external_link' => 'Tautan eksternal',
    'choose_file' => 'Pilih File',
    'new_item' => 'Item baru',
    'select_entry' => 'Pilih masukan',
    'select_entries' => 'Pilih masukan',

    //Table field
    'table_cant_add' => 'Tidak dapat menambahkan :entity yang baru',
    'table_max_reached' => 'Jumlah maksimum :max telah tercapai',

    // File manager
    'file_manager' => 'Manajer File',

    // InlineCreateOperation
    'related_entry_created_success' => 'Masukan terkait telah dibuat dan dipilih.',
    'related_entry_created_error' => 'Tidak dapat membuat masukan terkait.',

    // custom

    'filter' => [
        'all_year' => 'Semua Tahun',
        'all_paid' => 'Semua',
        'change_order' => 'Ubah Urutan',
    ],

    'card' => [
        'blank_cast_account' => 'Belum ada akun rekening',
        'cast_account_card' => [
            'name_bank' => 'Nama Bank',
            'no_rekening' => 'Nomor Rekening',
            'balance' => 'Saldo',
            'title_add_transaction' => "Tambah Data Transaksi",
        ],
    ],

    'modal' => [
        'close' => 'Tutup',
        'bank_name' => 'Nama Bank',
        'no_account' => 'Nomor Rekening',
        'transfer_balance' => 'Pindah Saldo',
        'move' => 'Pindah',
        'cancel' => 'Batalkan',
    ],

    'menu' => [
        'dashboard' => 'Dashboard',
        'vendor_subkon' => 'Vendor(Subkon)',
        'list_subkon' => 'Daftar Subkon',
        'po' => 'PO',
        'spk' => 'SPK',
        'client' => 'Client',
        'list_client' => 'Daftar Client',
        'client_po' => 'PO',
        'invoice_client' => 'Invoice (Client)',
        'cash_flow' => 'Arus Rekening',
        'cash_flow_cash' => 'Rekening Kas',
    ],
    'subkon' => [
        'title_header' => 'Daftar Subkon',
        'title_modal_create' => 'Data Vendor (Subkon)',
        'title_modal_edit' => 'Data Vendor (Subkon)',
        'column' => [
            'name' => 'Nama Perusahaan',
            'address' => 'Alamat',
            'npwp' => 'NPWP',
            'phone' => 'Telepon',
            'bank_name' => 'Nama Bank',
            'bank_account' => 'Rekening Bank',
            'list_po' => 'List PO',
            'count_po' => 'Jumlah PO',
            'list_spk' => 'List SPK',
            'count_spk' => 'Jumlah SPK',
        ]
    ],
    'po' => [
        'title_header' => 'PO',
        'title_modal_create' => 'Data PO Vendor (Subkon)',
        'title_modal_edit' => 'Data PO Vendor (Subkon)',
        'column' => [
            'subkon_id ' => 'Nama Perusahaan',
            'po_number' => 'No. PO',
            'date_po' => 'Tanggal PO',
            'job_name' => 'Nama Pekerjaan',
            'job_description' => 'Deskripsi/Detail',
            'job_value' => 'Nilai Pekerjaan',
            'tax_ppn' => 'PPN',
            'total_value_with_tax' => 'Nilai Pekerjaan Include PPn',
            'document_path' => 'Dokumen PO',
        ],
        'field' => [
            'date_po' => [
                'placeholder' => 'Pilih Tanggal',
            ]
        ]
    ],
    'spk' => [
        'title_header' => 'SPK',
        'title_modal_create' => 'Data SPK Vendor (Subkon)',
        'title_modal_edit' => 'Data SPK Vendor (Subkon)',
        'column' => [
            'subkon_id ' => 'Nama Perusahaan',
            'no_spk' => 'No. SPK',
            'date_spk' => 'Tanggal SPK',
            'job_name' => 'Nama Pekerjaan',
            'job_description' => 'Deskripsi/Detail',
            'job_value' => 'Nilai Pekerjaan',
            'tax_ppn' => 'PPN',
            'total_value_with_tax' => 'Nilai Pekerjaan Include PPn',
            'document_path' => 'Dokumen SPK',
        ],
        'field' => [
            'subkon_id' => [
                'placeholder' => 'NAMA PERUSAHAAN',
            ],
            'no_spk' => [
                'placeholder' => 'Masukkan nomor SPK perusahaan',
            ],
            'date_spk' => [
                'placeholder' => 'Pilih Tanggal',
            ],
            'job_name' => [
                'placeholder' => 'Masukkan nama pekerjaan',
            ],
            'job_description' => [
                'label' => 'Deskripsi Pekerjaan',
                'placeholder' => 'Tulis deskripsi pekerjaan',
            ],
            'job_value' => [
                'placeholder' => '000.000',
            ],
            'tax_ppn' => [
                'placeholder' => '0',
            ],
            'total_value_with_tax' => [
                'placeholder' => '000.000',
            ],
            'document_path' => [
                'label' => 'Unggah Dokumen SPK',
                'placeholder' => '000.000',
            ],
        ]
    ],
    'client' => [
        'title_header' => 'Daftar Client',
        'column' => [
            'name' => 'Nama Perusahaan',
            'address' => 'Alamat',
            'npwp' => 'No. NPWP',
            'phone' => 'No. Telepon'
        ],
    ],
    'client_po' => [
        'title_header' => 'PO',
        'column' => [
            'client_id' => 'Nama Perusahaan',
            'work_code' => 'Kode Kerja',
            'po_number' => 'No. PO',
            'job_name' => 'Nama Pekerjaan',
            'job_value' => 'Nilai Pekerjaan',
            'tax_ppn' => 'PPn',
            'total_value_with_tax' => 'Nilai Pekerjaan Include PPn',
            'startdate_and_enddate' => "Start Date - End Date",
            'reimburse_type' => 'Reimburse/NonReimburse',
            'price_total' => 'Total Biaya',
            'profit_and_loss' => "Laba/Rugi PO",
            'document_path' => 'Dokumen PO',
            'date_invoice' => 'Tanggal Invoice',
        ],
        'field' => [
            'client_id' => [
                'label' => 'Nama Perusahaan',
                'placeholder' => "- NAMA PERUSAHAAN",
            ],
            'work_code' => [
                'label' => 'Kode Kerja',
                'placeholder' => 'Masukan kode kerja perusahaan',
            ],
            'po_number' => [
                'label' => 'No. PO',
                'placeholder' => 'Masukan nomor PO Perusahaan',
            ],
            'job_name' => [
                'label' => 'Nama Pekerjaan',
                'placeholder' => 'Masukan nama pekerjaan',
            ],
            'job_value' => [
                'label' => 'Nilai Pekerjaan',
                'placeholder' => '000.000',
            ],
            'tax_ppn' => [
                'label' => 'PPn',
                'placeholder' => '0',
            ],
            'startdate_and_enddate' => [
                'label' => 'Start Date - End Date',
                'placeholder' => 'Start Date - End Date',
            ],
            'reimburse_type' => [
                'label' => 'Reimburse/NonReimburse',
                'placeholder' => '-PILIH',
            ],
            'price_total' => [
                'label' => 'Total Biaya'
            ],
            'profit_and_loss' => [
                'label' => 'Laba/Rugi PO',
            ],
            'document_path' => [
                'label' => 'Upload Dokumen PO',
            ],
            'date_invoice' => [
                'label' => 'Tanggal Invoice',
                'placeholder' => 'Pilih Tanggal',
            ]
        ]
    ],
    'invoice_client' => [
        'title_header' => 'Invoice (Client)',
        'title_modal_create' => 'Data Invoice (Client)',
        'title_modal_edit' => 'Data Invoice (Client)',
        'title_modal_delete' => 'Invoice (Client)',
        'column' => [
            'invoice_number' => 'No. Invoice',
            'name' => 'Nama Invoice',
            'invoice_date' => 'Tanggal Invoice',
            'client_po_id' => 'No. PO',
            'po_date' => 'Tanggal PO',
            'client_id' => 'Client',
            'price_total_exclude_ppn' => 'Nilai Exclude PPn',
            'price_total_include_ppn' => 'Nilai Include PPn',
            'status' => 'Status',
        ],
        'field' => [
            'invoice_number' => [
                'label' => 'No. Invoice',
                'placeholder' => 'Masukan nomor invoice',
            ],
            'name' => [
                'label' => 'Nama Invoice',
                'placeholder' => 'Masukan nama invoice',
            ],
            'invoice_date' => [
                'label' => 'Tanggal Invoice',
                'placeholder' => 'Pilih Tanggal',
            ],
            'client_po_id' => [
                'placeholder' => 'Masukan nomor PO Client',
                'label' => 'No. PO',
            ],
            'po_date' => [
                'label' => 'Tanggal PO',
                'placeholder' => 'Pilih tanggal',
            ],
            'client_id' => [
                'label' => 'Client',
                'placeholder' => 'Nama Client',
            ],
            'price_total_exclude_ppn' => [
                'label' => 'Total Biaya Exclude PPn',
                'placeholder' => '000.000',
            ],
            'price_total_include_ppn' => [
                'label' => 'Total Biaya Include PPn',
                'placeholder' => '000.000',
            ],
            'status' => [
                'label' => 'Status',
                'placeholder' => '-STATUS',
            ]
        ],
    ],
    'cash_account' => [
        'title_header' => 'Rekening Kas',
        'title_modal_create' => 'Akun Rekening Kas',
        'title_modal_edit' => 'Akun Rekening Kas',
        'title_modal_delete' => 'Akun Rekening Kas',
        'title_modal_create_transaction' => 'Data Transaksi',
        'field' => [
            'name' => [
                'label' => 'Nama Rekening',
                'placeholder' => 'Masukan nama rekening',
            ],
            'bank_name' => [
                'label' => 'Nama Bank',
                'placeholder' => '-List Bank',
            ],
            'no_account' => [
                'label' => 'No. Rekening',
                'placeholder' => 'Masukan nomor rekening',
            ],
            'total_saldo' => [
                'label' => 'Saldo Rekening',
                'placeholder' => '000.000'
            ],
            'additional_information' => [
                'label' => 'Keterangan Tambahan',
            ]
        ],
        'field_transaction' => [
            'date_transaction' => [
                'label' => 'Tanggal Transaksi',
                'placeholder' => 'Pilih Tanggal',
            ],
            'no_transaction' => [
                'label' => 'No. PO/SPK',
                'placeholder' => 'Masukan nomor PO/SPK transaksi'
            ],
            'no_invoice' => [
                'label' => 'No. Invoice',
                'placeholder' => 'Masukan nomor invoice transaksi'
            ],
            'nominal' => [
                'label' => 'Nominal',
            ],
            'nominal_transaction' => [
                'label' => 'Nominal Transaksi',
                'placeholder' => '000.000',
            ],
            'status' => [
                'label' => 'Keterangan',
                'placeholder' => '-KETERANGAN',
                'enter' => 'MASUK',
                'out' => 'KELUAR',
            ]
            ],
        'field_transfer' => [
            'nominal_transfer' => [
                'label' => 'Nominal Yang Ingin Dipindahkan',

            ],
            'to_account' => [
                'label' => 'Rekening Tujuan',
                'placeholder' => '-Rekening Tujuan'
            ],
            'errors' => [
                'nominal_transfer_to_more' => 'Nominal tidak boleh melebihi jumlah saldo.',
                'to_account_is_same' => 'Rekening tujuan harus berbeda dengan rekening yang dipindah',
            ]
        ]
    ],
    'save_submit' => 'Simpan',
    'cancel_submit' => 'Batal',
    'save_changes_submit' => 'Simpan Perubahan',

];
