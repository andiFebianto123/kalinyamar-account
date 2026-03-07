@php
    $field['wrapper'] = $field['wrapper'] ?? [];
    $field['wrapper']['class'] = $field['wrapper']['class'] ?? 'hidden';
@endphp

{{-- This field holds no visible input, just triggers the script --}}
@include('crud::fields.inc.wrapper_start')
@include('crud::fields.inc.wrapper_end')

@push('crud_fields_scripts')
<script>
    $(function () {
        SIAOPS.setAttribute('logic_tracker', function () {
            return {
                form_type: "{{ $crud->getActionMethod() }}",

                hitungTotalTime: function (actualEndDateStr) {
                    var form = (this.form_type === 'create') ? '#form-create' : '#form-edit';

                    if (!actualEndDateStr) {
                        $(form + ' #total_time_display').val('-');
                        return;
                    }

                    // Format tanggal dari datepicker: dd/mm/yyyy
                    var parts = actualEndDateStr.split('/');
                    if (parts.length !== 3) {
                        $(form + ' #total_time_display').val('-');
                        return;
                    }

                    var day   = parseInt(parts[0], 10);
                    var month = parseInt(parts[1], 10) - 1; // bulan 0-indexed
                    var year  = parseInt(parts[2], 10);

                    var endDate   = new Date(year, month, day);
                    var today     = new Date();
                    today.setHours(0, 0, 0, 0);

                    var diffMs   = endDate - today;
                    var diffDays = Math.round(diffMs / (1000 * 60 * 60 * 24));

                    $(form + ' #total_time_display').val(diffDays);
                },

                load: function () {
                    var instance = this;
                    var form = (this.form_type === 'create') ? '#form-create' : '#form-edit';

                    // Hitung saat halaman pertama kali dimuat (mode edit)
                    var initialVal = $(form + ' #actual_end_date').val();
                    instance.hitungTotalTime(initialVal);

                    // Hitung setiap kali actual_end_date berubah
                    $(form + ' #actual_end_date').on('change', function () {
                        instance.hitungTotalTime($(this).val());
                    });
                }
            };
        });

        SIAOPS.getAttribute('logic_tracker').load();
    });
</script>
@endpush
