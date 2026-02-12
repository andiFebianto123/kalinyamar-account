@push('inline_scripts')
    @once
        <style>
            #crudTable-voucher_payment_non_rutin_wrapper .dataTables_scrollHead table thead tr th {
                background-color: #FCD72D !important;
            }

            #crudTable-voucher_payment_plan_non_rutin_wrapper .dataTables_scrollHead table thead tr th {
                background-color: #FCD72D !important;
            }

            #crudTable-voucher_payment_rutin_wrapper .dataTables_scrollHead table thead tr th {
                background-color: #FCD72D !important;
            }

            #crudTable-voucher_payment_plan_rutin_wrapper .dataTables_scrollHead table thead tr th {
                background-color: #FCD72D !important;
            }

            /* Bulk action toolbar styles */
            .bulk-actions-toolbar {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 8px 0;
                flex-wrap: wrap;
            }
            .bulk-actions-toolbar .selected-count {
                font-weight: 600;
                color: #333;
                font-size: 13px;
                min-width: 120px;
            }
            .bulk-actions-toolbar .btn {
                font-size: 13px;
            }

            /* Checkbox styling */
            /* .bulk-checkbox {
                width: 18px;
                height: 18px;
                cursor: pointer;
                accent-color: #4361ee;
            }
            .bulk-checkbox-header {
                width: 18px;
                height: 18px;
                cursor: pointer;
                accent-color: #4361ee;
            } */
        </style>
    @endonce
@endpush

@push('after_scripts')
<script>
    $(function(){
        // ========================================
        // Bulk Selection State
        // ========================================
        window.bulkSelectedIds = [];
        window.bulkSelectedApprovalData = []; // stores {id, no_apprv} objects

        function updateBulkUI() {
            var count = window.bulkSelectedIds.length;
            $('#bulk-selected-count').text(count + ' {{ trans("backpack::crud.select_entries") }}');

            if (count > 0) {
                $('#bulk-selected-count').removeClass('d-none');
                $('#btn-bulk-approve').removeClass('d-none').prop('disabled', false);
                $('#btn-bulk-delete').removeClass('d-none').prop('disabled', false);
            } else {
                $('#bulk-selected-count').addClass('d-none');
                $('#btn-bulk-approve').addClass('d-none');
                $('#btn-bulk-delete').addClass('d-none');
            }
        }

        // ========================================
        // Checkbox Event Bindings
        // ========================================
        
        // Helper: toggle semua checkbox baris + sync kedua header checkbox
        function toggleAllCheckboxes(checked) {
            $('#crudTable-voucher_payment_plan_non_rutin tbody .bulk-checkbox').each(function() {
                $(this).prop('checked', checked);
                var id = $(this).data('id');
                var noApprv = $(this).data('no-apprv');
                var userId = $(this).data('user_id');

                if (checked) {
                    if (window.bulkSelectedIds.indexOf(id) === -1) {
                        window.bulkSelectedIds.push(id);
                        window.bulkSelectedApprovalData.push({
                            id: id,
                            no_apprv: noApprv,
                            user_id: userId,
                        });
                    }
                } else {
                    window.bulkSelectedIds = window.bulkSelectedIds.filter(function(item) {
                        return item !== id;
                    });
                    window.bulkSelectedApprovalData = window.bulkSelectedApprovalData.filter(function(item) {
                        return item.id !== id;
                    });
                }
            });

            // Sync kedua checkbox header
            $('#bulk-select-all').prop('checked', checked);
            $('.bulk_all_checkbox').prop('checked', checked);

            updateBulkUI();
        }

        // Klik checkbox di toolbar (Pilih Semua)
        $(document).on('change', '#bulk-select-all', function() {
            toggleAllCheckboxes($(this).is(':checked'));
        });

        // Klik checkbox di header tabel DataTable
        $(document).on('change', '.bulk_all_checkbox', function() {
            toggleAllCheckboxes($(this).is(':checked'));
        });

        // Klik checkbox per-baris
        $(document).on('change', '.bulk-checkbox', function() {
            var id = $(this).data('id');
            var noApprv = $(this).data('no-apprv');
            var checked = $(this).is(':checked');
            var userId = $(this).data('user_id');

            if (checked) {
                if (window.bulkSelectedIds.indexOf(id) === -1) {
                    window.bulkSelectedIds.push(id);
                    window.bulkSelectedApprovalData.push({
                        id: id,
                        no_apprv: noApprv,
                        user_id: userId,
                    });
                }
            } else {
                window.bulkSelectedIds = window.bulkSelectedIds.filter(function(item) {
                    return item !== id;
                });
                window.bulkSelectedApprovalData = window.bulkSelectedApprovalData.filter(function(item) {
                    return item.id !== id;
                });
            }

            // Update kedua header checkbox
            var totalCheckboxes = $('#crudTable-voucher_payment_plan_non_rutin tbody .bulk-checkbox').length;
            var checkedCheckboxes = $('#crudTable-voucher_payment_plan_non_rutin tbody .bulk-checkbox:checked').length;
            var allChecked = totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes;
            $('#bulk-select-all').prop('checked', allChecked);
            $('.bulk_all_checkbox').prop('checked', allChecked);

            updateBulkUI();
        });

        // After DataTable redraw, re-check checkboxes for already selected items
        $(document).on('draw.dt', '#crudTable-voucher_payment_plan_non_rutin', function() {
            $('#crudTable-voucher_payment_plan_non_rutin tbody .bulk-checkbox').each(function() {
                var id = $(this).data('id');
                if (window.bulkSelectedIds.indexOf(id) !== -1) {
                    $(this).prop('checked', true);
                }
            });

            var totalCheckboxes = $('#crudTable-voucher_payment_plan_non_rutin tbody .bulk-checkbox').length;
            var checkedCheckboxes = $('#crudTable-voucher_payment_plan_non_rutin tbody .bulk-checkbox:checked').length;
            var allChecked = totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes;
            $('#bulk-select-all').prop('checked', allChecked);
            $('.bulk_all_checkbox').prop('checked', allChecked);
        });

        // ========================================
        // Bulk Approve
        // ========================================
        $(document).on('click', '#btn-bulk-approve', function(e) {
            e.preventDefault();
            if (window.bulkSelectedIds.length === 0) {
                swal("{{ trans('backpack::crud.voucher.confirm.caution') }}", "{{ trans('backpack::crud.bulk_no_entries_selected_message') }}", "warning");
                return;
            }

            swal({
                title: "{{ trans('backpack::crud.voucher.confirm.title') }}",
                text: "{{ trans('backpack::crud.voucher.confirm.confirm_approved_statement_bulk') }} (" + window.bulkSelectedIds.length + " item)?",
                icon: "warning",
                buttons: {
                    cancel: "{{ trans('backpack::crud.voucher.confirm.cancel') }}",
                    confirm: {
                        text: "{{ trans('backpack::crud.voucher.confirm.yes_approved') }}",
                        value: true,
                        className: "swal-button--confirm"
                    }
                },
                dangerMode: false,
            }).then(function(value) {
                if (value) {
                    var btn = $('#btn-bulk-approve');
                    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Loading...');

                    $.ajax({
                        url: "{{ url($crud->route.'/bulk-approve') }}",
                        method: 'POST',
                        data: {
                            entries: JSON.stringify(window.bulkSelectedApprovalData),
                        },
                        success: function(response) {
                            btn.prop('disabled', false).html('<i class="la la-check-double"></i> Approve');
                            if (response.success) {
                                swal("{{ trans('backpack::crud.voucher.confirm.confirm_after_success') }}", "{{ trans('backpack::crud.approved_success') }} (" + (response.approved_count || 0) + " item).", "success").then(function() {
                                    window.bulkSelectedIds = [];
                                    window.bulkSelectedApprovalData = [];
                                    updateBulkUI();
                                    if (response.events) {
                                        forEachFlexible(response.events, function(eventname, data) {
                                            eventEmitter.emit(eventname, data);
                                        });
                                    }
                                });
                            } else {
                                swal("{{ trans('backpack::crud.voucher.confirm.caution') }}", response.error || "{{ trans('backpack::crud.details_row_loading_error') }}", "error");
                            }
                        },
                        error: function(xhr) {
                            btn.prop('disabled', false).html('<i class="la la-check-double"></i> {{ trans("backpack::crud.voucher.confirm.yes_approved") }}');
                            swal("{{ trans('backpack::crud.reorder_error_title') }}!", "{{ trans('backpack::crud.details_row_loading_error') }}", "error");
                        }
                    });
                }
            });
        });

        // ========================================
        // Bulk Delete
        // ========================================
        $(document).on('click', '#btn-bulk-delete', function(e) {
            e.preventDefault();
            if (window.bulkSelectedIds.length === 0) {
                swal("{{ trans('backpack::crud.voucher.confirm.caution') }}", "{{ trans('backpack::crud.bulk_no_entries_selected_message') }}", "warning");
                return;
            }

            swal({
                title: "{{ trans('backpack::crud.delete') }} " + window.bulkSelectedIds.length + " item?",
                text: "{{ trans('backpack::crud.delete_confirm_plan') }}",
                icon: "warning",
                buttons: {
                    cancel: "{{ trans('backpack::crud.cancel') }}",
                    confirm: {
                        text: "{{ trans('backpack::crud.delete') }}",
                        value: true,
                        className: "swal-button--danger"
                    }
                },
                dangerMode: true,
            }).then(function(value) {
                if (value) {
                    var btn = $('#btn-bulk-delete');
                    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Loading...');

                    $.ajax({
                        url: "{{ url($crud->route.'/bulk-delete') }}",
                        method: 'POST',
                        data: {
                            entries: JSON.stringify(window.bulkSelectedIds),
                        },
                        success: function(response) {
                            btn.prop('disabled', false).html('<i class="la la-trash"></i> {{ trans("backpack::crud.delete") }}');
                            if (response.success) {
                                swal("{{ trans('backpack::crud.delete_confirmation_title') }}!", "{{ trans('backpack::crud.delete_confirmation_message') }} (" + (response.deleted_count || 0) + " item).", "success").then(function() {
                                    window.bulkSelectedIds = [];
                                    window.bulkSelectedApprovalData = [];
                                    updateBulkUI();
                                    if (response.events) {
                                        forEachFlexible(response.events, function(eventname, data) {
                                            eventEmitter.emit(eventname, data);
                                        });
                                    }
                                });
                            } else {
                                swal("{{ trans('backpack::crud.voucher.confirm.caution') }}", response.error || "{{ trans('backpack::crud.details_row_loading_error') }}", "error");
                            }
                        },
                        error: function(xhr) {
                            btn.prop('disabled', false).html('<i class="la la-trash"></i> {{ trans("backpack::crud.delete") }}');
                            swal("{{ trans('backpack::crud.reorder_error_title') }}!", "{{ trans('backpack::crud.details_row_loading_error') }}", "error");
                        }
                    });
                }
            });
        });

        // ========================================
        // Original Plugin Logic (totals)
        // ========================================
        SIAOPS.setAttribute('voucher_payment_plugin', function(){
            return {
                name: 'voucher_plugin',
                accounts_compact:[],
                eventLoader: async function(){
                    var instance = this;
                    eventEmitter.on("crudTable-filter_voucher_payment_plugin_load", function(data){
                        instance.refresh();
                    });
                },
                refresh: function(){
                    var instance = this;
                    $.ajax({
                        url: "{{ url($crud->route.'/total') }}",
                        type: 'POST',
                        data: {
                            search: window.filterValues,
                        },
                        typeData: 'json',
                        success: function (result) {
                            $('#panel-voucher_payment_non_rutin').html(`
                                <div class="d-flex justify-content-start">
                                    <div class="p-2 bd-highlight"><strong>{{trans('backpack::crud.voucher_payment.total_payment_value')}} : ${result.voucher_payment_non_rutin_total}</strong></div>
                                </div>
                            `);

                            $('#panel-voucher_payment_plan_non_rutin').html(`
                                <div class="d-flex justify-content-start">
                                    <div class="p-2 bd-highlight"><strong>{{trans('backpack::crud.voucher_payment.total_payment_approve_value')}} : ${result.voucher_payment_plan_non_rutin_total}</strong></div>
                                </div>
                            `);

                            $('#panel-voucher_payment_rutin').html(`
                                <div class="d-flex justify-content-start">
                                    <div class="p-2 bd-highlight"><strong>{{trans('backpack::crud.voucher_payment.total_payment_value')}} : ${result.voucher_payment_rutin_total}</strong></div>
                                </div>
                            `);

                            $('#panel-voucher_payment_plan_rutin').html(`
                                <div class="d-flex justify-content-start">
                                    <div class="p-2 bd-highlight"><strong>{{trans('backpack::crud.voucher_payment.total_payment_approve_value')}} : ${result.voucher_payment_plan_rutin_total}</strong></div>
                                </div>
                            `);
                        },
                        error: function (xhr, status, error) {
                            console.error(xhr);
                            alert('An error occurred while loading the create form.');
                        }
                    });
                },
                load: function(){
                    var instance = this;
                    instance.eventLoader();
                    instance.refresh();
                }
            }
        });

        SIAOPS.getAttribute('voucher_payment_plugin').load();

    });
</script>
@endpush
