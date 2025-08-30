<?php
namespace App\Http\Controllers\Admin;

use App\Models\Setting;
use App\Models\Quotation;
use App\Models\SetupClient;
use App\Models\SetupOffering;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Exports\ExportExcel;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class StatusQuotaionCrudController extends CrudController{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup()
    {
        $this->crud->denyAllAccess(['list', 'show']);
        CRUD::setModel(Quotation::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/monitoring/quotation-status');
        CRUD::setEntityNameStrings(trans('backpack::crud.menu.quotation_status'), trans('backpack::crud.menu.quotation_status'));
        $user = backpack_user();
        $permissions = $user->getAllPermissions();
        if($permissions->whereIn('name', [
            'AKSES SEMUA VIEW PROJECT',
            'AKSES SEMUA MENU PROJECT',
            'AKSES SEMUA STATUS PENAWARAN PROJECT'
        ])->count() > 0)
        {
            $this->crud->allowAccess(['list', 'show']);
        }

        // if($permissions->whereIn('name',[
        //     'AKSES SEMUA MENU PROJECT',
        // ])->count() > 0){
        //     // $this->crud->allowAccess(['create', 'update', 'delete']);
        // }
    }

    public function listTableQotation(){
        $data = [];
        $quotationSetup = SetupOffering::orderBy('id', 'desc')->get();
        foreach($quotationSetup as $setup){
            $data[] = [
                'name' => str_replace(' ', '_', $setup->name),
                'title' => $setup->name,
                'column' => [
                    [
                        'name'      => 'row_number',
                        'type'      => 'row_number',
                        'label'     => 'No',
                        'orderable' => false,
                    ],
                    [
                        'label' => trans('backpack::crud.quotation.column.no_rfq.label'),
                        'type'      => 'text',
                        'name'      => 'no_rfq',
                        'orderable' => true,
                    ],
                    [
                        'label' => trans('backpack::crud.quotation.column.name_project.label'),
                        'type'      => 'text',
                        'name'      => 'name_project',
                        'orderable' => true,
                    ],
                    [
                        'label' => trans('backpack::crud.quotation.column.rab.label'),
                        'type'      => 'text',
                        'name'      => 'rab',
                        'orderable' => true,
                    ],
                    [
                        'label' => trans('backpack::crud.quotation.column.rap.label'),
                        'type'      => 'text',
                        'name'      => 'rap',
                        'orderable' => true,
                    ],
                    [
                        'label' => trans('backpack::crud.quotation.column.client_id.label'),
                        'type'      => 'text',
                        'name'      => 'client_id',
                        'orderable' => true,
                    ],
                    [
                        'label' => trans('backpack::crud.quotation.column.pic.label'),
                        'type'      => 'text',
                        'name'      => 'pic',
                        'orderable' => false,
                    ],
                    [
                        'label' => trans('backpack::crud.quotation.column.user.label'),
                        'type'      => 'text',
                        'name'      => 'user',
                        'orderable' => false,
                    ],
                    [
                        'label' => trans('backpack::crud.quotation.column.closing_date.label'),
                        'type'      => 'text',
                        'name'      => 'closing_date',
                        'orderable' => true,
                    ],
                    [
                        'label' => trans('backpack::crud.quotation.column.status.label'),
                        'type'      => 'text',
                        'name'      => 'status',
                        'orderable' => false,
                    ],
                    [
                        'label' => trans('backpack::crud.quotation.column.information.label'),
                        'type'      => 'text',
                        'name'      => 'information',
                        'orderable' => false,
                    ],
                ],
                'route' => url($this->crud->route.'/search?type='.$setup->name),
            ];
        }
        return $data;
    }

    function index(){
        $this->crud->hasAccessOrFail('list');

        $this->data['is_disabled_list'] = true;

        // $statusQuotation = $this->listTableQotation();
        // foreach($statusQuotation as $quotation){
        //     $this->card->addCard([
        //         'name' => $quotation['name'],
        //         'line' => 'bottom',
        //         'view' => 'crud::components.datatable-origin',
        //         'params' => [
        //             'title' => $quotation['title'],
        //             'crud_custom' => $this->crud,
        //             'columns' => $quotation['column'],
        //             'route' => $quotation['route'],
        //         ]
        //     ]);
        // }

        $this->card->addCard([
            'name' => 'quotation',
            'line' => 'top',
            'view' => 'crud::components.card-tab',
            'params' => [
                'tabs' => [
                    [
                        'name' => 'hps',
                        'label' => strtoupper('hps'),
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
                                    'label' => trans('backpack::crud.quotation.column.no_rfq.label'),
                                    'type'      => 'text',
                                    'name'      => 'no_rfq',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.quotation.column.name_project.label'),
                                    'type'      => 'text',
                                    'name'      => 'name_project',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.quotation.column.rab.label'),
                                    'type'      => 'text',
                                    'name'      => 'rab',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.quotation.column.rap.label'),
                                    'type'      => 'text',
                                    'name'      => 'rap',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.quotation.column.client_id.label'),
                                    'type'      => 'text',
                                    'name'      => 'client_id',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.quotation.column.pic.label'),
                                    'type'      => 'text',
                                    'name'      => 'pic',
                                    'orderable' => false,
                                ],
                                [
                                    'label' => trans('backpack::crud.quotation.column.user.label'),
                                    'type'      => 'text',
                                    'name'      => 'user',
                                    'orderable' => false,
                                ],
                                [
                                    'label' => trans('backpack::crud.quotation.column.closing_date.label'),
                                    'type'      => 'text',
                                    'name'      => 'closing_date',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.quotation.column.status.label'),
                                    'type'      => 'text',
                                    'name'      => 'status',
                                    'orderable' => false,
                                ],
                                [
                                    'label' => trans('backpack::crud.quotation.column.information.label'),
                                    'type'      => 'text',
                                    'name'      => 'information',
                                    'orderable' => false,
                                ],
                            ],
                            'route' => url($this->crud->route.'/search?type=hps'),
                            'route_export_pdf' => url($this->crud->route.'/export-pdf?type=hps'),
                            'title_export_pdf' => 'Status-quotation-HPS.pdf',
                            'route_export_excel' => url($this->crud->route.'/export-excel?type=hps'),
                            'title_export_excel' => 'Status-quotation-HPS.xlsx',
                        ]
                    ],
                    [
                        'name' => 'quotation',
                        'label' => strtoupper('Quotation'),
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
                                    'label' => trans('backpack::crud.quotation.column.no_rfq.label'),
                                    'type'      => 'text',
                                    'name'      => 'no_rfq',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.quotation.column.name_project.label'),
                                    'type'      => 'text',
                                    'name'      => 'name_project',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.quotation.column.rab.label'),
                                    'type'      => 'text',
                                    'name'      => 'rab',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.quotation.column.rap.label'),
                                    'type'      => 'text',
                                    'name'      => 'rap',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.quotation.column.client_id.label'),
                                    'type'      => 'text',
                                    'name'      => 'client_id',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.quotation.column.pic.label'),
                                    'type'      => 'text',
                                    'name'      => 'pic',
                                    'orderable' => false,
                                ],
                                [
                                    'label' => trans('backpack::crud.quotation.column.user.label'),
                                    'type'      => 'text',
                                    'name'      => 'user',
                                    'orderable' => false,
                                ],
                                [
                                    'label' => trans('backpack::crud.quotation.column.closing_date.label'),
                                    'type'      => 'text',
                                    'name'      => 'closing_date',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.quotation.column.status.label'),
                                    'type'      => 'text',
                                    'name'      => 'status',
                                    'orderable' => false,
                                ],
                                [
                                    'label' => trans('backpack::crud.quotation.column.information.label'),
                                    'type'      => 'text',
                                    'name'      => 'information',
                                    'orderable' => false,
                                ],
                            ],
                            'route' => url($this->crud->route.'/search?type=Quotation'),
                            'route_export_pdf' => url($this->crud->route.'/export-pdf?type=Quotation'),
                            'title_export_pdf' => 'Status-quotation-Quotation.pdf',
                            'route_export_excel' => url($this->crud->route.'/export-excel?type=Quotation'),
                            'title_export_excel' => 'Status-quotation-Quotation.xlsx',
                        ]
                    ],
                    [
                        'name' => 'close',
                        'label' => strtoupper('Close'),
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
                                    'label' => trans('backpack::crud.quotation.column.no_rfq.label'),
                                    'type'      => 'text',
                                    'name'      => 'no_rfq',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.quotation.column.name_project.label'),
                                    'type'      => 'text',
                                    'name'      => 'name_project',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.quotation.column.rab.label'),
                                    'type'      => 'text',
                                    'name'      => 'rab',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.quotation.column.rap.label'),
                                    'type'      => 'text',
                                    'name'      => 'rap',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.quotation.column.client_id.label'),
                                    'type'      => 'text',
                                    'name'      => 'client_id',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.quotation.column.pic.label'),
                                    'type'      => 'text',
                                    'name'      => 'pic',
                                    'orderable' => false,
                                ],
                                [
                                    'label' => trans('backpack::crud.quotation.column.user.label'),
                                    'type'      => 'text',
                                    'name'      => 'user',
                                    'orderable' => false,
                                ],
                                [
                                    'label' => trans('backpack::crud.quotation.column.closing_date.label'),
                                    'type'      => 'text',
                                    'name'      => 'closing_date',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.quotation.column.status.label'),
                                    'type'      => 'text',
                                    'name'      => 'status',
                                    'orderable' => false,
                                ],
                                [
                                    'label' => trans('backpack::crud.quotation.column.information.label'),
                                    'type'      => 'text',
                                    'name'      => 'information',
                                    'orderable' => false,
                                ],
                            ],
                            'route' => url($this->crud->route.'/search?type=Close'),
                            'route_export_pdf' => url($this->crud->route.'/export-pdf?type=Close'),
                            'title_export_pdf' => 'Status-quotation-Close.pdf',
                            'route_export_excel' => url($this->crud->route.'/export-excel?type=Close'),
                            'title_export_excel' => 'Status-quotation-Close.xlsx',
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
        $this->data['title_modal_create'] = trans('backpack::crud.quotation_status.title_modal_create');
        $this->data['title_modal_edit'] = trans('backpack::crud.quotation_status.title_modal_edit');
        $this->data['title_modal_delete'] = trans('backpack::crud.quotation_status.title_modal_delete');

        $breadcrumbs = [
            trans('backpack::crud.menu.monitoring_project') => backpack_url('monitoring'),
            trans('backpack::crud.menu.quotation_status') => backpack_url($this->crud->route)
        ];
        $this->data['breadcrumbs'] = $breadcrumbs;

        $this->data['cards'] = $this->card;
        $this->data['modals'] = $this->modal;
        $this->data['scripts'] = $this->script;
        $list = "crud::list-blank" ?? $this->crud->getListView();
        return view($list, $this->data);
    }

    protected function setupListOperation()
    {
        $type = request()->type;
        $settings = Setting::first();
        CRUD::disableResponsiveTable();
        CRUD::addButtonFromView('top', 'export-excel', 'export-excel', 'beginning');
        CRUD::addButtonFromView('top', 'export-pdf', 'export-pdf', 'beginning');
        CRUD::addClause('where', 'status', $type);
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
                                    'label' => trans('backpack::crud.quotation.column.no_rfq.label'),
                'name' => 'no_rfq',
                'type'  => 'text'
            ],
        );
        CRUD::column(
            [
                                    'label' => trans('backpack::crud.quotation.column.name_project.label'),
                'name' => 'name_project',
                'type'  => 'text'
            ],
        );
        CRUD::column([
                                    'label' => trans('backpack::crud.quotation.column.rab.label'),
            'name' => 'rab',
            'type'  => 'number',
            'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
        ]);
        CRUD::column([
                                    'label' => trans('backpack::crud.quotation.column.rap.label'),
            'name' => 'rap',
            'type'  => 'number',
            'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
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
        CRUD::column(
            [
                                    'label' => trans('backpack::crud.quotation.column.pic.label'),
                'name' => 'pic',
                'type'  => 'text'
            ],
        );
        CRUD::column(
            [
                                    'label' => trans('backpack::crud.quotation.column.user.label'),
                'name' => 'user',
                'type'  => 'text'
            ],
        );
        CRUD::column([
                                    'label' => trans('backpack::crud.quotation.column.closing_date.label'),
            'name' => 'closing_date',
            'type'  => 'date',
            'format' => 'D MMM Y'
        ]);
        CRUD::column(
            [
                                    'label' => trans('backpack::crud.quotation.column.status.label'),
                'name' => 'status',
                'type'  => 'closure',
                'function' => function ($entry) {
                    return strtoupper($entry->status);
                }
            ],
        );
        CRUD::column(
            [
                'label' => trans('backpack::crud.quotation.column.information.label'),
                'name' => 'information',
                'type'  => 'text'
            ],
        );
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
                if($column['name'] == 'status'){
                    $item->status = strtoupper($item->status);
                }
            }
        }

        $title = 'Status Quotation - '.$type;

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
                if($column['name'] == 'status'){
                    $item->status = strtoupper($item->status);
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
