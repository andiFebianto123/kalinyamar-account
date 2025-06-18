<?php

namespace App\Http\Controllers\Admin;

// use Backpack\CRUD\app\Http\Controllers\CrudController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Http\Exports\ExportVendorPo;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\CrudController;
use App\Http\Requests\PurchaseOrderRequest;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use App\Http\Controllers\Admin\PurchaseOrderTabController;
use App\Http\Helpers\CustomHelper;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class PurchaseOrderCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class PurchaseOrderCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\PurchaseOrder::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/vendor/purchase-order');
        CRUD::setEntityNameStrings('purchase order', 'purchase orders');
    }

    public function setupTabsCrud($nameTabs){
        if($nameTabs == 'open'){
            $crud = new PurchaseOrderTabController();
            $crud->get_crud();
            return $crud;
        }
    }

    public function index()
    {
        $this->crud->hasAccessOrFail('list');

        $this->card->addCard([
            'name' => 'po_tab',
            'line' => 'top',
            'view' => 'crud::components.card-tab',
            'params' => [
                'tabs' => [
                    [
                        'name' => 'list_all_po',
                        'label' => trans('backpack::crud.po.tab.title_all_po'),
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
                                    'label' => trans('backpack::crud.subkon.column.name'),
                                    'type'      => 'select',
                                    'name'      => 'subkon_id',
                                    'orderable' => true,
                                ],
                                [
                                    'label'  => trans('backpack::crud.po.column.po_number'),
                                    'name' => 'po_number',
                                    'type'  => 'text'
                                ],
                                [
                                    'label'  => trans('backpack::crud.po.column.date_po'),
                                    'name' => 'date_po',
                                    'type'  => 'date'
                                ],
                                [
                                    'label'  => trans('backpack::crud.po.column.job_name'),
                                    'name' => 'job_name',
                                    'type'  => 'text'
                                ],
                                [
                                    'label'  => trans('backpack::crud.po.column.job_description'),
                                    'name' => 'job_description',
                                    'type'  => 'textarea'
                                ],
                                [
                                    'label'  => trans('backpack::crud.po.column.job_value'),
                                    'name' => 'job_value',
                                    'type'  => 'number',
                                ],
                                [
                                    'label'  => trans('backpack::crud.po.column.tax_ppn'),
                                    'name' => 'tax_ppn',
                                    'type'  => 'number',
                                ],
                                [
                                    'label'  => trans('backpack::crud.po.column.total_value_with_tax'),
                                    'name' => 'total_value_with_tax',
                                    'type'  => 'number-custom',
                                ],
                                [
                                    'label'  => trans('backpack::crud.po.column.due_date'),
                                    'name' => 'date_po',
                                    'type'  => 'date'
                                ],
                                [
                                    'label' => trans('backpack::crud.po.column.status'),
                                    'name' => 'status',
                                    'type' => 'closure'
                                ],
                                [
                                    'name'   => 'document_path',
                                    'type'   => 'upload',
                                    'label'  => trans('backpack::crud.po.column.document_path'),
                                ],
                                [
                                    'name' => 'action',
                                    'type' => 'action',
                                    'label' =>  trans('backpack::crud.actions'),
                                ]
                            ],
                            'route' => backpack_url('/vendor/purchase-order/search?tab=list_all_po'),
                        ],
                    ],
                    [
                        'name' => 'list_open',
                        'label' => trans('backpack::crud.po.tab.open'),
                        // 'class' => '',
                        'active' => false,
                        'view' => 'crud::components.datatable-po',
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
                                    'label' => trans('backpack::crud.subkon.column.name'),
                                    'type'      => 'select',
                                    'name'      => 'subkon_id',
                                    'orderable' => true,
                                ],
                                [
                                    'label'  => trans('backpack::crud.po.column.po_number'),
                                    'name' => 'po_number',
                                    'type'  => 'text'
                                ],
                                [
                                    'label'  => trans('backpack::crud.po.column.date_po'),
                                    'name' => 'date_po',
                                    'type'  => 'date'
                                ],
                                [
                                    'label'  => trans('backpack::crud.po.column.job_name'),
                                    'name' => 'job_name',
                                    'type'  => 'text'
                                ],
                                [
                                    'label'  => trans('backpack::crud.po.column.job_description'),
                                    'name' => 'job_description',
                                    'type'  => 'textarea'
                                ],
                                [
                                    'label'  => trans('backpack::crud.po.column.job_value'),
                                    'name' => 'job_value',
                                    'type'  => 'number',
                                ],
                                [
                                    'label'  => trans('backpack::crud.po.column.tax_ppn'),
                                    'name' => 'tax_ppn',
                                    'type'  => 'number',
                                ],
                                [
                                    'label'  => trans('backpack::crud.po.column.total_value_with_tax'),
                                    'name' => 'total_value_with_tax',
                                    'type'  => 'number-custom',
                                ],
                                [
                                    'label'  => trans('backpack::crud.po.column.due_date'),
                                    'name' => 'date_po',
                                    'type'  => 'date'
                                ],
                                [
                                    'label' => trans('backpack::crud.po.column.status'),
                                    'name' => 'status',
                                    'type' => 'closure'
                                ],
                                [
                                    'name'   => 'document_path',
                                    'type'   => 'upload',
                                    'label'  => trans('backpack::crud.po.column.document_path'),
                                ],
                            ],
                            'total_include_ppn' => CustomHelper::formatRupiah(PurchaseOrder::where('status', PurchaseOrder::OPEN)->sum('total_value_with_tax')),
                            'route' => backpack_url('/vendor/purchase-order/search?tab=open'),
                        ],
                    ],
                    [
                        'name' => 'list_close',
                        'label' => trans('backpack::crud.po.tab.close'),
                        // 'class' => '',
                        'active' => false,
                        'view' => 'crud::components.datatable-po',
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
                                    'label' => trans('backpack::crud.subkon.column.name'),
                                    'type'      => 'select',
                                    'name'      => 'subkon_id',
                                    'orderable' => true,
                                ],
                                [
                                    'label'  => trans('backpack::crud.po.column.po_number'),
                                    'name' => 'po_number',
                                    'type'  => 'text'
                                ],
                                [
                                    'label'  => trans('backpack::crud.po.column.date_po'),
                                    'name' => 'date_po',
                                    'type'  => 'date'
                                ],
                                [
                                    'label'  => trans('backpack::crud.po.column.job_name'),
                                    'name' => 'job_name',
                                    'type'  => 'text'
                                ],
                                [
                                    'label'  => trans('backpack::crud.po.column.job_description'),
                                    'name' => 'job_description',
                                    'type'  => 'textarea'
                                ],
                                [
                                    'label'  => trans('backpack::crud.po.column.job_value'),
                                    'name' => 'job_value',
                                    'type'  => 'number',
                                ],
                                [
                                    'label'  => trans('backpack::crud.po.column.tax_ppn'),
                                    'name' => 'tax_ppn',
                                    'type'  => 'number',
                                ],
                                [
                                    'label'  => trans('backpack::crud.po.column.total_value_with_tax'),
                                    'name' => 'total_value_with_tax',
                                    'type'  => 'number-custom',
                                ],
                                [
                                    'label'  => trans('backpack::crud.po.column.due_date'),
                                    'name' => 'date_po',
                                    'type'  => 'date'
                                ],
                                [
                                    'label' => trans('backpack::crud.po.column.status'),
                                    'name' => 'status',
                                    'type' => 'closure'
                                ],
                                [
                                    'name'   => 'document_path',
                                    'type'   => 'upload',
                                    'label'  => trans('backpack::crud.po.column.document_path'),
                                ],
                            ],
                            'total_include_ppn' => CustomHelper::formatRupiah(PurchaseOrder::where('status', PurchaseOrder::CLOSE)->sum('total_value_with_tax')),
                            'route' => backpack_url('/vendor/purchase-order/search?tab=close'),
                        ],
                    ]
                ]
            ]
        ]);

        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? mb_ucfirst($this->crud->entity_name_plural);
        $this->data['title_modal_create'] = "PO vendor (Subkon)";
        $this->data['title_modal_edit'] = "PO Vendor (Subkon)";
        $this->data['title_modal_delete'] = "PO Vendor (Subkon)";
        $this->data['cards'] = $this->card;

        $breadcrumbs = [
            'Vendor (Subkon)' => backpack_url('vendor'),
            'PO' => backpack_url($this->crud->route)
        ];
        $this->data['breadcrumbs'] = $breadcrumbs;

        // $list = "crud::list-custom" ?? $this->crud->getListView();
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

    public function store()
    {
        $this->crud->hasAccessOrFail('create');

        request()->merge([
            'total_value_with_tax' => request()->job_value + (request()->job_value * request()->tax_ppn / 100),
        ]);

        if(request()->tax_ppn == null){
            request()->merge([
                'tax_ppn' => 0,
            ]);
        }

        $request = $this->crud->validateRequest();

        $this->crud->registerFieldEvents();

        DB::beginTransaction();
        try{

            $status = request()->status;
            $item = $this->crud->create($this->crud->getStrippedSaveRequest($request));
            $this->data['entry'] = $this->crud->entry = $item;

            \Alert::success(trans('backpack::crud.insert_success'))->flash();

            $events = [];
            // if($status == PurchaseOrder::OPEN){
            //     $events['crudTable-list_open_create_success'] = $item;
            // }else if($status == PurchaseOrder::CLOSE){
            //     $events['crudTable-list_close_create_success'] = $item;
            // }
            $events['crudTable-list_all_po_create_success'] = $item;
             $events['crudTable-list_open_create_success'] = $item;
            $events['crudTable-list_close_create_success'] = $item;
            $this->crud->setSaveAction();

            DB::commit();
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $item,
                    'events' => $events,
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

        $this->data['entry'] = $this->crud->getEntryWithLocale($id);

        $this->crud->setOperationSetting('fields', $this->crud->getUpdateFields());

        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.edit').' '.$this->crud->entity_name;
        $this->data['id'] = $id;

        return response()->json([
            'html' => view($this->crud->getEditView(), $this->data)->render()
        ]);
    }

    public function update()
    {
        $this->crud->hasAccessOrFail('update');

        request()->merge([
            'total_value_with_tax' => request()->job_value + (request()->job_value * request()->tax_ppn / 100),
        ]);

        if(request()->tax_ppn == null){
            request()->merge([
                'tax_ppn' => 0,
            ]);
        }

        $request = $this->crud->validateRequest();

        $this->crud->registerFieldEvents();

        DB::beginTransaction();
        try{

            $status = request()->status;
            $item = $this->crud->update(
                $request->get($this->crud->model->getKeyName()),
                $this->crud->getStrippedSaveRequest($request)
            );
            $this->data['entry'] = $this->crud->entry = $item;


            \Alert::success(trans('backpack::crud.update_success'))->flash();

            $events = [];
            // if($status == PurchaseOrder::OPEN){
            //     $events['crudTable-list_open_updated_success'] = $item;
            // }else if($status == PurchaseOrder::CLOSE){
            //     $events['crudTable-list_close_updated_success'] = $item;
            // }

            $events['crudTable-list_all_po_updated_success'] = $item;
            $events['crudTable-list_open_updated_success'] = $item;
            $events['crudTable-list_close_updated_success'] = $item;
            $this->crud->setSaveAction();

            DB::commit();
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $item,
                    'events' => $events,
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

    public function setCustomColumn($app){
        CRUD::disableResponsiveTable();
        $app->addColumn([
            'name'      => 'row_number',
            'type'      => 'row_number',
            'label'     => 'No',
            'orderable' => false,
            'wrapper' => [
                'element' => 'strong',
            ]
        ])->makeFirstColumn();

        $app->addColumn([
            // 1-n relationship
            'label' => trans('backpack::crud.subkon.column.name'),
            'type'      => 'select',
            'name'      => 'subkon_id', // the column that contains the ID of that connected entity;
            'entity'    => 'subkon', // the method that defines the relationship in your Model
            'attribute' => 'name', // foreign key attribute that is shown to user
            'model'     => "App\Models\Subkon", // foreign key model
            // OPTIONAL
            // 'limit' => 32, // Limit the number of characters shown
        ]);

        $app->addColumn(
            [
                'label'  => trans('backpack::crud.po.column.po_number'),
                'name' => 'po_number',
                'type'  => 'text'
            ],
        );

        $app->addColumn(
            [
                'label'  => trans('backpack::crud.po.column.date_po'),
                'name' => 'date_po',
                'type'  => 'date'
            ],
        );

        $app->addColumn(
            [
                'label'  => trans('backpack::crud.po.column.job_name'),
                'name' => 'job_name',
                'type'  => 'text'
            ],
        );

        $app->addColumn(
            [
                'label'  => trans('backpack::crud.po.column.job_description'),
                'name' => 'job_description',
                'type'  => 'textarea'
            ],
        );

        $app->addColumn(
            [
                'label'  => trans('backpack::crud.po.column.job_value'),
                'name' => 'job_value',
                'type'  => 'number',
                'prefix' => "Rp.",
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
            ],
        );

        $app->addColumn([
            'label'  => trans('backpack::crud.po.column.tax_ppn'),
            'name' => 'tax_ppn',
            'type'  => 'number',
            'suffix' => '%',
        ]);

        $app->addColumn(
            [
                'label'  => trans('backpack::crud.po.column.total_value_with_tax'),
                'name' => 'total_value_with_tax',
                'type'  => 'number-custom',
                'prefix' => "Rp.",
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
                'function' => function($entry){
                    return $entry->job_value + ($entry->job_value * $entry->tax_ppn / 100);
                }
            ],
        );

        $app->addColumn([
            'label'  => trans('backpack::crud.po.column.due_date'),
            'name' => 'due_date',
            'type'  => 'date'
        ]);

        $app->addColumn([
            'label'  => trans('backpack::crud.po.column.status'),
            'name' => 'status',
            'type'  => 'closure',
            'function' => function($entry){
                return strtoupper($entry->status);
            }
        ]);

        $app->addColumn([
            'name'   => 'document_path',
            'type'   => 'upload',
            'label'  => trans('backpack::crud.po.column.document_path'),
            'disk'   => 'public',
        ]);

    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::addButtonFromView('top', 'download-excel-po', 'download-excel-po', 'beginning');
        CRUD::addButtonFromView('top', 'download-pdf-po', 'download-pdf-po', 'beginning');

        $type = request()->tab;
        if($type == 'open'){
            $this->crud->query = $this->crud->query->where('status', PurchaseOrder::OPEN);
        }else if($type == 'close'){
            $this->crud->query = $this->crud->query->where('status', PurchaseOrder::CLOSE);
        }
        $this->setCustomColumn($this->crud);
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(PurchaseOrderRequest::class);
        // CRUD::setFromDb(); // set fields from db columns.

        CRUD::field([   // 1-n relationship
            'label'       => trans('backpack::crud.subkon.column.name'), // Table column heading
            'type'        => "select2_ajax_custom",
            'name'        => 'subkon_id', // the column that contains the ID of that connected entity
            'entity'      => 'subkon', // the method that defines the relationship in your Model
            'attribute'   => "name", // foreign key attribute that is shown to user
            'data_source' => backpack_url('vendor/select2-subkon-id'), // url to controller search function (with /{id} should return a single entry)
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::addField([   // Hidden
            'name'  => 'space',
            'type'  => 'hidden',
            'value' => 'active',
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
            'attributes' => [
                'disabled'  => 'disabled',
                // 'placeholder' => trans('backpack::crud.spk.field.')
            ]
        ]);

        CRUD::addField([
            'name' => 'po_number',
            'label' => trans('backpack::crud.po.column.po_number'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::addField([
            'name' => 'date_po',
            'label' => trans('backpack::crud.po.column.date_po'),
            'type' => 'date',
            'attributes' => [
                'placeholder' => trans('backpack::crud.po.field.date_po.placeholder'),
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::addField([
            'name' => 'job_name',
            'label' => trans('backpack::crud.po.column.job_name'),
            'type' => 'text',
            // 'wrapper'   => [
            //     'class' => 'form-group col-md-6'
            // ],
        ]);

        CRUD::addField([
            'name' => 'job_description',
            'label' => trans('backpack::crud.po.field.job_description.label'),
            'type' => 'textarea',
            'attributes' => [
                'placeholder' => trans('backpack::crud.po.field.job_description.placeholder')
            ]
            // 'wrapper'   => [
            //     'class' => 'form-group col-md-6'
            // ],
        ]);


        CRUD::addField([
            'name' => 'job_value',
            'label' => trans('backpack::crud.po.column.job_value'),
            'type' => 'mask',
            'mask' => '000.000.000.000.000.000',
            'mask_options' => [
                'reverse' => true
            ],
            'prefix' => 'Rp',
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::addField([
            'name' => 'tax_ppn',
            'label' => trans('backpack::crud.po.column.tax_ppn'),
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
            'name' => 'total_value_with_tax',
            'label' => trans('backpack::crud.po.column.total_value_with_tax'),
            'type' => 'number-disable-po',
            'mask' => '000.000.000.000.000.000',
            'mask_options' => [
                'reverse' => true
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.spk.field.total_value_with_tax.placeholder'),
            ],
              // optionals
            // 'attributes' => ["step" => "any"], // allow decimals
            'prefix'     => "Rp.",
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::addField([
            'name' => 'due_date',
            'label' => trans('backpack::crud.po.field.due_date.label'),
            'type' => 'date',
            'attributes' => [
                'placeholder' => trans('backpack::crud.po.field.field.due_date.placeholder'),
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::addField([
            'name'        => 'status',
            'label'       => trans('backpack::crud.po.field.status.label'),
            'type'        => 'select_from_array',
            'options'     => [
                '' => trans('backpack::crud.po.field.status.placeholder'),
                'open' => trans('backpack::crud.po.field.status.open'),
                'close' => trans('backpack::crud.po.field.status.close')
            ],
            'allows_null' => false,
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            // 'allows_multiple' => true, // OPTIONAL; needs you to cast this to array in your model;
        ]);

        CRUD::addField([
            'name' => 'document_path',
            'label' => trans('backpack::crud.po.column.document_path'),
            'type' => 'upload',
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
             'withFiles' => [
                'disk' => 'public',
                'path' => 'document_po',
                'deleteWhenEntryIsDeleted' => true,
            ],
        ]);


        /**
         * Fields can be defined using the fluent syntax:
         * - CRUD::field('price')->type('number');
         */
    }

    public function select2SubkonId()
    {
        $this->crud->hasAccessOrFail('create');

        $search = request()->input('q');
        $dataset = \App\Models\Subkon::select(['id', 'name'])
            ->where('name', 'LIKE', "%$search%")
            ->paginate(10);

        $results = [];
        foreach ($dataset as $item) {
            $results[] = [
                'id' => $item->id,
                'text' => $item->name,
            ];
        }
        return response()->json(['results' => $results]);
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

     protected function setupShowOperation()
    {
        $this->setupCreateOperation();

        // update field hidden
        CRUD::field('space')->remove();

        // update subkon id
        CRUD::field('subkon_id')->remove();
        CRUD::field([   // 1-n relationship
            'label'       => trans('backpack::crud.subkon.column.name'), // Table column heading
            'type'        => "select2_ajax_custom",
            'name'        => 'subkon_id', // the column that contains the ID of that connected entity
            'entity'      => 'subkon', // the method that defines the relationship in your Model
            'attribute'   => "name", // foreign key attribute that is shown to user
            'data_source' => backpack_url('vendor/select2-subkon-id'), // url to controller search function (with /{id} should return a single entry)
            'wrapper'   => [
                'class' => 'form-group col-md-12'
            ],
        ])->before('po_number');
        // update job_name
        CRUD::field('job_name')->remove();
        CRUD::field([
            'label'  => trans('backpack::crud.po.column.job_name'),
            'name' => 'job_name',
            'type'  => 'text',
            'wrapper' => [
                'class' => 'form-group col-md-12',
            ]
        ])->before('job_description');
        // update job_description
        CRUD::field('job_description')->remove();
        CRUD::field([
            'name' => 'job_description',
            'label' => trans('backpack::crud.po.field.job_description.label'),
            'type' => 'textarea',
            'wrapper' => [
                'class' => 'form-group col-md-12',
            ]
        ])->before('job_value');

        // load entry data
        $this->setupListOperation();

        // remove row number
        CRUD::column('row_number')->remove();

        // update document path
        CRUD::column('document_path')->remove();
        CRUD::column(
            [
                'label'  => trans('backpack::crud.po.column.document_path'),
                'name' => 'document_path',
                'type'  => 'text',
                 'wrapper'   => [
                    'element' => 'a', // the element will default to "a" so you can skip it here
                    'href' => function ($crud, $column, $entry, $related_key) {
                        if($entry->document_path != ''){
                            return url('storage/document_po/'.$entry->document_path);
                        }
                        return "javascript:void(0)";
                    },
                    'target' => '_blank',
                    // 'class' => 'some-class',
                ],
            ],
        );

        // update date_po
        CRUD::column('date_po')->remove();
        CRUD::column([
            'name' => 'date_po',
            'label' => trans('backpack::crud.po.column.date_po'),
            'type' => 'date',
            'format' => 'DD/MM/Y',
            'attributes' => [
                'placeholder' => trans('backpack::crud.po.field.date_po.placeholder'),
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ])->after('po_number');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function show($id)
    {
        $this->crud->hasAccessOrFail('show');

        // get entry ID from Request (makes sure its the last ID for nested resources)
        $id = $this->crud->getCurrentEntryId() ?? $id;

        // get the info for that entry (include softDeleted items if the trait is used)
        if ($this->crud->get('show.softDeletes') && in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($this->crud->model))) {
            $this->data['entry'] = $this->crud->getModel()->withTrashed()->findOrFail($id);
        } else {
            $this->data['entry'] = $this->crud->getEntryWithLocale($id);
        }

        $this->data['entry_value'] = $this->crud->getRowViews($this->data['entry']);
        $this->data['crud'] = $this->crud;

        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.preview').' '.$this->crud->entity_name;

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        // return view($this->crud->getShowView(), $this->data);
        return response()->json([
            'html' => view($this->crud->getShowView(), $this->data)->render()
        ]);
    }

    public function search()
    {
        $this->crud->hasAccessOrFail('list');

        $this->crud->applyUnappliedFilters();

        $start = (int) request()->input('start');
        $length = (int) request()->input('length');
        $search = request()->input('search');

        // check if length is allowed by developer
        if ($length && ! in_array($length, $this->crud->getPageLengthMenu()[0])) {
            return response()->json([
                'error' => 'Unknown page length.',
            ], 400);
        }

        // if a search term was present
        if ($search && $search['value'] ?? false) {
            // filter the results accordingly
            $this->crud->applySearchTerm($search['value']);
        }
        // start the results according to the datatables pagination
        if ($start) {
            $this->crud->skip($start);
        }
        // limit the number of results according to the datatables pagination
        if ($length) {
            $this->crud->take($length);
        }
        // overwrite any order set in the setup() method with the datatables order
        $this->crud->applyDatatableOrder();

        $entries = $this->crud->getEntries();

        // if show entry count is disabled we use the "simplePagination" technique to move between pages.
        if ($this->crud->getOperationSetting('showEntryCount')) {
            $totalEntryCount = (int) (request()->get('totalEntryCount') ?: $this->crud->getTotalQueryCount());
            $filteredEntryCount = $this->crud->getFilteredQueryCount() ?? $totalEntryCount;
        } else {
            $totalEntryCount = $length;
            $entryCount = $entries->count();
            $filteredEntryCount = $entryCount < $length ? $entryCount : $length + $start + 1;
        }

        // store the totalEntryCount in CrudPanel so that multiple blade files can access it
        $this->crud->setOperationSetting('totalEntryCount', $totalEntryCount);

        return $this->crud->getEntriesAsJsonForDatatables($entries, $totalEntryCount, $filteredEntryCount, $start);
    }

    public function exportExcel(Request $request){
        $name = "document-subkon-po".now()->format('Ymd_His').".xlsx";
        $type = $request->type;
        return response()->streamDownload(function () use($type){
            echo Excel::raw(new ExportVendorPo($type), \Maatwebsite\Excel\Excel::XLSX);
        }, $name, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $name . '"',
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Download Failure',
        ], 400);

    }

    public function exportPdf(Request $request){
        $type = $request->type;
        if($type == 'list_open'){
            $type_origin = 'open';
        }else if($type == 'list_close'){
            $type_origin = 'close';
        }else {
            $type_origin = 'all';
        }
        $items = new PurchaseOrder;

        if($type_origin != 'all'){
            $items = $items->where('status', strtolower($type_origin));
        }

        $items = $items->get();

        $pdf = Pdf::loadView('exports.vendor-po-pdf', compact('items'))->setPaper('A4', 'landscape');

        $fileName = 'vendor_po_' . now()->format('Ymd_His') . '.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

}
