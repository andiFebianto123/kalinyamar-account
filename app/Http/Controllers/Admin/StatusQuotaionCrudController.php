<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CrudController;
use App\Models\Quotation;
use App\Models\SetupOffering;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class StatusQuotaionCrudController extends CrudController{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup()
    {
        CRUD::setModel(Quotation::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/monitoring/quotation-status');
        CRUD::setEntityNameStrings(trans('backpack::crud.menu.quotation_status'), trans('backpack::crud.menu.quotation_status'));
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
                        'label' => 'HPS',
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
                        ]
                    ],
                    [
                        'name' => 'quotation',
                        'label' => 'Quotation',
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
                        ]
                    ],
                    [
                        'name' => 'close',
                        'label' => 'Close',
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
        CRUD::disableResponsiveTable();
        CRUD::addButtonFromView('top', 'download-excel', 'download-excel', 'beginning');
        CRUD::addButtonFromView('top', 'download-pdf', 'download-pdf', 'beginning');
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
                'label'  => '',
                'name' => 'no_rfq',
                'type'  => 'text'
            ],
        );
        CRUD::column(
            [
                'label'  => '',
                'name' => 'name_project',
                'type'  => 'text'
            ],
        );
        CRUD::column([
            'label'  => '',
            'name' => 'rab',
            'type'  => 'number',
            'prefix' => "Rp.",
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
        ]);
        CRUD::column([
            'label'  => '',
            'name' => 'rap',
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
        CRUD::column([
            'label'  => '',
            'name' => 'closing_date',
            'type'  => 'date',
            'format' => 'D MMM Y'
        ]);
        CRUD::column(
            [
                'label'  => '',
                'name' => 'status',
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

}
