<?php
    $tabs = (isset($params['tabs'])) ? $params['tabs'] : [];
    $name_tab = $params['name'] ?? 'myTab';
?>
<div>
    <div class="row mb-2 align-items-center">
        <div class="col-sm-9 datatable-widget-stack">
            <div class="dataTables_length" id="crudTable_length"><label>
                <select name="crudTable_length" aria-controls="crudTable" class="form-select form-select-sm">
                    @foreach ($crud->getPageLengthMenu()[0] as $key => $value)
                        <option value="{{$value}}">{{$crud->getPageLengthMenu()[1][$key]}}</option>
                    @endforeach
                </select> {{trans('backpack::crud.po.tab.input_per_page')}}</label>
            </div>
        </div>
        <div class="col-sm-3">
            <div id="datatable_search_stack" class="mt-sm-0 mt-2 d-print-none">
                <div class="input-icon">
                    <span class="input-icon-addon">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"></path><path d="M21 21l-6 -6"></path></svg>
                    </span>
                    <input type="search" id="datatable_search_input" class="form-control" placeholder="{{ trans('backpack::crud.search') }}..."/>
                </div>
            </div>
        </div>
    </div>
    <ul class="nav nav-tabs" role="tablist">
        @foreach ($tabs as $tab)
            @php
                $tab['disabled'] = $tab['disabled'] ?? false;
                $tab['active'] = $tab['active'] ?? false;
            @endphp
            <li class="nav-link {{ ($tab['active']) ? 'active' : '' }}" id="{{ $tab['name'] }}-tab" data-alt-name="{{$tab['name']}}" data-bs-toggle="tab" data-bs-target="#{{ $tab['name'] }}-pane" type="button" role="tab" aria-controls="{{ $tab['name'] }}-pane" aria-selected="{{ ($tab['active']) ? 'true' : 'false' }}" {{ ($tab['disabled']) ? 'disabled' : '' }}>
                {{ $tab['label'] }}
            </li>
        @endforeach
    </ul>
    <div class="tab-content" id="myTabContent">
        @foreach ($tabs as $tab)
            @php
                $tab['disabled'] = $tab['disabled'] ?? false;
                $tab['active'] = $tab['active'] ?? false;
            @endphp
            <div class="tab-pane fade {{ ($tab['active']) ? 'show active' : '' }}" id="{{ $tab['name'] }}-pane" role="tabpanel" aria-labelledby="{{ $tab['name'] }}-tab" tabindex="0">
                @php
                    $content_params = (isset($tab['params'])) ? $tab['params'] : [];
                    $content_params['name'] = (isset($content_params['name'])) ? $content_params['name'] : $tab['name'];
                @endphp
                @include($tab['view'], $content_params)
            </div>
        @endforeach
    </div>
</div>
