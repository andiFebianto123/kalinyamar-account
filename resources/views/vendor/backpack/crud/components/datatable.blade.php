
<div class="pt-3 pb-2">
    <div id="panel-{{$name}}">
        {{-- <div class="d-flex justify-content-between">
            <div class="p-2 bd-highlight">Flex item 1</div>
            <div class="p-2 bd-highlight">Flex item 2</div>
            <div class="p-2 bd-highlight">Flex item 3</div>
        </div> --}}
    </div>
    <div class="row" bp-section="crud-operation-list">
        {{-- THE ACTUAL CONTENT --}}
        <div class="{{ $crud->getListContentClass() }}">

            {{-- Backpack List Filters --}}
            {{-- @if ($crud->filtersEnabled())
            @include('crud::inc.filters_navbar')
            @endif --}}

            <div class="{{ backpack_theme_config('classes.tableWrapper') }}">
                <table
                id="crudTable-{{$name}}"
                class="{{ backpack_theme_config('classes.table') ?? 'table table-hover nowrap rounded card-table table-vcenter card d-table shadow-xs border-xs' }}"
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
                    @foreach ($columns as $column)
                    @php

                    $column['orderable'] = $column['orderable'] ?? true;
                    $column['priority'] = $column['priority'] ?? 1;
                    $column['visibleInTable'] = $column['visibleInTable'] ?? true;
                    $column['visibleInModal'] = $column['visibleInModal'] ?? true;
                    $column['visibleInExport'] = $column['visibleInExport'] ?? true;
                    $column['forceExport'] = $column['forceExport'] ?? false;
                    $column['exportOnlyColumn'] = $column['exportOnlyColumn'] ?? false;

                    $exportOnlyColumn = $column['exportOnlyColumn'] ?? false;
                    $visibleInTable = $column['visibleInTable'] ?? ($exportOnlyColumn ? false : true);
                    $visibleInModal = $column['visibleInModal'] ?? ($exportOnlyColumn ? false : true);
                    $visibleInExport = $column['visibleInExport'] ?? true;
                    $forceExport = $column['forceExport'] ?? (isset($column['exportOnlyColumn']) ? true : false);
                    @endphp
                        @if ($column['type'] == 'action')
                            <th data-orderable="false"
                            data-priority="1"
                            data-visible-in-export="false"
                            data-action-column="true"
                            >{{ $column['label'] ?? trans('backpack::crud.actions') }}</th>
                        @else
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
                                @if ($column['name'] == 'bulk_actions')
                                    {!! View::make('crud::columns.inc.bulk_actions_checkbox')->render() !!}
                                @else
                                    {!! $column['label'] !!}
                                @endif
                                {{-- @if($loop->first && $crud->getOperationSetting('bulkActions'))
                                @endif --}}
                            </th>
                        @endif
                    @endforeach
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            </div>

            {{-- @if ( $crud->buttons()->where('stack', 'bottom')->count() )
                <div id="bottom_buttons" class="d-print-none text-sm-left">
                    @include('crud::inc.button_stack', ['stack' => 'bottom'])
                    <div id="datatable_button_stack" class="float-right float-end text-right hidden-xs"></div>
                </div>
            @endif --}}
        </div>
    </div>
</div>

@push('inline_scripts')
    @once
        @basset('https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css')
        @basset('https://cdn.datatables.net/fixedheader/3.3.1/css/fixedHeader.dataTables.min.css')
        @basset('https://cdn.datatables.net/responsive/2.4.0/css/responsive.dataTables.min.css')
    @endonce
@endpush

@push('inline_scripts')
    <style>
        #crudTable-{{$name}}_processing.dataTables_processing.card {
            all: unset;
            position: absolute;
            background: rgba(255, 255, 255, 0.9);
            height: calc(100% - 6px);
            width: calc(100% - 20px);
            top: 0;
            left: 10px;
            z-index: 999;
            border-radius: 5px;
        }
        #crudTable-{{$name}}_processing.dataTables_processing.card > img {
            margin: 0;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        #crudTable-{{$name}}_processing.dataTables_processing.card > div {
            display: none !important;
        }
    </style>
@endpush

@push('after_scripts')
    @once
        {{-- @include('crud::inc.datatables_logic') --}}
        @basset('https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js')
        @basset('https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js')
        @basset('https://cdn.datatables.net/responsive/2.4.0/js/dataTables.responsive.min.js')
        @basset('https://cdn.datatables.net/responsive/2.4.0/css/responsive.dataTables.min.css')
        @basset('https://cdn.datatables.net/fixedheader/3.3.1/js/dataTables.fixedHeader.min.js')
        @basset('https://cdn.datatables.net/fixedheader/3.3.1/css/fixedHeader.dataTables.min.css')
        @basset(base_path('vendor/backpack/crud/src/resources/assets/img/spinner.svg'), false)
        <script>
            var $dtDefaultPageLength = {{ $crud->getDefaultPageLength() }};
            let $pageLength = @json($crud->getPageLengthMenu());
            window.crud = {
                ...window.crud,
                exportButtons: JSON.parse('{!! json_encode($crud->get('list.export_buttons')) !!}'),
                functionsToRunOnDataTablesDrawEvent: [],
                addFunctionToDataTablesDrawEventQueue: function (functionName) {
                    if (this.functionsToRunOnDataTablesDrawEvent.indexOf(functionName) == -1) {
                    this.functionsToRunOnDataTablesDrawEvent.push(functionName);
                    }
                },
                responsiveToggle: function(dt) {
                    $(dt.table().header()).find('th').toggleClass('all');
                    dt.responsive.rebuild();
                    dt.responsive.recalc();
                },
                executeFunctionByName: function(str, args) {
                    var arr = str.split('.');
                    var fn = window[ arr[0] ];

                    for (var i = 1; i < arr.length; i++)
                    { fn = fn[ arr[i] ]; }
                    fn.apply(window, args);
                },
                updateUrl : function (url) {
                    // let urlStart = "{{ url($crud->route) }}";
                    // let urlEnd = url.replace(urlStart, '');
                    // urlEnd = urlEnd.replace('/search', '');
                    // let newUrl = urlStart + urlEnd;
                    // let tmpUrl = newUrl.split("?")[0],
                    // params_arr = [],
                    // queryString = (newUrl.indexOf("?") !== -1) ? newUrl.split("?")[1] : false;

                    // // exclude the persistent-table parameter from url
                    // if (queryString !== false) {
                    //     params_arr = queryString.split("&");
                    //     for (let i = params_arr.length - 1; i >= 0; i--) {
                    //         let param = params_arr[i].split("=")[0];
                    //         if (param === 'persistent-table') {
                    //             params_arr.splice(i, 1);
                    //         }
                    //     }
                    //     newUrl = params_arr.length ? tmpUrl + "?" + params_arr.join("&") : tmpUrl;
                    // }
                    // window.history.pushState({}, '', newUrl);
                    @if ($crud->getPersistentTable())
                        // localStorage.setItem('{{ Str::slug($crud->getRoute()) }}_list_url', newUrl);
                    @endif
                },
                dataTableConfiguration: {
                    bInfo: {{ var_export($crud->getOperationSetting('showEntryCount') ?? true) }},
                    @if ($crud->getResponsiveTable())
                    responsive: {
                        details: {
                            display: $.fn.dataTable.Responsive.display.modal( {
                                header: function ( row ) {
                                    // show the content of the first column
                                    // as the modal header
                                    // var data = row.data();
                                    // return data[0];
                                    return '';
                                }
                            }),
                            type: 'none',
                            target: '.dtr-control',
                            renderer: function ( api, rowIdx, columns ) {
                                var data = $.map( columns, function ( col, i ) {
                                    var columnHeading = crud.table.columns().header()[col.columnIndex];
                                    // hide columns that have VisibleInModal false
                                    if ($(columnHeading).attr('data-visible-in-modal') == 'false') {
                                        return '';
                                    }

                                    if (col.data.indexOf('crud_bulk_actions_checkbox') !== -1) {
                                        col.data = col.data.replace('crud_bulk_actions_checkbox', 'crud_bulk_actions_checkbox d-none');
                                    }

                                    let colTitle = '';
                                    if (col.title) {
                                        let tempDiv = document.createElement('div');
                                        tempDiv.innerHTML = col.title;

                                        let checkboxSpan = tempDiv.querySelector('.crud_bulk_actions_checkbox');
                                        if (checkboxSpan) {
                                            checkboxSpan.remove();
                                        }

                                        colTitle = tempDiv.textContent.trim();
                                    } else {
                                        colTitle = '';
                                    }

                                    return '<tr data-dt-row="'+col.rowIndex+'" data-dt-column="'+col.columnIndex+'">'+
                                            '<td style="vertical-align:top; border:none;"><strong>'+colTitle+':'+'<strong></td> '+
                                            '<td style="padding-left:10px;padding-bottom:10px; border:none;">'+col.data+'</td>'+
                                            '</tr>';
                                }).join('');

                                return data ?
                                    $('<table class="table table-striped mb-0">').append( '<tbody>' + data + '</tbody>' ) :
                                    false;
                            },
                        }
                    },
                    fixedHeader: true,
                    @else
                    responsive: false,
                    scrollX: true,
                    @endif

                    @if ($crud->getPersistentTable())
                    stateSave: true,
                    /*
                        if developer forced field into table 'visibleInTable => true' we make sure when saving datatables state
                        that it reflects the developer decision.
                    */

                    stateSaveParams: function(settings, data) {

                        localStorage.setItem('{{ Str::slug($crud->getRoute()) }}_list_url_time', data.time);

                        data.columns.forEach(function(item, index) {
                            var columnHeading = crud.table.columns().header()[index];
                            if ($(columnHeading).attr('data-visible-in-table') == 'true') {
                                return item.visible = true;
                            }
                        });
                    },
                    @if($crud->getPersistentTableDuration())
                    stateLoadParams: function(settings, data) {
                        var $saved_time = new Date(data.time);
                        var $current_date = new Date();

                        $saved_time.setMinutes($saved_time.getMinutes() + {{$crud->getPersistentTableDuration()}});

                        //if the save time as expired we force datatabled to clear localStorage
                        if($saved_time < $current_date) {
                            if (localStorage.getItem('{{ Str::slug($crud->getRoute())}}_list_url')) {
                                localStorage.removeItem('{{ Str::slug($crud->getRoute()) }}_list_url');
                            }
                            if (localStorage.getItem('{{ Str::slug($crud->getRoute())}}_list_url_time')) {
                                localStorage.removeItem('{{ Str::slug($crud->getRoute()) }}_list_url_time');
                            }
                        return false;
                        }
                    },
                    @endif
                    @endif
                    autoWidth: false,
                    pageLength: $dtDefaultPageLength,
                    lengthMenu: $pageLength,
                    /* Disable initial sort */
                    aaSorting: [],
                    language: {
                        "emptyTable":     "{{ trans('backpack::crud.emptyTable') }}",
                        "info":           "{{ trans('backpack::crud.info') }}",
                        "infoEmpty":      "{{ trans('backpack::crud.infoEmpty') }}",
                        "infoFiltered":   "{{ trans('backpack::crud.infoFiltered') }}",
                        "infoPostFix":    "{{ trans('backpack::crud.infoPostFix') }}",
                        "thousands":      "{{ trans('backpack::crud.thousands') }}",
                        "lengthMenu":     "{{ trans('backpack::crud.lengthMenu') }}",
                        "loadingRecords": "{{ trans('backpack::crud.loadingRecords') }}",
                        "processing":     "<img src='{{ Basset::getUrl('vendor/backpack/crud/src/resources/assets/img/spinner.svg') }}' alt='{{ trans('backpack::crud.processing') }}'>",
                        "search": "_INPUT_",
                        "searchPlaceholder": "{{ trans('backpack::crud.search') }}...",
                        "zeroRecords":    "{{ trans('backpack::crud.zeroRecords') }}",
                        "paginate": {
                            "first":      "{{ trans('backpack::crud.paginate.first') }}",
                            "last":       "{{ trans('backpack::crud.paginate.last') }}",
                            //   "next":       '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M8 5l5 5l-5 5"></path></svg>',
                            //   "previous":   '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M15 5l-5 5l5 5"></path></svg>'
                        },
                        "aria": {
                            "sortAscending":  "{{ trans('backpack::crud.aria.sortAscending') }}",
                            "sortDescending": "{{ trans('backpack::crud.aria.sortDescending') }}"
                        },
                        "buttons": {
                            "copy":   "{{ trans('backpack::crud.export.copy') }}",
                            "excel":  "{{ trans('backpack::crud.export.excel') }}",
                            "csv":    "{{ trans('backpack::crud.export.csv') }}",
                            "pdf":    "{{ trans('backpack::crud.export.pdf') }}",
                            "print":  "{{ trans('backpack::crud.export.print') }}",
                            "colvis": "{{ trans('backpack::crud.export.column_visibility') }}"
                        },
                    },
                    processing: true,
                    serverSide: true,
                    searchDelay: {{ $crud->getOperationSetting('searchDelay') }},
                    @if($crud->getOperationSetting('showEntryCount') === false)
                        pagingType: "simple",
                    @endif
                    searching: @json($crud->getOperationSetting('searchableTable') ?? true),
                    ajax: {
                        "url": "{!! url($crud->route.'/search').'?'.Request::getQueryString() !!}",
                        "type": "POST",
                        "data": {
                            "totalEntryCount": "{{$crud->getOperationSetting('totalEntryCount') ?? false}}"
                        },
                    },
                    //   dom:
                    //     "<'row hidden'<'col-sm-6'i><'col-sm-6 d-print-none'f>>" +
                    //     "<'table-content row'<'col-sm-12'tr>>" +
                    //     "<'table-footer row mt-2 d-print-none align-items-center '<'col-sm-12 col-md-4'l><'col-sm-0 col-md-4 text-center'B><'col-sm-12 col-md-4 'p>>",
                    dom:
                        "<'row hidden'<'col-sm-6'l><'col-sm-6 d-print-none'f>>" +
                        "<'table-content row'<'col-sm-12'tr>>" +
                        "<'table-footer row mt-2 d-print-none align-items-center '<'col-sm-12 col-md-4'p><'col-sm-0 col-md-4 text-center'B><'col-sm-12 col-md-4 'i>>",
                }
            };
        </script>
    @endonce
@endpush

@push('after_scripts')
    <script>
        var filterValues = [];
        function setupFilterInputs(tableId) {
            const $table = $(tableId);
            const totalColumns = $(tableId+' thead tr th').length;

            $table.find('thead').append('<tr class="filters"></tr>');

            $table.find('thead tr:first th').each(function (i) {
                if (i === 0 || i === totalColumns - 1) {
                    $table.find('thead tr.filters').append('<th><input type="hidden" /></th>');
                } else {
                    $table.find('thead tr.filters').append(
                        `<th><input type="text" class="form-control form-control-sm" placeholder="" /></th>`
                    );
                }
            });

            $table.find('thead tr.filters th input').each(function (i) {
                if (filterValues[i] !== undefined) {
                    $(this).val(filterValues[i]);
                }
            });
        }

        function bindFilterEvents(tableInstance, tableId) {
            const totalColumns = $(tableId).find('thead tr:first th').length;

            tableInstance.columns().every(function (i) {
                if (i !== 0 && i !== totalColumns - 1) {
                    const column = this;
                    const input = $(`${tableId} thead tr.filters th`).eq(i).find('input');
                    input.on('change clear', function () {
                        filterValues[i] = this.value;
                        column.search(this.value).draw();
                    });
                }
            });
        }
    </script>
    <script>
        SIAOPS.setAttribute('crudTable-{{$name}}', function() {
            return {
                id: $('#crudTable-{{$name}}'),
                route: "{!! url($route) !!}",
                table: null,
                eventLoader: function(){
                    // event when create success

                    var instance = this;

                    // eventEmitter.on("cast_account_store_success", function(){
                    //     window.location.href = location.href;
                    // });

                    eventEmitter.on("crudTable-{{$name}}_create_success", function(data){
                        instance.table.ajax.reload();
                    });

                    // event when delete success
                    // eventEmitter.on("{{$name}}_delete_success", function(data){
                    //     $('#{{$name}} .saldo-str').html(data.new_saldo);
                    // });

                    eventEmitter.on("crudTable-{{$name}}_updated_success", function(data){
                        instance.table.ajax.reload();
                    });
                },
                createDatatable: function(){
                    var instance = this;
                    instance.table = instance.id.DataTable({
                        ...window.crud.dataTableConfiguration,
                        bInfo: true,
                        responsive: false,
                        scrollX: true,
                        stateSave: false,
                        stateSaveParams: function(settings, data) {
                            // localStorage.setItem('adminauthpermission_list_url_time', data.time);
                            data.columns.forEach(function(item, index) {
                                var columnHeading = instance.table.columns().header()[index];
                                if ($(columnHeading).attr('data-visible-in-table') == 'true') {
                                    return item.visible = true;
                                }
                            });
                        },
                        autoWidth: false,
                        pageLength: $dtDefaultPageLength,
                        lengthMenu: $pageLength,
                        aaSorting: [],
                        language: {
                            "emptyTable":     "{{ trans('backpack::crud.emptyTable') }}",
                            "info":           "{{ trans('backpack::crud.info') }}",
                            "infoEmpty":      "{{ trans('backpack::crud.infoEmpty') }}",
                            "infoFiltered":   "{{ trans('backpack::crud.infoFiltered') }}",
                            "infoPostFix":    "{{ trans('backpack::crud.infoPostFix') }}",
                            "thousands":      "{{ trans('backpack::crud.thousands') }}",
                            "lengthMenu":     "{{ trans('backpack::crud.lengthMenu') }}",
                            "loadingRecords": "{{ trans('backpack::crud.loadingRecords') }}",
                            "processing":     "<img src='{{ Basset::getUrl('vendor/backpack/crud/src/resources/assets/img/spinner.svg') }}' alt='{{ trans('backpack::crud.processing') }}'>",
                            "search": "_INPUT_",
                            "searchPlaceholder": "{{ trans('backpack::crud.search') }}...",
                            "zeroRecords":    "{{ trans('backpack::crud.zeroRecords') }}",
                            "paginate": {
                                "first":      "{{ trans('backpack::crud.paginate.first') }}",
                                "last":       "{{ trans('backpack::crud.paginate.last') }}",
                                //   "next":       '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M8 5l5 5l-5 5"></path></svg>',
                                //   "previous":   '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M15 5l-5 5l5 5"></path></svg>'
                            },
                            "aria": {
                                "sortAscending":  "{{ trans('backpack::crud.aria.sortAscending') }}",
                                "sortDescending": "{{ trans('backpack::crud.aria.sortDescending') }}"
                            },
                            "buttons": {
                                "copy":   "{{ trans('backpack::crud.export.copy') }}",
                                "excel":  "{{ trans('backpack::crud.export.excel') }}",
                                "csv":    "{{ trans('backpack::crud.export.csv') }}",
                                "pdf":    "{{ trans('backpack::crud.export.pdf') }}",
                                "print":  "{{ trans('backpack::crud.export.print') }}",
                                "colvis": "{{ trans('backpack::crud.export.column_visibility') }}"
                            },
                        },
                        processing: true,
                        serverSide: true,
                        searching: 400,
                        ajax: {
                            "url": "{!! url($route) !!}",
                            "type": "POST",
                            // "data": {
                            //     "totalEntryCount": "{{$crud->getOperationSetting('totalEntryCount') ?? false}}"
                            // },
                            data: function(d){
                                d.totalEntryCount = "{{$crud->getOperationSetting('totalEntryCount') ?? false}}";
                                const totalColumns = $('#crudTable-{{$name}}').find('thead tr:first th').length;
                                $('#crudTable-{{$name}} thead tr.filters th').each(function (i) {
                                    if (i !== 0 && i !== totalColumns - 1) {
                                        const input = $(this).find('input');
                                        if (input.length) {
                                            d.columns[i].search.value = input.val();
                                        }
                                    }
                                });
                            }
                        },
                        initComplete: function () {
                            // SIAOPS.getAttribute('crudTable-{{$name}}').table.columns.adjust();

                            @if (isset($filter))
                                setupFilterInputs('#crudTable-{{$name}}');
                                bindFilterEvents(this.api(), '#crudTable-{{$name}}');
                            @endif

                        },
                        dom:
                        "<'row hidden'<'col-sm-6'l><'col-sm-6 d-print-none'f>>" +
                        "<'table-content row'<'col-sm-12'tr>>" +
                        "<'table-footer row mt-2 d-print-none align-items-center '<'col-sm-12 col-md-4'p><'col-sm-0 col-md-4 text-center'B><'col-sm-12 col-md-4 'i>>",
                    });

                    $("#crudTable-{{$name}}_filter").remove();
                    $('#crudTable-{{$name}}_length').remove();

                    $(".navbar.navbar-filters + div").css('overflow','initial');

                   $('#crudTable-{{$name}}').on( 'draw.dt',   function () {
                        @if (isset($filter))
                            setupFilterInputs('#crudTable-{{$name}}');
                            bindFilterEvents(SIAOPS.getAttribute('crudTable-{{$name}}').table, '#crudTable-{{$name}}');
                        @endif
                        // setupFilterInputs('#crudTable-{{$name}}');
                        // instance.table.fixedHeader.adjust();

                        // crud.functionsToRunOnDataTablesDrawEvent.forEach(function(functionName) {
                        //     crud.executeFunctionByName(functionName);
                        // });
                        // if ($('#crudTable-{{$name}}').data('has-line-buttons-as-dropdown')) {
                        //     formatActionColumnAsDropdown();
                        // }

                        // if (! instance.table.responsive.hasHidden()) {
                        //     instance.table.columns().header()[0].style.paddingLeft = '0.6rem';
                        // }

                        // if (instance.table.responsive.hasHidden()) {

                        //     $('.dtr-control').removeClass('d-none');
                        //     $('.dtr-control').addClass('d-inline');
                        //     $("#crudTable-{{$name}}").removeClass('has-hidden-columns').addClass('has-hidden-columns');
                        // }

                    }).dataTable();

                    $('#crudTable-{{$name}}').on( 'column-visibility.dt',   function (event) {
                        console.log('column-visibility.dt');
                        instance.table.responsive.rebuild();
                    } ).dataTable();

                },
                load: function(){
                    var instance = this;
                    instance.eventLoader();
                    instance.createDatatable();

                    $('#crudTable_length select').on('change', function(){
                        var val = parseInt($(this).val(), 10);
                        instance.table.page.len(val).draw();
                    });

                    $('#datatable_search_input').on('input', function(){
                        var val = this.value;
                        if (val === "") {
                            instance.table.search(this.value).draw();
                        }else{
                            instance.table.search(this.value).draw();
                        }
                    });

                }
            };
        });
        $(function(){
            SIAOPS.getAttribute('crudTable-{{$name}}').load();

            @if (!isset($filter))

                var datatable = SIAOPS.getAttribute('crudTable-{{$name}}').table;

                var resizeTimer;
                function resizeCrudTableColumnWidths() {
                    clearTimeout(resizeTimer);
                    resizeTimer = setTimeout(function() {
                        // Run code here, resizing has "stopped"
                        datatable.columns.adjust();
                    }, 250);
                }

                $(window).on('resize', function(e) {
                    resizeCrudTableColumnWidths();
                });

                $('.sidebar-toggler').click(function() {
                    resizeCrudTableColumnWidths();
                });


                $(window).on('click', function(){
                    SIAOPS.getAttribute('crudTable-{{$name}}').table.columns.adjust();
                });

            @endif
        });
    </script>
@endpush
