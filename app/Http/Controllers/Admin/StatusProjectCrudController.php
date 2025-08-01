<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Project;
use App\Models\SetupClient;
use App\Models\ProjectHistory;
use App\Models\SetupStatusProject;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\CrudController;
use App\Http\Helpers\CustomHelper;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;


class StatusProjectCrudController extends CrudController {
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;

    public function setup()
    {
        CRUD::setModel(Project::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/monitoring/project-status');
        CRUD::setEntityNameStrings(trans('backpack::crud.project_status.title_header'), trans('backpack::crud.project_status.title_header'));
    }

    public function projectTab(){
        $setupProject = SetupStatusProject::orderBy('id', 'DESC')->get();
        $tabSetup = [];
        foreach($setupProject as $key => $status){
            $tab = [
                'name' => str_replace(' ', '_', $status->name),
                'label' => $status->name,
                'active' => ($key == 0) ? true : false,
                'view' => 'crud::components.datatable',
                'params' => [
                    'crud_custom' => $this->crud,
                ]
            ];
            $tab['params']['route'] = url($this->crud->route.'/search?tab='.$status->name);
            if($status->name == 'UNPAID'){
                $tab['params']['columns'] = [
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
                        'name' => 'invoice_date',
                        'type' => 'text',
                        'label' => trans('backpack::crud.project.column.project.invoice_date.label'),
                        'orderable' => true,
                    ],
                    [
                        'name' => 'total_progress_day',
                        'type' => 'text',
                        'label' => trans('backpack::crud.project.column.project.total_progress_day.label'),
                        'orderable' => true,
                    ],
                    [
                        'name' => 'information',
                        'type' => 'text',
                        'label' => trans('backpack::crud.project.column.project.information.label'),
                        'orderable' => false,
                    ],
                    [
                        'name' => 'action',
                        'type' => 'action',
                        'label' =>  trans('backpack::crud.actions'),
                    ]
                ];
            }else if($status->name == 'TERTUNDA'){
                $tab['params']['columns'] = [
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
                        'name' => 'end_date',
                        'type' => 'text',
                        'label' => trans('backpack::crud.project.column.project.end_date.label'),
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
                        'name' => 'information',
                        'type' => 'text',
                        'label' => trans('backpack::crud.project.column.project.information.label'),
                        'orderable' => false,
                    ],
                ];
            }else if($status->name == 'BELUM SELESAI'){
                 $tab['params']['columns'] = [
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
                        'name' => 'information',
                        'type' => 'text',
                        'label' => trans('backpack::crud.project.column.project.information.label'),
                        'orderable' => false,
                    ],
                 ];
            }else if($status->name == 'RETENSI'){
                 $tab['params']['columns'] = [
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
                        'name' => 'information',
                        'type' => 'text',
                        'label' => trans('backpack::crud.project.column.project.information.label'),
                        'orderable' => false,
                    ],
                 ];
            }else if($status->name == 'BELUM ADA PO'){
                $tab['params']['columns'] = [
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
                        'name' => 'end_date',
                        'type' => 'text',
                        'label' => trans('backpack::crud.project.column.project.end_date.label'),
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
                        'name' => 'information',
                        'type' => 'text',
                        'label' => trans('backpack::crud.project.column.project.information.label'),
                        'orderable' => false,
                    ],
                ];
            }else if($status->name == 'CLOSE'){
                $tab['params']['columns'] = [
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
                        'name' => 'invoice_date',
                        'type' => 'text',
                        'label' => trans('backpack::crud.project.column.project.invoice_date.label'),
                        'orderable' => false,
                    ],
                    [
                        'name' => 'payment_date',
                        'type' => 'text',
                        'label' => trans('backpack::crud.project.column.project.payment_date.label'),
                        'orderable' => false,
                    ],
                    [
                        'name' => 'information',
                        'type' => 'text',
                        'label' => trans('backpack::crud.project.column.project.information.label'),
                        'orderable' => false,
                    ],
                    [
                        'name' => 'action',
                        'type' => 'action',
                        'label' =>  trans('backpack::crud.actions'),
                    ]
                ];
            }
            $tabSetup[str_replace(' ', '_', $status->name)] = $tab;
        }
        $tabSetup['resume'] = [
            'name' => 'resume',
            'label' => 'RESUME',
            'view' => 'crud::components.resume-project',
            'params' => []
        ];
        return $tabSetup;
    }

    function index(){
        $this->crud->hasAccessOrFail('list');

        $tabs = $this->projectTab();

         $this->card->addCard([
            'name' => 'project',
            'line' => 'top',
            'view' => 'crud::components.card-tab',
            'params' => [
                'tabs' => $tabs,
            ]
        ]);

        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? mb_ucfirst($this->crud->entity_name_plural);
        $this->data['title_modal_create'] = trans('backpack::crud.project_status.title_modal_create');
        $this->data['title_modal_edit'] = trans('backpack::crud.project_status.title_modal_edit');
        $this->data['title_modal_delete'] = trans('backpack::crud.project_status.title_modal_delete');
        $this->data['cards'] = $this->card;

        $breadcrumbs = [
            trans('backpack::crud.menu.monitoring_project') => backpack_url('monitoring'),
            trans('backpack::crud.project_status.title_header') => backpack_url($this->crud->route)
        ];
        $this->data['breadcrumbs'] = $breadcrumbs;

        // $list = "crud::list-custom" ?? $this->crud->getListView();
        $list = "crud::list-blank" ?? $this->crud->getListView();
        return view($list, $this->data);
    }

    public function resumeTotal(){
        $invoiceold = Project::where('status_po', 'UNPAID')
        ->orderBy('total_progress_day', 'DESC')
        ->first();

        $data['invoice_old'] = $invoiceold;
        $data['tgl_start_invoice'] = Carbon::parse($invoiceold->invoice_date)->locale(App::getLocale())->isoFormat('dddd, D MMMM Y');

        $invoice_1 = Project::where('status_po', 'UNPAID')
        ->orderBy('id', 'DESC')
        ->get();
        $data['invoice_1'] = $invoice_1;

        $grand_total = 0;

        $total_invoice_1 = 0;
        foreach($invoice_1 as $val1){
            $total_invoice_1 += $val1->price_total_include_ppn;
            $val1->price_total_include_ppn_str = CustomHelper::formatRupiah($val1->price_total_include_ppn);
            $val1->client_name_str = $val1->setup_client->name;
        }
        $grand_total += $total_invoice_1;
        $data['invoice_1_total'] = $total_invoice_1;

        $invoice_2 = Project::where('status_po', 'TERTUNDA')
        ->where('category', 'RUTIN')
        ->orderBy('id', 'DESC')->get();
        $data['invoice_2'] = $invoice_2;

        $total_invoice_2 = 0;
        foreach($invoice_2 as $val2){
            $total_invoice_2 += $val2->price_total_include_ppn;
            $val2->price_total_include_ppn_str = CustomHelper::formatRupiah($val2->price_total_include_ppn);
            $val2->client_name_str = $val2->setup_client->name;
        }
        $grand_total += $total_invoice_1;
        $data['invoice_2_total'] = $total_invoice_2;


        $invoice_3 = Project::where('status_po', 'TERTUNDA')
        ->where('category', 'NON RUTIN')
        ->orderBy('id', 'DESC')->get();
        $data['invoice_3'] = $invoice_3;

        $total_invoice_3 = 0;
        foreach($invoice_3 as $val3){
            $total_invoice_3 += $val3->price_total_include_ppn;
            $val3->price_total_include_ppn_str = CustomHelper::formatRupiah($val3->price_total_include_ppn);
            $val3->client_name_str = $val3->setup_client->name;
        }
        $grand_total += $total_invoice_3;
        $data['invoice_3_total'] = $total_invoice_3;

        $invoice_4 = Project::where('status_po', 'RETENSI')
        ->orderBy('id', 'DESC')->get();
        $data['invoice_4'] = $invoice_4;

        $total_invoice_4 = 0;
        foreach($invoice_4 as $val4){
            $total_invoice_4 += $val4->price_total_include_ppn;
            $val4->price_total_include_ppn_str = CustomHelper::formatRupiah($val4->price_total_include_ppn);
            $val4->client_name_str = $val4->setup_client->name;
        }
        $grand_total += $total_invoice_4;
        $data['invoice_4_total'] = $total_invoice_4;

        $invoice_5 = Project::where('status_po', 'BELUM SELESAI')
        ->where('category', 'RUTIN')
        ->orderBy('id', 'DESC')->get();
        $data['invoice_5'] = $invoice_5;

        $total_invoice_5 = 0;
        foreach($invoice_5 as $val5){
            $total_invoice_5 += $val5->price_total_include_ppn;
            $val5->price_total_include_ppn_str = CustomHelper::formatRupiah($val5->price_total_include_ppn);
            $val5->client_name_str = $val5->setup_client->name;
        }
        $grand_total += $total_invoice_5;
        $data['invoice_5_total'] = $total_invoice_5;

        $invoice_6 = Project::where('status_po', 'BELUM SELESAI')
        ->where('category', 'NON RUTIN')
        ->orderBy('id', 'DESC')->get();
        $data['invoice_6'] = $invoice_6;

        $total_invoice_6 = 0;
        foreach($invoice_6 as $val6){
            $total_invoice_6 += $val6->price_total_include_ppn;
            $val6->price_total_include_ppn_str = CustomHelper::formatRupiah($val6->price_total_include_ppn);
            $val6->client_name_str = $val6->setup_client->name;
        }
        $grand_total += $total_invoice_6;
        $data['invoice_6_total'] = $total_invoice_6;

        return response()->json([
            'list' => $data,
            'grand_total' => CustomHelper::formatRupiah($grand_total),
        ]);
    }

    protected function setupListOperation()
    {
        CRUD::disableResponsiveTable();
        $type = request()->tab;

        CRUD::addButtonFromView('top', 'filter-project', 'filter-project', 'beginning');
        CRUD::addButtonFromView('top', 'download-excel', 'download-excel', 'beginning');
        CRUD::addButtonFromView('top', 'download-pdf', 'download-pdf', 'beginning');

        $this->crud->removeButton('create');
        $this->crud->removeButton('update');

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
        $this->crud->addClause('where', 'status_po', $type);

        if($type == 'UNPAID'){
            CRUD::addButtonFromView('line', 'update-unpaid-project', 'update-unpaid-project', 'beginning');
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
                    'label'  => '',
                    'name' => 'no_po_spk',
                    'type'  => 'text'
                ],
            );
            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'name',
                    'type'  => 'text'
                ],
            );
            CRUD::column([
                'label'  => '',
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
                'label'  => '',
                'name' => 'invoice_date',
                'type'  => 'date',
                'format' => 'D MMM Y'
            ]);
            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'total_progress_day',
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
        }else if($type == 'TERTUNDA'){
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
                    'label'  => '',
                    'name' => 'no_po_spk',
                    'type'  => 'text'
                ],
            );
            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'name',
                    'type'  => 'text'
                ],
            );
            CRUD::column([
                'label'  => '',
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
                'label'  => '',
                'name' => 'end_date',
                'type'  => 'date',
                'format' => 'D MMM Y'
            ]);
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
        }else if($type == 'BELUM SELESAI'){
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
                    'label'  => '',
                    'name' => 'no_po_spk',
                    'type'  => 'text'
                ],
            );
            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'name',
                    'type'  => 'text'
                ],
            );
            CRUD::column([
                'label'  => '',
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
            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'status_po',
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
        }else if($type == 'RETENSI'){
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
                    'label'  => '',
                    'name' => 'no_po_spk',
                    'type'  => 'text'
                ],
            );
            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'name',
                    'type'  => 'text'
                ],
            );
            CRUD::column([
                'label'  => '',
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
            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'information',
                    'type'  => 'text'
                ],
            );
        }else if($type == 'BELUM ADA PO'){
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
                    'label'  => '',
                    'name' => 'no_po_spk',
                    'type'  => 'text'
                ],
            );
            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'name',
                    'type'  => 'text'
                ],
            );
            CRUD::column([
                'label'  => '',
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
                'label'  => '',
                'name' => 'end_date',
                'type'  => 'date',
                'format' => 'D MMM Y'
            ]);
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
        }else if($type == 'CLOSE'){
            CRUD::addButtonFromView('line', 'update-close-project', 'update-close-project', 'beginning');
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
                    'label'  => '',
                    'name' => 'no_po_spk',
                    'type'  => 'text'
                ],
            );
            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'name',
                    'type'  => 'text'
                ],
            );
            CRUD::column([
                'label'  => '',
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
                'label'  => '',
                'name' => 'invoice_date',
                'type'  => 'date',
                'format' => 'D MMM Y'
            ]);
            CRUD::column([
                'label'  => '',
                'name' => 'payment_date',
                'type'  => 'date',
                'format' => 'D MMM Y'
            ]);
            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'information',
                    'type'  => 'text'
                ],
            );
        }

    }

    public function edit($id)
    {
        $this->crud->hasAccessOrFail('update');

        $id = $this->crud->getCurrentEntryId() ?? $id;

        $this->crud->registerFieldEvents();

        $project = $this->crud->getEntry($id);

        $this->data['entry'] = $project;

        $this->fieldEditProject(strtoupper($project->status_po));
        $this->crud->setOperationSetting('fields', $this->crud->getUpdateFields());

        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.edit').' '.$this->crud->entity_name;
        $this->data['id'] = $id;

        return response()->json([
            'html' => view($this->crud->getEditView(), $this->data)->render()
        ]);
    }

    protected function setupUpdateOperation(){
    }

    public function fieldEditProject($po_status){
        $this->setupUpdateOperationUnpaid($po_status);
    }

    private function setupUpdateOperationUnpaid($po_status)
    {
        CRUD::addField([
            'name' => 'name',
            'label' => trans('backpack::crud.project.field.name.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-12',
            ],
            'attributes' => [
                'disabled' => true,
                'placeholder' => trans('backpack::crud.project.field.name.placeholder'),
            ]
        ]);
        CRUD::addField([
            'name' => 'no_po_spk',
            'label' => trans('backpack::crud.project.field.no_po_spk.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'disabled' => true,
                'placeholder' => trans('backpack::crud.project.field.name.placeholder'),
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
                'disabled' => true,
            ]
        ]);

        if($po_status == 'UNPAID'){
            CRUD::addField([   // date_picker
                'name'  => 'invoice_date',
                'type'  => 'date_picker',
                'label' => trans('backpack::crud.project.column.project.invoice_date.label'),

                // optional:
                'date_picker_options' => [
                    'language' => App::getLocale(),
                ],
                'wrapper'   => [
                    'class' => 'form-group col-md-6'
                ],
            ]);

            CRUD::addField([
                'name' => 'total_progress_day',
                'label' => trans('backpack::crud.project.column.project.total_progress_day.label'),
                'type' => 'number',
                // optionals
                'attributes' => [
                    "step" => "any",
                ], // allow decimals
                // 'suffix'     => ".00",
                'wrapper'   => [
                    'class' => 'form-group col-md-6'
                ],
            ]);
        }else if($po_status == 'CLOSE'){
            CRUD::addField([   // date_picker
                'name'  => 'invoice_date',
                'type'  => 'date_picker',
                'label' => trans('backpack::crud.project.column.project.invoice_date.label'),

                // optional:
                'date_picker_options' => [
                    'language' => App::getLocale(),
                ],
                'wrapper'   => [
                    'class' => 'form-group col-md-6'
                ],
            ]);

            CRUD::addField([   // date_picker
                'name'  => 'payment_date',
                'type'  => 'date_picker',
                'label' => trans('backpack::crud.project.column.project.payment_date.label'),

                // optional:
                'date_picker_options' => [
                    'language' => App::getLocale(),
                ],
                'wrapper'   => [
                    'class' => 'form-group col-md-6'
                ],
            ]);
        }


        CRUD::addField([
            'name' => 'information',
            'label' => trans('backpack::crud.project.field.information.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'disabled' => true,
                'placeholder' => trans('backpack::crud.project.field.information.placeholder'),
            ]
        ]);

        CRUD::addField([
            'label' => '',
            'name' => 'logic-status-project-unpaid',
            'type' => 'logic-status-project-unpaid',
        ]);

    }

    public function update()
    {
        $this->crud->hasAccessOrFail('update');

        $old = DB::table('projects')->where('id', $this->crud->getCurrentEntryId())->first();

        if($old->status_po == 'CLOSE'){
            $rule = [
                'invoice_date' => 'nullable|date',
                'payment_date' => 'nullable|date|after_or_equal:invoice_date',
            ];
            CRUD::setValidation($rule);
        }

        $request = $this->crud->validateRequest();

        $this->crud->registerFieldEvents();


        DB::beginTransaction();
        try{

            $item = Project::find($this->crud->getCurrentEntryId());

            if($item->status_po == 'UNPAID'){
                if($old->invoice_date != $request->invoice_date){
                    $flag_update = 1;
                }
                $item->invoice_date = $request->invoice_date;

                if($old->total_progress_day != $request->total_progress_day){
                    $flag_update = 1;
                }
                $item->total_progress_day = $request->total_progress_day;
            }else if($item->status_po == 'CLOSE'){
                if($old->invoice_date != $request->invoice_date){
                    $flag_update = 1;
                }
                $item->invoice_date = $request->invoice_date;
                if($old->payment_date != $request->payment_date){
                    $flag_update = 1;
                }
                $item->payment_date = $request->payment_date;
            }

            if(isset($flag_update)){
                $project_history = new ProjectHistory;
                $project_history->project_id = $item->id;
                $project_history->name = $item->name;
                $project_history->user_id = backpack_auth()->user()->id;
                $project_history->date_update = Carbon::now();
                $project_history->history_update = "Mengedit data proyek";
                $project_history->save();
            }

            $item->save();

            $status_po = str_replace(' ', '_', $item->status_po);

            $this->data['entry'] = $this->crud->entry = $item;

            $tab = $this->projectTab();

            \Alert::success(trans('backpack::crud.update_success'))->flash();


            $this->crud->setSaveAction();

            DB::commit();
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $item,
                    'events' => [
                        'crudTable-'.$tab[$status_po]['name'].'_updated_success' => $item,
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


}
