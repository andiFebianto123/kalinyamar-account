@php
    $field['attributes']['readonly'] = 'readonly';
@endphp


@include('crud::fields.inc.wrapper_start')
    <input type="hidden" class="form-control" name="{{ $field['name'] }}" value="{{ $field['value'] }}">
    <label>{!! $field['label'] !!}</label>
    @include('crud::fields.inc.translatable_icon')
    <div class="input-group date">
        <input
            type="text"
            name="{{ $field['name'] }}"
            value="{{ old($field['name']) ?? $field['value'] ?? '' }}"
            @include('crud::inc.field_attributes')
        >
        <span class="input-group-text" id="basic-addon1"><span class="la la-calendar"></span></span>
    </div>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
@include('crud::fields.inc.wrapper_end')

@push('after_scripts')
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <style>
        .ui-datepicker-calendar {
            display: none;
        }
    </style>
    <script>
        $(function() {
            $('input[name="{{ $field['name'] }}"]').datepicker({
                changeMonth: true,
                changeYear: true,
                showButtonPanel: true,
                dateFormat: 'mm-yy',
                onClose: function(dateText, inst) {
                    let month = inst.selectedMonth + 1;
                    let year = inst.selectedYear;
                    if (!month || !year) return;
                    $(this).val(("0" + month).slice(-2) + "-" + year);
                },
                beforeShow: function(input, inst) {
                    $(input).datepicker("widget").addClass("hide-calendar");
                }
            });
        });
    </script>
@endpush
