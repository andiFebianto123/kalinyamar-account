<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Http\Requests\ClientPoRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class ClientPoCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ClientPoCrudController extends CrudController
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
        CRUD::setModel(\App\Models\ClientPo::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/client/po');
        CRUD::setEntityNameStrings(trans('backpack::crud.client_po.title_header'), trans('backpack::crud.client_po.title_header'));
    }

    public function index()
    {
        $this->crud->hasAccessOrFail('list');

        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? mb_ucfirst($this->crud->entity_name_plural);
        $this->data['title_modal_create'] = "PO Client";
        $this->data['title_modal_edit'] = "PO Client";
        $this->data['title_modal_delete'] = "PO Client";

        $breadcrumbs = [
            'Client' => backpack_url('client'),
            'PO' => backpack_url($this->crud->route)
        ];
        $this->data['breadcrumbs'] = $breadcrumbs;

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

    public function select2Client()
    {
        $this->crud->hasAccessOrFail('create');

        $search = request()->input('q');
        $dataset = \App\Models\Client::select(['id', 'name'])
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

        $profit_and_loss = request()->job_value - request()->price_total;

        request()->merge([
            'profit_and_loss' => $profit_and_loss,
        ]);


        $request = $this->crud->validateRequest();

        $this->crud->registerFieldEvents();

        DB::beginTransaction();
        try{

            $item = $this->crud->create($this->crud->getStrippedSaveRequest($request));
            $this->data['entry'] = $this->crud->entry = $item;

            \Alert::success(trans('backpack::crud.insert_success'))->flash();

            $this->crud->setSaveAction();

            DB::commit();
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

            $item = $this->crud->update(
                $request->get($this->crud->model->getKeyName()),
                $this->crud->getStrippedSaveRequest($request)
            );
            $this->data['entry'] = $this->crud->entry = $item;

            \Alert::success(trans('backpack::crud.update_success'))->flash();

            $this->crud->setSaveAction();

            DB::commit();

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

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        // CRUD::setFromDb(); // set columns from db columns.

        CRUD::disableResponsiveTable();

        /**
         * Columns can be defined using the fluent syntax:
         * - CRUD::column('price')->type('number');
         */

        $this->crud->addColumn([
            'name'      => 'row_number',
            'type'      => 'row_number',
            'label'     => 'No',
            'orderable' => false,
            'wrapper' => [
                'element' => 'strong',
            ]
        ])->makeFirstColumn();

        CRUD::column([
            // 1-n relationship
            'label' => trans('backpack::crud.client_po.column.client_id'),
            'type'      => 'select',
            'name'      => 'client_id', // the column that contains the ID of that connected entity;
            'entity'    => 'client', // the method that defines the relationship in your Model
            'attribute' => 'name', // foreign key attribute that is shown to user
            'model'     => "App\Models\Client", // foreign key model
            // OPTIONAL
            // 'limit' => 32, // Limit the number of characters shown
        ]);

        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.work_code'),
                'name' => 'work_code',
                'type'  => 'text'
            ],
        );


        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.po_number'),
                'name' => 'po_number',
                'type'  => 'text'
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.job_name'),
                'name' => 'job_name',
                'type'  => 'text'
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.job_value'),
                'name' => 'job_value',
                'type'  => 'number',
                'prefix' => "Rp.",
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
            ],
        );

        CRUD::column([
            'label'  => trans('backpack::crud.client_po.column.tax_ppn'),
            'name' => 'tax_ppn',
            'type'  => 'number',
            'suffix' => '%',
        ]);

        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.total_value_with_tax'),
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

        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.startdate_and_enddate'),
                'name' => 'start_date,end_date',
                'type'  => 'date_range_custom'
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.reimburse_type'),
                'name' => 'reimburse_type',
                'type'  => 'text'
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.price_total'),
                'name' => 'price_total',
                'type'  => 'number',
                'prefix' => "Rp.",
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.profit_and_loss'),
                'name' => 'profit_and_loss',
                'type'  => 'number',
                'prefix' => "Rp.",
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
            ],
        );

        CRUD::column([
            'name'   => 'document_path',
            'type'   => 'upload',
            'label'  => trans('backpack::crud.client_po.column.document_path'),
            'disk'   => 'public',
        ]);

        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.date_invoice'),
                'name' => 'date_invoice',
                'type'  => 'date'
            ],
        );

    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(ClientPoRequest::class);
        // CRUD::setFromDb(); // set fields from db columns.
        CRUD::field([   // 1-n relationship
            'label'       => trans('backpack::crud.client_po.field.client_id.label'), // Table column heading
            'type'        => "select2_ajax_custom",
            'name'        => 'client_id', // the column that contains the ID of that connected entity
            'entity'      => 'client', // the method that defines the relationship in your Model
            'attribute'   => "name", // foreign key attribute that is shown to user
            'data_source' => backpack_url('client/select2-client'), // url to controller search function (with /{id} should return a single entry)
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.client_po.field.client_id.placeholder'),
            ]
        ]);

        CRUD::addField([
            'name' => 'work_code',
            'label' => trans('backpack::crud.client_po.field.work_code.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.client_po.field.work_code.placeholder'),
            ]
        ]);

        CRUD::addField([
            'name' => 'po_number',
            'label' => trans('backpack::crud.client_po.field.po_number.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
                'placeholder' => trans('backpack::crud.client_po.field.po_number.placeholder')
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.client_po.field.po_number.placeholder')
            ]
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
            'name' => 'job_name',
            'label' => trans('backpack::crud.client_po.field.job_name.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-12',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.client_po.field.job_name.placeholder'),
            ]
        ]);

        CRUD::addField([
            'name' => 'job_value',
            'label' => trans('backpack::crud.client_po.field.job_value.label'),
            'type' => 'mask',
            'mask' => '000.000.000.000.000.000',
            'mask_options' => [
                'reverse' => true
            ],
            'prefix' => 'Rp',
            'wrapper'   => [
                'class' => 'form-group col-md-4',
            ],
            'attributes' => [
                'placeholder' => '000.000',
            ]
        ]);

        CRUD::addField([
            'name' => 'tax_ppn',
            'label' => trans('backpack::crud.client_po.field.tax_ppn.label'),
            'type' => 'number',
             // optionals
            'attributes' => ["step" => "any"], // allow decimals
            'prefix'     => "%",
            // 'suffix'     => ".00",
            'wrapper'   => [
                'class' => 'form-group col-md-2',
            ],
            'attributes' => [
                'placeholder' => '0',
            ]
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



        CRUD::field([   // date_range
            'name'  => 'start_date,end_date', // db columns for start_date & end_date
            'label' => trans('backpack::crud.client_po.field.startdate_and_enddate.label'),
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

        CRUD::field([
            'name'        => 'reimburse_type',
            'label'       => trans('backpack::crud.client_po.field.reimburse_type.label'),
            'type'        => 'select_from_array',
            'options'     => ['' => trans('backpack::crud.client_po.field.reimburse_type.placeholder'), 'REIMBURSE' => 'REIMBURSE', 'NON REIMBURSE' => 'NON REIMBURSE'],
            'allows_null' => false,
             'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            // 'allows_multiple' => true, // OPTIONAL; needs you to cast this to array in your model;
        ]);

        CRUD::addField([
            'name' => 'price_total',
            'label' => trans('backpack::crud.client_po.field.price_total.label'),
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

        CRUD::addField([
            'name' => 'profit_and_loss',
            'label' => trans('backpack::crud.client_po.field.profit_and_loss.label'),
            'type' => 'number_profit_loss',
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
                'disabled' => 'disabled',
            ]
        ]);

        CRUD::addField([
            'name' => 'document_path',
            'label' => trans('backpack::crud.client_po.field.document_path.label'),
            'type' => 'upload',
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
             'withFiles' => [
                'disk' => 'public',
                'path' => 'document_client_po',
                'deleteWhenEntryIsDeleted' => true,
            ],
        ]);

        CRUD::field([   // date_picker
            'name'  => 'date_invoice',
            'type'  => 'date_picker',
            'label' => trans('backpack::crud.client_po.field.date_invoice.label'),

            // optional:
            'date_picker_options' => [
                'language' => App::getLocale(),
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);




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

    protected function setupShowOperation()
    {
        $this->setupCreateOperation();
        CRUD::field('po_number')->remove();
        CRUD::field('tax_ppn')->remove();
        CRUD::field('space')->remove();
        CRUD::field([
            'name' => 'po_number',
            'label' => trans('backpack::crud.client_po.field.po_number.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-12',
                'placeholder' => trans('backpack::crud.client_po.field.po_number.placeholder')
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.client_po.field.po_number.placeholder')
            ]
        ])->after('work_code');
        CRUD::field('job_value')->remove();
        CRUD::field([
            'name' => 'job_value',
            'label' => trans('backpack::crud.client_po.field.job_value.label'),
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
        ])->after('job_name');


        $this->setupListOperation();
        CRUD::column('row_number')->remove();
        CRUD::column('tax_ppn')->remove();
        CRUD::column('document_path')->remove();
        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.field.document_path.label'),
                'name' => 'document_path',
                'type'  => 'text',
                 'wrapper'   => [
                    'element' => 'a', // the element will default to "a" so you can skip it here
                    'href' => function ($crud, $column, $entry, $related_key) {
                        if($entry->document_path != ''){
                            return url('storage/document_client_po/'.$entry->document_path);
                        }
                        return "javascript:void(0)";
                    },
                    'target' => '_blank',
                    // 'class' => 'some-class',
                ],
            ],
        )->after('profit_and_loss');
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
}
