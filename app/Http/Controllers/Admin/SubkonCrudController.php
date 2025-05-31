<?php

namespace App\Http\Controllers\Admin;

use App\Http\Helpers\CustomHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\SubkonRequest;
use App\Models\PurchaseOrder;
use App\Models\Spk;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Dotenv\Parser\Entry;

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
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

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

        CRUD::disableResponsiveTable();
        $request = request();

        CRUD::addButtonFromView('top', 'filter_year', 'filter-year', 'beginning');

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

        CRUD::addColumn([
            'name'     => 'list_po_number',
            'label'    => trans('backpack::crud.subkon.column.list_po'),
            'type'     => 'custom_html',
            'value' => function($entry) {
                return "".$entry->purchase_orders->map(function($item, $key){
                    return "<li>".$item->po_number."</li>";
                })->implode('')."";
            },
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhereHas('purchase_orders', function ($q) use ($column, $searchTerm) {
                    $q->where('po_number', 'like', '%'.$searchTerm.'%');
                });
            }
        ]);

        CRUD::addColumn([
            'name'     => 'list_po_count',
            'label'    => trans('backpack::crud.subkon.column.count_po'),
            'type'     => 'custom_html',
            'value' => function($entry) {
                $count_data = $entry->purchase_orders->count();
                if($count_data > 0){
                    return $count_data;
                }
                return '-';
            },
            'orderable'  => true,
            'orderLogic' => function ($query, $column, $columnDirection) {
                $po = PurchaseOrder::select(DB::raw('subkon_id, count(po_number) as total_po'))
                ->groupBy('subkon_id');
                return $query->leftJoinSub($po, 'po', function($join){
                    $join->on('po.subkon_id', 'subkons.id');
                })->select('subkons.*')->orderBy('po.total_po', $columnDirection);
            }
        ]);

        CRUD::addColumn([
            'name'     => 'list_spk_number',
            'label'    => trans('backpack::crud.subkon.column.list_spk'),
            'type'     => 'custom_html',
            'value' => function($entry) {
                return "".$entry->spks->map(function($item, $key){
                    return "<li>".$item->no_spk."</li>";
                })->implode('')."";
            },
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhereHas('spks', function ($q) use ($column, $searchTerm) {
                    $q->where('no_spk', 'like', '%'.$searchTerm.'%');
                });
            }
        ]);

        CRUD::addColumn([
            'name'     => 'list_spk_count',
            'label'    => trans('backpack::crud.subkon.column.count_spk'),
            'type'     => 'custom_html',
            'value' => function($entry) {
                $count_data = $entry->spks->count();
                if($count_data > 0){
                    return $count_data;
                }
                return '-';
            },
            'orderable'  => true,
            'orderLogic' => function ($query, $column, $columnDirection) {
                $spk = Spk::select(DB::raw('subkon_id, count(no_spk) as total_spk'))
                ->groupBy('subkon_id');
                return $query->leftJoinSub($spk, 'spk', function($join){
                    $join->on('spk.subkon_id', 'subkons.id');
                })->select('subkons.*')->orderBy('spk.total_spk', $columnDirection);
            }
        ]);

        if($request->has('filter_year')){
            if($request->filter_year != 'all'){
                $filterYear = $request->filter_year;
                $this->crud->query = $this->crud->query
                ->where(function($query) use($filterYear){
                    $query->whereHas('purchase_orders', function($q) use($filterYear){
                        $q->where(DB::raw("YEAR(date_po)"), $filterYear);
                    })
                    ->orWhereHas('spks', function($q) use($filterYear){
                        $q->where(DB::raw("YEAR(date_spk)"), $filterYear);
                    });
                });
            }
        }

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
            'name' => 'npwp',
            'label' => trans('backpack::crud.subkon.column.npwp'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::addField([
            'name' => 'phone',
            'label' => trans('backpack::crud.subkon.column.phone'),
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

    protected function setupShowOperation()
    {
        $this->setupCreateOperation();
        $this->setupListOperation();
        CRUD::column('row_number')->remove();
        CRUD::column('list_po_count')->remove();
        CRUD::column('list_spk_count')->remove();
        CRUD::addField([
            'name' => 'list_po_number',
            'label' => trans('backpack::crud.subkon.column.list_po'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);
        CRUD::addField([
            'name' => 'list_spk_number',
            'label' => trans('backpack::crud.subkon.column.list_spk'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);
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
