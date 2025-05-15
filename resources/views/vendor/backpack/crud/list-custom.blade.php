@extends(backpack_view('blank'))

@php
  $defaultBreadcrumbs = [
    trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
    $crud->entity_name_plural => url($crud->route),
    trans('backpack::crud.list') => false,
  ];

  // if breadcrumbs aren't defined in the CrudController, use the default breadcrumbs
  $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
@endphp

@section('header')
    <section class="header-operation container-fluid animated fadeIn d-flex mb-2 align-items-baseline d-print-none mt-3" bp-section="page-header">
        <h1 class="text-capitalize mb-0" bp-section="page-heading">{!! $crud->getHeading() ?? $crud->entity_name_plural !!}</h1>
        <p class="ms-2 ml-2 mb-0" id="datatable_info_stack" bp-section="page-subheading">{!! $crud->getSubheading() ?? '' !!}</p>
    </section>
    @if (backpack_theme_config('breadcrumbs') && isset($breadcrumbs) && is_array($breadcrumbs) && count($breadcrumbs))
        <nav aria-label="breadcrumb" class="d-none d-lg-block">
            <div class="d-flex justify-content-between">
                <ol class="breadcrumb bg-transparent p-0 mx-3">
                    @foreach ($breadcrumbs as $label => $link)
                        @if ($link)
                            <li class="breadcrumb-item text-capitalize"><a href="{{ $link }}">{{ $label }}</a></li>
                        @else
                            <li class="breadcrumb-item text-capitalize active" aria-current="page">{{ $label }}</li>
                        @endif
                    @endforeach
                </ol>
                <div class="d-print-none mb-2 pe-3 {{ $crud->hasAccess('create')?'with-border':'' }}">
                    @include('crud::inc.button_stack', ['stack' => 'top'])
                </div>
            </div>
        </nav>
    @endif
@endsection

@section('content')
  {{-- Default box --}}
  <div class="row" bp-section="crud-operation-list">
    {{-- THE ACTUAL CONTENT --}}
    <div class="{{ $crud->getListContentClass() }}">

        <div class="row mb-2 align-items-center">
          <div class="col-sm-9">
            {{-- @if ( $crud->buttons()->where('stack', 'top')->count() ||  $crud->exportButtons())
              <div class="d-print-none {{ $crud->hasAccess('create')?'with-border':'' }}">

                @include('crud::inc.button_stack', ['stack' => 'top'])

              </div>
            @endif --}}
          </div>
          @if($crud->getOperationSetting('searchableTable'))
          <div class="col-sm-3">
            <div id="datatable_search_stack" class="mt-sm-0 mt-2 d-print-none">
              <div class="input-icon">
                <span class="input-icon-addon">
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"></path><path d="M21 21l-6 -6"></path></svg>
                </span>
                <input type="search" class="form-control" placeholder="{{ trans('backpack::crud.search') }}..."/>
              </div>
            </div>
          </div>
          @endif
        </div>

        {{-- Backpack List Filters --}}
        @if ($crud->filtersEnabled())
          @include('crud::inc.filters_navbar')
        @endif

        <div class="{{ backpack_theme_config('classes.tableWrapper') }}">
            <table
              id="crudTable"
              class="{{ backpack_theme_config('classes.table') ?? 'table table-striped table-hover nowrap rounded card-table table-vcenter card d-table shadow-xs border-xs' }}"
              data-responsive-table="{{ (int) $crud->getOperationSetting('responsiveTable') }}"
              data-has-details-row="{{ (int) $crud->getOperationSetting('detailsRow') }}"
              data-has-bulk-actions="{{ (int) $crud->getOperationSetting('bulkActions') }}"
              data-has-line-buttons-as-dropdown="{{ (int) $crud->getOperationSetting('lineButtonsAsDropdown') }}"
              data-line-buttons-as-dropdown-minimum="{{ (int) $crud->getOperationSetting('lineButtonsAsDropdownMinimum') }}"
              data-line-buttons-as-dropdown-show-before-dropdown="{{ (int) $crud->getOperationSetting('lineButtonsAsDropdownShowBefore') }}"
              cellspacing="0">
            <thead>
              <tr>
                {{-- Table columns --}}
                @foreach ($crud->columns() as $column)
                  @php
                  $exportOnlyColumn = $column['exportOnlyColumn'] ?? false;
                  $visibleInTable = $column['visibleInTable'] ?? ($exportOnlyColumn ? false : true);
                  $visibleInModal = $column['visibleInModal'] ?? ($exportOnlyColumn ? false : true);
                  $visibleInExport = $column['visibleInExport'] ?? true;
                  $forceExport = $column['forceExport'] ?? (isset($column['exportOnlyColumn']) ? true : false);
                  @endphp
                  <th
                    data-orderable="{{ var_export($column['orderable'], true) }}"
                    data-priority="{{ $column['priority'] }}"
                    data-column-name="{{ $column['name'] }}"
                    {{--
                    data-visible-in-table => if developer forced column to be in the table with 'visibleInTable => true'
                    data-visible => regular visibility of the column
                    data-can-be-visible-in-table => prevents the column to be visible into the table (export-only)
                    data-visible-in-modal => if column appears on responsive modal
                    data-visible-in-export => if this column is exportable
                    data-force-export => force export even if columns are hidden
                    --}}

                    data-visible="{{ $exportOnlyColumn ? 'false' : var_export($visibleInTable) }}"
                    data-visible-in-table="{{ var_export($visibleInTable) }}"
                    data-can-be-visible-in-table="{{ $exportOnlyColumn ? 'false' : 'true' }}"
                    data-visible-in-modal="{{ var_export($visibleInModal) }}"
                    data-visible-in-export="{{ $exportOnlyColumn ? 'true' : ($visibleInExport ? 'true' : 'false') }}"
                    data-force-export="{{ var_export($forceExport) }}"
                  >
                    {{-- Bulk checkbox --}}
                    @if($loop->first && $crud->getOperationSetting('bulkActions'))
                      	{!! View::make('crud::columns.inc.bulk_actions_checkbox')->render() !!}
                    @endif
                    {!! $column['label'] !!}
                  </th>
                @endforeach

                @if ( $crud->buttons()->where('stack', 'line')->count() )
                  <th data-orderable="false"
                      data-priority="{{ $crud->getActionsColumnPriority() }}"
                      data-visible-in-export="false"
                      data-action-column="true"
                      >{{ trans('backpack::crud.actions') }}</th>
                @endif
              </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
              <tr>
                {{-- Table columns --}}
                @foreach ($crud->columns() as $column)
                  <th>
                    {{-- Bulk checkbox --}}
                    @if($loop->first && $crud->getOperationSetting('bulkActions'))
                      	{!! View::make('crud::columns.inc.bulk_actions_checkbox')->render() !!}
                    @endif
                    {!! $column['label'] !!}
                  </th>
                @endforeach

                @if ( $crud->buttons()->where('stack', 'line')->count() )
                  <th>{{ trans('backpack::crud.actions') }}</th>
                @endif
              </tr>
            </tfoot>
          </table>
        </div>

        @if ( $crud->buttons()->where('stack', 'bottom')->count() )
            <div id="bottom_buttons" class="d-print-none text-sm-left">
                @include('crud::inc.button_stack', ['stack' => 'bottom'])
                <div id="datatable_button_stack" class="float-right float-end text-right hidden-xs"></div>
            </div>
        @endif

    </div>

  </div>

@endsection

@section('after_styles')
  {{-- DATA TABLES --}}
  @basset('https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css')
  @basset('https://cdn.datatables.net/fixedheader/3.3.1/css/fixedHeader.dataTables.min.css')
  @basset('https://cdn.datatables.net/responsive/2.4.0/css/responsive.dataTables.min.css')

  {{-- CRUD LIST CONTENT - crud_list_styles stack --}}
  @stack('crud_list_styles')
@endsection

@section('after_scripts')
  @include('crud::inc.datatables_logic')

  {{-- CRUD LIST CONTENT - crud_list_scripts stack --}}
  @stack('crud_list_scripts')
@endsection

@push('after_scripts')
    <!-- Modal -->
    <div class="modal fade" id="modalCreate" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">{!! $crud->getSubheading() ?? trans('backpack::crud.add').' '.$title_modal_create !!}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Loading...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ trans('backpack::crud.cancel_submit') }}</button>
                    <button type="button" id="btn-submit-create" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        {{ trans('backpack::crud.save_submit') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalEdit" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">{!! $crud->getSubheading() ?? trans('backpack::crud.edit').' '.$title_modal_edit !!}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Loading...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ trans('backpack::crud.cancel_submit') }}</button>
                    <button type="button" id="btn-submit-edit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        {{ trans('backpack::crud.save_submit') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script>
        function btnLoader(btn_id, enabled = true){
            var idbtn = $('#'+btn_id);
            if(enabled){
                idbtn.removeAttr('disabled');
                idbtn.html("{{trans('backpack::crud.save_submit')}}");
            }else{
                idbtn.attr('disabled', 'disabled');
                idbtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...');
            }
        }

        function hideModal(modal_id){
            document.querySelector('#'+modal_id+' button.btn-close').click();
        }
    </script>
    <script>
        if(errorShowMessage === undefined && normalizeShowMessage === undefined){
            function errorShowMessage(rootForm, errorJson){
                window.errors = {
                    default: errorJson
                };
                $.each(errors, function(bag, errorMessages){
                    $.each(errorMessages,  function (inputName, messages) {
                        var normalizedProperty = inputName.split('.').map(function(item, index){
                                return index === 0 ? item : '['+item+']';
                            }).join('');

                        var field = $('#'+rootForm+' [name="' + normalizedProperty + '[]"]').length ?
                                    $('#'+rootForm+' [name="' + normalizedProperty + '[]"]') :
                                    $('#'+rootForm+' [name="' + normalizedProperty + '"]'),
                                    container = field.closest('.form-group');

                        // iterate the inputs to add invalid classes to fields and red text to the field container.
                        container.find('input, textarea, select').each(function() {
                            let containerField = $(this);
                            // add the invalid class to the field.
                            containerField.addClass('is-invalid');
                            // get field container
                            let container = containerField.closest('.form-group');

                            // TODO: `repeatable-group` should be deprecated in future version as a BC in favor of a more generic class `no-error-display`
                            if(!container.hasClass('repeatable-group') && !container.hasClass('no-error-display')){
                                container.addClass('text-danger');
                            }
                        });

                        $.each(messages, function(key, msg){
                            // highlight the input that errored
                            var row = $('<div class="invalid-feedback d-block">' + msg + '</div>');

                            // TODO: `repeatable-group` should be deprecated in future version as a BC in favor of a more generic class `no-error-display`
                            if(!container.hasClass('repeatable-group') && !container.hasClass('no-error-display')){
                                row.appendTo(container);
                            }


                            // highlight its parent tab
                            @if ($crud->tabsEnabled())
                            var tab_id = $(container).closest('[role="tabpanel"]').attr('id');
                            $("#form_tabs [aria-controls="+tab_id+"]").addClass('text-danger');
                            @endif
                        });
                    });
                });
            }

            function normalizeShowMessage(rootForm){
                if(window.errors === undefined){
                    window.errors = {
                        default: {}
                    };

                }
                $.each(errors, function(bag, errorMessages){
                    $.each(errorMessages,  function (inputName, messages) {
                        var normalizedProperty = inputName.split('.').map(function(item, index){
                                return index === 0 ? item : '['+item+']';
                            }).join('');

                        var field = $('#'+rootForm+' [name="' + normalizedProperty + '[]"]').length ?
                                    $('#'+rootForm+' [name="' + normalizedProperty + '[]"]') :
                                    $('#'+rootForm+' [name="' + normalizedProperty + '"]'),
                                    container = field.closest('.form-group');

                        // iterate the inputs to add invalid classes to fields and red text to the field container.
                        container.find('input, textarea, select').each(function() {
                            let containerField = $(this);
                            // add the invalid class to the field.
                            containerField.removeClass('is-invalid');
                            // get field container
                            let container = containerField.closest('.form-group');

                            // TODO: `repeatable-group` should be deprecated in future version as a BC in favor of a more generic class `no-error-display`
                            if(!container.hasClass('repeatable-group') && !container.hasClass('no-error-display')){
                                container.removeClass('text-danger');
                            }
                        });

                        $.each(messages, function(key, msg){
                            // highlight the input that errored
                            var row = $('<div class="invalid-feedback d-block">' + msg + '</div>');

                            // TODO: `repeatable-group` should be deprecated in future version as a BC in favor of a more generic class `no-error-display`
                            // if(!container.hasClass('repeatable-group') && !container.hasClass('no-error-display')){
                            //     row.appendTo(container);
                            // }

                            $('.invalid-feedback').remove();


                            // highlight its parent tab
                            @if ($crud->tabsEnabled())
                            var tab_id = $(container).closest('[role="tabpanel"]').attr('id');
                            $("#form_tabs [aria-controls="+tab_id+"]").addClass('text-danger');
                            @endif
                        });
                    });
                });
            }
        }
    </script>
@endpush
