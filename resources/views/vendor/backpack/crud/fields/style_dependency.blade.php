@php
	// if not otherwise specified, the hidden input should take up no space in the form
  $field['wrapper'] = $field['wrapper'] ?? $field['wrapperAttributes'] ?? [];
  $field['wrapper']['class'] = $field['wrapper']['class'] ?? "hidden";
@endphp

{{-- hidden input --}}
@include('crud::fields.inc.wrapper_start')
  @if($field['name'] == 'style_dependency_role')
    <style>
        .checklist-options-container .checkbox{
            margin-bottom: 13px;
        }
    </style>
  @else
    <style>
        .checklist_dependency .container .row:nth-child(4) .checkbox{
            margin-bottom: 13px;
        }
    </style>
  @endif
@include('crud::fields.inc.wrapper_end')

@push('crud_fields_scripts')
@endpush
