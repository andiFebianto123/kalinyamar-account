<?php
namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Spk;
use App\Models\User;
use App\Models\Project;
use App\Models\SetupPpn;
use App\Models\SetupClient;
use App\Models\PurchaseOrder;
use App\Models\ProjectHistory;
use App\Models\CategoryProject;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Exports\ExportExcel;
use App\Http\Helpers\CustomHelper;
use App\Models\SetupStatusProject;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class ProjectListCrudController extends CrudController {
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        $this->crud->denyAllAccess(['create', 'update', 'delete', 'list', 'show']);
        CRUD::setModel(Project::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/monitoring/project-list');
        CRUD::setEntityNameStrings(trans('backpack::crud.menu.project_list'), trans('backpack::crud.menu.project_list'));
        CRUD::allowAccess('print');
        $user = backpack_user();
        $permissions = $user->getAllPermissions();
        if($permissions->whereIn('name', [
            'AKSES SEMUA VIEW PROJECT',
            'AKSES SEMUA MENU PROJECT',
            'EDIT KOLOM PROGRES DAN KETERANGAN DAFTAR PROJECT'
        ])->count() > 0)
        {
            $this->crud->allowAccess(['list', 'show']);
        }

        if($permissions->whereIn('name',[
            'AKSES SEMUA MENU PROJECT',
        ])->count() > 0){
            $this->crud->allowAccess(['create', 'update', 'delete']);
        }

        if($permissions->whereIn('name', [
            'EDIT KOLOM PROGRES DAN KETERANGAN DAFTAR PROJECT'
        ])){
            $this->crud->allowAccess(['update']);
        }
    }

    function index(){
        $this->crud->hasAccessOrFail('list');

        $this->card->addCard([
            'name' => 'voucher',
            'line' => 'top',
            'view' => 'crud::components.card-tab',
            'params' => [
                'tabs' => [
                    [
                        'name' => 'project',
                        'label' => trans('backpack::crud.project.tab.title_project'),
                        // 'class' => '',
                        'active' => true,
                        'view' => 'crud::components.datatable',
                        'params' => [
                            'crud_custom' => $this->crud,
                            'columns' => [
                                [
                                    'name'      => 'row_number',
                                    'type'      => 'row_number',
                                    'label'     => 'No',
                                    'orderable' => false,
                                ],
                                [
                                    'name' => 'no_po_spk',
                                    'type' => 'text',
                                    'label' => trans('backpack::crud.project.column.project.no_po_spk.label'),
                                    'orderable' => true,
                                ],
                                [
                                    'name' => 'name',
                                    'type' => 'text',
                                    'label' => trans('backpack::crud.project.column.project.name.label'),
                                    'orderable' => true,
                                ],
                                [
                                    'name' => 'po_date',
                                    'type' => 'text',
                                    'label' => trans('backpack::crud.project.column.project.po_date.label'),
                                    'orderable' => true,
                                ],
                                [
                                    'name' => 'price_total_exclude_ppn',
                                    'type' => 'text',
                                    'label' => trans('backpack::crud.project.column.project.price_total_exclude_ppn.label'),
                                    'orderable' => true,
                                ],
                                [
                                    'name' => 'price_ppn',
                                    'type' => 'text',
                                    'label' => trans('backpack::crud.project.column.project.price_ppn.label'),
                                    'orderable' => true,
                                ],
                                [
                                    'name' => 'tax_ppn',
                                    'type' => 'text',
                                    'label' => trans('backpack::crud.project.column.project.tax_ppn.label'),
                                    'orderable' => true,
                                ],
                                [
                                    'name' => 'price_total_include_ppn',
                                    'type' => 'text',
                                    'label' => trans('backpack::crud.project.column.project.price_total_include_ppn.label'),
                                    'orderable' => true,
                                ],
                                [
                                    'name' => 'client_id',
                                    'type' => 'text',
                                    'label' => trans('backpack::crud.project.column.project.client_id.label'),
                                    'orderable' => false,
                                ],
                                [
                                    'name' => 'received_po_date',
                                    'type' => 'text',
                                    'label' => trans('backpack::crud.project.column.project.received_po_date.label'),
                                    'orderable' => true,
                                ],
                                [
                                    'name' => 'startdate_and_enddate',
                                    'type' => 'text',
                                    'label' => trans('backpack::crud.project.column.project.startdate_and_enddate.label'),
                                    'orderable' => false,
                                ],
                                [
                                    'name' => 'duration',
                                    'type' => 'text',
                                    'label' => trans('backpack::crud.project.column.project.duration.label'),
                                    'orderable' => true,
                                ],
                                [
                                    'name' => 'actual_start_date',
                                    'type' => 'text',
                                    'label' => trans('backpack::crud.project.column.project.actual_start_date.label'),
                                    'orderable' => true,
                                ],
                                [
                                    'name' => 'actual_end_date',
                                    'type' => 'text',
                                    'label' => trans('backpack::crud.project.column.project.actual_end_date.label'),
                                    'orderable' => true,
                                ],
                                [
                                    'name' => 'status_po',
                                    'type' => 'text',
                                    'label' => trans('backpack::crud.project.column.project.status_po.label'),
                                    'orderable' => true,
                                ],
                                [
                                    'name' => 'progress',
                                    'type' => 'text',
                                    'label' => trans('backpack::crud.project.column.project.progress.label'),
                                    'orderable' => true,
                                ],
                                [
                                    'name' => 'pic',
                                    'type' => 'text',
                                    'label' => trans('backpack::crud.project.column.project.pic.label'),
                                    'orderable' => false,
                                ],
                                [
                                    'name' => 'user',
                                    'type' => 'text',
                                    'label' => trans('backpack::crud.project.column.project.user.label'),
                                    'orderable' => false,
                                ],
                                [
                                    'name' => 'category',
                                    'type' => 'text',
                                    'label' => trans('backpack::crud.project.column.project.category.label'),
                                    'orderable' => true,
                                ],
                                [
                                    'name' => 'information',
                                    'type' => 'text',
                                    'label' => trans('backpack::crud.project.column.project.information.label'),
                                    'orderable' => false,
                                ],
                                [
                                    'name' => 'document_path',
                                    'type' => 'text',
                                    'label' => 'Dokumen Proyek',
                                    'orderable' => false,
                                ],
                                [
                                    'name' => 'action',
                                    'type' => 'action',
                                    'label' =>  trans('backpack::crud.actions'),
                                ]
                            ],
                            'route' => backpack_url('/monitoring/project-list/search?tab=project'),
                            'route_export_pdf' => url($this->crud->route.'/export-pdf?tab=project'),
                            'title_export_pdf' => 'Daftar-project.pdf',
                            'route_export_excel' => url($this->crud->route.'/export-excel?tab=project'),
                            'title_export_excel' => 'Daftar-project.xlsx',
                        ],
                    ],
                    [
                        'name' => 'project_edit',
                        'label' => trans('backpack::crud.project.tab.title_project_edit'),
                        'view' => 'crud::components.datatable',
                        'params' => [
                            'crud_custom' => $this->crud,
                            'columns' => [
                                [
                                    'name'      => 'row_number',
                                    'type'      => 'row_number',
                                    'label'     => 'No',
                                    'orderable' => false,
                                ],
                                [
                                    'label' => trans('backpack::crud.project.column.project_edit.name.label'),
                                    'type'      => 'text',
                                    'name'      => 'name',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.project.column.project_edit.user_id.label'),
                                    'type'      => 'text',
                                    'name'      => 'user_id',
                                    'orderable' => false,
                                ],
                                [
                                    'label' => trans('backpack::crud.project.column.project_edit.date_update.label'),
                                    'type'      => 'text',
                                    'name'      => 'date_update',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.project.column.project_edit.history_update.label'),
                                    'type'      => 'text',
                                    'name'      => 'history_update',
                                    'orderable' => false,
                                ],
                            ],
                            'route' => backpack_url('/monitoring/project-list/search?tab=project_edit'),
                            'route_export_pdf' => url($this->crud->route.'/export-pdf?tab=project_edit'),
                            'title_export_pdf' => 'Daftar-project-edit.pdf',
                            'route_export_excel' => url($this->crud->route.'/export-excel?tab=project_edit'),
                            'title_export_excel' => 'Daftar-project-edit.xlsx',
                        ]
                    ]
                ]
            ]
        ]);

        $this->card->addCard([
            'name' => 'hightlight',
            'line' => 'top',
            'label' => '',
            'parent_view' => 'crud::components.filter-parent',
            'view' => 'crud::components.hightligh-column',
        ]);

        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? mb_ucfirst($this->crud->entity_name_plural);
        $this->data['title_modal_create'] = trans('backpack::crud.project.title_modal_create');
        $this->data['title_modal_edit'] = trans('backpack::crud.project.title_modal_edit');
        $this->data['title_modal_delete'] = trans('backpack::crud.project.title_modal_delete');
        $this->data['cards'] = $this->card;

        $breadcrumbs = [
            trans('backpack::crud.menu.monitoring_project') => backpack_url('monitoring'),
            trans('backpack::crud.menu.project_list') => backpack_url($this->crud->route)
        ];
        $this->data['breadcrumbs'] = $breadcrumbs;
        $list = "crud::list-blank" ?? $this->crud->getListView();
        return view($list, $this->data);
    }

    public function create()
    {
        $this->crud->hasAccessOrFail('create');

        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.add').' '.$this->crud->entity_name;

        return response()->json([
            'html' => view('crud::create', $this->data)->render()
        ]);
    }

    protected function setupListOperation()
    {
        $type = request()->tab;
        CRUD::addButtonFromView('top', 'filter-project', 'filter-project', 'beginning');
        CRUD::addButtonFromView('top', 'export-excel', 'export-excel', 'beginning');
        CRUD::addButtonFromView('top', 'export-pdf', 'export-pdf', 'beginning');

        if($type == 'project'){
            CRUD::setModel(Project::class);
            CRUD::disableResponsiveTable();

            if(request()->has('filter_category')){
                if(request()->filter_category != 'all'){
                    $this->crud->addClause('where', 'category', request()->filter_category);
                }
            }

            if(request()->has('filter_client')){
                if(request()->filter_client != 'all'){
                    $this->crud->query = $this->crud->query
                    ->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                        ->from('setup_clients')
                        ->whereRaw('setup_clients.id = projects.client_id')
                        ->where('setup_clients.id', request()->filter_client);
                    });
                }
            }

            CRUD::addColumn([
                'name'      => 'row_number',
                'type'      => 'row_number',
                'label'     => 'No',
                'orderable' => false,
                'wrapper' => [
                    'element' => 'strong',
                ]
            ])->makeFirstColumn();
            CRUD::column(
                [
                                    'label' => trans('backpack::crud.project.column.project.no_po_spk.label'),
                    'name' => 'no_po_spk',
                    'type'  => 'text'
                ],
            );
            CRUD::column(
                [
                                    'label' => trans('backpack::crud.project.column.project.name.label'),
                    'name' => 'name',
                    'type'  => 'text'
                ],
            );
            CRUD::column([
                                    'label' => trans('backpack::crud.project.column.project.po_date.label'),
                'name' => 'po_date',
                'type'  => 'date',
                'format' => 'D MMM Y'
            ]);
            CRUD::column([
                                    'label' => trans('backpack::crud.project.column.project.price_total_exclude_ppn.label'),
                'name' => 'price_total_exclude_ppn',
                'type'  => 'number',
                'prefix' => "Rp.",
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
            ]);
            CRUD::column([
                                    'label' => trans('backpack::crud.project.column.project.price_ppn.label'),
                'name' => 'price_ppn',
                'type'  => 'number',
                'prefix' => "Rp.",
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
            ]);

            CRUD::column([
                                    'label' => trans('backpack::crud.project.column.project.tax_ppn.label'),
                'name' => 'tax_ppn',
                'type'  => 'number',
                'suffix' => '%',
            ]);
            CRUD::column([
                                    'label' => trans('backpack::crud.project.column.project.price_total_include_ppn.label'),
                'name' => 'price_total_include_ppn',
                'type'  => 'number',
                'prefix' => "Rp.",
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
            ]);
            CRUD::column([
                // 1-n relationship
                'label' => trans('backpack::crud.client_po.column.client_id'),
                'type'      => 'select',
                'name'      => 'client_id', // the column that contains the ID of that connected entity;
                'entity'    => 'setup_client', // the method that defines the relationship in your Model
                'attribute' => 'name', // foreign key attribute that is shown to user
                'model'     => "App\Models\SetupClient", // foreign key model
                // OPTIONAL
                // 'limit' => 32, // Limit the number of characters shown
            ]);
            CRUD::column([
                                    'label' => trans('backpack::crud.project.column.project.received_po_date.label'),
                'name' => 'received_po_date',
                'type'  => 'date',
                'format' => 'D MMM Y'
            ]);
            CRUD::column(
                [
                    'label'  => trans('backpack::crud.client_po.column.startdate_and_enddate'),
                    'name' => 'start_date,end_date',
                    'type'  => 'date_range_custom'
                ],
            );
            CRUD::column(
                [
                                    'label' => trans('backpack::crud.project.column.project.duration.label'),
                    'name' => 'duration',
                    'type'  => 'text'
                ],
            );
            CRUD::column([
                                    'label' => trans('backpack::crud.project.column.project.actual_start_date.label'),
                'name' => 'actual_start_date',
                'type'  => 'date',
                'format' => 'D MMM Y'
            ]);
            CRUD::column([
                                    'label' => trans('backpack::crud.project.column.project.actual_end_date.label'),
                'name' => 'actual_end_date',
                'type'  => 'date',
                'format' => 'D MMM Y'
            ]);
            CRUD::column(
                [
                                    'label' => trans('backpack::crud.project.column.project.status_po.label'),
                    'name' => 'status_po',
                    'type'  => 'text'
                ],
            );
            CRUD::column(
                [
                                    'label' => trans('backpack::crud.project.column.project.progress.label'),
                    'name' => 'progress',
                    'type'  => 'text'
                ],
            );
            CRUD::column(
                [
                                    'label' => trans('backpack::crud.project.column.project.pic.label'),
                    'name' => 'pic',
                    'type'  => 'text'
                ],
            );
            CRUD::column(
                [
                                    'label' => trans('backpack::crud.project.column.project.user.label'),
                    'name' => 'user',
                    'type'  => 'text'
                ],
            );
            CRUD::column(
                [
                                    'label' => trans('backpack::crud.project.column.project.category.label'),
                    'name' => 'category',
                    'type'  => 'text'
                ],
            );
            CRUD::column(
                [
                                    'label' => trans('backpack::crud.project.column.project.information.label'),
                    'name' => 'information',
                    'type'  => 'text'
                ],
            );

            CRUD::column([
                'label'  => 'Dokumen Proyek',
                'name' => 'document_path',
                'type'  => 'text',
                    'wrapper'   => [
                    'element' => 'a', // the element will default to "a" so you can skip it here
                    'href' => function ($crud, $column, $entry, $related_key) {
                        if($entry->document_path != ''){
                            return url('storage/'.$entry->document_path);
                        }
                        return "javascript:void(0)";
                    },
                    'target' => '_blank',
                    // 'class' => 'some-class',
                ],
            ]);

        }else if($type == 'project_edit'){
            CRUD::setModel(ProjectHistory::class);
            CRUD::disableResponsiveTable();

            if(request()->has('filter_category')){
                if(request()->filter_category != 'all'){
                    $filter_category = request()->filter_category;
                    $this->crud->addClause('whereExists', function ($query) use($filter_category){
                        $query->select(DB::raw(1))
                            ->from('projects')
                            ->whereRaw('projects.id = project_history.project_id')
                            ->where('projects.category', $filter_category);
                    });
                }
            }

            if(request()->has('filter_client')){
                if(request()->filter_client != 'all'){
                    $this->crud->query = $this->crud->query
                    ->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                        ->from('projects')
                        ->whereRaw('projects.id = project_history.project_id')
                        ->whereExists(function ($query) {
                            $query->select(DB::raw(1))
                            ->from('setup_clients')
                            ->whereRaw('setup_clients.id = projects.client_id')
                            ->where('setup_clients.id', request()->filter_client);
                        });
                    });
                }
            }

            CRUD::addColumn([
                'name'      => 'row_number',
                'type'      => 'row_number',
                'label'     => 'No',
                'orderable' => false,
                'wrapper' => [
                    'element' => 'strong',
                ]
            ])->makeFirstColumn();
            CRUD::column(
                [
                                    'label' => trans('backpack::crud.project.column.project_edit.name.label'),
                    'name' => 'name',
                    'type'  => 'text'
                ],
            );
            CRUD::column([
                // 1-n relationship
                                    'label' => trans('backpack::crud.project.column.project_edit.user_id.label'),
                'type'      => 'select',
                'name'      => 'user_id', // the column that contains the ID of that connected entity;
                'entity'    => 'user', // the method that defines the relationship in your Model
                'attribute' => 'name', // foreign key attribute that is shown to user
                'model'     => "App\Models\User", // foreign key model
                // OPTIONAL
                // 'limit' => 32, // Limit the number of characters shown
            ]);
            CRUD::column([
                                    'label' => trans('backpack::crud.project.column.project_edit.date_update.label'),
                'name' => 'date_update',
                'type'  => 'date',
                'format' => 'DD MMM YYYY HH:mm'
            ]);
            CRUD::column([
                                    'label' => trans('backpack::crud.project.column.project_edit.history_update.label'),
                'name' => 'history_update',
                'type'  => 'text',
            ]);

        }
    }

    function ruleValidation(){

        $rule = [
            'name' => 'required',
            'price_total_exclude_ppn' => 'required|numeric',
            'tax_ppn' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'status_po' => 'required',
            'client_id' => 'required|exists:setup_clients,id',
            'category' => 'required',
            'actual_start_date' => 'nullable|date',
            'actual_end_date' => 'nullable|date|after_or_equal:actual_start_date'
        ];

        if(request()->has('po_status')){
            $po_status = request()->po_status;
            if($po_status == 0){
                $rule['no_po_spk'] = 'required|max:100';
                $rule['po_date'] = 'required|date';
                $rule['received_po_date'] = 'required|date';
            }
        }
        return $rule;
    }

    protected function setupCreateOperation(){

        $user = backpack_user();
        $permissions = $user->getAllPermissions();
        $total_permission = $permissions->whereIn('name', [
            'EDIT KOLOM PROGRES DAN KETERANGAN DAFTAR PROJECT'
        ])->count();
        if($total_permission){
            CRUD::setValidation([
                'progress' => 'nullable|numeric',
                'information' => 'nullable',
            ]);
            $edit_column_progress_and_information = true;
        }else{
            CRUD::setValidation($this->ruleValidation());
            $edit_column_progress_and_information = false;
        }

       $attributes_added = [];
       if($edit_column_progress_and_information){
            $attributes_added = [
                'disabled' => true,
            ];
       }

        CRUD::addField([
            'name' => 'name',
            'label' => trans('backpack::crud.project.field.name.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-12',
            ],
            'attributes' => [
                ...$attributes_added,
                'placeholder' => trans('backpack::crud.project.field.name.placeholder'),
            ]
        ]);

        CRUD::field([
            'name'  => 'po_status',
            'label' => trans('backpack::crud.project.field.po_status.label'),
            'type'  => 'checkbox',
            'attributes' => [
                ...$attributes_added,
                'name' => 'po_status_check',
            ]
        ]);

        // CRUD::addField([
        //     'label'       => trans('backpack::crud.project.field.no_po_spk.label'), // Table column heading
        //     'type'        => "select2_ajax_po_spk",
        //     'name'        => 'no_po_spk',
        //     'entity'      => 'setup_client',
        //     'model'       => 'App\Models\SetupClient',
        //     'attribute'   => "name",
        //     'data_source' => backpack_url('fa/voucher/select2-po-spk'),
        //     'wrapper'   => [
        //         'class' => 'form-group col-md-6 no_po_spk',
        //     ],
        //     'attributes' => [
        //         'placeholder' => trans('backpack::crud.project.field.no_po_spk.placeholder'),
        //     ]
        // ]);

        CRUD::addField([
            'name' => 'no_po_spk',
            'label' => trans('backpack::crud.project.field.no_po_spk.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6 no_po_spk',
            ],
            'attributes' => [
                ...$attributes_added,
                'placeholder' => trans('backpack::crud.project.field.no_po_spk.placeholder'),
            ]
        ]);

        CRUD::addField([   // date_picker
            'name'  => 'po_date',
            'type'  => 'date_picker',
            'label' => trans('backpack::crud.project.field.po_date.label'),

            // optional:
            'date_picker_options' => [
                'language' => App::getLocale(),
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6 po_date'
            ],
            'attributes' =>  [
                ...$attributes_added,
            ]
        ]);

        CRUD::addField([   // date_picker
            'name'  => 'received_po_date',
            'type'  => 'date_picker',
            'label' => trans('backpack::crud.project.field.received_po_date.label'),

            // optional:
            'date_picker_options' => [
                'language' => App::getLocale(),
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6 received_po_date'
            ],
            'attributes' =>  [
                ...$attributes_added,
            ]
        ]);

        CRUD::addField([
            'label' => '',
            'name' => 'space',
            'type' => 'hidden',
            'wrapper'   => [
                'class' => 'form-group col-md-6 space'
            ],
        ]);

        CRUD::addField([
            'name' => 'price_total_exclude_ppn',
            'label' =>  trans('backpack::crud.project.field.price_total_exclude_ppn.label'),
            'type' => 'mask',
            'mask' => '000.000.000.000.000.000',
            'mask_options' => [
                'reverse' => true
            ],
            'prefix' => 'Rp',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => '000.000',
                ...$attributes_added,
            ]
        ]);

        $tarif_ppn = SetupPpn::pluck('name', 'name');

        $tax_ppn_option = [
            '' => trans('backpack::crud.project.field.tax_ppn.placeholder'),
            ...$tarif_ppn->map(function ($value, $key) {
                $name = number_format($value, 0, '.', ',').' %';
                return $name;
            }),
        ];


        CRUD::addField([  // Select2
            'label'     => trans('backpack::crud.project.field.tax_ppn.label'),
            'type'      => 'select2_array',
            'name'      => 'tax_ppn',
            'options'   => $tax_ppn_option, // force the related options to be a custom query, instead of all(); you can use this to filter the results show in the select
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ],
            'attributes' => [
                ...$attributes_added,
            ]
        ]);

        CRUD::addField([
            'name' => 'price_ppn',
            'label' =>  trans('backpack::crud.project.field.price_ppn.label'),
            'type' => 'mask',
            'mask' => '000.000.000.000.000.000',
            'mask_options' => [
                'reverse' => true
            ],
            'prefix' => 'Rp',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'disabled' => true,
                'placeholder' => '000.000',
            ]
        ]);

        CRUD::addField([
            'name' => 'price_total_include_ppn',
            'label' =>  trans('backpack::crud.project.field.price_total_include_ppn.label'),
            'type' => 'mask',
            'mask' => '000.000.000.000.000.000',
            'mask_options' => [
                'reverse' => true
            ],
            'prefix' => 'Rp',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'disabled' => true,
                'placeholder' => '000.000',
            ]
        ]);

        CRUD::field([   // date_range
            'name'  => 'start_date,end_date', // db columns for start_date & end_date
            'label' => trans('backpack::crud.project.field.start_date.label'),
            'type'  => 'date_range',

            'date_range_options' => [
                'drops' => 'down', // can be one of [down/up/auto]
                // 'locale' => ['format' => 'DD/MM/YYYY']
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                ...$attributes_added,
                'placeholder' => trans('backpack::crud.client_po.field.startdate_and_enddate.placeholder'),
            ]
        ]);

        CRUD::addField([
            'name' => 'duration',
            'label' => trans('backpack::crud.project.field.duration.label'),
            'type' => 'number',
             // optionals
            'attributes' => [
                "step" => "any",
                'disabled' => true,
            ], // allow decimals
            // 'suffix'     => ".00",
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::addField([   // date_picker
            'name'  => 'actual_start_date',
            'type'  => 'date_picker',
            'label' => trans('backpack::crud.project.field.actual_start_date.label'),

            // optional:
            'date_picker_options' => [
                'language' => App::getLocale(),
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
            'attributes' => [
                ...$attributes_added,
            ]
        ]);

        CRUD::addField([   // date_picker
            'name'  => 'actual_end_date',
            'type'  => 'date_picker',
            'label' => trans('backpack::crud.project.field.actual_end_date.label'),

            // optional:
            'date_picker_options' => [
                'language' => App::getLocale(),
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
            'attributes' => [
                ...$attributes_added,
            ]
        ]);

        $status_po = SetupStatusProject::pluck('name', 'name');

        $status_po_option = [
            '' => trans('backpack::crud.project.field.status_po.placeholder'),
            ...$status_po->map(function ($value, $key) {
                $name = $value;
                return $name;
            }),
        ];

        CRUD::addField([  // Select2
            'label'     => trans('backpack::crud.project.field.status_po.label'),
            'type'      => 'select2_array',
            'name'      => 'status_po',
            'options'   => $status_po_option, // force the related options to be a custom query, instead of all(); you can use this to filter the results show in the select
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ],
            'attributes' => [
                ...$attributes_added,
            ]
        ]);

        $client = SetupClient::all();
        $client_option = [
            '' => trans('backpack::crud.project.field.client_id.placeholder'),
        ];

        foreach($client as $c){
            $client_option[$c->id] = $c->name;
        }


        CRUD::addField([  // Select2
            'label'     => trans('backpack::crud.project.field.client_id.label'),
            'type'      => 'select2_array',
            'name'      => 'client_id',
            'options'   => $client_option, // force the related options to be a custom query, instead of all(); you can use this to filter the results show in the select
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ],
            'attributes' => [
                ...$attributes_added,
            ]
        ]);

        $category = CategoryProject::pluck('name', 'name');
        $category_option = [
            '' => trans('backpack::crud.project.field.category.placeholder'),
            ...$category->map(function ($value, $key) {
                $name = $value;
                return $name;
            }),
        ];

        CRUD::addField([  // Select2
            'label'     => trans('backpack::crud.project.field.category.label'),
            'type'      => 'select2_array',
            'name'      => 'category',
            'options'   => $category_option, // force the related options to be a custom query, instead of all(); you can use this to filter the results show in the select
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ],
            'attributes' => [
                ...$attributes_added,
            ]
        ]);

        CRUD::addField([
            'name' => 'progress',
            'label' => trans('backpack::crud.project.field.progress.label'),
            'type' => 'number',
             // optionals
            'attributes' => ["step" => "any"], // allow decimals
            'prefix'     => "%",
            // 'suffix'     => ".00",
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::addField([
            'name' => 'pic',
            'label' => trans('backpack::crud.project.field.pic.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                ...$attributes_added,
                'placeholder' => trans('backpack::crud.project.field.pic.placeholder'),
            ]
        ]);

        CRUD::addField([
            'name' => 'user',
            'label' => trans('backpack::crud.project.field.user.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                ...$attributes_added,
                'placeholder' => trans('backpack::crud.project.field.user.placeholder'),
            ]
        ]);

        CRUD::addField([
            'name' => 'information',
            'label' => trans('backpack::crud.project.field.information.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.project.field.information.placeholder'),
            ]
        ]);

        CRUD::addField([
            'name' => 'document_path',
            'label' => 'Dokumen Proyek',
            'type' => 'upload',
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
            'attributes' => [
                ...$attributes_added,
            ],
             'withFiles' => [
                'disk' => 'public',
                'path' => 'document_proyek',
                'deleteWhenEntryIsDeleted' => true,
            ],
        ]);

        CRUD::addField([
            'name' => 'logic_project',
            'type' => 'logic_project',
        ]);

    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }


    function hitungDurasiHari($actualEndDate)
    {
        $today = Carbon::today();
        $endDate = Carbon::parse($actualEndDate);

        // Selisih termasuk hari ini
        return $endDate->diffInDays($today);
    }

    function calculateAll($request){
        $excludePpn = floatval($request->input('price_total_exclude_ppn')); // harga belum termasuk ppn
        $ppn = floatval($request->input('tax_ppn')); // nilai ppn dalam persen, misalnya 11

        // Hitung nilai PPN
        $nilaiPpn = ($ppn == 0) ? 0 : ($excludePpn * ($ppn / 100));

        // Total dengan PPN
        $totalWithPpn = $excludePpn + $nilaiPpn;

        // Ambil tanggal
        // $startDate = Carbon::parse($request->input('start_date'));
        // $endDate = Carbon::parse($request->input('end_date'));

        // // Hitung durasi hari (inklusif)
        // $totalDays = (int) $startDate->diffInDays($endDate) + 1;

        // Return atau set ke variabel lain sesuai kebutuhan
        $data = [
            'price_ppn_masked' => $nilaiPpn,
            'price_total_include_ppn_masked' => $totalWithPpn,
            'actual_price_ppn' => $nilaiPpn,
            'actual_price_total_include_ppn' => $totalWithPpn,
            'actual_duration' => ($request->actual_end_date) ? $this->hitungDurasiHari($request->actual_end_date) : 0,
        ];
        return $data;
    }

    public function store()
    {
        $this->crud->hasAccessOrFail('create');

        $request = $this->crud->validateRequest();

        $this->crud->registerFieldEvents();

        DB::beginTransaction();
        try{

            $event = [];

            $po_status = $request->po_status;


            $calculate = $this->calculateAll($request);
            $item = new Project;
            $item->name = $request->name;

            if($po_status == 0){
                // $item->reference_type = ($request->no_type == 'po') ? PurchaseOrder::class : Spk::class;
                // $item->reference_id = $request->no_po_spk;
                $item->no_po_spk = $request->no_po_spk;
                $item->po_date = $request->po_date;
                $item->received_po_date = $request->received_po_date;

                // if($request->no_type == 'po'){
                //     $data_po_spk = PurchaseOrder::leftJoin('subkons', 'subkons.id', '=', 'purchase_orders.subkon_id')
                //     ->select(DB::raw("
                //         subkons.name as name_company,
                //         subkons.bank_name as bank_name,
                //         subkons.bank_account as bank_account,
                //         purchase_orders.id as id,
                //         purchase_orders.po_number as no_po_spk,
                //         purchase_orders.date_po as date_po_spk,
                //         'po' as type
                //     "))->where('purchase_orders.id',  $request->no_po_spk)->first();
                // }else{
                //     $data_po_spk = Spk::leftJoin('subkons', 'subkons.id', '=', 'spk.subkon_id')
                //     ->select(DB::raw("
                //         subkons.name as name_company,
                //         subkons.bank_name as bank_name,
                //         subkons.bank_account as bank_account,
                //         spk.id as id,
                //         spk.no_spk as no_po_spk,
                //         spk.date_spk as date_po_spk,
                //         'spk' as type
                //     "))->where('spk.id',  $request->no_po_spk)->first();
                // }
                // $item->no_po_spk = $data_po_spk->no_po_spk;
            }

            $item->po_status = $po_status;
            $item->price_total_exclude_ppn = $request->price_total_exclude_ppn;
            $item->tax_ppn = $request->tax_ppn;
            $item->price_ppn = $calculate['actual_price_ppn'];
            $item->price_total_include_ppn = $calculate['actual_price_total_include_ppn'];
            $item->start_date = $request->start_date;
            $item->end_date = $request->end_date;
            $item->duration = $calculate['actual_duration'];
            $item->actual_start_date = $request->actual_start_date;
            $item->actual_end_date = $request->actual_end_date;
            $item->status_po = $request->status_po;
            $item->client_id = $request->client_id;
            $item->category = $request->category;
            $item->progress = $request->progress;
            $item->pic = $request->pic;
            $item->user = $request->user;
            $item->information = $request->information;
            $item->save();

            $project_history = new ProjectHistory;
            $project_history->project_id = $item->id;
            $project_history->name = $item->name;
            $project_history->user_id = backpack_auth()->user()->id;
            $project_history->date_update = Carbon::now();
            $project_history->history_update = "Menambahkan data proyek baru";
            $project_history->save();


            $event['crudTable-project_create_success'] = $item;
            $event['crudTable-project_edit_create_success'] = $item;

            // $item = $aset;
            // $this->data['entry'] = $this->crud->entry = $item;

            \Alert::success(trans('backpack::crud.insert_success'))->flash();

            $this->crud->setSaveAction();

            DB::commit();
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $item,
                    'events' => $event,
                ]);
            }
            return $this->crud->performSaveAction($item->getKey());

        }catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function edit($id)
    {
        $this->crud->hasAccessOrFail('update');

        $id = $this->crud->getCurrentEntryId() ?? $id;

        $this->crud->registerFieldEvents();
        $project = Project::find($id);
        $objdata = new \stdClass;
        $objdata->no_type = null;
        $objdata->po_status = $project->po_status;
        $objdata->actual_price_ppn = $project->price_ppn;
        $objdata->actual_price_total_include_ppn = $project->price_total_include_ppn;
        $objdata->actual_duration = $project->duration;
        $this->data['no_po_spk'] = $objdata;
        $project->logic_project = $objdata;


        $this->data['entry'] = $project;

        $this->crud->setOperationSetting('fields', $this->crud->getUpdateFields());

        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.edit').' '.$this->crud->entity_name;
        $this->data['id'] = $id;

        return response()->json([
            'html' => view($this->crud->getEditView(), $this->data)->render()
        ]);
    }

    public function updateProgress(){
        $request = $this->crud->validateRequest();

        $this->crud->registerFieldEvents();

        DB::beginTransaction();
        try{

            $old = DB::table('projects')->where('id', $this->crud->getCurrentEntryId())->first();
            $item = Project::find($this->crud->getCurrentEntryId());

            $item->progress = $request->progress;
            if ($old->progress != $item->progress) {
                $flag_update = true;
            }

            $item->information = $request->information;
            if ($old->information != $item->information) {
                $flag_update = true;
            }
            $item->save();

            if(isset($flag_update)){
                $project_history = new ProjectHistory;
                $project_history->project_id = $item->id;
                $project_history->name = $item->name;
                $project_history->user_id = backpack_auth()->user()->id;
                $project_history->date_update = Carbon::now();
                $project_history->history_update = "Mengedit data proyek";
                $project_history->save();
            }

            $this->data['entry'] = $this->crud->entry = $item;

            \Alert::success(trans('backpack::crud.update_success'))->flash();

            $this->crud->setSaveAction();

            DB::commit();
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $item,
                    'events' => [
                        'crudTable-project_updated_success' => $item,
                        'crudTable-project_edit_updated_success' => $item,
                    ]
                ]);
            }

            return $this->crud->performSaveAction($item->getKey());

        }catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }

    }

    public function update()
    {
        $this->crud->hasAccessOrFail('update');

        $user = backpack_user();
        $permissions = $user->getAllPermissions();
        if($permissions->whereIn('name', [
            'EDIT KOLOM PROGRES DAN KETERANGAN DAFTAR PROJECT'
        ])){
            return $this->updateProgress();
        }

        $request = $this->crud->validateRequest();

        $this->crud->registerFieldEvents();


        DB::beginTransaction();
        try{

            $old = DB::table('projects')->where('id', $this->crud->getCurrentEntryId())->first();
            $item = Project::find($this->crud->getCurrentEntryId());

            $item->name = $request->name;
            if($old->name != $item->name){
                $flag_update = true;
            }
            $po_status = $request->po_status;
            if($old->po_status != $item->po_status){
                $flag_update = true;
            }
            $calculate = $this->calculateAll($request);

            if($po_status == 0){
                // $item->reference_type = ($request->no_type == 'po') ? PurchaseOrder::class : Spk::class;
                // if($old->reference_type != $item->reference_type){
                //     $flag_update = true;
                // }
                // $item->reference_id = $request->no_po_spk;
                // if($old->reference_id != $item->reference_id){
                //     $flag_update = true;
                // }
                $item->po_date = $request->po_date;
                if($old->po_date != $item->po_date){
                    $flag_update = true;
                }
                $item->received_po_date = $request->received_po_date;
                if($old->received_po_date != $item->received_po_date){
                    $flag_update = true;
                }

                // if($request->no_type == 'po'){
                //     $data_po_spk = PurchaseOrder::leftJoin('subkons', 'subkons.id', '=', 'purchase_orders.subkon_id')
                //     ->select(DB::raw("
                //         subkons.name as name_company,
                //         subkons.bank_name as bank_name,
                //         subkons.bank_account as bank_account,
                //         purchase_orders.id as id,
                //         purchase_orders.po_number as no_po_spk,
                //         purchase_orders.date_po as date_po_spk,
                //         'po' as type
                //     "))->where('purchase_orders.id',  $request->no_po_spk)->first();
                // }else{
                //     $data_po_spk = Spk::leftJoin('subkons', 'subkons.id', '=', 'spk.subkon_id')
                //     ->select(DB::raw("
                //         subkons.name as name_company,
                //         subkons.bank_name as bank_name,
                //         subkons.bank_account as bank_account,
                //         spk.id as id,
                //         spk.no_spk as no_po_spk,
                //         spk.date_spk as date_po_spk,
                //         'spk' as type
                //     "))->where('spk.id',  $request->no_po_spk)->first();
                // }
                $item->no_po_spk = $request->no_po_spk;
                if($old->no_po_spk != $item->no_po_spk){
                    $flag_update = true;
                }
            }

            $item->po_status = $po_status;
            if($old->po_status != $item->po_status){
                $flag_update = true;
            }
            $item->price_total_exclude_ppn = $request->price_total_exclude_ppn;
            if($old->price_total_exclude_ppn != $item->price_total_exclude_ppn){
                $flag_update = true;
            }
            $item->tax_ppn = $request->tax_ppn;
            if($old->tax_ppn != $item->tax_ppn){
                $flag_update = true;
            }
            $item->price_ppn = $calculate['actual_price_ppn'];
            if ($old->price_ppn != $item->price_ppn) {
                $flag_update = true;
            }

            $item->price_total_include_ppn = $calculate['actual_price_total_include_ppn'];
            if ($old->price_total_include_ppn != $item->price_total_include_ppn) {
                $flag_update = true;
            }

            $item->start_date = $request->start_date;
            if ($old->start_date != $item->start_date) {
                $flag_update = true;
            }

            $item->end_date = $request->end_date;
            if ($old->end_date != $item->end_date) {
                $flag_update = true;
            }

            $item->duration = $calculate['actual_duration'];
            if ($old->duration != $item->duration) {
                $flag_update = true;
            }

            $item->actual_start_date = $request->actual_start_date;
            if ($old->actual_start_date != $item->actual_start_date) {
                $flag_update = true;
            }

            $item->actual_end_date = $request->actual_end_date;
            if ($old->actual_end_date != $item->actual_end_date) {
                $flag_update = true;
            }

            $item->status_po = $request->status_po;
            if ($old->status_po != $item->status_po) {
                $flag_update = true;
            }

            $item->client_id = $request->client_id;
            if ($old->client_id != $item->client_id) {
                $flag_update = true;
            }

            $item->category = $request->category;
            if ($old->category != $item->category) {
                $flag_update = true;
            }

            $item->progress = $request->progress;
            if ($old->progress != $item->progress) {
                $flag_update = true;
            }

            $item->pic = $request->pic;
            if ($old->pic != $item->pic) {
                $flag_update = true;
            }

            $item->user = $request->user;
            if ($old->user != $item->user) {
                $flag_update = true;
            }

            $item->information = $request->information;
            if ($old->information != $item->information) {
                $flag_update = true;
            }
            $item->save();

            if(isset($flag_update)){
                $project_history = new ProjectHistory;
                $project_history->project_id = $item->id;
                $project_history->name = $item->name;
                $project_history->user_id = backpack_auth()->user()->id;
                $project_history->date_update = Carbon::now();
                $project_history->history_update = "Mengedit data proyek";
                $project_history->save();
            }

            $this->data['entry'] = $this->crud->entry = $item;


            \Alert::success(trans('backpack::crud.update_success'))->flash();


            $this->crud->setSaveAction();

            DB::commit();
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $item,
                    'events' => [
                        'crudTable-project_updated_success' => $item,
                        'crudTable-project_edit_updated_success' => $item,
                    ]
                ]);
            }

            return $this->crud->performSaveAction($item->getKey());

        }catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function setupShowOperation(){
        $this->setupCreateOperation();
        CRUD::field('received_po_date')->remove();
        CRUD::field([   // date_picker
            'name'  => 'received_po_date',
            'type'  => 'date_picker',
            'label' => trans('backpack::crud.project.field.received_po_date.label'),

            // optional:
            'date_picker_options' => [
                'language' => App::getLocale(),
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-12 received_po_date'
            ],
        ])->after('po_date');
        CRUD::field('space')->remove();
        CRUD::field('logic_project')->remove();
        CRUD::field('po_status')->remove();
        // column
        CRUD::column(
            [
                'label'  => '',
                'name' => 'name',
                'type'  => 'text'
            ],
        );

        CRUD::column(
            [
                'label'  => '',
                'name' => 'no_po_spk',
                'type'  => 'text'
            ],
        );

        CRUD::column([
            'label'  => '',
            'name' => 'po_date',
            'type'  => 'date',
            'format' => 'D MMM Y'
        ]);

        CRUD::column([
            'label'  => '',
            'name' => 'received_po_date',
            'type'  => 'date',
            'format' => 'D MMM Y'
        ]);

        CRUD::column([
            'label'  => '',
            'name' => 'price_total_exclude_ppn',
            'type'  => 'number',
            'prefix' => "Rp.",
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
        ]);

        CRUD::column([
            'label'  => '',
            'name' => 'price_ppn',
            'type'  => 'number',
            'prefix' => "Rp.",
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
        ]);

        CRUD::column([
            'label'  => trans('backpack::crud.client_po.column.tax_ppn'),
            'name' => 'tax_ppn',
            'type'  => 'number',
            'suffix' => '%',
        ]);

        CRUD::column([
            'label'  => '',
            'name' => 'price_total_include_ppn',
            'type'  => 'number',
            'prefix' => "Rp.",
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
        ]);

        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.startdate_and_enddate'),
                'name' => 'start_date,end_date',
                'type'  => 'date_range_custom'
            ],
        );

        CRUD::column(
            [
                'label'  => '',
                'name' => 'duration',
                'type'  => 'text'
            ],
        );

        CRUD::column([
            'label'  => '',
            'name' => 'actual_start_date',
            'type'  => 'date',
            'format' => 'D MMM Y'
        ]);

        CRUD::column([
            'label'  => '',
            'name' => 'actual_end_date',
            'type'  => 'date',
            'format' => 'D MMM Y'
        ]);
        CRUD::column(
            [
                'label'  => '',
                'name' => 'status_po',
                'type'  => 'text'
            ],
        );
        CRUD::column([
            // 1-n relationship
            'label' => trans('backpack::crud.client_po.column.client_id'),
            'type'      => 'select',
            'name'      => 'client_id', // the column that contains the ID of that connected entity;
            'entity'    => 'setup_client', // the method that defines the relationship in your Model
            'attribute' => 'name', // foreign key attribute that is shown to user
            'model'     => "App\Models\SetupClient", // foreign key model
            // OPTIONAL
            // 'limit' => 32, // Limit the number of characters shown
        ]);
        CRUD::column(
            [
                'label'  => '',
                'name' => 'category',
                'type'  => 'text'
            ],
        );
        CRUD::column(
            [
                'label'  => '',
                'name' => 'progress',
                'type'  => 'text'
            ],
        );
        CRUD::column(
            [
                'label'  => '',
                'name' => 'pic',
                'type'  => 'text'
            ],
        );
        CRUD::column(
            [
                'label'  => '',
                'name' => 'user',
                'type'  => 'text'
            ],
        );
        CRUD::column(
            [
                'label'  => '',
                'name' => 'information',
                'type'  => 'text'
            ],
        );

        CRUD::column([
            'label'  => 'Dokumen Proyek',
            'name' => 'document_path',
            'type'  => 'text',
                'wrapper'   => [
                'element' => 'a', // the element will default to "a" so you can skip it here
                'href' => function ($crud, $column, $entry, $related_key) {
                    if($entry->document_path != ''){
                        return url('storage/document_proyek/'.$entry->document_path);
                    }
                    return "javascript:void(0)";
                },
                'target' => '_blank',
                // 'class' => 'some-class',
            ],
        ]);
    }

    public function show($id)
    {
        $this->crud->hasAccessOrFail('show');

        $id = $this->crud->getCurrentEntryId() ?? $id;

        if ($this->crud->get('show.softDeletes') && in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($this->crud->model))) {
            $this->data['entry'] = $this->crud->getModel()->withTrashed()->findOrFail($id);
        } else {
            $this->data['entry'] = $this->crud->getEntryWithLocale($id);
        }

        $this->data['entry_value'] = $this->crud->getRowViews($this->data['entry']);
        $this->data['crud'] = $this->crud;

        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.preview').' '.$this->crud->entity_name;

        return response()->json([
            'html' => view($this->crud->getShowView(), $this->data)->render()
        ]);
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $this->crud->hasAccessOrFail('delete');

            $item = Project::find($id);

            $project_history = new ProjectHistory;
            $project_history->project_id = $item->id;
            $project_history->name = $item->name;
            $project_history->user_id = backpack_auth()->user()->id;
            $project_history->date_update = Carbon::now();
            $project_history->history_update = "Menghapus data proyek";
            $project_history->save();

            $item->delete();

            $messages['success'][] = trans('backpack::crud.delete_confirmation_message');
            $messages['events'] = [
                'crudTable-project_updated_success' => 1,
                'crudTable-project_edit_updated_success' => 1,
            ];

            DB::commit();
            return response()->json($messages);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'type' => 'errors',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function exportPdf(){
        $type = request()->tab;

        $this->setupListOperation();

        CRUD::removeColumn('document_path');

        $columns = $this->crud->columns();
        $items =  $this->crud->getEntries();

        $row_number = 0;
        foreach($items as $item){
            foreach($columns as $column){
                if($column['name'] == 'row_number'){
                    $row_number++;
                    $item->{$column['name']} = $row_number;
                }
                if($column['name'] == 'client_id'){
                    $item->client_id = SetupClient::find($item->client_id)->name;
                }
                if($column['name'] == 'start_date,end_date'){
                    $item->{"start_date,end_date"} = $item->start_date.' - '.$item->end_date;
                }
                if($column['name'] == 'user_id'){
                    $item->user_id = User::find($item->user_id)->name;
                }
            }
        }

        $title = 'Daftar Project - '.$type;

        $pdf = Pdf::loadView('exports.table-pdf', compact('columns', 'items', 'title'))->setPaper('A4', 'landscape');

        $fileName = 'vendor_po_' . now()->format('Ymd_His') . '.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    public function exportExcel(){
        $type = request()->tab;

        $this->setupListOperation();
        CRUD::removeColumn('document_path');

        $columns = $this->crud->columns();
        $items =  $this->crud->getEntries();

        $row_number = 0;
        foreach($items as $item){
            foreach($columns as $column){
                if($column['name'] == 'row_number'){
                    $row_number++;
                    $item->{$column['name']} = $row_number;
                }
                if($column['name'] == 'client_id'){
                    $item->client_id = SetupClient::find($item->client_id)->name;
                }
                if($column['name'] == 'start_date,end_date'){
                    $item->{"start_date,end_date"} = $item->start_date.' - '.$item->end_date;
                }
                if($column['name'] == 'user_id'){
                    $item->user_id = User::find($item->user_id)->name;
                }
            }
        }

        $name = 'Status Project - '.$type;

        return response()->streamDownload(function () use($type, $columns, $items){
            echo Excel::raw(new ExportExcel($columns, $items), \Maatwebsite\Excel\Excel::XLSX);
        }, $name, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $name . '"',
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Download Failure',
        ], 400);

    }

}
