<?php
namespace App\Http\Controllers\Admin;

use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;


class PurchaseOrderTabController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        // if(!$this->crud){
        //     $this->crud = new CrudPanel();

        //     $this->crud->setRequest(request());

        //     // $this->setupDefaults();
        //     // $this->setup();
        //     // $this->setupConfigurationForCurrentOperation();

        //     $this->card = app('component.card');
        //     $this->modal = app('component.modal');
        //     $this->script = app('component.script');
        // }

        $this->crud->setModel(\App\Models\PurchaseOrder::class);
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/vendor/purchase-order-tab');
        $this->crud->setEntityNameStrings('purchase order', 'purchase orders');
    }

    public function setupInline(){
        $this->crud = new CrudPanel();
        $this->crud = clone $this->crud;
        $this->crud->setModel(\App\Models\PurchaseOrder::class);
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/vendor/purchase-order-tab');
        $this->crud->setEntityNameStrings('purchase order', 'purchase orders');
    }

    protected function setupListOperation(){
        $this->crud->addColumn([
            'name'      => 'row_number',
            'type'      => 'row_number',
            'label'     => 'No',
            'orderable' => false,
            'wrapper' => [
                'element' => 'strong',
            ]
        ])->makeFirstColumn();

        $this->crud->addColumn([
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

        $this->crud->addColumn(
            [
                'label'  => trans('backpack::crud.po.column.po_number'),
                'name' => 'po_number',
                'type'  => 'text'
            ],
        );

        $this->crud->addColumn(
            [
                'label'  => trans('backpack::crud.po.column.date_po'),
                'name' => 'date_po',
                'type'  => 'date'
            ],
        );

        $this->crud->addColumn(
            [
                'label'  => trans('backpack::crud.po.column.job_name'),
                'name' => 'job_name',
                'type'  => 'text'
            ],
        );

        $this->crud->addColumn(
            [
                'label'  => trans('backpack::crud.po.column.job_description'),
                'name' => 'job_description',
                'type'  => 'textarea'
            ],
        );

        $this->crud->addColumn(
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

        $this->crud->addColumn([
            'label'  => trans('backpack::crud.po.column.tax_ppn'),
            'name' => 'tax_ppn',
            'type'  => 'number',
            'suffix' => '%',
        ]);

        $this->crud->addColumn(
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

        $this->crud->addColumn(
            [
                'label'  => trans('backpack::crud.po.column.due_date'),
                'name' => 'due_date',
                'type'  => 'date'
            ],
        );

        $this->crud->addColumn(
            [
                'label'  => trans('backpack::crud.po.column.status'),
                'name' => 'status',
                'type' => 'closure',
                'function' => function($entry){
                    return strtoupper($entry->status);
                }
            ],
        );

        $this->crud->addColumn([
            'name'   => 'document_path',
            'type'   => 'upload',
            'label'  => trans('backpack::crud.po.column.document_path'),
            'disk'   => 'public',
        ]);

        if(request()->has('tab')){
            $type = request()->get('tab');
            if($type == PurchaseOrder::OPEN){
                $this->crud->query = $this->crud->query
                ->where('status', PurchaseOrder::OPEN);
            }else if($type == PurchaseOrder::CLOSE){
                $this->crud->query = $this->crud->query
                ->where('status', PurchaseOrder::CLOSE);
            }
        }

    }

    public function get_crud(){
        $this->setupInline();
        $this->setupListOperation();
        return $this->crud;
    }

}
