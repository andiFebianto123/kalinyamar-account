<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Project;
use App\Models\Setting;
use App\Models\SetupPpn;
use App\Models\SetupClient;
use App\Models\CategoryProject;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Exports\ExportExcel;
use App\Models\SetupStatusProject;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class ProjectListReportCrudController extends CrudController {
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        $this->crud->denyAllAccess(['list', 'show']);
        CRUD::setModel(Project::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/monitoring/project-report');
        CRUD::setEntityNameStrings(trans('backpack::crud.menu.project_report'), trans('backpack::crud.menu.project_report'));
        CRUD::allowAccess('print');
        $user = backpack_user();
        $permissions = $user->getAllPermissions();
        if($permissions->whereIn('name', [
            'AKSES SEMUA VIEW PROJECT',
            'AKSES SEMUA MENU PROJECT',
        ])->count() > 0)
        {
            $this->crud->allowAccess(['list', 'show']);
        }

        // if($permissions->whereIn('name',[
        //     'AKSES SEMUA MENU PROJECT',
        // ])->count() > 0){
        //     $this->crud->allowAccess(['create', 'update', 'delete']);
        // }
    }

    function index(){
        $this->crud->hasAccessOrFail('list');

        $this->card->addCard([
            'name' => 'hightlight',
            'line' => 'top',
            'label' => '',
            'parent_view' => 'crud::components.filter-parent',
            'view' => 'crud::components.hightligh-column',
        ]);

        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? mb_ucfirst($this->crud->entity_name_plural);
        $this->data['title_modal_create'] = trans('backpack::crud.project_report.title_modal_create');
        $this->data['title_modal_edit'] = trans('backpack::crud.project_report.title_modal_edit');
        $this->data['title_modal_delete'] = trans('backpack::crud.project_report.title_modal_delete');
        $this->data['cards'] = $this->card;

        $breadcrumbs = [
            trans('backpack::crud.menu.monitoring_project') => backpack_url('monitoring'),
            trans('backpack::crud.menu.project_report') => backpack_url($this->crud->route)
        ];
        $this->data['breadcrumbs'] = $breadcrumbs;
        $list = "crud::list-custom" ?? $this->crud->getListView();
        return view($list, $this->data);
    }

    function hitungDurasiHari($actualEndDate)
    {
        $today = Carbon::today();
        $endDate = Carbon::parse($actualEndDate);

        // Selisih termasuk hari ini
        return $endDate->diffInDays($today);
    }

    protected function setupListOperation()
    {
        CRUD::disableResponsiveTable();
        $settings = Setting::first();

        CRUD::addButtonFromView('top', 'filter-project', 'filter-project', 'beginning');
        CRUD::addButtonFromView('top', 'export-excel', 'export-excel', 'beginning');
        CRUD::addButtonFromView('top', 'export-pdf', 'export-pdf', 'beginning');

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
                'label'  => trans('backpack::crud.project_report.column.no_po_spk.label'),
                'name' => 'no_po_spk',
                'type'  => 'text'
            ],
        );
        CRUD::column(
            [
                'label'  => trans('backpack::crud.project_report.column.name.label'),
                'name' => 'name',
                'type'  => 'text'
            ],
        );
        CRUD::column([
            'label'  => trans('backpack::crud.project_report.column.price_total_include_ppn.label'),
            'name' => 'price_total_include_ppn',
            'type'  => 'number',
            'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : 'Rp.',
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
        ]);
        CRUD::column([
            // 1-n relationship
            'label' => trans('backpack::crud.project_report.column.client_id.label'),
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
                'label'  => trans('backpack::crud.client_po.column.startdate_and_enddate'),
                'name' => 'start_date,end_date',
                'type'  => 'date_range_custom'
            ],
        );
        CRUD::column(
            [
                'label'  => trans('backpack::crud.project_report.column.duration.label'),
                'name' => 'duration',
                'type'  => 'closure',
                'function' => function ($row) {
                    $total_day = $this->hitungDurasiHari($row->actual_end_date);
                    return ($row->actual_end_date) ? $total_day : '-';
                }
            ],
        );
        CRUD::column([
            'label'  => trans('backpack::crud.project_report.column.actual_start_date.label'),
            'name' => 'actual_start_date',
            'type'  => 'date',
            'format' => 'D MMM Y'
        ]);
        CRUD::column([
            'label'  => trans('backpack::crud.project_report.column.actual_end_date.label'),
            'name' => 'actual_end_date',
            'type'  => 'date',
            'format' => 'D MMM Y'
        ]);
        CRUD::column(
            [
                'label'  => trans('backpack::crud.project_report.column.status_po.label'),
                'name' => 'status_po',
                'type'  => 'text'
            ],
        );
        CRUD::column(
            [
                'label'  => trans('backpack::crud.project_report.column.progress.label'),
                'name' => 'progress',
                'type'  => 'text'
            ],
        );
    }

    protected function setupCreateOperation(){
        CRUD::setValidation([]);
        CRUD::addField([
            'name' => 'name',
            'label' => trans('backpack::crud.project.field.name.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-12',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.project.field.name.placeholder'),
            ]
        ]);

        CRUD::field([
            'name'  => 'po_status',
            'label' => trans('backpack::crud.project.field.po_status.label'),
            'type'  => 'checkbox',
            'attributes' => [
                'name' => 'po_status_check',
            ]
        ]);

        CRUD::addField([
            'label'       => trans('backpack::crud.project.field.no_po_spk.label'), // Table column heading
            'type'        => "select2_ajax_po_spk",
            'name'        => 'no_po_spk',
            'entity'      => 'setup_client',
            'model'       => 'App\Models\SetupClient',
            'attribute'   => "name",
            'data_source' => backpack_url('fa/voucher/select2-po-spk'),
            'wrapper'   => [
                'class' => 'form-group col-md-6 no_po_spk',
            ],
            'attributes' => [
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
            'prefix'     => "%",
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
            'name' => 'logic_project',
            'type' => 'logic_project',
        ]);

    }

    protected function setupShowOperation(){
        $settings = Setting::first();
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
            'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : 'Rp.',
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
        ]);

        CRUD::column([
            'label'  => '',
            'name' => 'price_ppn',
            'type'  => 'number',
            'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : 'Rp.',
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
            'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : 'Rp.',
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

    public function exportPdf(){
        $type = request()->type;

        $this->setupListOperation();
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
                if($column['name'] == 'duration'){
                    $total_day = $this->hitungDurasiHari($item->actual_end_date);
                    $item->duration = ($item->actual_end_date) ? $total_day : '-';
                }
            }
        }

        $title = 'Project Report';

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
        $type = request()->type;

        $this->setupListOperation();
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
                if($column['name'] == 'duration'){
                    $total_day = $this->hitungDurasiHari($item->actual_end_date);
                    $item->duration = ($item->actual_end_date) ? $total_day : '-';
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
