<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
// use Backpack\CRUD\app\Http\Controllers\CrudController;
use App\Models\ClientPo;
use App\Models\InvoiceClient;
use App\Models\PurchaseOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Helpers\CustomHelper;
use Illuminate\Support\Facades\DB;
use App\Models\InvoiceClientDetail;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\CrudController;
use App\Http\Requests\InvoiceClientRequest;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class InvoiceClientCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class InvoiceClientCrudController extends CrudController
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
        CRUD::setModel(\App\Models\InvoiceClient::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/invoice-client');
        CRUD::setEntityNameStrings(trans('backpack::crud.menu.invoice_client'), trans('backpack::crud.menu.invoice_client'));
        CRUD::allowAccess('print');
    }

    public function select2ClientPo()
    {
        $this->crud->hasAccessOrFail('create');

        $search = request()->input('q');
        $dataset = \App\Models\ClientPo::select(['id', 'po_number'])
            ->where('po_number', 'LIKE', "%$search%")
            ->orWhere('work_code', 'like', "$search")
            ->orWhere('job_name', 'LIKE', "%$search%")
            ->paginate(10);

        $results = [];
        foreach ($dataset as $item) {
            $results[] = [
                'id' => $item->id,
                'text' => $item->po_number,
            ];
        }
        return response()->json(['results' => $results]);
    }

    public function selectedClientPo(){
        $this->crud->hasAccessOrFail('create');
        $id = request()->id;
        $entry = ClientPo::where('id', $id)->first();

        $entry->date_invoice = Carbon::createFromFormat('Y-m-d', $entry->date_invoice)->format('d/m/Y');
        $entry->job_value = CustomHelper::formatRupiah($entry->job_value);
        $entry->total_value_with_tax = CustomHelper::formatRupiah($entry->total_value_with_tax);
        $entry->client_name = $entry->client->name;
        return response()->json([
            'result' => $entry,
        ]);
    }

    public function index()
    {
        $this->crud->hasAccessOrFail('list');

        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? mb_ucfirst($this->crud->entity_name_plural);
        $this->data['title_modal_create'] = trans('backpack::crud.invoice_client.title_modal_create');
        $this->data['title_modal_edit'] = trans('backpack::crud.invoice_client.title_modal_edit');
        $this->data['title_modal_delete'] = trans('backpack::crud.invoice_client.title_modal_delete');

        // $breadcrumbs = [
        //     'Client' => backpack_url('vendor'),
        //     trans('backpack::crud.menu.list_client') => backpack_url($this->crud->route)
        // ];
        // $this->data['breadcrumbs'] = $breadcrumbs;

        $list = "crud::list-custom" ?? $this->crud->getListView();
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

    public function edit($id)
    {
        $this->crud->hasAccessOrFail('update');

        $id = $this->crud->getCurrentEntryId() ?? $id;

        $this->crud->registerFieldEvents();

        $entry = $this->crud->getEntryWithLocale($id);
        $entry->po_date = Carbon::createFromFormat('Y-m-d', $entry->po_date)->format('d/m/Y');
        // $entry->client_name = $entry->client->name;
        $entry->price_total_exclude_ppn = CustomHelper::formatRupiah($entry->price_total_exclude_ppn);
        $entry->price_total_include_ppn = CustomHelper::formatRupiah($entry->price_total_include_ppn);

        $entry->invoice_client_details_edit = $entry->invoice_client_details;
        $entry->client_name = $entry->client_po->client->name;
        $entry->nominal_exclude_ppn = $entry->price_total_exclude_ppn;
        $entry->nominal_include_ppn = $entry->price_total_include_ppn;
        $entry->send_invoice_normal = $entry->send_invoice_normal_date;
        $entry->send_invoice_revision = $entry->send_invoice_revision_date;

        $this->data['entry'] = $entry;

        $this->crud->setOperationSetting('fields', $this->crud->getUpdateFields());

        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.edit').' '.$this->crud->entity_name;
        $this->data['id'] = $id;

        return response()->json([
            'html' => view($this->crud->getEditView(), $this->data)->render()
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
        CRUD::disableResponsiveTable();
        CRUD::removeButtons(['delete', 'show', 'update'], 'line');

        CRUD::addButtonFromView('top', 'filter_paid_unpaid', 'filter-paid_unpaid', 'beginning');
        CRUD::addButtonFromView('line', 'show', 'show', 'end');
        CRUD::addButtonFromView('line', 'update', 'update', 'end');
        CRUD::addButtonFromView('line', 'print', 'print', 'end');
        CRUD::addButtonFromView('line', 'delete', 'delete', 'end');

        $this->crud->addColumn([
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
                'label'  => trans('backpack::crud.invoice_client.column.invoice_number'),
                'name' => 'invoice_number',
                'type'  => 'text'
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.invoice_client.column.name'),
                'name' => 'name',
                'type'  => 'text'
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.invoice_client.column.invoice_date'),
                'name' => 'invoice_date',
                'type'  => 'date'
            ],
        );

        CRUD::column([
            // 1-n relationship
            'label' => trans('backpack::crud.invoice_client.column.client_po_id'),
            'type'      => 'select',
            'name'      => 'client_po_id', // the column that contains the ID of that connected entity;
            'entity'    => 'client_po', // the method that defines the relationship in your Model
            'attribute' => 'po_number', // foreign key attribute that is shown to user
            'model'     => "App\Models\ClientPo", // foreign key model
            // OPTIONAL
            // 'limit' => 32, // Limit the number of characters shown
        ]);

        CRUD::column(
            [
                'label' => trans('backpack::crud.invoice_client.column.po_date'),
                'name' => 'po_date',
                'type' => 'date',
            ]
        );

        CRUD::column([
            // 1-n relationship
            'label' => trans('backpack::crud.invoice_client.column.client_id'),
            'type'      => 'closure',
            'name'      => 'client_name',
            'function' => function($entry) {
                return $entry->client_po->client->name;
            } // the column that contains the ID of that connected entity;
            // OPTIONAL
            // 'limit' => 32, // Limit the number of characters shown
        ]);

        CRUD::column(
            [
                'label'  => trans('backpack::crud.invoice_client.column.price_total_include_ppn'),
                'name' => 'price_total_include_ppn',
                'type'  => 'number',
                'prefix' => "Rp.",
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.invoice_client.column.price_total_exclude_ppn'),
                'name' => 'price_total_exclude_ppn',
                'type'  => 'number',
                'prefix' => "Rp.",
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
            ],
        );


        CRUD::column(
            [
                'label' => trans('backpack::crud.invoice_client.column.status'),
                'name' => 'status',
                'type' => 'text',
            ]
        );

        $request = request();
        if($request->has('filter_paid_status')){
            if($request->filter_paid_status != 'all'){
                $this->crud->query = $this->crud->query
            ->where('status', $request->filter_paid_status);
            }
        }

    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(InvoiceClientRequest::class);
        // CRUD::setFromDb(); // set fields from db columns.
        CRUD::addField([
            'name' => 'invoice_number',
            'label' => trans('backpack::crud.invoice_client.field.invoice_number.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-12',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.invoice_client.field.invoice_number.placeholder'),
            ]
        ]);

        CRUD::addField([   // date_picker
            'name'  => 'invoice_date',
            'type'  => 'date_picker',
            'label' => trans('backpack::crud.invoice_client.field.invoice_date.label'),

            // optional:
            'date_picker_options' => [
                'language' => App::getLocale(),
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.invoice_client.field.invoice_date.placeholder')
            ]
        ]);

        CRUD::addField([
            'name' => 'client_name',
            'label' => trans('backpack::crud.invoice_client.field.client_id.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.invoice_client.field.client_id.placeholder'),
                'disabled' => true,
            ]
        ]);

        CRUD::addField([
            'name' => 'address_po',
            'label' => trans('backpack::crud.invoice_client.field.address.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-12',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.invoice_client.field.address.placeholder'),
            ]
        ]);

        CRUD::addField([   // 1-n relationship
            'label'       => trans('backpack::crud.invoice_client.field.client_po_id.label'), // Table column heading
            'type'        => "select2_ajax_invoice_client",
            'name'        => 'client_po_id', // the column that contains the ID of that connected entity
            'entity'      => 'client_po', // the method that defines the relationship in your Model
            'attribute'   => 'po_number', // foreign key attribute that is shown to user
            'data_source' => backpack_url('invoice-client/select2-client-po'), // url to controller search function (with /{id} should return a single entry)
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'placeholder' => trans('backpack::crud.invoice_client.field.client_po_id.placeholder'),
        ]);

        CRUD::addField([   // date_picker
            'name'  => 'po_date',
            'type'  => 'text',
            'label' => trans('backpack::crud.invoice_client.field.po_date.label'),

            'suffix' => '<i class="la la-calendar"></i>',
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.invoice_client.field.po_date.placeholder'),
                'disabled' => true,
            ]
        ]);

        CRUD::addField([
            'name' => 'description',
            'label' => trans('backpack::crud.invoice_client.field.description.label'),
            'type' => 'textarea',
            'wrapper'   => [
                'class' => 'form-group col-md-12',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.invoice_client.field.description.placeholder'),
            ]
        ]);

        CRUD::addField([
            'name' => 'nominal_exclude_ppn',
            'label' => trans('backpack::crud.invoice_client.field.nominal_exclude_ppn.label'),
            'type' => 'text',
            'prefix' => 'Rp',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.invoice_client.field.nominal_exclude_ppn.placeholder'),
                'disabled' => true,
            ]
        ]);

        CRUD::addField([
            'name' => 'dpp_other',
            'label' => trans('backpack::crud.invoice_client.field.dpp_other.label'),
            'type' => 'dpp_other_invoice_client',
            'mask' => '000.000.000.000.000.000',
            'mask_options' => [
                'reverse' => true
            ],
            'prefix' => 'Rp',
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.invoice_client.field.dpp_other.placeholder'),
            ]
        ]);

        CRUD::addField([
            'name' => 'tax_ppn',
            'label' => trans('backpack::crud.invoice_client.field.tax_ppn.label'),
            'type' => 'number',
             // optionals
            'attributes' => ["step" => "any"], // allow decimals
            'prefix'     => "%",
            // 'suffix'     => ".00",
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => '0',
            ]
        ]);

        CRUD::addField([
            'name' => 'nominal_include_ppn',
            'label' => trans('backpack::crud.invoice_client.field.nominal_include_ppn.label'),
            'type' => 'text',
            'prefix' => 'Rp',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.invoice_client.field.nominal_include_ppn.placeholder'),
                'disabled' => true,
            ]
        ]);

        CRUD::addField([
            'name' => 'kdp',
            'label' => trans('backpack::crud.invoice_client.field.kdp.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.invoice_client.field.kdp.placeholder'),
            ]
        ]);

        CRUD::addField([
            'name' => 'space_1',
            'label' => '',
            'type' => 'hidden',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
        ]);


        CRUD::addField([   // date_picker
            'name'  => 'send_invoice_normal',
            'type'  => 'date_picker',
            'label' => trans('backpack::crud.invoice_client.field.send_invoice_normal.label'),

            // optional:
            'date_picker_options' => [
                'language' => App::getLocale(),
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::addField([   // date_picker
            'name'  => 'send_invoice_revision',
            'type'  => 'date_picker',
            'label' => trans('backpack::crud.invoice_client.field.send_invoice_revision.label'),

            // optional:
            'date_picker_options' => [
                'language' => App::getLocale(),
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);


        CRUD::addField([
            'name'        => 'status',
            'label'       => trans('backpack::crud.invoice_client.field.status.label'),
            'type'        => 'select_from_array',
            'options'     => ['' => trans('backpack::crud.invoice_client.field.status.placeholder'), 'Paid' => 'Paid', 'Unpaid' => 'Unpaid'],
            'allows_null' => false,
             'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            // 'allows_multiple' => true, // OPTIONAL; needs you to cast this to array in your model;
        ]);


        $id = request()->segment(3);

        if($id != 'create'){
            CRUD::addField([
                'name' => 'invoice_client_details_edit',
                'label' => trans('backpack::crud.invoice_client.field.item.label'),
                'type' => 'repeatable',
                'new_item_label'  => trans('backpack::crud.invoice_client.field.item.new_item_label'),
                'fields' => [
                    [
                        'name' => 'name',
                        'type' => 'text',
                        'label' => trans('backpack::crud.invoice_client.field.item.items.name.label'),
                        'wrapper' => [
                            'class' => 'form-group col-md-6',
                        ]
                    ],
                    [
                        'name' => 'price',
                        'type' => 'mask_repeat',
                        'label' => trans('backpack::crud.invoice_client.field.item.items.name.label'),
                        'wrapper' => [
                            'class' => 'form-group col-md-6',
                        ],
                        'prefix' => 'Rp',
                        'mask' => '000.000.000.000.000.000',
                        'mask_options' => [
                            'reverse' => true
                        ],
                    ],
                ]
            ]);
        }else{
            CRUD::addField([
                'name' => 'invoice_client_details',
                'label' => trans('backpack::crud.invoice_client.field.item.label'),
                'type' => 'repeatable',
                'new_item_label'  => trans('backpack::crud.invoice_client.field.item.new_item_label'),
                'fields' => [
                    [
                        'name' => 'name',
                        'type' => 'text',
                        'label' => trans('backpack::crud.invoice_client.field.item.items.name.label'),
                        'wrapper' => [
                            'class' => 'form-group col-md-6',
                        ]
                    ],
                    [
                        'name' => 'price',
                        'label' => trans('backpack::crud.invoice_client.field.item.items.price.label'),
                        'type' => 'mask',
                        'mask' => '000.000.000.000.000.000',
                        'mask_options' => [
                            'reverse' => true
                        ],
                        'prefix' => 'Rp',
                        'wrapper'   => [
                            'class' => 'form-group col-md-6'
                        ],
                    ]
                ]
            ]);
        }

        /**
         * Fields can be defined using the fluent syntax:
         * - CRUD::field('price')->type('number');
         */
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

    public function store()
    {
        $this->crud->hasAccessOrFail('create');

        $po = ClientPo::find(request()->client_po_id);

        request()->merge([
            'nominal_exclude_ppn' => $po->job_value,
            'nominal_include_ppn' => (int) $po->job_value + ($po->job_value * request()->tax_ppn / 100),
        ]);

        $request = $this->crud->validateRequest();

        $this->crud->registerFieldEvents();

        DB::beginTransaction();
        try{

            $total_price = 0;
            if($request->dpp_other){
                $total_price += $request->dpp_other;
            }
            if($request->nominal_include_ppn){
                $total_price += $request->nominal_include_ppn;
            }

            $items = $request->invoice_client_details;
            foreach($items as $item){
                $total_price += $item['price'];
            }

            // $item = $this->crud->create($this->crud->getStrippedSaveRequest($request));
            $invoice = new InvoiceClient();
            $invoice->invoice_number = $request->invoice_number;
            $invoice->name = 'invoice';
            $invoice->address_po = $request->address_po;
            $invoice->description = $request->description;
            $invoice->invoice_date = $request->invoice_date;
            $invoice->client_po_id = $request->client_po_id;
            $invoice->po_date = $po->date_invoice;
            $invoice->tax_ppn = $request->tax_ppn;
            $invoice->price_dpp = $request->dpp_other;
            $invoice->kdp = $request->kdp;
            $invoice->send_invoice_normal_date = $request->send_invoice_normal;
            $invoice->send_invoice_revision_date = $request->send_invoice_revision;
            $invoice->price_total_exclude_ppn = $request->nominal_exclude_ppn;
            $invoice->price_total_include_ppn = $request->nominal_include_ppn;
            $invoice->status = $request->status;
            $invoice->price_total = $total_price;
            $invoice->save();

            foreach($items as $item){
                $invoice_item = new InvoiceClientDetail();
                $invoice_item->invoice_client_id = $invoice->id;
                $invoice_item->name = $item['name'];
                $invoice_item->price = $item['price'];
                $invoice_item->save();
            }

            $this->data['entry'] = $this->crud->entry = $invoice;

            \Alert::success(trans('backpack::crud.insert_success'))->flash();

            $this->crud->setSaveAction();

            DB::commit();
            return $this->crud->performSaveAction($invoice->getKey());
        }catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function update($id)
    {
        $this->crud->hasAccessOrFail('update');

        $po = ClientPo::find(request()->client_po_id);

        request()->merge([
            'nominal_exclude_ppn' => $po->job_value,
            'nominal_include_ppn' => (int) $po->job_value + ($po->job_value * request()->tax_ppn / 100),
        ]);

        // execute the FormRequest authorization and validation, if one is required
        $request = $this->crud->validateRequest();

        // register any Model Events defined on fields
        $this->crud->registerFieldEvents();

        // update the row in the db
        DB::beginTransaction();
        try{

            $total_price = 0;
            if($request->dpp_other){
                $total_price += $request->dpp_other;
            }
            if($request->nominal_include_ppn){
                $total_price += $request->nominal_include_ppn;
            }

            $items = $request->invoice_client_details_edit;
            foreach($items as $item){
                $total_price += $item['price'];
            }

            // $item = $this->crud->create($this->crud->getStrippedSaveRequest($request));
            $invoice = InvoiceClient::where('id', $id)->first();
            $invoice->invoice_number = $request->invoice_number;
            $invoice->name = 'invoice';
            $invoice->address_po = $request->address_po;
            $invoice->description = $request->description;
            $invoice->invoice_date = $request->invoice_date;
            $invoice->client_po_id = $request->client_po_id;
            $invoice->po_date = $po->date_invoice;
            $invoice->tax_ppn = $request->tax_ppn;
            $invoice->price_dpp = $request->dpp_other;
            $invoice->kdp = $request->kdp;
            $invoice->send_invoice_normal_date = $request->send_invoice_normal;
            $invoice->send_invoice_revision_date = $request->send_invoice_revision;
            $invoice->price_total_exclude_ppn = $request->nominal_exclude_ppn;
            $invoice->price_total_include_ppn = $request->nominal_include_ppn;
            $invoice->status = $request->status;
            $invoice->price_total = $total_price;
            $invoice->save();

            InvoiceClientDetail::where('invoice_client_id', $id)->delete();

            foreach($items as $item){
                $invoice_item = new InvoiceClientDetail();
                $invoice_item->invoice_client_id = $invoice->id;
                $invoice_item->name = $item['name'];
                $invoice_item->price = $item['price'];
                $invoice_item->save();
            }

            $this->data['entry'] = $this->crud->entry = $invoice;

            DB::commit();
            // show a success message
            \Alert::success(trans('backpack::crud.update_success'))->flash();
            // save the redirect choice for next time
            $this->crud->setSaveAction();
            return $this->crud->performSaveAction($invoice);
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
        $this->setupListOperation();
        CRUD::column('row_number')->remove();
    }

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


    public function destroy($id)
    {
        $this->crud->hasAccessOrFail('delete');

        // get entry ID from Request (makes sure its the last ID for nested resources)
        $id = $this->crud->getCurrentEntryId() ?? $id;

        return $this->crud->delete($id);
    }


    public function printInvoice($id){

        $data = [];
        $data['header'] = InvoiceClient::where('id', $id)->first();
        $data['details'] = InvoiceClientDetail::where('invoice_client_id', $id)->get();

        $pdf = Pdf::loadView('exports.invoice-client-pdf-new', $data);
        return $pdf->stream('invoice.pdf');
        return view('exports.invoice-client-pdf-new');
    }

}
