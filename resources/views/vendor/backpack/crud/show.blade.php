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
                        $set_attributes = "";
                        if(array_key_exists('wrapper', $field)){
                            foreach ($field['wrapper'] as $attribute => $value){
                                if (is_string($attribute)){
                                    $set_attributes .= $attribute."='".$value."'";
                                }
                            }
                        }else{
                            $set_attributes = "class='form-group col-md-12'";
                        }
                    @endphp

                    <div {!! $set_attributes !!} >
                        <label>{{ $field['label'] }}</label>
                        <div>{!! $entry_value[$index] !!}</div>
                    </div>
                        {{-- @dd($field); --}}
                @endforeach
            </div>
        {{-- </div> --}}
    {{-- </div> --}}
@endif

