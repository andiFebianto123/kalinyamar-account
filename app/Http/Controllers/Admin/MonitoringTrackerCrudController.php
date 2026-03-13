<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Project;
use App\Models\ProjectHistory;
use App\Models\SetupClient;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\CrudController;
use App\Http\Controllers\Operation\PermissionAccess;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class MonitoringTrackerCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use PermissionAccess;

    public function setup()
    {
        CRUD::setModel(Project::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/monitoring/tracker');
        CRUD::setEntityNameStrings(trans('backpack::crud.monitoring_tracker.title.monitoring_tracker'), trans('backpack::crud.monitoring_tracker.title.monitoring_tracker'));

        $base     = 'INDEX MONITORING TRACKER';
        $viewMenu = ["MENU $base"];

        $this->settingPermission([
            'create' => ["CREATE $base"],
            'update' => ["UPDATE $base"],
            'delete' => ["DELETE $base"],
            'list'   => $viewMenu,
            'show'   => $viewMenu,
        ]);
    }

    public function index()
    {
        $this->crud->hasAccessOrFail('list');

        $this->card->addCard([
            'name' => 'tracker',
            'line' => 'top',
            'view' => 'crud::components.card-tab',
            'params' => [
                'tabs' => [
                    [
                        'name'   => 'tracker',
                        'label'  => trans('backpack::crud.monitoring_tracker.tab.tracker'),
                        'active' => true,
                        'view'   => 'crud::components.datatable',
                        'params' => [
                            'crud_custom' => $this->crud,
                            'columns'     => [
                                [
                                    'name'      => 'row_number',
                                    'type'      => 'row_number',
                                    'label'     => trans('backpack::crud.monitoring_tracker.column.no'),
                                    'orderable' => false,
                                ],
                                [
                                    'name'  => 'action',
                                    'type'  => 'action',
                                    'label' => trans('backpack::crud.actions'),
                                ],
                                [
                                    'name'      => 'no_po_spk',
                                    'type'      => 'text',
                                    'label'     => trans('backpack::crud.monitoring_tracker.column.no_po_spk'),
                                    'orderable' => true,
                                ],
                                [
                                    'name'      => 'name',
                                    'type'      => 'text',
                                    'label'     => trans('backpack::crud.monitoring_tracker.column.name'),
                                    'orderable' => true,
                                ],
                                [
                                    'name'      => 'client_id',
                                    'type'      => 'text',
                                    'label'     => trans('backpack::crud.monitoring_tracker.column.client_id'),
                                    'orderable' => false,
                                ],
                                [
                                    'name'      => 'actual_end_date',
                                    'type'      => 'text',
                                    'label'     => trans('backpack::crud.monitoring_tracker.column.actual_end_date'),
                                    'orderable' => true,
                                ],
                                [
                                    'name'      => 'total_time',
                                    'type'      => 'text',
                                    'label'     => trans('backpack::crud.monitoring_tracker.column.total_time'),
                                    'orderable' => false,
                                ],
                                [
                                    'name'      => 'progress',
                                    'type'      => 'text',
                                    'label'     => trans('backpack::crud.monitoring_tracker.column.progress'),
                                    'orderable' => true,
                                ],
                                [
                                    'name'      => 'pic',
                                    'type'      => 'text',
                                    'label'     => trans('backpack::crud.monitoring_tracker.column.pic'),
                                    'orderable' => false,
                                ],
                                [
                                    'name'      => 'user',
                                    'type'      => 'text',
                                    'label'     => trans('backpack::crud.monitoring_tracker.column.user'),
                                    'orderable' => false,
                                ],
                                [
                                    'name'      => 'information',
                                    'type'      => 'text',
                                    'label'     => trans('backpack::crud.monitoring_tracker.column.information'),
                                    'orderable' => false,
                                ],
                                [
                                    'name'      => 'status_po',
                                    'type'      => 'text',
                                    'label'     => trans('backpack::crud.monitoring_tracker.column.status'),
                                    'orderable' => true,
                                ],
                            ],
                            'route' => backpack_url('/monitoring/tracker/search?tab=tracker'),
                            'route_export_pdf' => url($this->crud->route . '/export-pdf?tab=tracker'),
                            'title_export_pdf' => "Monitoring_Tracker.pdf",
                            'route_export_excel' => url($this->crud->route . '/export-excel?tab=tracker'),
                            'title_export_excel' => "Monitoring_Tracker.xlsx",
                        ],
                    ],
                    [
                        'name'   => 'tracker_edit',
                        'label'  => trans('backpack::crud.project.tab.title_project_edit'),
                        'view'   => 'crud::components.datatable',
                        'params' => [
                            'crud_custom' => $this->crud,
                            'columns'     => [
                                [
                                    'name'      => 'row_number',
                                    'type'      => 'row_number',
                                    'label'     => trans('backpack::crud.monitoring_tracker.column.no'),
                                    'orderable' => false,
                                ],
                                [
                                    'label'     => trans('backpack::crud.project.column.project_edit.name.label'),
                                    'type'      => 'text',
                                    'name'      => 'name',
                                    'orderable' => true,
                                ],
                                [
                                    'label'     => trans('backpack::crud.project.column.project_edit.user_id.label'),
                                    'type'      => 'text',
                                    'name'      => 'user_id',
                                    'orderable' => false,
                                ],
                                [
                                    'label'     => trans('backpack::crud.project.column.project_edit.date_update.label'),
                                    'type'      => 'text',
                                    'name'      => 'date_update',
                                    'orderable' => true,
                                ],
                                [
                                    'label'     => trans('backpack::crud.project.column.project_edit.history_update.label'),
                                    'type'      => 'text',
                                    'name'      => 'history_update',
                                    'orderable' => false,
                                ],
                            ],
                            'route'              => backpack_url('/monitoring/tracker/search?tab=tracker_edit'),
                            'route_export_pdf'   => url($this->crud->route . '/export-pdf?tab=tracker_edit'),
                            'title_export_pdf'   => 'Daftar-tracker-edit.pdf',
                            'route_export_excel' => url($this->crud->route . '/export-excel?tab=tracker_edit'),
                            'title_export_excel' => 'Daftar-tracker-edit.xlsx',
                        ]
                    ]
                ],
            ],
        ]);

        $this->card->addCard([
            'name' => 'hightlight',
            'line' => 'top',
            'label' => '',
            'parent_view' => 'crud::components.filter-parent',
            'view' => 'crud::components.hightligh-column',
        ]);

        $this->data['crud']               = $this->crud;
        $this->data['title']              = $this->crud->getTitle() ?? trans('backpack::crud.monitoring_tracker.title.monitoring_tracker');
        $this->data['title_modal_create'] = trans('backpack::crud.monitoring_tracker.title.monitoring_tracker');
        $this->data['title_modal_edit']   = trans('backpack::crud.monitoring_tracker.title.monitoring_tracker');
        $this->data['title_modal_delete'] = trans('backpack::crud.monitoring_tracker.title.monitoring_tracker');
        $this->data['cards']              = $this->card;

        $breadcrumbs = [
            trans('backpack::crud.monitoring_tracker.breadcrumb.monitoring') => backpack_url('monitoring'),
            trans('backpack::crud.monitoring_tracker.breadcrumb.tracker') => backpack_url($this->crud->route),
        ];
        $this->data['breadcrumbs'] = $breadcrumbs;

        $yearOptionsProject = Project::selectRaw('YEAR(actual_end_date) as year')
            ->pluck('year')
            ->filter()
            ->toArray();

        $yearOptionsHistory = ProjectHistory::selectRaw('YEAR(created_at) as year')
            ->pluck('year')
            ->filter()
            ->toArray();

        $yearOptions = array_unique(array_merge($yearOptionsProject, $yearOptionsHistory));
        rsort($yearOptions);

        $this->crud->year_options = $yearOptions;

        $list = 'crud::list-blank' ?? $this->crud->getListView();
        return view($list, $this->data);
    }

    protected function setupListOperation()
    {
        $tab = request()->tab;

        CRUD::removeButton('delete');
        $this->crud->removeButtonFromStack('update', 'line');
        $this->crud->removeButtonFromStack('delete', 'line');
        $this->crud->removeButtonFromStack('show', 'line');
        $this->crud->addButton('line_start', 'show', 'view', 'crud::buttons.show', 'end');
        $this->crud->addButton('line_start', 'update', 'view', 'crud::buttons.update', 'end');
        // $this->crud->addButton('line_start', 'delete', 'view', 'crud::buttons.delete', 'end');

        CRUD::addButtonFromView('top', 'filter-year', 'filter-year', 'beginning');
        CRUD::addButtonFromView('top', 'filter-project', 'filter-project', 'beginning');
        CRUD::addButtonFromView('top', 'export-excel', 'export-excel', 'end');
        CRUD::addButtonFromView('top', 'export-pdf', 'export-pdf', 'end');

        if ($tab == 'tracker') {
            CRUD::setModel(Project::class);
            CRUD::disableResponsiveTable();

            $this->crud->query = $this->crud->query
                ->leftJoin('setup_clients', 'setup_clients.id', '=', 'projects.client_id')
                ->select('projects.*', 'setup_clients.name as client_name')
                ->whereIn('projects.status_po', ['TERTUNDA', 'BELUM SELESAI']);

            if (request()->has('filter_category') && request()->filter_category != 'all') {
                $this->crud->addClause('where', 'projects.category', request()->filter_category);
            }

            if (request()->has('filter_client') && request()->filter_client != 'all') {
                $this->crud->addClause('where', 'projects.client_id', request()->filter_client);
            }

            if (request()->has('filter_year') && request()->filter_year != 'all') {
                $this->crud->addClause('whereYear', 'projects.actual_end_date', request()->filter_year);
            }

            CRUD::addColumn([
                'name'      => 'row_number',
                'type'      => 'row_number',
                'label'     => 'No',
                'orderable' => false,
                'wrapper'   => ['element' => 'strong'],
            ])->makeFirstColumn();

            CRUD::addColumn([
                'name'  => 'action',
                'type'  => 'closure',
                'label' => '',
                'escaped' => false,
                'function' => function ($entry, $rowNumber) {
                    $crud = $this->crud;
                    return \View::make('crud::inc.button_stack', ['stack' => 'line_start'])
                        ->with('crud', $crud)
                        ->with('entry', $entry)
                        ->with('row_number', $rowNumber)
                        ->render();
                },
            ]);

            CRUD::column([
                'label'       => trans('backpack::crud.monitoring_tracker.column.no_po_spk'),
                'name'        => 'no_po_spk',
                'type'        => 'text',
            ]);

            CRUD::column([
                'label'       => trans('backpack::crud.monitoring_tracker.column.name'),
                'name'        => 'name',
                'type'        => 'wrap_text',
            ]);

            CRUD::column([
                'label'     => trans('backpack::crud.monitoring_tracker.column.client_id'),
                'type'      => 'select',
                'name'      => 'client_id',
                'entity'    => 'setup_client',
                'attribute' => 'name',
                'model'     => SetupClient::class,
                'limit'     => 50,
            ]);

            CRUD::column([
                'label'  => 'Actual End Date',
                'name'   => 'actual_end_date',
                'type'   => 'date',
                'format' => 'DD/MM/YYYY',
            ]);

            CRUD::column([
                'label'    => 'Total Time',
                'name'     => 'total_time',
                'type'     => 'closure',
                'function' => function ($entry) {
                    if (!$entry->actual_end_date) {
                        return '-';
                    }
                    $endDate = Carbon::parse($entry->actual_end_date);
                    $today   = Carbon::today();
                    $diff    = $today->diffInDays($endDate, false);

                    return $diff;
                },
                'escaped' => false,
            ]);

            CRUD::column([
                'label'    => 'Progress (%)',
                'name'     => 'progress',
                'type'  => 'closure',
                'function' => function ($entry) {
                    $val = number_format($entry->progress, 2, ',', '.');
                    return ($entry->progress == 0) ? "0" : str_replace(',00', '', $val);
                }
            ]);

            CRUD::column([
                'label' => 'Status',
                'name'  => 'status_po',
                'type'  => 'text',
            ]);

            CRUD::column([
                'label' => 'PIC',
                'name'  => 'pic',
                'type'  => 'text',
            ]);

            CRUD::column([
                'label' => 'User',
                'name'  => 'user',
                'type'  => 'text',
            ]);

            CRUD::column([
                'label' => 'Keterangan',
                'name'  => 'information',
                'type'  => 'wrap_text',
            ]);
        } else if ($tab == 'tracker_edit') {
            CRUD::setModel(ProjectHistory::class);
            CRUD::disableResponsiveTable();

            $this->crud->addClause('whereExists', function ($query) {
                $query->select(DB::raw(1))
                    ->from('projects')
                    ->whereRaw('projects.id = project_history.project_id')
                    ->whereIn('projects.status_po', ['TERTUNDA', 'BELUM SELESAI']);
            });

            if (request()->has('filter_category') && request()->filter_category != 'all') {
                $filter_category = request()->filter_category;
                $this->crud->addClause('whereExists', function ($query) use ($filter_category) {
                    $query->select(DB::raw(1))
                        ->from('projects')
                        ->whereRaw('projects.id = project_history.project_id')
                        ->where('projects.category', $filter_category);
                });
            }

            if (request()->has('filter_client') && request()->filter_client != 'all') {
                $filter_client = request()->filter_client;
                $this->crud->addClause('whereExists', function ($query) use ($filter_client) {
                    $query->select(DB::raw(1))
                        ->from('projects')
                        ->whereRaw('projects.id = project_history.project_id')
                        ->where('projects.client_id', $filter_client);
                });
            }

            if (request()->has('filter_year') && request()->filter_year != 'all') {
                $this->crud->addClause('whereYear', 'project_history.created_at', request()->filter_year);
            }

            CRUD::addColumn([
                'name'      => 'row_number',
                'type'      => 'row_number',
                'label'     => 'No',
                'orderable' => false,
                'wrapper'   => ['element' => 'strong'],
            ])->makeFirstColumn();

            CRUD::column([
                'label'       => trans('backpack::crud.project.column.project_edit.name.label'),
                'name'        => 'name',
                'type'        => 'wrap_text',
                'searchLogic' => function ($query, $column, $searchTerm) {
                    $query->orWhere('name', 'like', '%' . $searchTerm . '%');
                }
            ]);

            CRUD::column([
                'label'     => trans('backpack::crud.project.column.project_edit.user_id.label'),
                'type'      => 'select',
                'name'      => 'user_id',
                'entity'    => 'user',
                'attribute' => 'name',
                'model'     => "App\Models\User",
            ]);

            CRUD::column([
                'label'  => trans('backpack::crud.project.column.project_edit.date_update.label'),
                'name'   => 'date_update',
                'type'   => 'date',
                'format' => 'DD MMM YYYY HH:mm'
            ]);

            CRUD::column([
                'label' => trans('backpack::crud.project.column.project_edit.history_update.label'),
                'name'  => 'history_update',
                'type'  => 'wrap_text',
            ]);
        }
    }

    public function create()
    {
        $this->crud->hasAccessOrFail('create');

        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.add') . ' ' . $this->crud->entity_name;

        return response()->json([
            'html' => view('crud::create', $this->data)->render()
        ]);
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation([
            'name'            => 'required|max:100',
            'client_id'       => 'required|exists:setup_clients,id',
            'actual_end_date' => 'required|date',
            'progress'        => 'nullable|numeric|min:0|max:100',
            'pic'             => 'nullable|max:100',
            'user'            => 'nullable|max:150',
            'status_po'       => 'required',
            'information'     => 'nullable',
            // Required for projects table
            'price_total_exclude_ppn' => 'required|numeric',
            'tax_ppn' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'duration' => 'required|integer',
        ]);

        CRUD::addField([
            'name'    => 'name',
            'label'   => trans('backpack::crud.monitoring_tracker.column.name'),
            'type'    => 'text',
            'wrapper' => ['class' => 'form-group col-md-12'],
        ]);

        CRUD::addField([
            'name' => 'no_po_spk',
            'label' => trans('backpack::crud.monitoring_tracker.column.no_po_spk'),
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name'        => 'client_id',
            'label'       => trans('backpack::crud.monitoring_tracker.column.client_id'),
            'type'        => 'select2_ajax_custom',
            'entity'      => 'setup_client',
            'model'       => SetupClient::class,
            'attribute'   => 'name',
            'data_source' => backpack_url('monitoring/tracker/select2-client'),
            'allows_null' => false,
            'wrapper'     => ['class' => 'form-group col-md-6'],
            'attributes'  => ['placeholder' => trans('backpack::crud.monitoring_tracker.column.search_client')],
        ]);

        CRUD::addField([
            'name'                => 'actual_end_date',
            'label'               => 'Actual End Date',
            'type'                => 'date_picker',
            'date_picker_options' => [
                'language' => App::getLocale(),
            ],
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name'       => 'progress',
            'label'      => trans('backpack::crud.monitoring_tracker.column.progress'),
            'type'       => 'number',
            'attributes' => [
                'min'         => 0,
                'max'         => 100,
                'step'        => 0.01,
                'placeholder' => '0 - 100',
            ],
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name'    => 'status_po',
            'label'   => trans('backpack::crud.monitoring_tracker.column.status'),
            'type'    => 'select2_array',
            'options' => [
                'TERTUNDA'      => 'TERTUNDA',
                'BELUM SELESAI' => 'BELUM SELESAI',
                'CLOSE'         => 'CLOSE',
                'RETENSI'       => 'RETENSI',
                'UNPAID'        => 'UNPAID',
                'BELUM ADA PO'  => 'BELUM ADA PO',
            ],
            'allows_null' => true,
            'wrapper'     => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name'    => 'pic',
            'label'   => trans('backpack::crud.monitoring_tracker.column.pic'),
            'type'    => 'text',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name'    => 'user',
            'label'   => trans('backpack::crud.monitoring_tracker.column.user'),
            'type'    => 'text',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name'    => 'information',
            'label'   => trans('backpack::crud.monitoring_tracker.column.information_tracker'),
            'type'    => 'textarea',
            'wrapper' => ['class' => 'form-group col-md-12'],
        ]);

        // Hidden / Essential fields for Project model
        CRUD::addField([
            'name' => 'price_total_exclude_ppn',
            'type' => 'hidden',
            'default' => 0,
        ]);
        CRUD::addField([
            'name' => 'tax_ppn',
            'type' => 'hidden',
            'default' => 0,
        ]);
        CRUD::addField([
            'name' => 'start_date',
            'type' => 'hidden',
            'default' => date('Y-m-d'),
        ]);
        CRUD::addField([
            'name' => 'end_date',
            'type' => 'hidden',
            'default' => date('Y-m-d'),
        ]);
        CRUD::addField([
            'name' => 'duration',
            'type' => 'hidden',
            'default' => 0,
        ]);
    }

    public function edit($id)
    {
        $this->crud->hasAccessOrFail('update');
        $id = $this->crud->getCurrentEntryId() ?? $id;
        $this->crud->registerFieldEvents();
        $entry = Project::find($id);

        $this->data['entry'] = $entry;
        $this->crud->setOperationSetting('fields', $this->crud->getUpdateFields());
        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.edit') . ' ' . $this->crud->entity_name;
        $this->data['id'] = $id;

        return response()->json([
            'html' => view($this->crud->getEditView(), $this->data)->render()
        ]);
    }

    protected function setupUpdateOperation()
    {
        CRUD::setValidation([
            'progress'    => 'nullable|numeric|min:0|max:100',
            'pic'         => 'nullable|max:100',
            'user'        => 'nullable|max:150',
            'information' => 'nullable',
        ]);

        CRUD::addField([
            'name'       => 'progress',
            'label'      => 'Progress (%)',
            'type'       => 'number',
            'attributes' => [
                'min'         => 0,
                'max'         => 100,
                'step'        => 0.01,
                'placeholder' => '0 - 100',
            ],
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name'    => 'pic',
            'label'   => trans('backpack::crud.monitoring_tracker.column.pic'),
            'type'    => 'text',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name'    => 'user',
            'label'   => trans('backpack::crud.monitoring_tracker.column.user'),
            'type'    => 'text',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name'    => 'information',
            'label'   => trans('backpack::crud.monitoring_tracker.column.information'),
            'type'    => 'textarea',
            'wrapper' => ['class' => 'form-group col-md-12'],
        ]);
    }

    protected function setupShowOperation()
    {
        $this->setupCreateOperation();

        // Sync fields and columns for the custom show.blade.php
        CRUD::removeAllFields();
        CRUD::removeAllColumns();

        $col6 = ['wrapper' => ['class' => 'form-group col-md-6']];
        $col12 = ['wrapper' => ['class' => 'form-group col-md-12']];

        // 1. no_po_spk
        CRUD::field(['label' => trans('backpack::crud.monitoring_tracker.column.no_po_spk'), 'name' => 'no_po_spk'])->wrapper($col6['wrapper']);
        CRUD::column(['label' => '', 'name' => 'no_po_spk', 'type' => 'wrap_text']);

        // 2. name
        CRUD::field(['label' => trans('backpack::crud.monitoring_tracker.column.name'), 'name' => 'name'])->wrapper($col12['wrapper']);
        CRUD::column(['label' => '', 'name' => 'name', 'type' => 'wrap_text']);

        // 3. client_id
        CRUD::field(['label' => trans('backpack::crud.monitoring_tracker.column.client_id'), 'name' => 'client_id'])->wrapper($col6['wrapper']);
        $this->crud->addColumn([
            'label' => '',
            'type'      => 'select',
            'name'      => 'client_id',
            'entity'    => 'setup_client',
            'attribute' => 'name',
            'model'     => "App\Models\SetupClient",
        ]);

        // 4. actual_end_date
        CRUD::field(['label' => trans('backpack::crud.monitoring_tracker.column.actual_end_date'), 'name' => 'actual_end_date'])->wrapper($col6['wrapper']);
        CRUD::column(['label'  => '', 'name' => 'actual_end_date', 'type'  => 'date', 'format' => 'DD/MM/YYYY']);

        // 5. total_time
        CRUD::field(['label' => trans('backpack::crud.monitoring_tracker.column.total_time'), 'name' => 'total_time'])->wrapper($col6['wrapper']);
        CRUD::column([
            'label'    => '',
            'name'     => 'total_time',
            'type'     => 'closure',
            'function' => function ($entry) {
                if (!$entry->actual_end_date) {
                    return '-';
                }
                $endDate = \Carbon\Carbon::parse($entry->actual_end_date);
                $today   = \Carbon\Carbon::today();
                $diff    = $today->diffInDays($endDate, false);

                return $diff;
            },
        ]);

        // 6. progress
        CRUD::field(['label' => trans('backpack::crud.monitoring_tracker.column.progress'), 'name' => 'progress'])->wrapper($col6['wrapper']);
        CRUD::column([
            'label'  => '',
            'name' => 'progress',
            'type'  => 'closure',
            'function' => function ($entry) {
                $val = number_format($entry->progress, 2, ',', '.');
                return ($entry->progress == 0) ? "0" : str_replace(',00', '', $val);
            }
        ]);

        // 7. status_po
        CRUD::field(['label' => trans('backpack::crud.monitoring_tracker.column.status'), 'name' => 'status_po'])->wrapper($col6['wrapper']);
        CRUD::column(['label'  => '', 'name' => 'status_po', 'type'  => 'text']);

        // 8. pic
        CRUD::field(['label' => trans('backpack::crud.monitoring_tracker.column.pic'), 'name' => 'pic'])->wrapper($col6['wrapper']);
        CRUD::column(['label'  => '', 'name' => 'pic', 'type'  => 'text']);

        // 9. user
        CRUD::field(['label' => trans('backpack::crud.monitoring_tracker.column.user'), 'name' => 'user'])->wrapper($col6['wrapper']);
        CRUD::column(['label'  => '', 'name' => 'user', 'type'  => 'text']);

        // 10. information
        CRUD::field(['label' => trans('backpack::crud.monitoring_tracker.column.information'), 'name' => 'information'])->wrapper($col12['wrapper']);
        CRUD::column(['label'  => '', 'name' => 'information', 'type'  => 'wrap_text']);

        // --- Additional Project Details ---

        // po_date
        CRUD::field(['label' => trans('backpack::crud.project.field.po_date.label'), 'name' => 'po_date'])->wrapper($col6['wrapper']);
        CRUD::column(['label' => '', 'name' => 'po_date', 'type' => 'date', 'format' => 'D MMM Y']);

        // received_po_date
        CRUD::field(['label' => trans('backpack::crud.project.field.received_po_date.label'), 'name' => 'received_po_date'])->wrapper($col6['wrapper']);
        CRUD::column(['label' => '', 'name' => 'received_po_date', 'type' => 'date', 'format' => 'D MMM Y']);

        // price_total_exclude_ppn
        CRUD::field(['label' => trans('backpack::crud.project.field.price_total_exclude_ppn.label'), 'name' => 'price_total_exclude_ppn'])->wrapper($col6['wrapper']);
        CRUD::column([
            'label'  => '',
            'name' => 'price_total_exclude_ppn',
            'type'  => 'number',
            'prefix' => "Rp.",
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
        ]);

        // tax_ppn
        CRUD::field(['label' => trans('backpack::crud.project.field.tax_ppn.label'), 'name' => 'tax_ppn'])->wrapper($col6['wrapper']);
        CRUD::column([
            'label'  => '',
            'name' => 'tax_ppn',
            'type'  => 'closure',
            'function' => function ($entry) {
                $val = number_format($entry->tax_ppn, 2, ',', '.');
                return str_replace(',00', '', $val) . '%';
            }
        ]);

        // price_ppn
        CRUD::field(['label' => trans('backpack::crud.project.field.price_ppn.label'), 'name' => 'price_ppn'])->wrapper($col6['wrapper']);
        CRUD::column([
            'label'  => '',
            'name' => 'price_ppn',
            'type'  => 'number',
            'prefix' => "Rp.",
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
        ]);

        // price_total_include_ppn
        CRUD::field(['label' => trans('backpack::crud.project.field.price_total_include_ppn.label'), 'name' => 'price_total_include_ppn'])->wrapper($col6['wrapper']);
        CRUD::column([
            'label'  => '',
            'name' => 'price_total_include_ppn',
            'type'  => 'number',
            'prefix' => "Rp.",
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
        ]);

        // transfer_value (Calculated)
        CRUD::field(['label' => trans('backpack::crud.project.column.project.transfer_value.label'), 'name' => 'transfer_value'])->wrapper($col6['wrapper']);
        CRUD::column([
            'label' => '',
            'name' => 'transfer_value',
            'type'  => 'closure',
            'function' => function ($entry) {
                $price_pph = $entry->price_pph ?? 0;
                $price_fine = $entry->fine_price ?? 0;
                if ($entry->company_classification == 'WAPU') {
                    $transfer_value = $entry->price_total_exclude_ppn - $price_pph - $price_fine;
                } else if ($entry->company_classification == 'NON WAPU') {
                    $transfer_value = $entry->price_total_include_ppn - $price_pph - $price_fine;
                } else {
                    return '-';
                }
                return 'Rp.' . number_format($transfer_value, 2, ',', '.');
            },
        ]);

        // tax_pph
        CRUD::field(['label' => trans('backpack::crud.project.field.tax_pph.label'), 'name' => 'tax_pph'])->wrapper($col6['wrapper']);
        CRUD::column([
            'label'  => '',
            'name' => 'tax_pph',
            'type'  => 'closure',
            'function' => function ($entry) {
                $val = number_format($entry->tax_pph, 2, ',', '.');
                return str_replace(',00', '', $val) . '%';
            }
        ]);

        // price_pph
        CRUD::field(['label' => trans('backpack::crud.project.field.price_pph.label'), 'name' => 'price_pph'])->wrapper($col6['wrapper']);
        CRUD::column([
            'label'  => '',
            'name' => 'price_pph',
            'type'  => 'number',
            'prefix' => "Rp.",
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
        ]);

        // fine_price
        CRUD::field(['label' => trans('backpack::crud.project.field.fine_price.label'), 'name' => 'fine_price'])->wrapper($col6['wrapper']);
        CRUD::column([
            'label'  => '',
            'name' => 'fine_price',
            'type'  => 'number',
            'prefix' => "Rp.",
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
        ]);

        // start_date,end_date
        CRUD::field(['label' => trans('backpack::crud.client_po.column.startdate_and_enddate'), 'name' => 'start_date_range'])->wrapper($col6['wrapper']);
        CRUD::column([
            'label'  => '',
            'name' => 'start_date,end_date',
            'type'  => 'date_range_custom'
        ]);

        // duration
        CRUD::field(['label' => trans('backpack::crud.monitoring_tracker.column.duration'), 'name' => 'duration'])->wrapper($col6['wrapper']);
        CRUD::column(['label'  => '', 'name' => 'duration', 'type'  => 'text']);

        // actual_start_date
        CRUD::field(['label' => trans('backpack::crud.project.field.actual_start_date.label'), 'name' => 'actual_start_date'])->wrapper($col6['wrapper']);
        CRUD::column(['label'  => '', 'name' => 'actual_start_date', 'type'  => 'date', 'format' => 'D MMM Y']);

        // category
        CRUD::field(['label' => trans('backpack::crud.project.column.project.category.label'), 'name' => 'category'])->wrapper($col6['wrapper']);
        CRUD::column(['label'  => '', 'name' => 'category', 'type'  => 'text']);

        // company_classification
        CRUD::field(['label' => trans('backpack::crud.project.column.project.company_classification.label'), 'name' => 'company_classification'])->wrapper($col6['wrapper']);
        CRUD::column(['label'  => '', 'name' => 'company_classification', 'type'  => 'text']);

        // document_path
        CRUD::field(['label' => 'Dokumen Proyek', 'name' => 'document_path'])->wrapper($col6['wrapper']);
        CRUD::column([
            'label'  => '',
            'name' => 'document_path',
            'type'  => 'text',
            'wrapper'   => [
                'element' => 'a',
                'href' => function ($crud, $column, $entry, $related_key) {
                    if ($entry->document_path != '') {
                        return url('storage/document_proyek/' . $entry->document_path);
                    }
                    return "javascript:void(0)";
                },
                'target' => '_blank',
            ],
        ]);
    }

    public function store()
    {
        $this->crud->hasAccessOrFail('create');
        $request = $this->crud->validateRequest();

        DB::beginTransaction();
        try {
            $item = $this->crud->create($this->crud->getStrippedSaveRequest($request));
            $this->data['entry'] = $this->crud->entry = $item;

            \Alert::success(trans('backpack::crud.insert_success'))->flash();
            $this->crud->setSaveAction();
            DB::commit();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'data'    => $item,
                    'events'  => [
                        'crudTable-tracker_create_success' => $item,
                    ],
                ]);
            }
            return $this->crud->performSaveAction($item->getKey());
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'success' => false,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    public function update()
    {
        $this->crud->hasAccessOrFail('update');
        $request = $this->crud->validateRequest();

        DB::beginTransaction();
        try {
            $old = Project::find($request->get($this->crud->model->getKeyName()));

            $item = $this->crud->update(
                $request->get($this->crud->model->getKeyName()),
                $this->crud->getStrippedSaveRequest($request)
            );

            // Record history
            $project_history = new ProjectHistory;
            $project_history->project_id = $item->id;
            $project_history->name = $item->name;
            $project_history->user_id = backpack_auth()->user()->id;
            $project_history->date_update = Carbon::now();
            $project_history->history_update = trans('backpack::crud.monitoring_tracker.history_update_text');
            $project_history->save();

            $this->data['entry'] = $this->crud->entry = $item;

            \Alert::success(trans('backpack::crud.update_success'))->flash();
            $this->crud->setSaveAction();
            DB::commit();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'data'    => $item,
                    'events'  => [
                        'crudTable-tracker_updated_success' => $item,
                        'crudTable-tracker_edit_updated_success' => $item,
                    ],
                ]);
            }
            return $this->crud->performSaveAction($item->getKey());
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'success' => false,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    public function destroy($id)
    {
        $this->crud->hasAccessOrFail('delete');
        $id = $this->crud->getCurrentEntryId() ?? $id;

        DB::beginTransaction();
        try {
            $item = Project::find($id);
            $item->delete();
            DB::commit();

            return response()->json([
                'success' => [trans('backpack::crud.delete_confirmation_message')],
                'events'  => [
                    'crudTable-tracker_updated_success' => 1,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'type'    => 'errors',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        $this->crud->hasAccessOrFail('show');
        $id = $this->crud->getCurrentEntryId() ?? $id;
        $this->data['entry'] = $this->crud->getEntryWithLocale($id);
        $this->data['entry_value'] = $this->crud->getRowViews($this->data['entry']);
        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.preview') . ' ' . $this->crud->entity_name;

        return response()->json([
            'html' => view($this->crud->getShowView(), $this->data)->render()
        ]);
    }

    public function select2Client()
    {
        $q = request()->q ?? '';
        $clients = SetupClient::where('name', 'like', "%{$q}%")
            ->select('id', 'name')
            ->paginate(20);

        $results = [];
        foreach ($clients as $client) {
            $results[] = [
                'id'   => $client->id,
                'text' => $client->name,
            ];
        }
        return response()->json(['results' => $results]);
    }

    public function exportPdf()
    {
        $this->setupListOperation();
        CRUD::removeColumn('action');
        $columns = $this->crud->columns();
        $items =  $this->crud->getEntries();

        $row_number = 0;
        $all_items = [];
        foreach ($items as $item) {
            $row_items = [];
            $row_number++;
            foreach ($columns as $column) {
                $item_value = ($column['name'] == 'row_number') ? $row_number : $this->crud->getCellView($column, $item, $row_number);
                $item_value = str_replace('<span>', '', $item_value);
                $item_value = str_replace('</span>', '', $item_value);
                $item_value = str_replace("\n", '', $item_value);
                $item_value = \App\Http\Helpers\CustomHelper::clean_html($item_value);
                $row_items[] = trim($item_value);
            }
            $all_items[] = $row_items;
        }

        $title = 'Monitoring Tracker';
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.table-pdf', [
            'columns' => $columns,
            'items' => $all_items,
            'title' => $title
        ])->setPaper('A4', 'landscape');

        $fileName = 'monitoring_tracker_' . now()->format('Ymd_His') . '.pdf';
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function exportExcel()
    {
        $this->setupListOperation();
        CRUD::removeColumn('action');
        $columns = $this->crud->columns();
        $items =  $this->crud->getEntries();

        $row_number = 0;
        $all_items = [];
        foreach ($items as $item) {
            $row_items = [];
            $row_number++;
            foreach ($columns as $column) {
                $item_value = ($column['name'] == 'row_number') ? $row_number : $this->crud->getCellView($column, $item, $row_number);
                $item_value = str_replace('<span>', '', $item_value);
                $item_value = str_replace('</span>', '', $item_value);
                $item_value = str_replace("\n", '', $item_value);
                $item_value = \App\Http\Helpers\CustomHelper::clean_html($item_value);
                $row_items[] = trim($item_value);
            }
            $all_items[] = $row_items;
        }

        $name = 'Monitoring Tracker.xlsx';
        return response()->streamDownload(function () use ($columns, $all_items) {
            echo \Maatwebsite\Excel\Facades\Excel::raw(new \App\Http\Exports\ExportExcel(
                $columns,
                $all_items
            ), \Maatwebsite\Excel\Excel::XLSX);
        }, $name, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $name . '"',
        ]);
    }
}
