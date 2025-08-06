<?php

namespace App\Http\Controllers\Admin;

use App\Models\Setting;
use App\Models\Quotation;
use App\Models\SetupClient;
use App\Models\QuotationCheck;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Exports\ExportExcel;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;


class QuotationCheckCrudController extends CrudController {
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        $this->crud->denyAllAccess(['create', 'update', 'delete', 'list', 'show']);
        CRUD::setModel(QuotationCheck::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/monitoring/quotation-check');
        CRUD::setEntityNameStrings(trans('backpack::crud.quotation_check.title_header'), trans('backpack::crud.quotation_check.title_header'));
        $user = backpack_user();
        $permissions = $user->getAllPermissions();
        if($permissions->whereIn('name', [
            'AKSES SEMUA VIEW PROJECT',
            'AKSES SEMUA MENU PROJECT',
            'AKSES SEMUA DATA PROYEKSI PEKERJAAN PROJECT'
        ])->count() > 0)
        {
            $this->crud->allowAccess(['list', 'show']);
        }

        if($permissions->whereIn('name',[
            'AKSES SEMUA MENU PROJECT',
            'AKSES SEMUA DATA PROYEKSI PEKERJAAN PROJECT'
        ])->count() > 0){
            $this->crud->allowAccess(['create', 'delete']);
        }
    }

    function index(){
        $this->crud->hasAccessOrFail('list');

         $this->card->addCard([
            'name' => 'quotation_panel',
            'line' => 'top',
            'view' => 'crud::components.card-tab',
            'params' => [
                'tabs' => [
                    // [
                    //     'name' => 'quotation',
                    //     'label' => trans('backpack::crud.project.tab.title_project'),
                    //     // 'class' => '',
                    //     'active' => true,
                    //     'view' => 'crud::components.datatable',
                    //     'params' => [
                    //         'crud_custom' => $this->crud,
                    //         'columns' => [
                    //             [
                    //                 'name'      => 'row_number',
                    //                 'type'      => 'row_number',
                    //                 'label'     => 'No',
                    //                 'orderable' => false,
                    //             ],
                    //             [
                    //                 'label' => trans('backpack::crud.quotation.column.no_rfq.label'),
                    //                 'type'      => 'text',
                    //                 'name'      => 'no_rfq',
                    //                 'orderable' => true,
                    //             ],
                    //             [
                    //                 'label' => trans('backpack::crud.quotation.column.name_project.label'),
                    //                 'type' => 'text',
                    //                 'name' => 'name_project',
                    //                 'orderable' => true,
                    //             ],
                    //             [
                    //                 'label' => trans('backpack::crud.quotation.column.rab.label'),
                    //                 'type' => 'text',
                    //                 'name' => 'rab',
                    //                 'orderable' => true,
                    //             ],
                    //             [
                    //                 'label' => trans('backpack::crud.quotation.column.rap.label'),
                    //                 'type' => 'text',
                    //                 'name' => 'rap',
                    //                 'orderable' => true,
                    //             ],
                    //             [
                    //                 'label' => trans('backpack::crud.quotation.column.client_id.label'),
                    //                 'type' => 'text',
                    //                 'name' => 'client_id',
                    //                 'orderable' => true,
                    //             ],
                    //             [
                    //                 'label' => trans('backpack::crud.quotation.column.pic.label'),
                    //                 'type' => 'text',
                    //                 'name' => 'pic',
                    //                 'orderable' => true,
                    //             ],
                    //             [
                    //                 'label' => trans('backpack::crud.quotation.column.user.label'),
                    //                 'type' => 'text',
                    //                 'name' => 'user',
                    //                 'orderable' => true,
                    //             ],
                    //             [
                    //                 'label' => trans('backpack::crud.quotation.column.closing_date.label'),
                    //                 'type' => 'text',
                    //                 'name' => 'closing_date',
                    //                 'orderable' => true,
                    //             ],
                    //             [
                    //                 'label' => trans('backpack::crud.quotation.column.status.label'),
                    //                 'type' => 'text',
                    //                 'name' => 'status',
                    //                 'orderable' => true,
                    //             ],
                    //             [
                    //                 'label' => trans('backpack::crud.quotation.column.information.label'),
                    //                 'type' => 'text',
                    //                 'name' => 'information',
                    //                 'orderable' => false,
                    //             ],
                    //         ],
                    //         'route' => backpack_url('/monitoring/quotation-check/search?tab=quotation'),
                    //     ],
                    // ],
                    [
                        'name' => 'quotation_check',
                        'label' => trans('backpack::crud.quotation_check.tab.quotation_check'),
                        'view' => 'crud::components.datatable',
                        'active' => true,
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
                                    'type' => 'text',
                                    'name' => 'name_project',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.quotation.column.rab.label'),
                                    'type' => 'text',
                                    'name' => 'rab',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.quotation.column.rap.label'),
                                    'type' => 'text',
                                    'name' => 'rap',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.quotation.column.client_id.label'),
                                    'type' => 'text',
                                    'name' => 'client_id',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.quotation.column.pic.label'),
                                    'type' => 'text',
                                    'name' => 'pic',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.quotation.column.user.label'),
                                    'type' => 'text',
                                    'name' => 'user',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.quotation.column.closing_date.label'),
                                    'type' => 'text',
                                    'name' => 'closing_date',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.quotation.column.status.label'),
                                    'type' => 'text',
                                    'name' => 'status',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.quotation.column.information.label'),
                                    'type' => 'text',
                                    'name' => 'information',
                                    'orderable' => false,
                                ],
                                [
                                    'name' => 'action',
                                    'type' => 'action',
                                    'label' =>  trans('backpack::crud.actions'),
                                ]
                            ],
                            'route' => backpack_url('/monitoring/quotation-check/search?tab=quotation_check'),
                            'route_export_pdf' => backpack_url('/monitoring/quotation-check/export-pdf?tab=quotation_check'),
                            'title_export_pdf' => 'Data_Proyeksi_Pekerjaan-check.pdf',
                            'route_export_excel' => backpack_url('/monitoring/quotation-check/export-excel?tab=quotation_check'),
                            'title_export_excel' => 'Data_Proyeksi_Pekerjaan-check.xlsx',
                        ]
                    ]
                ]
            ]
        ]);

        $this->card->addCard([
            'name' => 'quotation-plugin',
            'line' => 'top',
            'view' => 'crud::components.quotation-plugin',
            'parent_view' => 'crud::components.filter-parent',
            'params' => [
                'crud_custom' => $this->crud,
            ],
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
        $this->data['title_modal_create'] = trans('backpack::crud.quotation_check.title_modal_create');
        $this->data['title_modal_edit'] = trans('backpack::crud.quotation_check.title_modal_edit');
        $this->data['title_modal_delete'] = trans('backpack::crud.quotation_check.title_modal_delete');
        $this->data['cards'] = $this->card;

        $breadcrumbs = [
            trans('backpack::crud.menu.monitoring_project') => backpack_url('monitoring'),
            trans('backpack::crud.quotation_check.title_header') => backpack_url($this->crud->route)
        ];
        $this->data['breadcrumbs'] = $breadcrumbs;

        // $list = "crud::list-custom" ?? $this->crud->getListView();
        $list = "crud::list-blank" ?? $this->crud->getListView();
        return view($list, $this->data);
    }


    protected function setupListOperation()
    {
        $type = request()->tab;
        CRUD::disableResponsiveTable();
        $settings = Setting::first();

        if($type == 'quotation_check'){
            // CRUD::setModel(QuotationCheck::class);
            $this->crud->query = $this->crud->query
            ->join('quotations', 'quotations.id', '=', 'quotation_checks.quotation_id')
            ->join('setup_clients', 'setup_clients.id', '=', 'quotations.client_id');
            CRUD::addClause('select', [
                DB::raw("
                    quotations.*
                ")
            ]);
            if(request()->has('search')){
                if(strlen(trim(request()->search['value'])) > 0){
                    $search = request()->search['value'];
                    $this->crud->query = $this->crud->query
                    ->where(function($query) use ($search){
                        $query->where('quotations.no_rfq', 'like', '%'.$search.'%')
                        ->orWhere('quotations.name_project', 'like', '%'.$search.'%')
                        ->orWhere('quotations.rab', 'like', '%'.$search.'%')
                        ->orWhere('quotations.rap', 'like', '%'.$search.'%')
                        ->orWhere('setup_clients.name', 'like', '%'.$search.'%')
                        ->orWhere('quotations.pic', 'like', '%'.$search.'%')
                        ->orWhere('quotations.user', 'like', '%'.$search.'%')
                        ->orWhere('quotations.closing_date', 'like', '%'.$search.'%')
                        ->orWhere('quotations.status', 'like', '%'.$search.'%');
                    });
                }
            }

        }
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
                    'type'  => 'text'
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
            $query_clone = $this->crud->query->toBase()->clone();

            $outer_query = $query_clone->newQuery();
            $subQuery = $query_clone->cloneWithout(['limit', 'offset']);

            $totalEntryCount = $outer_query->select(DB::raw('count(*) as total_rows'))
            ->fromSub($subQuery, 'total_aggregator')->cursor()->first()->total_rows;
            $filteredEntryCount = $totalEntryCount;

            // $totalEntryCount = (int) (request()->get('totalEntryCount') ?: $this->crud->getTotalQueryCount());
            // $filteredEntryCount = $this->crud->getFilteredQueryCount() ?? $totalEntryCount;
        } else {
            $totalEntryCount = $length;
            $entryCount = $entries->count();
            $filteredEntryCount = $entryCount < $length ? $entryCount : $length + $start + 1;
        }

        // store the totalEntryCount in CrudPanel so that multiple blade files can access it
        $this->crud->setOperationSetting('totalEntryCount', $totalEntryCount);

        return $this->crud->getEntriesAsJsonForDatatables($entries, $totalEntryCount, $filteredEntryCount, $start);
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

    private function ruleValidation(){
        return [
            'quotation' => [
                'required',
                'array',
                'min:1',
                function($attr, $value, $fail){
                    foreach($value as $id_quo){
                        $quotation_check = QuotationCheck::where('quotation_id', $id_quo)->first();
                        if($quotation_check != null){
                            $fail(trans('backpack::crud.quotation.validate.quotation_check_exists'));
                        }
                    }
                }
            ],
        ];
    }

    protected function setupCreateOperation(){
        CRUD::setValidation($this->ruleValidation());
        $this->setupListOperation();
        CRUD::column(
            [
                'label'  => '',
                'name' => 'id',
                'type'  => 'bald',
            ],
        )->after('information');
        CRUD::column('row_number')->remove();
        $dataset = Quotation::whereNotExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('quotation_checks')
                ->whereColumn('quotation_checks.quotation_id', 'quotations.id');
        })
        ->orderBy('id', 'desc')
        ->get();

        $quotation_entry_value = [];

        foreach($dataset as $data){
            $quotation_entry_value[] = $this->crud->getRowViews($data);
        }

        CRUD::addField([
            'name' => 'voucher',
            'label' => '',
            'type' => 'quotation-list',
            'value' => $quotation_entry_value,
        ]);
    }

    public function store()
    {
        $this->crud->hasAccessOrFail('create');

        $request = $this->crud->validateRequest();

        $this->crud->registerFieldEvents();

        DB::beginTransaction();
        try{

            $event = [];
            $quotation = $request->quotation;

            foreach($quotation as $quo){
                $quotation_check = new QuotationCheck;
                $quotation_check->quotation_id = $quo;
                $quotation_check->save();
            }

            $event['crudTable-quotation_create_success'] = true;
            $event['crudTable-quotation_check_create_success'] = true;

            \Alert::success(trans('backpack::crud.insert_success'))->flash();

            $this->crud->setSaveAction();

            DB::commit();
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $quotation_check,
                    'events' => $event,
                ]);
            }
            return $this->crud->performSaveAction($quotation_check->getKey());
        }catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function destroy($id)
    {
        $this->crud->hasAccessOrFail('delete');

        // get entry ID from Request (makes sure its the last ID for nested resources)
        $id = $this->crud->getCurrentEntryId() ?? $id;

        $this->crud->delete($id);

        $messages['success'][] = trans('backpack::crud.delete_confirmation_message');
        $messages['events'] = [
            'crudTable-quotation_create_success' => true,
            'crudTable-quotation_check_create_success' => true,
        ];
        return response()->json($messages);
    }

    public function exportPdf(){
        $type = request()->tab;

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
            }
        }

        $title = 'Status Project - '.$type;

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
        $type = request()->tab;

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
