<?php

namespace App\Http\Controllers\Admin;

use App\Http\Helpers\CustomHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\SubkonRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class SubkonCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class SubkonCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Subkon::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/vendor/subkon');
        CRUD::setEntityNameStrings(trans('backpack::crud.subkon.title_header'), trans('backpack::crud.subkon.title_header'));
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
        $this->crud->addColumn([
            'name'      => 'row_number',
            'type'      => 'row_number',
            'label'     => 'No',
            'orderable' => false,
            'wrapper' => [
                'element' => 'strong',
            ]
        ])->makeFirstColumn();

        CRUD::addColumn([
            'name'  => 'name',
            'label' => trans('backpack::crud.subkon.column.name'),
            'type'  => 'text',
        ]);

        CRUD::addColumn([
            'name'  => 'address',
            'label' => trans('backpack::crud.subkon.column.address'),
            'type'  => 'text',
        ]);

        CRUD::addColumn([
            'name'  => 'npwp',
            'label' => trans('backpack::crud.subkon.column.npwp'),
            'type'  => 'text',
        ]);

        CRUD::addColumn([
            'name'  => 'phone',
            'label' => trans('backpack::crud.subkon.column.phone'),
            'type'  => 'text',
        ]);

        CRUD::addColumn([
            'name'  => 'bank_name',
            'label' => trans('backpack::crud.subkon.column.bank_name'),
            'type'  => 'text',
        ]);

        CRUD::addColumn([
            'name'  => 'bank_account',
            'label' => trans('backpack::crud.subkon.column.bank_account'),
            'type'  => 'text',
        ]);
    }

    public function index()
    {
        $this->crud->hasAccessOrFail('list');


        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? mb_ucfirst($this->crud->entity_name_plural);
        $this->data['title_modal_create'] = "Data Vendor (Subkon)";
        $this->data['title_modal_edit'] = "Data Vendor (Subkon)";
        $this->data['title_modal_delete'] = "Vendor (Subkon)";

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
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(SubkonRequest::class);
        // CRUD::setFromDb(); // set fields from db columns.

        CRUD::addField([
            'name' => 'name',
            'label' => trans('backpack::crud.subkon.column.name'),
            'type' => 'text',
        ]);

        CRUD::addField([
            'name' => 'address',
            'label' => trans('backpack::crud.subkon.column.address'),
            'type' => 'text',
        ]);

        CRUD::addField([
            'name' => 'phone',
            'label' => trans('backpack::crud.subkon.column.phone'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::addField([
            'name' => 'npwp',
            'label' => trans('backpack::crud.subkon.column.npwp'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        // CRUD::addField([
        //     'name' => 'bank_name',
        //     'label' => trans('backpack::crud.subkon.column.bank_name'),
        //     'type' => 'text',
        //     'wrapper'   => [
        //         'class' => 'form-group col-md-6'
        //     ],
        // ]);

        CRUD::field([  // Select2
            'label'     => trans('backpack::crud.subkon.column.bank_name'),
            'type'      => 'select2_array',
            'name'      => 'bank_name',
            'options'   => CustomHelper::getBanks(), // force the related options to be a custom query, instead of all(); you can use this to filter the results show in the select
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ]
        ]);

        CRUD::addField([
            'name' => 'bank_account',
            'label' => trans('backpack::crud.subkon.column.bank_account'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);

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
}
