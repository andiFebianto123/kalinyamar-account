<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\SpkRequest;
use Illuminate\Support\Facades\DB;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class SpkCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class SpkCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Spk::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/vendor/spk-trans');
        CRUD::setEntityNameStrings('SPK', 'SPK');
    }

    public function index()
    {
        $this->crud->hasAccessOrFail('list');

        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? mb_ucfirst($this->crud->entity_name_plural);
        $this->data['title_modal_create'] = "SPK vendor (Subkon)";
        $this->data['title_modal_edit'] = "SPK Vendor (Subkon)";
        $this->data['title_modal_delete'] = "SPK Vendor (Subkon)";

        $breadcrumbs = [
            'Vendor (Subkon)' => backpack_url('vendor'),
            trans($this->data['title']) => backpack_url($this->crud->route)
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
            'label' => trans('backpack::crud.subkon.column.name'),
            'type'      => 'select',
            'name'      => 'subkon_id', // the column that contains the ID of that connected entity;
            'entity'    => 'subkon', // the method that defines the relationship in your Model
            'attribute' => 'name', // foreign key attribute that is shown to user
            'model'     => "App\Models\Subkon", // foreign key model
            // OPTIONAL
            // 'limit' => 32, // Limit the number of characters shown
        ]);

        CRUD::column(
            [
                'label'  => trans('backpack::crud.spk.column.no_spk'),
                'name' => 'no_spk',
                'type'  => 'text'
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.spk.column.date_spk'),
                'name' => 'date_spk',
                'type'  => 'date'
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.spk.column.job_name'),
                'name' => 'job_name',
                'type'  => 'text'
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.spk.column.job_description'),
                'name' => 'job_description',
                'type'  => 'textarea'
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.spk.column.job_value'),
                'name' => 'job_value',
                'type'  => 'number',
                'prefix' => "Rp.",
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
            ],
        );

        CRUD::column([
            'label'  => trans('backpack::crud.spk.column.tax_ppn'),
            'name' => 'tax_ppn',
            'type'  => 'number',
            'suffix' => '%',
        ]);

        CRUD::column(
            [
                'label'  => trans('backpack::crud.spk.column.total_value_with_tax'),
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

        CRUD::column([
            'name'   => 'document_path',
            'type'   => 'upload',
            'label'  => trans('backpack::crud.spk.column.document_path'),
            'disk'   => 'public',
        ]);

    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(SpkRequest::class);
        // CRUD::setFromDb(); // set fields from db columns.
        CRUD::field([   // 1-n relationship
            'label'       => trans('backpack::crud.subkon.column.name'), // Table column heading
            'type'        => "select2_ajax_custom",
            'name'        => 'subkon_id', // the column that contains the ID of that connected entity
            'entity'      => 'subkon', // the method that defines the relationship in your Model
            'attribute'   => "name", // foreign key attribute that is shown to user
            'data_source' => backpack_url('vendor/select2-subkon-id'), // url to controller search function (with /{id} should return a single entry)
            // 'attributes' => [
            //     'disabled'  => 'disabled',
            //     'placeholder' => trans('backpack::crud.spk.field.subkon_id.placeholder')
            // ],
            'placeholder' => trans('backpack::crud.spk.field.subkon_id.placeholder'),
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
            'name' => 'no_spk',
            'label' => trans('backpack::crud.spk.column.no_spk'),
            'type' => 'text',
            'attributes' => [
                'placeholder' => trans('backpack::crud.spk.field.no_spk.placeholder'),
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        CRUD::addField([
            'name' => 'date_spk',
            'label' => trans('backpack::crud.spk.column.date_spk'),
            'type' => 'date',
            'attributes' => [
                'placeholder' => trans('backpack::crud.spk.field.date_spk.placeholder'),
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::addField([
            'name' => 'job_name',
            'label' => trans('backpack::crud.po.column.job_name'),
            'type' => 'text',
            'attributes' => [
                'placeholder' => trans('backpack::crud.spk.field.job_name.placeholder'),
            ],
            // 'wrapper'   => [
            //     'class' => 'form-group col-md-6'
            // ],
        ]);

        CRUD::addField([
            'name' => 'job_description',
            'label' => trans('backpack::crud.spk.field.job_description.label'),
            'type' => 'textarea',
            'attributes' => [
                'placeholder' => trans('backpack::crud.spk.field.job_description.placeholder'),
            ],
            // 'wrapper'   => [
            //     'class' => 'form-group col-md-6'
            // ],
        ]);

        // CRUD::addField([
        //     'name' => 'job_value',
        //     'label' => trans('backpack::crud.spk.column.job_value'),
        //     'type' => 'number',
        //       // optionals
        //     'attributes' => [
        //         "step" => "any",
        //         'placeholder' => trans('backpack::crud.spk.field.job_value.placeholder'),
        //     ], // allow decimals
        //     'prefix'     => "Rp.",
        //     'wrapper'   => [
        //         'class' => 'form-group col-md-6'
        //     ],
        // ]);

        CRUD::addField([
            'name' => 'job_value',
            'label' => trans('backpack::crud.spk.column.job_value'),
            'type' => 'mask',
            'mask' => '000.000.000.000.000.000',
            'mask_options' => [
                'reverse' => true
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.spk.field.job_value.placeholder'),
            ],
            'prefix' => 'Rp',
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::addField([
            'name' => 'tax_ppn',
            'label' => trans('backpack::crud.spk.column.tax_ppn'),
            'type' => 'number',
             // optionals
            'attributes' => [
                "step" => "any",
                "placeholder" => trans('backpack::crud.spk.field.tax_ppn.placeholder'),
            ], // allow decimals
            'prefix'     => "%",
            // 'suffix'     => ".00",
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
            'value' => 0,
        ]);

        CRUD::addField([
            'name' => 'total_value_with_tax',
            'label' => trans('backpack::crud.po.column.total_value_with_tax'),
            'type' => 'number-disable-po',
            'mask' => '000.000.000.000.000.000',
            'mask_options' => [
                'reverse' => true
            ],
              // optionals
            'attributes' => [
                'placeholder' => trans('backpack::crud.spk.field.total_value_with_tax.placeholder'),
            ], // allow decimals
            'prefix'     => "Rp.",
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::addField([
            'name' => 'document_path',
            'label' => trans('backpack::crud.spk.field.document_path.label'),
            'type' => 'upload',
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
             'withFiles' => [
                'disk' => 'public',
                'path' => 'document_spk',
                'deleteWhenEntryIsDeleted' => true,
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
        CRUD::field('tax_ppn')->remove();
        CRUD::addField([
            'name' => 'tax_ppn',
            'label' => trans('backpack::crud.spk.column.tax_ppn'),
            'type' => 'number',
             // optionals
            'attributes' => [
                "step" => "any",
                "placeholder" => trans('backpack::crud.spk.field.tax_ppn.placeholder'),
            ], // allow decimals
            'prefix'     => "%",
            // 'suffix'     => ".00",
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);
        CRUD::field('tax_ppn')->after('job_value');
    }

     protected function setupShowOperation()
    {
        $this->setupUpdateOperation();

        CRUD::field('space')->remove();

        // urutan 1
        CRUD::field('subkon_id')->remove();
        CRUD::field([   // 1-n relationship
            'label'       => trans('backpack::crud.subkon.column.name'), // Table column heading
            'type'        => "select2_ajax_custom",
            'name'        => 'subkon_id', // the column that contains the ID of that connected entity
            'entity'      => 'subkon', // the method that defines the relationship in your Model
            'attribute'   => "name", // foreign key attribute that is shown to user
            'data_source' => backpack_url('vendor/select2-subkon-id'), // url to controller search function (with /{id} should return a single entry)
            // 'attributes' => [
            //     'disabled'  => 'disabled',
            //     'placeholder' => trans('backpack::crud.spk.field.subkon_id.placeholder')
            // ],
            'placeholder' => trans('backpack::crud.spk.field.subkon_id.placeholder'),
            'wrapper'   => [
                'class' => 'form-group col-md-12'
            ],
        ])->before('no_spk');


        $this->setupListOperation();

        CRUD::column('row_number')->remove();
        CRUD::column('document_path')->remove();

        CRUD::column(
            [
                'label'  => trans('backpack::crud.spk.column.document_path'),
                'name' => 'document_path',
                'type'  => 'text',
                 'wrapper'   => [
                    'element' => 'a', // the element will default to "a" so you can skip it here
                    'href' => function ($crud, $column, $entry, $related_key) {
                        if($entry->document_path != ''){
                            return url('storage/document_spk/'.$entry->document_path);
                        }
                        return "javascript:void(0)";
                    },
                    'target' => '_blank',
                    // 'class' => 'some-class',
                ],
            ],
        );

        CRUD::column('date_spk')->remove();
        CRUD::column([
            'name' => 'date_spk',
            'label' => trans('backpack::crud.spk.column.date_spk'),
            'type' => 'date',
            'format' => 'DD/MM/Y',
            'attributes' => [
                'placeholder' => trans('backpack::crud.spk.field.date_spk.placeholder'),
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ])->after('no_spk');
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
