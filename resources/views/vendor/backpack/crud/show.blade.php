@if ($crud->tabsEnabled() && count($crud->getTabs()))
    @include('crud::inc.show_tabbed_fields')
    <input type="hidden" name="_current_tab" value="{{ Str::slug($crud->getTabs()[0]) }}" />
@else
    {{-- @include('crud::inc.show_fields', ['fields' => $crud->fields()]) --}}
    {{-- <div class="card"> --}}
        {{-- <div class="card-body"> --}}
            <div class="row">
                @foreach ($crud->fields() as $key => $field)
                    @php
                        $index = array_search($key, array_keys(($crud->fields())));
                    @endphp

                    <div class="form-group col-md-6" element="div" bp-field-wrapper="true" bp-field-name="subkon_id" bp-field-type="select2_ajax_custom" bp-section="crud-field">
                        <label>{{ $field['label'] }}</label>
                        <div>{!! $entry_value[$index] !!}</div>
                    </div>
                        {{-- @dd($field); --}}
                @endforeach
            </div>
        {{-- </div> --}}
    {{-- </div> --}}
@endif

