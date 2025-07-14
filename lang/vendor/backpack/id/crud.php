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
            'name_rekening' => 'Nama Rekening',
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
        'cash_flow_loan' => 'Rekening Pinjaman',
        'finance_report' => 'Laporan Keuangan',
        'expense_account' => 'Akun Biaya',
        'profit_lost' => 'Laba Rugi',
        'balance_sheet' => 'Neraca',
        'asset' => 'Daftar Aset',
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
        'tab' => [
            'input_per_page' => 'masukan per halaman',
            'title_all_po' => 'Daftar PO',
            'open' => 'Open',
            'close' => 'Close',
            'title_total_incl_ppn' => 'Total Incl PPn',
        ],
        'column' => [
            'subkon_id ' => 'Nama Perusahaan',
            'po_number' => 'No. PO',
            'date_po' => 'Tanggal PO',
            'job_name' => 'Nama Pekerjaan',
            'job_description' => 'Deskripsi/Detail',
            'job_value' => 'Nilai Pekerjaan',
            'tax_ppn' => 'PPN',
            'total_value_with_tax' => 'Nilai Pekerjaan Include PPn',
            'due_date' => 'Jatuh Tempo',
            'status' => 'Status PO',
            'document_path' => 'Dokumen PO',
        ],
        'field' => [
            'date_po' => [
                'placeholder' => 'Pilih Tanggal',
            ],
            'job_description' => [
                'placeholder' => 'Masukan nama pekerjaan',
                'label' => 'Deskripsi Pekerjaan'
            ],
            'due_date' => [
                'label' => 'Jatuh Tempo',
                'placeholder' => 'Pilih Tanggal'
            ],
            'status' => [
                'label' => 'Status PO',
                'placeholder' => '-STATUS',
                'open' => 'OPEN',
                'close' => 'CLOSE',
            ]
        ],
        'export' => [
            'pdf' => [
                'title_header' => 'Daftar Purchase Order (Subkon)',
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
            'address' => [
                'placeholder' => 'Masukan alamat PO',
                'label' => 'Alamat',
            ],
            'description' => [
                'placeholder' => 'Tulis deskripsi invoice',
                'label' => 'Deskripsi'
            ],
            'nominal_exclude_ppn' => [
                'placeholder' => '000.000',
                'label' => 'Nominal Exclude PPn',
            ],
            'dpp_other' => [
                'placeholder' => '000.000',
                'label' => 'DPP Nilai Lainnya',
            ],
            'tax_ppn' => [
                'placeholder' => '',
                'label' => 'PPn',
            ],
            'nominal_include_ppn' => [
                'placeholder' => '000.000',
                'label' => 'Nominal Include PPn',
            ],
            'kdp' => [
                'placeholder' => 'Masukan KDP',
                'label' => 'KDP',
            ],
            'send_invoice_normal' => [
                'label' => 'Pengiriman Invoice - Normal',
                'placeholder' => '',
            ],
            'send_invoice_revision' => [
                'label' => 'Pengiriman Invoice - Revisi',
                'placeholder' => '',
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
            ],
            'item' => [
                'label' => 'Invoice Item',
                'new_item_label' => 'Tambah Item',
                'errors' => [
                    'total_price' => 'Total harga pada item anda tidak sama dengan total harga PO',
                ],
                'items' => [
                    'name' => [
                        'label' => 'Nama Item',
                        'placeholder' => 'Nama Item',
                    ],
                    'price' => [
                        'label' => 'Harga Item',
                        'placeholder' => '000.000',
                    ]
                ]
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
            'description' => [
                'label' => 'Deskripsi Pembayaran',
                'placeholder' => 'Masukan deskripsi pembayaran'
            ],
            'kdp' => [
                'label' => 'KDP',
                'placeholder' => 'Masukan kode perusahaan',
            ],
            'job_name' => [
                'label' => 'Nama Pekerjaan',
                'placeholder' => 'Masukan nama pekerjaan'
            ],
            'account_id' => [
                'label' => 'Akun Biaya',
                'placeholder' => '-AKUN BIAYA'
            ],
            'account' => [
                'label' => 'Akun',
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
                'label' => 'Keluar/Masuk',
                'placeholder' => '-KELUAR/MASUK',
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
                'placeholder' => '-REKENING TUJUAN'
            ],
            'errors' => [
                'nominal_transfer_to_more' => 'Nominal tidak boleh melebihi jumlah saldo.',
                'to_account_is_same' => 'Rekening tujuan harus berbeda dengan rekening yang dipindah',
            ]
        ]
    ],
    'cash_account_loan' => [
        'title_header' => 'Rekening Pinjaman',
        'title_modal_create' => 'Akun Rekening Pinjaman',
        'title_modal_edit' => 'Akun Rekening Pinjaman',
        'title_modal_delete' => 'Akun Rekening Pinjaman',
        'field' => [
            'name' => [
                'label' => 'Nama Rekening',
                'placeholder' => "Masukan nama rekening",
            ],
            'bank_name' => [
                'label' => 'Nama Bank',
                'placeholder' => '-List Bank',
            ],
            'no_account' => [
                'label' => 'No. Rekening',
                'placeholder' => 'Masukan nomor rekening',
            ],
            'account' => [
                'label' => 'Akun',
                'placeholder' => '-AKUN'
            ],
            'total_saldo' => [
                'label' => 'Saldo Rekening',
                'placeholder' => '000.000'
            ],
            'cast_account_destination_id' => [
                'label' => 'Asal/Tujuan Rekening',
                'placeholder' => '-ASAL/TUJUAN REKENING',
                'bank_loan' => 'PINJAMAN BANK',
                'bank_loan_placeholder' => 'PINJAMAN BANK',
                'bank_loan_alert' => "Maaf pinjaman bank hanya untuk uang masuk bukan keluar",
            ],
            'balance_information' => [
                'label' => 'Saldo',
                'placeholder' => 'Nominal Yang Ingin Dipindahkan'
            ]
        ]
    ],
    'expense_account' => [
        'title_header' => 'Akun Biaya',
        'title_modal_create' => 'Akun Biaya',
        'title_modal_edit' => 'Akun Biaya',
        'title_modal_delete' => 'Akun Biaya',
        'column' => [
            'code' => 'Kode Akun',
            'name' => 'Nama Akun',
            'balance' => 'Saldo',
            'action' => 'Action',
        ],
        'field' => [
            'code' => [
                'placeholder' => 'Masukan nomor kode akun',
                'errors' => [
                    'depedency' => "Kode akun tidak dapat diubah karena digantungkan pada akun lainnya.",
                    'delete' => 'Maaf akun tidak dapat dihapus karena digantungkan pada akun lainnya.',
                    'not_change_balance' => "Tidak dapat mengubah saldo karena akun telah digunakan untuk transaksi data lainnya.",
                ]
            ],
            'name' => [
                'placeholder' => 'Masukan nama akun',
            ],
        ]
    ],
    'profit_lost' => [
        'title_header' => 'Laba Rugi',
        'title_modal_create_consolidation' => 'Akun Laba Rugi Konsolidasi',
        'title_modal_edit_consolidation' => 'Akun Laba Rugi Konsolidasi',
        'title_modal_delete_consolidation' => 'Akun Laba Rugi Konsolidasi',
        'title_modal_create_project' => '',
        'title_modal_edit_project' => '',

        'title_modal_create_project' => 'Tambah Laporan Laba Rugi Proyek',
        'title_modal_delete' => 'Akun Biaya',
        'empty_account' => 'Belum ada akun',
        'consolidation_income_statement' => 'Laporan Laba Rugi Konsolidasi',
        'project_income_statement' => 'Laporan Laba Rugi Proyek',
        'choose_create' => [
            'consolidation_account' => 'Akun Laba Rugi Konsolidasi',
            'project_account' => 'Laba Rugi Proyek Proyek',
        ],
        'show_detail' => 'LIHAT DETAIL',
        'fields' => [
            'no_po' => [
                'label' => 'No PO',
                'placeholder' => '-No. PO'
            ],
            'job_code' => [
                'label' => 'Kode Kerja',
                'placeholder' => 'Kode Kerja',
            ],
            'contract_value' => [
                'label' => 'Nilai Kontrak',
                'placeholder' => '',
            ],
            'total_project' => [
                'label' => 'Total Biaya Proyek',
                'placeholder' => '000.000'
            ],
            'price_material' => [
                'label' => 'Biaya Material',
                'placeholder' => '000.000'
            ],
            'price_subkon' => [
                'label' => 'Biaya Subkon',
                'placeholder' => '000.000',
            ],
            'price_btkl' => [
                'label' => 'Upah Pekerja Langsung (BTKL)',
                'placeholder' => '',
            ],
            'price_transport_project' => [
                'label' => 'Biaya Transportasi Proyek',
                'placeholder' => '',
            ],
            'price_worker_consumption' => [
                'label' => 'Biaya Konsumsi Pekerja',
                'placeholder' => '',
            ],
            'price_project_equipment' => [
                'label' => 'Sewa Peralatan Proyek',
            ],
            'price_other' => [
                'label' => 'Biaya Lain - lain (jika ada)'
            ],
            'price_profit_lost_project' => [
                'label' => 'Nilai Laba Rugi Proyek',
            ]
        ],
        'column' => [
            'client_po_id' => 'Nama Client',
            'job_code' => 'Kode Kerja',
            'no_po' => 'No.PO',
            'contract_value' => 'Nilai Kontrak',
            'total_project' => 'Total Biaya Proyek',
            'price_profit_lost_project' => 'Laba/Rugi Proyek'
        ],
        'detail' => [
            'project_profit_and_loss_report' => 'Laporan Laba Rugi Proyek',
            'contract_revenue' => 'Pendapatan Kontrak',
            'contract_value' => 'Nilai Kontrak',
            'project_related_costs' => 'Biaya-biaya Terkait Proyek',
            'fee_type' => 'Jenis Biaya',
            'balance' => 'Saldo',
            'material' => 'Material',
            'subcon' => 'Subkon',
            'direct_labor_wages' => 'Upah Pekerja Langsung (BTKL)',
            'project_transportation' => 'Transportasi Proyek',
            'worker_consumption' => 'Konsumsi Pekerja',
            'project_equipment_rental' => 'Sewa Peralatan Proyek',
            'other_costs' => 'Biaya Lain-lain (jika ada)',
            'total_project_cost' => 'Total Biaya Proyek',
            'project_profit_loss' => 'Laba (Rugi) Proyek',
            'project_profit_loss_value' => 'Nilai Laba (Rugi) Proyek',
        ],
    ],
    'balance_sheet' => [
        'title_header' => 'Neraca',
        'title_modal_create' => 'Akun Neraca',
        'title_modal_edit' => 'Ubah Akun Neraca',
        'title_modal_delete' => 'Akun Neraca',
        'card' => [
            'asset' => 'Aset',
            'liabilities' => 'Kewajiban',
            'equity' => 'Ekuitas',
        ],
        'filters' => [
            'year' => [
                'label' => 'Tahun',
                'placeholder' => '- PILIH TAHUN'
            ],
            'quarter' => [
                'label' => 'Kuartal',
                'placeholder' => '- PILIH KUARTAL',
            ]
        ],
        'fields' => [
            'account_type' => [
                'label' => 'Jenis Akun',
                'placeholder' => '-PILIH JENIS AKUN',
                'options' => [
                    'account_asset' => 'AKUN ASET',
                    'account_liabilities' => 'AKUN KEWAJIBAN',
                    'account_equity' => 'AKUN EKUITAS',
                ]
            ],
            'code' => [
                'label' => 'Kode Akun',
                'placeholder' => 'Masukan nomor kode akun',
            ],
            'name' => [
                'label' => 'Nama Akun',
                'placeholder' => 'Masukan nama akun',
            ],
            'balance' => [
                'label' => 'Saldo',
                'placeholder' => '000.000',
            ],
            'date' => [
                'label' => 'Tanggal Akun',
                'placeholder' => 'Pilih Tanggal',
            ]
        ]

    ],
    'asset' => [
        'title_header' => 'Daftar Aset',
        'title_modal_create' => 'Daftar Aset',
        'title_modal_edit' => 'Ubah Daftar Aset',
        'title_modal_delete' => 'Daftar Aset',
        'column' => [
            'account_id' => 'Akun',
            'depreciation_account_id' => 'Akun Penyusutan',
            'expense_account_id' => 'Akun Beban',
            'description' => 'Keterangan',
            'year_acquisition' => 'Tahun Perolehan',
            'price_acquisition' => 'Harga Perolehan',
            'economic_age' => 'U.E',
            'tarif' => 'Tarif (%)',
            'price_rate_per_year' => 'Tarif Penyusutan Per Tahun',
            'price_rate_year_ago' => 'Tarif Penyusutan Tahun Lalu',
            'accumulated_until_december_last_year' => 'Akumulasi Penyusutan s.d. Desember Tahun Lalu',
            'book_value_last_december' => 'Nilai Buku Desember Tahun Lalu',
            'this_year_depreciation_rate' => 'Tarif Penysutan Tahun Ini',
            'accumulated_until_december_this_year' => 'Akumulasi Penyusutan s.d. Desember Tahun Ini',
            'book_value_this_december' => 'Nilai Buku Desember Tahun Ini',
        ],
        'field' => [
            'account_id' => [
                'label' => 'Akun Asset',
                'placeholder' => ''
            ],
            'account_depreciation' => [
                'label' => 'Akun Penyusutan',
                'placeholder' => ''
            ],
            'expense_account_id' => [
                'label' => 'Akun Beban',
            ],
            'description' => [
                'label' => 'Keterangan',
                'placeholder' => 'Masukan keterangan Asset',
            ],
            'year_acquisition' => [
                'label' => 'Tahun Perolehan',
                'placeholder' => 'Masukan Tahun Perolehan',
            ],
            'price_acquisition' => [
                'label' => 'Harga Perolehan',
                'placeholder' => '000.000',
            ],
            'economic_age' => [
                'label' => 'U.E. (Tahun)',
                'placeholder' => 'Masukan U.E.'
            ],
            'tarif' => [
                'label' => 'Tarif (%)',
                'placeholder' => '',
            ],
            'price_rate_per_year' => [
                'label' => 'Tarif Penyusutan Per Tahun',
            ],
            'price_rate_year_ago' => [
                'label' => 'Penyusutan Tahun Lalu',
                'placeholder' => '',
            ],
            'accumulated_until_december_last_year' => [
                'label' => 'Akumulasi Penyusutan s.d. Desember Tahun Lalu',
                'placeholder' => '',
            ],
            'book_value_last_december' => [
                'label' => 'Nilai Buku Desember Tahun Lalu',
                'placeholder' => '',
            ],
            'this_year_depreciation_rate' => [
                'label' => 'Tarif Penysutan Tahun Ini',
                'placeholder' => ''
            ],
            'accumulated_until_december_this_year' => [
                'label' => 'Akumulasi Penyusutan s.d. Desember Tahun Ini',
                'placeholder' => '',
            ],
            'book_value_this_december' => [
                'label' => 'Nilai Buku Desember Tahun Ini',
            ]
        ],
    ],
    'save_submit' => 'Simpan',
    'cancel_submit' => 'Batal',
    'save_changes_submit' => 'Simpan Perubahan',

];
