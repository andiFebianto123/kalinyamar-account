<?php
namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Spk;
use App\Models\User;
use App\Models\Voucher;
use App\Models\Approval;
use App\Models\VoucherEdit;
use App\Models\PurchaseOrder;
use App\Http\Helpers\CustomHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\CrudController;
use App\Models\JournalEntry;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class VoucherCrudController extends CrudController {
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(Voucher::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/fa/voucher');
        CRUD::setEntityNameStrings('Voucher', 'Voucher');
        CRUD::allowAccess('print');
    }

    function total_voucher(){
        $data = Voucher::selectRaw('
            SUM(bill_value) as jumlah_exclude_ppn,
            SUM(total) as jumlah_include_ppn,
            SUM(payment_transfer) as jumlah_nilai_transfer
        ')->first();

        return response()->json([
            'total_exclude_ppn' => CustomHelper::formatRupiah($data->jumlah_exclude_ppn),
            'total_include_ppn' => CustomHelper::formatRupiah($data->jumlah_include_ppn),
            'total_nilai_transfer' => CustomHelper::formatRupiah($data->jumlah_nilai_transfer),
        ]);
    }


    function index(){
        $this->crud->hasAccessOrFail('list');

        $this->card->addCard([
            'name' => 'voucher',
            'line' => 'top',
            'view' => 'crud::components.card-tab',
            'params' => [
                'tabs' => [
                    [
                        'name' => 'voucher',
                        'label' => trans('backpack::crud.voucher.tab.title_voucher'),
                        // 'class' => '',
                        'active' => true,
                        'view' => 'crud::components.datatable',
                        'params' => [
                            'filter' => true,
                            'crud_custom' => $this->crud,
                            'columns' => [
                                [
                                    'name'      => 'row_number',
                                    'type'      => 'row_number',
                                    'label'     => 'No',
                                    'orderable' => false,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.no_voucher.label'),
                                    'type'      => 'text',
                                    'name'      => 'no_voucher',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.date_voucher.label'),
                                    'type' => 'text',
                                    'name' => 'date_voucher',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.bussines_entity_name.label'),
                                    'type' => 'text',
                                    'name' => 'bussines_entity_name',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.bill_number.label'),
                                    'type' => 'text',
                                    'name' => 'bill_number',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.bill_date.label'),
                                    'type' => 'text',
                                    'name' => 'bill_date',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.payment_description.label'),
                                    'type' => 'text',
                                    'name' => 'payment_description',
                                    'orderable' => true,
                                ],
                                 [
                                    'label' => trans('backpack::crud.voucher.column.voucher.bill_value.label'),
                                    'type' => 'text',
                                    'name' => 'bill_value',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.total.label'),
                                    'type' => 'text',
                                    'name' => 'total',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.payment_transfer.label'),
                                    'type' => 'text',
                                    'name' => 'payment_transfer',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.factur_status.label'),
                                    'type' => 'text',
                                    'name' => 'factur_status',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.bussines_entity_code.label'),
                                    'type' => 'text',
                                    'name' => 'bussines_entity_code',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.job_name.label'),
                                    'type' => 'text',
                                    'name' => 'job_name',
                                    'orderable' => true,
                                ],
                                 [
                                    'label' => trans('backpack::crud.voucher.column.voucher.account_id.label'),
                                    'type' => 'text',
                                    'name' => 'account_id',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.no_account.label'),
                                    'type' => 'text',
                                    'name' => 'no_account',
                                    'orderable' => true,
                                ],
                                 [
                                    'label' => trans('backpack::crud.voucher.column.voucher.payment_type.label'),
                                    'type' => 'text',
                                    'name' => 'payment_type',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.payment_status.label'),
                                    'type' => 'text',
                                    'name' => 'payment_status',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.due_date.label'),
                                    'type' => 'text',
                                    'name' => 'due_date',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.status.label'),
                                    'type' => 'text',
                                    'name' => 'status',
                                    'orderable' => true,
                                ],
                                [
                                    'name' => 'action',
                                    'type' => 'action',
                                    'label' =>  trans('backpack::crud.actions'),
                                ]
                            ],
                            'route' => backpack_url('/fa/voucher/search?tab=voucher'),
                        ],
                    ],
                    [
                        'name' => 'history_edit_voucher',
                        'label' => trans('backpack::crud.voucher.tab.title_voucher_edit'),
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
                                    'label' => trans('backpack::crud.voucher.column.voucher.no_voucher.label'),
                                    'type'      => 'text',
                                    'name'      => 'no_voucher',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher_edit.user_id.label'),
                                    'type'      => 'text',
                                    'name'      => 'user_id',
                                    'orderable' => false,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher_edit.date_update.label'),
                                    'type'      => 'text',
                                    'name'      => 'date_update',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher_edit.history_update.label'),
                                    'type'      => 'text',
                                    'name'      => 'history_update',
                                    'orderable' => false,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher_edit.no_apprv.label'),
                                    'type'      => 'text',
                                    'name'      => 'no_apprv',
                                    'orderable' => false,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher_edit.status.label'),
                                    'type'      => 'text',
                                    'name'      => 'status',
                                    'orderable' => false,
                                ],
                            ],
                            'route' => backpack_url('/fa/voucher/search?tab=voucher_edit'),
                        ]
                    ]
                ]
            ]
        ]);

        $this->card->addCard([
            'name' => 'voucher-plugin',
            'line' => 'top',
            'view' => 'crud::components.voucher-plugin',
            'parent_view' => 'crud::components.filter-parent',
            'params' => [],
        ]);


        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? mb_ucfirst($this->crud->entity_name_plural);
        $this->data['title_modal_create'] = "Voucher";
        $this->data['title_modal_edit'] = "Voucher";
        $this->data['title_modal_delete'] = "Voucher";
        $this->data['cards'] = $this->card;

        $breadcrumbs = [
            'FA' => backpack_url('fa'),
            'Voucher' => backpack_url($this->crud->route)
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

    protected function setupListOperation()
    {
        $type = request()->tab;
        if($type == 'voucher'){
            CRUD::setModel(Voucher::class);
            CRUD::disableResponsiveTable();

            CRUD::removeButtons(['delete', 'show', 'update'], 'line');
            CRUD::addButtonFromView('line', 'show', 'show', 'end');
            CRUD::addButtonFromView('line', 'update', 'update', 'end');
            CRUD::addButtonFromView('line', 'print', 'print', 'end');
            CRUD::addButtonFromView('line', 'delete', 'delete', 'end');
            CRUD::addButtonFromView('line', 'approve_button', 'approve_button', 'end');


            $user_id = backpack_user()->id;
            $user_approval = \App\Models\User::permission(['APPROVE VOUCHER', 'APPROVE EDIT VOUCHER'])
            ->where('id', $user_id)
            ->get();

            // voucher_edit_terbaru
            $v_e = DB::table('voucher_edit')
            ->select(DB::raw('MAX(id) as id'), 'voucher_id')
            ->groupBy('voucher_id');

            $this->crud->query = $this->crud->query
            ->leftJoin('accounts', 'accounts.id', '=', 'vouchers.account_id')
            ->leftJoinSub($v_e, 'v_e', function ($join) {
                $join->on('v_e.voucher_id', '=', 'vouchers.id');
            })
            ->leftJoin('voucher_edit', 'voucher_edit.id', '=', 'v_e.id');

            if($user_approval->count() > 0){
                $this->crud->query = $this->crud->query
                ->leftJoin('approvals', function ($join) use($user_id){
                    $join->on('approvals.model_id', '=', 'voucher_edit.id')
                        ->where('approvals.model_type', 'App\\Models\\VoucherEdit')
                        ->where('approvals.user_id', $user_id);
                });
            }else{
                $a_p = DB::table('approvals')
                ->select(DB::raw('MAX(id) as id'), 'model_type', 'model_id')
                ->groupBy('model_type', 'model_id');

                $this->crud->query = $this->crud->query
                ->leftJoinSub($a_p, 'a_p', function ($join) {
                    $join->on('a_p.model_id', '=', 'voucher_edit.id')
                    ->where('a_p.model_type', '=', DB::raw('"App\\\\Models\\\\VoucherEdit"'));
                })
                ->leftJoin('approvals', 'approvals.id', '=', 'a_p.id');
            }


            $request = request();

            if(trim($request->columns[1]['search']['value']) != ''){
                // dd(trim($request->columns[1]['search']['value']));
                $this->crud->query = $this->crud->query
                ->orWhere('no_voucher', 'like', '%'.$request->columns[1]['search']['value'].'%');
            }

            if(trim($request->columns[2]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->orWhere('date_voucher', 'like', '%'.$request->columns[1]['search']['value'].'%');
            }

            if(trim($request->columns[3]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->orWhere('bussines_entity_name', 'like', '%'.$request->columns[1]['search']['value'].'%');
            }

            if(trim($request->columns[4]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->orWhere('bill_number', 'like', '%'.$request->columns[1]['search']['value'].'%');
            }

            if(trim($request->columns[5]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->orWhere('bill_date', 'like', '%'.$request->columns[1]['search']['value'].'%');
            }

            if(trim($request->columns[6]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->orWhere('payment_description', 'like', '%'.$request->columns[1]['search']['value'].'%');
            }

            if(trim($request->columns[7]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->orWhere('bill_value', 'like', '%'.$request->columns[1]['search']['value'].'%');
            }

            if(trim($request->columns[8]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->orWhere('total', 'like', '%'.$request->columns[1]['search']['value'].'%');
            }

            if(trim($request->columns[9]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->orWhere('payment_transfer', 'like', '%'.$request->columns[1]['search']['value'].'%');
            }

            if(trim($request->columns[10]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->orWhere('factur_status', 'like', '%'.$request->columns[1]['search']['value'].'%');
            }

            if(trim($request->columns[11]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->orWhere('bussines_entity_code', 'like', '%'.$request->columns[1]['search']['value'].'%');
            }

            if(trim($request->columns[12]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->orWhere('job_name', 'like', '%'.$request->columns[1]['search']['value'].'%');
            }

            if(trim($request->columns[13]['search']['value']) != ''){
                $search = trim($request->columns[13]['search']['value']);
                $this->crud->query = $this->crud->query
                ->orWhere(function($q) use($search){
                    $q->where('accounts.code', 'LIKE', '%'.$search.'%')
                    ->orWhere('accounts.name', 'LIKE', "%".$search."%");
                });
            }

            if(trim($request->columns[14]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->orWhere('no_account', 'like', '%'.$request->columns[1]['search']['value'].'%');
            }

            if(trim($request->columns[15]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->orWhere('payment_type', 'like', '%'.$request->columns[1]['search']['value'].'%');
            }

            if(trim($request->columns[16]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->orWhere('payment_status', 'like', '%'.$request->columns[1]['search']['value'].'%');
            }

            if(trim($request->columns[17]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->orWhere('due_date', 'like', '%'.$request->columns[1]['search']['value'].'%');
            }

            if(trim($request->columns[18]['search']['value']) != ''){
                $status_search = trim($request->columns[18]['search']['value']);
                $this->crud->query = $this->crud->query
                ->orWhere('approvals.status', 'like', '%'.$status_search.'%');
            }

            CRUD::addClause('select', [
                DB::raw("
                    vouchers.*,
                    accounts.name as account_name,
                    accounts.code as account_code,
                    voucher_edit.id as voucer_edit_id,
                    approvals.status as approval_status,
                    approvals.user_id as approval_user_id,
                    approvals.no_apprv as approval_no_apprv
                ")
            ]);


            CRUD::addColumn([
                'name'      => 'row_number',
                'type'      => 'row_number',
                'label'     => 'No',
                'orderable' => false,
                'wrapper' => [
                    'element' => 'strong',
                ]
            ])->makeFirstColumn();
            CRUD::column([
                'label'  => '',
                'name' => 'no_voucher',
                'type'  => 'text',
                'searchLogic' => function ($query, $column, $searchTerm) {
                    // $query->orWhereHas('client_po', function ($q) use ($column, $searchTerm) {
                    //     $q->where('po_number', 'like', '%'.$searchTerm.'%');
                    // });
                }
            ]);
            CRUD::column([
                'label'  => '',
                'name' => 'date_voucher',
                'type'  => 'date',
                'format' => 'D MMM Y'
            ]);
            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'bussines_entity_name',
                    'type'  => 'text'
                ],
            );
            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'bill_number',
                    'type'  => 'text'
                ],
            );
            CRUD::column([
                'label'  => '',
                'name' => 'bill_date',
                'type'  => 'date',
                'format' => 'D MMM Y'
            ]);
            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'payment_description',
                    'type'  => 'text'
                ],
            );
            CRUD::column([
                'label'  => '',
                'name' => 'bill_value',
                'type'  => 'number',
                'prefix' => "Rp.",
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
            ]);
            CRUD::column([
                'label'  => '',
                'name' => 'total',
                'type'  => 'number',
                'prefix' => "Rp.",
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
            ]);
            CRUD::column([
                'label'  => '',
                'name' => 'payment_transfer',
                'type'  => 'number',
                'prefix' => "Rp.",
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
            ]);
            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'factur_status',
                    'type'  => 'text'
                ],
            );
            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'bussines_entity_code',
                    'type'  => 'text'
                ],
            );
            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'job_name',
                    'type'  => 'text'
                ],
            );
            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'account_id',
                    'type'  => 'closure',
                    'function' => function($entry){
                        return $entry->account->code ." - ".$entry->account->name;
                    }
                ],
            );
            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'no_account',
                    'type'  => 'text'
                ],
            );
            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'payment_type',
                    'type'  => 'text'
                ],
            );
            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'payment_status',
                    'type'  => 'text'
                ],
            );
            CRUD::column([
                'label'  => '',
                'name' => 'due_date',
                'type'  => 'date',
                'format' => 'D MMM Y'
            ]);
            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'status',
                    'type'  => 'approval-voucher',
                ],
            );
        }else if($type == 'voucher_edit'){
            CRUD::setModel(VoucherEdit::class);
            CRUD::disableResponsiveTable();

            $a_p = DB::table('approvals')
            ->select(DB::raw('MAX(id) as id'), 'model_type', 'model_id')
            ->groupBy('model_type', 'model_id');

            $this->crud->query = $this->crud->query
            ->leftJoinSub($a_p, 'a_p', function ($join) {
                $join->on('a_p.model_id', '=', 'voucher_edit.id')
                ->where('a_p.model_type', '=', DB::raw('"App\\\\Models\\\\VoucherEdit"'));
            })
            ->leftJoin('approvals', 'approvals.id', '=', 'a_p.id')
            ->leftJoin('vouchers', 'vouchers.id', '=', 'voucher_edit.voucher_id');

            CRUD::addClause('select', [
                DB::raw("
                    voucher_edit.*,
                    vouchers.no_voucher,
                    approvals.no_apprv as approval_no_apprv,
                    approvals.status as approval_status,
                    'voucher_edit' as type
                ")
            ]);

            CRUD::addColumn([
                'name'      => 'row_number',
                'type'      => 'row_number',
                'label'     => 'No',
                'orderable' => false,
                'wrapper' => [
                    'element' => 'strong',
                ]
            ])->makeFirstColumn();

            CRUD::column([
                'label'  => '',
                'name' => 'no_voucher',
                'type'  => 'text',
            ]);

            CRUD::column([
                'label'  => '',
                'name' => 'user_id',
                'type'  => 'closure',
                'function' => function($entry){
                    return $entry->user->name;
                }
            ]);

            CRUD::column([
                'label'  => '',
                'name' => 'date_update',
                'type'  => 'date',
                'format' => 'DD MMM YYYY HH:mm'
            ]);

            CRUD::column([
                'label'  => '',
                'name' => 'history_update',
                'type'  => 'text',
            ]);

            CRUD::column([
                'label'  => '',
                'name' => 'no_apprv',
                'type'  => 'closure',
                'function' => function($entry){
                    return 'Final - Approver User';
                }
            ]);

            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'status',
                    'type'  => 'approval-voucher',
                ],
            );

        }
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

    public function select2_no_po_spk(){
        $q = request()->q;
        $po = PurchaseOrder::leftJoin('subkons', 'subkons.id', '=', 'purchase_orders.subkon_id')
        ->select(DB::raw("
            subkons.name as name_company,
            subkons.bank_name as bank_name,
            subkons.bank_account as bank_account,
            purchase_orders.id as id,
            purchase_orders.po_number as no_po_spk,
            purchase_orders.date_po as date_po_spk,
            'po' as type
        "))->where('purchase_orders.po_number', 'like', "%$q%");

        $spk = Spk::leftJoin('subkons', 'subkons.id', '=', 'spk.subkon_id')
        ->select(DB::raw("
            subkons.name as name_company,
            subkons.bank_name as bank_name,
            subkons.bank_account as bank_account,
            spk.id as id,
            spk.no_spk as no_po_spk,
            spk.date_spk as date_po_spk,
            'spk' as type
        "))->where('spk.no_spk', 'like', "%$q%");

        $union = $po->unionAll($spk)
        ->paginate(20);

        $results = [];
        foreach ($union as $item) {
            $item->date_po_spk_str = Carbon::parse($item->date_po_spk)->format('d/m/Y');
            $results[] = [
                'id' => $item->id,
                'text' => $item->no_po_spk,
                'data' => $item,
            ];
        }
        return response()->json(['results' => $results]);
    }

    public function ruleValidation(){

        $rule = [
            'no_payment' => 'required|max:150',
            'account_id' => 'required|exists:accounts,id',
            'no_voucher' => 'required|max:120|unique:vouchers,no_voucher,'. request('id'),
            'work_code' => 'required|max:30',
            'for_voucher' => 'required',
            'date_voucher' => 'required|date',
            'bussines_entity_code' => 'required|max:30',
            'bussines_entity_type' => 'required|max:30',
            'bill_number' => 'required|max:50',
            'bill_date' => 'required|date',
            'date_receipt_bill' => 'required|date',
            'payment_description' => 'required',
            'no_po_spk' => 'required|numeric',
            'bill_value' => 'required|numeric',
            'due_date' => 'required|date',
            'factur_status' => 'required',
            'payment_type' => 'required|max:50',
            'payment_status' => 'required|max:50',
            'priority' => 'required|max:50',
        ];

        if(request()->has('id')){

        }

        return $rule;
    }

    protected function setupCreateOperation(){
        CRUD::setValidation($this->ruleValidation());

        CRUD::addField([
            'name' => 'no_payment',
            'label' => trans('backpack::crud.voucher.field.no_payment.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.voucher.field.no_payment.placeholder'),
            ]
        ]);

        CRUD::addField([
            'label'       => trans('backpack::crud.voucher.field.account_id.label'), // Table column heading
            'type'        => "select2_ajax_custom",
            'name'        => 'account_id',
            'entity'      => 'account',
            'model'       => 'App\Models\Account',
            'attribute'   => "name",
            'data_source' => backpack_url('account/select2-account'),
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.voucher.field.account_id.placeholder'),
            ]
        ]);

        CRUD::addField([
            'name' => 'no_voucher',
            'label' => trans('backpack::crud.voucher.field.no_voucher.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.voucher.field.no_voucher.placeholder'),
            ]
        ]);

        CRUD::addField([
            'name' => 'work_code',
            'label' => trans('backpack::crud.voucher.field.work_code.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.voucher.field.work_code.placeholder'),
            ]
        ]);

        CRUD::addField([
            'name' => 'job_name',
            'label' => trans('backpack::crud.voucher.field.job_name.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-12',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.voucher.field.job_name.placeholder'),
            ]
        ]);

        CRUD::addField([
            'name' => 'for_voucher',
            'label' => trans('backpack::crud.voucher.field.for_voucher.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.voucher.field.for_voucher.placeholder'),
            ]
        ]);

        CRUD::addField([   // date_picker
            'name'  => 'date_voucher',
            'type'  => 'date_picker',
            'label' => trans('backpack::crud.voucher.field.date_voucher.label'),

            // optional:
            'date_picker_options' => [
                'language' => App::getLocale(),
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::addField([
            'name' => 'bussines_entity_code',
            'label' => trans('backpack::crud.voucher.field.bussines_entity_code.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.voucher.field.bussines_entity_code.placeholder'),
            ]
        ]);

        CRUD::addField([  // Select2
            'label'     => trans('backpack::crud.voucher.field.bussines_entity_type.label'),
            'type'      => 'select2_array',
            'name'      => 'bussines_entity_type',
            'options'   => [
                '' => trans('backpack::crud.voucher.field.bussines_entity_type.placeholder'),
                'PT' => 'PT',
                'CV' => 'CV',
            ], // force the related options to be a custom query, instead of all(); you can use this to filter the results show in the select
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ]
        ]);

        CRUD::addField([
            'name' => 'bussines_entity_name',
            'label' => trans('backpack::crud.voucher.field.bussines_entity_name.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-12',
            ],
            'attributes' => [
                'disabled' => true,
                'placeholder' => trans('backpack::crud.voucher.field.bussines_entity_name.placeholder'),
            ]
        ]);

        CRUD::addField([
            'name' => 'bill_number',
            'label' => trans('backpack::crud.voucher.field.bill_number.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                // 'disabled' => true,
                'placeholder' => trans('backpack::crud.voucher.field.bill_number.placeholder'),
            ]
        ]);

        CRUD::addField([   // date_picker
            'name'  => 'bill_date',
            'type'  => 'date_picker',
            'label' => trans('backpack::crud.voucher.field.bill_date.label'),

            // optional:
            'date_picker_options' => [
                'language' => App::getLocale(),
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::addField([   // date_picker
            'name'  => 'date_receipt_bill',
            'type'  => 'date_picker',
            'label' => trans('backpack::crud.voucher.field.date_receipt_bill.label'),

            // optional:
            'date_picker_options' => [
                'language' => App::getLocale(),
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::addField([
            'name' => 'payment_description',
            'label' => trans('backpack::crud.voucher.field.payment_description.label'),
            'type' => 'textarea',
            'attributes' => [
                'placeholder' => trans('backpack::crud.voucher.field.payment_description.placeholder'),
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-12'
            ],
        ]);

        CRUD::addField([
            'label'       => trans('backpack::crud.voucher.field.no_po_spk.label'), // Table column heading
            'type'        => "select2_ajax_po_spk",
            'name'        => 'no_po_spk',
            'entity'      => 'account',
            'model'       => 'App\Models\Account',
            'attribute'   => "name",
            'data_source' => backpack_url('fa/voucher/select2-po-spk'),
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.voucher.field.no_po_spk.placeholder'),
            ]
        ]);

        CRUD::addField([   // date_picker
            'name'  => 'date_po_spk',
            'type'  => 'text',
            'label' => trans('backpack::crud.voucher.field.date_po_spk.label'),

            // optional:
            'date_picker_options' => [
                'language' => App::getLocale(),
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
            'suffix' => '<span class="la la-calendar"></span>',
            'attributes' => [
                'disabled' => true,
            ]
        ]);

        CRUD::addField([
            'name' => 'bill_value',
            'label' =>  trans('backpack::crud.voucher.field.bill_value.label'),
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
            'label' => trans('backpack::crud.voucher.field.tax_ppn.label'),
            'type' => 'number',
             // optionals
            'attributes' => ["step" => "any"], // allow decimals
            'prefix'     => "%",
            // 'suffix'     => ".00",
            'wrapper'   => [
                'class' => 'form-group col-md-4'
            ],
        ]);

        CRUD::addField([
            'name' => 'total',
            'label' =>  trans('backpack::crud.voucher.field.total.label'),
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
                'disabled' => true,
                'placeholder' => '000.000',
            ]
        ]);

        CRUD::addField([
            'name' => 'pph_23',
            'label' => trans('backpack::crud.voucher.field.pph_23.label'),
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
            'name' => 'discount_pph_23',
            'label' =>  trans('backpack::crud.voucher.field.discount_pph_23.label'),
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

        CRUD::addField([
            'name' => 'pph_4',
            'label' => trans('backpack::crud.voucher.field.pph_4.label'),
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
            'name' => 'discount_pph_4',
            'label' =>  trans('backpack::crud.voucher.field.discount_pph_4.label'),
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

        CRUD::addField([
            'name' => 'pph_21',
            'label' => trans('backpack::crud.voucher.field.pph_21.label'),
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
            'name' => 'discount_pph_21',
            'label' =>  trans('backpack::crud.voucher.field.discount_pph_21.label'),
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

        CRUD::addField([
            'name' => 'payment_transfer',
            'label' =>  trans('backpack::crud.voucher.field.payment_transfer.label'),
            'type' => 'mask',
            'mask' => '000.000.000.000.000.000',
            'mask_options' => [
                'reverse' => true
            ],
            'prefix' => 'Rp',
            'wrapper'   => [
                'class' => 'form-group col-md-12',
            ],
            'attributes' => [
                'disabled' => true,
                'placeholder' => '000.000',
            ]
        ]);

        CRUD::addField([   // date_picker
            'name'  => 'due_date',
            'type'  => 'date_picker',
            'label' => trans('backpack::crud.voucher.field.due_date.label'),

            // optional:
            'date_picker_options' => [
                'language' => App::getLocale(),
            ],
            'wrapper'   => [
                'disabled' => true,
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::addField([  // Select2
            'label'     => trans('backpack::crud.voucher.field.factur_status.label'),
            'type'      => 'select2_array',
            'name'      => 'factur_status',
            'options'   => [
                '' => trans('backpack::crud.voucher.field.factur_status.placeholder'),
                'ADA' => 'ADA',
                'TIDAK ADA' => 'TIDAK ADA',
            ], // force the related options to be a custom query, instead of all(); you can use this to filter the results show in the select
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ]
        ]);

         CRUD::addField([
            'name' => 'no_factur',
            'label' => trans('backpack::crud.voucher.field.no_factur.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.voucher.field.no_factur.placeholder'),
            ]
        ]);

        CRUD::addField([   // date_picker
            'name'  => 'date_factur',
            'type'  => 'date_picker',
            'label' => trans('backpack::crud.voucher.field.date_factur.label'),

            // optional:
            'date_picker_options' => [
                'language' => App::getLocale(),
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::addField([
            'name' => 'bank_name',
            'label' => trans('backpack::crud.voucher.field.bank_name.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'disabled' => true,
                'placeholder' => trans('backpack::crud.voucher.field.bank_name.placeholder'),
            ]
        ]);

        CRUD::addField([
            'name' => 'no_account',
            'label' => trans('backpack::crud.voucher.field.no_account.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'disabled' => true,
                'placeholder' => trans('backpack::crud.voucher.field.no_account.placeholder'),
            ]
        ]);

        CRUD::addField([  // Select2
            'label'     => trans('backpack::crud.voucher.field.payment_type.label'),
            'type'      => 'select2_array',
            'name'      => 'payment_type',
            'options'   => [
                '' => trans('backpack::crud.voucher.field.payment_type.placeholder'),
                'RUTIN' => 'RUTIN',
                'NON RUTIN' => 'NON RUTIN',
            ], // force the related options to be a custom query, instead of all(); you can use this to filter the results show in the select
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ]
        ]);

        CRUD::addField([  // Select2
            'label'     => trans('backpack::crud.voucher.field.payment_status.label'),
            'type'      => 'select2_array',
            'name'      => 'payment_status',
            'options'   => [
                '' => trans('backpack::crud.voucher.field.payment_status.placeholder'),
                'BAYAR' => 'BAYAR',
                'BELUM BAYAR' => 'BELUM BAYAR',
            ], // force the related options to be a custom query, instead of all(); you can use this to filter the results show in the select
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ]
        ]);

        CRUD::addField([  // Select2
            'label'     => trans('backpack::crud.voucher.field.priority.label'),
            'type'      => 'select2_array',
            'name'      => 'priority',
            'options'   => [
                '' => trans('backpack::crud.voucher.field.priority.placeholder'),
                'MENENGAH' => 'MENENGAH',
            ], // force the related options to be a custom query, instead of all(); you can use this to filter the results show in the select
            'wrapper' => [
                'class' => 'form-group col-md-12'
            ]
        ]);

        CRUD::addField([
            'name' => 'information',
            'label' => trans('backpack::crud.voucher.field.information.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.voucher.field.information.placeholder'),
            ]
        ]);

        CRUD::addField([
            'name' => 'logic_voucher',
            'type' => 'logic_voucher',
        ]);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    function normalize($value)
    {
        if (is_null($value)) return null;

        // Ubah ke float kalau numeric (biar 35000000 == 35000000.00)
        if (is_numeric($value)) {
            return (float) $value;
        }

        // Trim string biasa
        return trim((string) $value);
    }

    public function update()
    {
        $this->crud->hasAccessOrFail('update');

        $request = $this->crud->validateRequest();

        $this->crud->registerFieldEvents();

        $voucher_status = Voucher::query();

        $v_e = DB::table('voucher_edit')
            ->select(DB::raw('MAX(id) as id'), 'voucher_id')
            ->groupBy('voucher_id');
        $voucher_status = $voucher_status
        ->leftJoinSub($v_e, 'v_e', function ($join) {
            $join->on('v_e.voucher_id', '=', 'vouchers.id');
        })->leftJoin('voucher_edit', 'voucher_edit.id', '=', 'v_e.id');

        $a_p = DB::table('approvals')
        ->select(DB::raw('MAX(id) as id'), 'model_type', 'model_id')
        ->groupBy('model_type', 'model_id');

        $voucher_status = $voucher_status
        ->leftJoinSub($a_p, 'a_p', function ($join) {
            $join->on('a_p.model_id', '=', 'voucher_edit.id')
            ->where('a_p.model_type', '=', DB::raw('"App\\\\Models\\\\VoucherEdit"'));
        })
        ->leftJoin('approvals', 'approvals.id', '=', 'a_p.id')
        ->where('vouchers.id', $request->id)
        ->select(DB::raw("
            vouchers.*,
            voucher_edit.id as voucer_edit_id,
            approvals.status as approval_status,
            approvals.user_id as approval_user_id,
            approvals.no_apprv as approval_no_apprv
        "))
        ->first();

        $flag_approval_status = ($voucher_status->approval_status == Approval::APPROVED || $voucher_status->approval_status == Approval::REJECTED) ? true : false;

        DB::beginTransaction();
        try{

            if($flag_approval_status){
                return response()->json([
                    'status' => false,
                    'success' => false,
                    'error' => trans('backpack::crud.voucher.confirm.update_failed_status'),
                ]);
            }

            $data = $request->only(['bill_value', 'tax_ppn', 'pph_23', 'pph_4', 'pph_21']);

            $hasilPerhitungan = $this->calculatePayment($data);

            $oldItem = DB::table('vouchers')->where('id', $request->id)->first();

            $item = Voucher::where('id', $request->id)->first();
            $item->no_payment = $request->no_payment;
            $item->account_id  = $request->account_id;
            $item->reference_type = ($request->no_type == 'po') ? PurchaseOrder::class : Spk::class;

            $item->reference_id = $request->no_po_spk;

            if($request->no_type == 'po'){
                $data_po_spk = PurchaseOrder::leftJoin('subkons', 'subkons.id', '=', 'purchase_orders.subkon_id')
                ->select(DB::raw("
                    subkons.name as name_company,
                    subkons.bank_name as bank_name,
                    subkons.bank_account as bank_account,
                    purchase_orders.id as id,
                    purchase_orders.po_number as no_po_spk,
                    purchase_orders.date_po as date_po_spk,
                    'po' as type
                "))->where('purchase_orders.id',  $request->no_po_spk)->first();
            }else{
                $data_po_spk = Spk::leftJoin('subkons', 'subkons.id', '=', 'spk.subkon_id')
                ->select(DB::raw("
                    subkons.name as name_company,
                    subkons.bank_name as bank_name,
                    subkons.bank_account as bank_account,
                    spk.id as id,
                    spk.no_spk as no_po_spk,
                    spk.date_spk as date_po_spk,
                    'spk' as type
                "))->where('spk.id',  $request->no_po_spk)->first();
            }

            $item->no_voucher = $request->no_voucher;
            $item->work_code = $request->work_code;
            $item->job_name = $request->job_name;
            $item->for_voucher = $request->for_voucher;
            $item->date_voucher = $request->date_voucher;
            $item->bussines_entity_code = $request->bussines_entity_code;
            $item->bussines_entity_type = $request->bussines_entity_type;
            $item->bussines_entity_name = $data_po_spk->name_company;
            $item->bill_number = $request->bill_number;
            $item->bill_date = $request->bill_date;
            $item->date_receipt_bill = $request->date_receipt_bill;
            $item->payment_description = $request->payment_description;
            $item->no_po_spk = $request->no_po_spk;
            $item->date_po_spk = $data_po_spk->date_po_spk;
            $item->bill_value = $request->bill_value;
            $item->tax_ppn = $request->tax_ppn ?? 0;
            $item->total = $hasilPerhitungan['total'];
            $item->pph_23 = $request->pph_23 ?? 0;
            $item->discount_pph_23 = $hasilPerhitungan['diskon_pph_23'];
            $item->pph_4 = $request->pph_4 ?? 0;
            $item->discount_pph_4 = $hasilPerhitungan['diskon_pph_4'];
            $item->pph_21 = $request->pph_21 ?? 0;
            $item->discount_pph_21 = $hasilPerhitungan['diskon_pph_21'];
            $item->payment_transfer = $hasilPerhitungan['payment_transfer'];
            $item->due_date = $request->due_date;
            $item->factur_status = $request->factur_status;
            $item->no_factur = $request->no_factur;
            $item->date_factur = $request->date_factur;
            $item->bank_name = $data_po_spk->bank_name;
            $item->no_account = $data_po_spk->bank_account;
            $item->payment_type = $request->payment_type;
            $item->payment_status = $request->payment_status;
            $item->priority = $request->priority;
            $item->information = $request->information;
            $item->save();

            $fieldsToLog = [
                'no_payment',
                'account_id',
                'reference_type',
                'reference_id',
                'no_voucher',
                'work_code',
                'job_name',
                'for_voucher',
                'date_voucher',
                'bussines_entity_code',
                'bussines_entity_type',
                'bussines_entity_name',
                'bill_number',
                'bill_date',
                'date_receipt_bill',
                'payment_description',
                'no_po_spk',
                'date_po_spk',
                'bill_value',
                'tax_ppn',
                'total',
                'pph_23',
                'discount_pph_23',
                'pph_4',
                'discount_pph_4',
                'pph_21',
                'discount_pph_21',
                'payment_transfer',
                'due_date',
                'factur_status',
                'no_factur',
                'date_factur',
                'bank_name',
                'no_account',
                'payment_type',
                'payment_status',
                'priority',
                'information',
            ];

            $edit_flag = 0;

            foreach ($fieldsToLog as $field) {
                $old = optional($oldItem)->$field;
                $new = $item->$field;

                $normalizedOld = $this->normalize($old);
                $normalizedNew = $this->normalize($new);

                if ($normalizedOld !== $normalizedNew) {
                    $edit_flag++;
                }
            }

            if($edit_flag > 0){
                $voucher_edit = new VoucherEdit;
                $voucher_edit->voucher_id = $item->id;
                $voucher_edit->user_id  = backpack_auth()->user()->id;
                $voucher_edit->date_update = Carbon::now();
                $voucher_edit->history_update = "Mengedit data voucher";
                $voucher_edit->save();

                $users = User::permission(['APPROVE EDIT VOUCHER'])
                ->orderBy('no_order', 'ASC')->get();

                foreach($users as $key => $user){
                    $approval = new Approval;
                    $approval->model_type = VoucherEdit::class;
                    $approval->model_id = $voucher_edit->id;
                    $approval->no_apprv = $key + 1;
                    $approval->user_id = $user->id;
                    $approval->position = '';
                    $approval->status = Approval::PENDING;
                    $approval->save();
                }

                $journal_entry = JournalEntry::where('reference_type', Voucher::class)
                ->where('reference_id', $item->id)->delete();
            }

            $this->data['entry'] = $this->crud->entry = $item;


            \Alert::success(trans('backpack::crud.update_success'))->flash();


            $this->crud->setSaveAction();

            DB::commit();
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $item,
                    'events' => [
                        'crudTable-voucher_plugin_load' => $item,
                        'crudTable-voucher_updated_success' => $item,
                        'crudTable-history_edit_voucher_updated_success' => $item,
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

    public function edit($id)
    {
        $this->crud->hasAccessOrFail('update');

        $id = $this->crud->getCurrentEntryId() ?? $id;

        $this->crud->registerFieldEvents();
        $voucher = Voucher::find($id);

        if($voucher->reference_type == PurchaseOrder::class){
            $data_po_spk = PurchaseOrder::leftJoin('subkons', 'subkons.id', '=', 'purchase_orders.subkon_id')
            ->select(DB::raw("
                subkons.name as name_company,
                subkons.bank_name as bank_name,
                subkons.bank_account as bank_account,
                purchase_orders.id as id,
                purchase_orders.po_number as no_po_spk,
                purchase_orders.date_po as date_po_spk,
                'po' as type
            "))->where('purchase_orders.id', $voucher->reference_id)->first();
        }else if($voucher->reference_type == Spk::class){
            $data_po_spk = Spk::leftJoin('subkons', 'subkons.id', '=', 'spk.subkon_id')
            ->select(DB::raw("
                subkons.name as name_company,
                subkons.bank_name as bank_name,
                subkons.bank_account as bank_account,
                spk.id as id,
                spk.no_spk as no_po_spk,
                spk.date_spk as date_po_spk,
                'spk' as type
            "))->where('spk.id', $voucher->reference_id)->first();
        }

        $data_po_spk->date_po_spk_str = Carbon::parse($data_po_spk->date_po_spk)->format('d/m/Y');

        $voucher->no_po_spk = $data_po_spk;
        $voucher->logic_voucher = $data_po_spk;
        $this->data['entry'] = $voucher;
        $this->data['no_po_spk'] = $data_po_spk;

        $this->crud->setOperationSetting('fields', $this->crud->getUpdateFields());

        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.edit').' '.$this->crud->entity_name;
        $this->data['id'] = $id;

        return response()->json([
            'html' => view($this->crud->getEditView(), $this->data)->render()
        ]);
    }

    function calculatePayment(array $inputs)
    {
        $billValue = (float) $inputs['bill_value'];
        $ppn       = (float) ($inputs['tax_ppn'] ?? 0);
        $pph23     = (float) ($inputs['pph_23'] ?? 0);
        $pph4      = (float) ($inputs['pph_4'] ?? 0);
        $pph21     = (float) ($inputs['pph_21'] ?? 0);

        $nilaiPpn = ($ppn == 0) ? 0 : ($billValue * ($ppn / 100));
        $total    = $billValue + $nilaiPpn;

        $diskonPph23 = ($pph23 == 0) ? 0 : $billValue * ($pph23 / 100);
        $diskonPph4  = ($pph4  == 0) ? 0 : $billValue * ($pph4  / 100);
        $diskonPph21 = ($pph21 == 0) ? 0 : $billValue * ($pph21 / 100);

        $paymentTransfer = $total - $diskonPph23 - $diskonPph4 - $diskonPph21;

        return [
            'bill_value'               => $billValue,
            'nilai_ppn'                => $nilaiPpn,
            'total'                    => $total,
            'diskon_pph_23'            => $diskonPph23,
            'diskon_pph_4'             => $diskonPph4,
            'diskon_pph_21'            => $diskonPph21,
            'payment_transfer'         => $paymentTransfer,
        ];
    }

    public function store()
    {
        $this->crud->hasAccessOrFail('create');

        $request = $this->crud->validateRequest();

        $this->crud->registerFieldEvents();

        DB::beginTransaction();
        try{

            $event = [];
            $event['crudTable-voucher_plugin_load'] = true;
            $data = $request->only(['bill_value', 'tax_ppn', 'pph_23', 'pph_4', 'pph_21']);

            $hasilPerhitungan = $this->calculatePayment($data);
            // dd($hasilPerhitungan, $request->all());
            $item = new Voucher;
            $item->no_payment = $request->no_payment;
            $item->account_id  = $request->account_id;
            $item->reference_type = ($request->no_type == 'po') ? PurchaseOrder::class : Spk::class;

            $item->reference_id = $request->no_po_spk;

            if($request->no_type == 'po'){
                $data_po_spk = PurchaseOrder::leftJoin('subkons', 'subkons.id', '=', 'purchase_orders.subkon_id')
                ->select(DB::raw("
                    subkons.name as name_company,
                    subkons.bank_name as bank_name,
                    subkons.bank_account as bank_account,
                    purchase_orders.id as id,
                    purchase_orders.po_number as no_po_spk,
                    purchase_orders.date_po as date_po_spk,
                    'po' as type
                "))->where('purchase_orders.id',  $request->no_po_spk)->first();
            }else{
                $data_po_spk = Spk::leftJoin('subkons', 'subkons.id', '=', 'spk.subkon_id')
                ->select(DB::raw("
                    subkons.name as name_company,
                    subkons.bank_name as bank_name,
                    subkons.bank_account as bank_account,
                    spk.id as id,
                    spk.no_spk as no_po_spk,
                    spk.date_spk as date_po_spk,
                    'spk' as type
                "))->where('spk.id',  $request->no_po_spk)->first();
            }

            $item->no_voucher = $request->no_voucher;
            $item->work_code = $request->work_code;
            $item->job_name = $request->job_name;
            $item->for_voucher = $request->for_voucher;
            $item->date_voucher = $request->date_voucher;
            $item->bussines_entity_code = $request->bussines_entity_code;
            $item->bussines_entity_type = $request->bussines_entity_type;
            $item->bussines_entity_name = $data_po_spk->name_company;
            $item->bill_number = $request->bill_number;
            $item->bill_date = $request->bill_date;
            $item->date_receipt_bill = $request->date_receipt_bill;
            $item->payment_description = $request->payment_description;
            $item->no_po_spk = $request->no_po_spk;
            $item->date_po_spk = $data_po_spk->date_po_spk;
            $item->bill_value = $request->bill_value;
            $item->tax_ppn = $request->tax_ppn ?? 0;
            $item->total = $hasilPerhitungan['total'];
            $item->pph_23 = $request->pph_23 ?? 0;
            $item->discount_pph_23 = $hasilPerhitungan['diskon_pph_23'];
            $item->pph_4 = $request->pph_4 ?? 0;
            $item->discount_pph_4 = $hasilPerhitungan['diskon_pph_4'];
            $item->pph_21 = $request->pph_21 ?? 0;
            $item->discount_pph_21 = $hasilPerhitungan['diskon_pph_21'];
            $item->payment_transfer = $hasilPerhitungan['payment_transfer'];
            $item->due_date = $request->due_date;
            $item->factur_status = $request->factur_status;
            $item->no_factur = $request->no_factur;
            $item->date_factur = $request->date_factur;
            $item->bank_name = $data_po_spk->bank_name;
            $item->no_account = $data_po_spk->bank_account;
            $item->payment_type = $request->payment_type;
            $item->payment_status = $request->payment_status;
            $item->priority = $request->priority;
            $item->information = $request->information;
            $item->save();

            $voucher_edit = new VoucherEdit;
            $voucher_edit->voucher_id = $item->id;
            $voucher_edit->user_id  = backpack_auth()->user()->id;
            $voucher_edit->date_update = Carbon::now();
            $voucher_edit->history_update = "Menambahkan data voucher baru";
            $voucher_edit->save();

            $users = User::permission('APPROVE VOUCHER')
            ->orderBy('no_order', 'ASC')->get();

            foreach($users as $key => $user){
                $approval = new Approval;
                $approval->model_type = VoucherEdit::class;
                $approval->model_id = $voucher_edit->id;
                $approval->no_apprv = $key + 1;
                $approval->user_id = $user->id;
                $approval->position = '';
                $approval->status = Approval::PENDING;
                $approval->save();
            }

            $event['crudTable-voucher_create_success'] = $item;
            $event['crudTable-history_edit_voucher_create_success'] = $item;

            // $item = $aset;
            // $this->data['entry'] = $this->crud->entry = $item;

            \Alert::success(trans('backpack::crud.insert_success'))->flash();

            $this->crud->setSaveAction();

            DB::commit();
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $item,
                    'events' => $event,
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

    public function approvedStore($id){
        $this->crud->hasAccessOrFail('create');

        $request = $this->crud->validateRequest();

        $this->crud->registerFieldEvents();

        DB::beginTransaction();
        try{

            $user_id = backpack_user()->id;

            $voucher_edit = VoucherEdit::find($id);
            $voucher_id = $voucher_edit->voucher_id;

            $event = [];

            $approval = Approval::where('model_type', VoucherEdit::class)
            ->where('model_id', $voucher_edit->id)
            ->where('user_id', $user_id)
            ->where('no_apprv', $request->no_apprv)
            ->first();

            $final_approval = Approval::where('model_type', VoucherEdit::class)
            ->where('model_id', $voucher_edit->id)
            ->orderBy('no_apprv', 'DESC')->first();

            $approval->status = $request->action;
            $approval->approved_at = Carbon::now();
            $approval->save();

            $voucher = Voucher::find($voucher_id);

            if($request->action == Approval::APPROVED){

                // if($final_approval->no_apprv == $request->no_apprv){
                //     CustomHelper::updateOrCreateJournalEntry([
                //         'account_id' => $voucher->account_id,
                //         'reference_id' => $voucher->id,
                //         'reference_type' => Voucher::class,
                //         'description' => 'FIRST BALANCE',
                //         'date' => Carbon::now(),
                //         'debit' => $voucher->payment_transfer,
                //         // 'credit' => ($status == CastAccount::OUT) ? $nominal_transaction : 0,
                //     ], [
                //         'account_id' => $voucher->account_id,
                //         'reference_id' => $voucher->id,
                //         'reference_type' => Voucher::class,
                //     ]);
                // }
            }

            $event['crudTable-voucher_create_success'] = true;
            $event['crudTable-history_edit_voucher_create_success'] = true;

            DB::commit();
            return response()->json([
                'success' => true,
                'data' => $approval,
                'events' => $event,
            ]);
            // return $this->crud->performSaveAction($item->getKey());

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
        CRUD::field([
            'name' => 'no_payment',
            'label' => trans('backpack::crud.voucher.field.no_payment.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        CRUD::field([
            'name' => 'account_id',
            'label' => trans('backpack::crud.voucher.field.account_id.label'), // Table column heading
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        CRUD::field([
            'name' => 'no_voucher',
            'label' => trans('backpack::crud.voucher.field.no_voucher.label'),// Table column heading
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        CRUD::field([
            'name' => 'work_code',
            'label' => trans('backpack::crud.voucher.field.work_code.label'),// Table column heading
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        CRUD::field([
            'name' => 'job_name',
            'label' => trans('backpack::crud.voucher.field.job_name.label'), // Table column heading
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-12',
            ],
        ]);

        CRUD::field([
            'name' => 'for_voucher',
            'label' => trans('backpack::crud.voucher.field.for_voucher.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        CRUD::field([
            'name' => 'date_voucher',
            'label' => trans('backpack::crud.voucher.field.date_voucher.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        CRUD::field([
            'name' => 'bussines_entity_code',
            'label' => trans('backpack::crud.voucher.field.bussines_entity_code.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        CRUD::field([
            'name' => 'bussines_entity_type',
            'label' => trans('backpack::crud.voucher.field.bussines_entity_type.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        CRUD::field([
            'name' => 'bill_number',
            'label' => trans('backpack::crud.voucher.field.bill_number.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        CRUD::field([
            'name' => 'bill_date',
            'label' => trans('backpack::crud.voucher.field.bill_date.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        CRUD::field([
            'name' => 'date_receipt_bill',
            'label' => trans('backpack::crud.voucher.field.date_receipt_bill.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-12',
            ],
        ]);

        CRUD::field([
            'name' => 'payment_description',
            'label' => trans('backpack::crud.voucher.field.payment_description.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-12',
            ],
        ]);

        CRUD::field([
            'name' => 'bussines_entity_name',
            'label' => trans('backpack::crud.voucher.field.bussines_entity_name.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-12',
            ],
        ]);

        CRUD::field([
            'name' => 'no_po_spk',
            'label' => trans('backpack::crud.voucher.field.no_po_spk.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        CRUD::field([
            'name' => 'date_po_spk',
            'label' => trans('backpack::crud.voucher.field.date_po_spk.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        CRUD::field([
            'name' => 'bill_value',
            'label' => trans('backpack::crud.voucher.field.bill_value.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-4',
            ],
        ]);

        CRUD::field([
            'name' => 'tax_ppn',
            'label' => trans('backpack::crud.voucher.field.tax_ppn.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-2',
            ],
        ]);

        CRUD::field([
            'name' => 'total',
            'label' => trans('backpack::crud.voucher.field.total.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-4',
            ],
        ]);

        CRUD::field([
            'name' => 'pph_23',
            'label' => trans('backpack::crud.voucher.field.pph_23.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        CRUD::field([
            'name' => 'discount_pph_23',
            'label' => trans('backpack::crud.voucher.field.discount_pph_23.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        CRUD::field([
            'name' => 'pph_4',
            'label' => trans('backpack::crud.voucher.field.pph_4.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        CRUD::field([
            'name' => 'discount_pph_4',
            'label' => trans('backpack::crud.voucher.field.discount_pph_4.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        CRUD::field([
            'name' => 'pph_21',
            'label' => trans('backpack::crud.voucher.field.pph_21.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        CRUD::field([
            'name' => 'discount_pph_21',
            'label' => trans('backpack::crud.voucher.field.discount_pph_21.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        CRUD::field([
            'name' => 'payment_transfer',
            'label' => trans('backpack::crud.voucher.field.payment_transfer.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-12',
            ],
        ]);

        CRUD::field([
            'name' => 'due_date',
            'label' => trans('backpack::crud.voucher.field.due_date.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        CRUD::field([
            'name' => 'factur_status',
            'label' => trans('backpack::crud.voucher.field.factur_status.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        CRUD::field([
            'name' => 'no_factur',
            'label' => trans('backpack::crud.voucher.field.no_factur.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        CRUD::field([
            'name' => 'date_factur',
            'label' => trans('backpack::crud.voucher.field.date_factur.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        CRUD::field([
            'name' => 'bank_name',
            'label' => trans('backpack::crud.voucher.field.bank_name.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        CRUD::field([
            'name' => 'no_account',
            'label' => trans('backpack::crud.voucher.field.no_account.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        CRUD::field([
            'name' => 'payment_type',
            'label' => trans('backpack::crud.voucher.field.payment_type.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        CRUD::field([
            'name' => 'payment_status',
            'label' => trans('backpack::crud.voucher.field.payment_status.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        CRUD::field([
            'name' => 'priority',
            'label' => trans('backpack::crud.voucher.field.priority.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-12',
            ],
        ]);

        CRUD::field([
            'name' => 'information',
            'label' => trans('backpack::crud.voucher.field.information.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-12',
            ],
        ]);
        // column
        CRUD::column([
            'label'  => '',
            'name' => 'no_payment',
            'type'  => 'text',
        ]);

        CRUD::column(
            [
                'label'  => '',
                'name' => 'account_id',
                'type'  => 'closure',
                'function' => function($entry){
                    return $entry->account->code ." - ".$entry->account->name;
                }
            ],
        );

        CRUD::column([
            'label'  => '',
            'name' => 'no_voucher',
            'type'  => 'text',
        ]);

        CRUD::column([
            'label'  => '',
            'name' => 'work_code',
            'type'  => 'text',
        ]);

        CRUD::column([
            'label'  => '',
            'name' => 'job_name',
            'type'  => 'text',
        ]);

        CRUD::column([
            'label'  => '',
            'name' => 'for_voucher',
            'type'  => 'text',
        ]);

        CRUD::column([
            'label'  => '',
            'name' => 'date_voucher',
            'type'  => 'date',
            'format' => 'D MMM Y'
        ]);

        CRUD::column([
            'label'  => '',
            'name' => 'bussines_entity_code',
            'type'  => 'text',
        ]);

        CRUD::column([
            'label'  => '',
            'name' => 'bussines_entity_type',
            'type'  => 'text',
        ]);

        CRUD::column([
            'label'  => '',
            'name' => 'bill_number',
            'type'  => 'text',
        ]);

        CRUD::column([
            'label'  => '',
            'name' => 'bill_date',
            'type'  => 'date',
            'format' => 'D MMM Y'
        ]);

        CRUD::column([
            'label'  => '',
            'name' => 'date_receipt_bill',
            'type'  => 'date',
            'format' => 'D MMM Y'
        ]);

        CRUD::column(
            [
                'label'  => '',
                'name' => 'payment_description',
                'type'  => 'text'
            ],
        );

        CRUD::column(
            [
                'label'  => '',
                'name' => 'bussines_entity_name',
                'type'  => 'text'
            ],
        );

        CRUD::column(
            [
                'label'  => '',
                'name' => 'no_po_spk',
                'type'  => 'closure',
                'function' => function($entry){
                    if($entry->reference_type == PurchaseOrder::class){
                        $data_po_spk = PurchaseOrder::leftJoin('subkons', 'subkons.id', '=', 'purchase_orders.subkon_id')
                        ->select(DB::raw("
                            subkons.name as name_company,
                            subkons.bank_name as bank_name,
                            subkons.bank_account as bank_account,
                            purchase_orders.id as id,
                            purchase_orders.po_number as no_po_spk,
                            purchase_orders.date_po as date_po_spk,
                            'po' as type
                        "))->where('purchase_orders.id', $entry->reference_id)->first();
                    }else if($entry->reference_type == Spk::class){
                        $data_po_spk = Spk::leftJoin('subkons', 'subkons.id', '=', 'spk.subkon_id')
                        ->select(DB::raw("
                            subkons.name as name_company,
                            subkons.bank_name as bank_name,
                            subkons.bank_account as bank_account,
                            spk.id as id,
                            spk.no_spk as no_po_spk,
                            spk.date_spk as date_po_spk,
                            'spk' as type
                        "))->where('spk.id', $entry->reference_id)->first();
                    }

                    return $data_po_spk->no_po_spk;

                }
            ],
        );

        CRUD::column([
            'label'  => '',
            'name' => 'date_po_spk',
            'type'  => 'date',
            'format' => 'D MMM Y'
        ]);

        CRUD::column([
            'label'  => '',
            'name' => 'bill_value',
            'type'  => 'number',
            'prefix' => "Rp.",
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
        ]);

        CRUD::column([
            'label'  => '',
            'name' => 'tax_ppn',
            'type'  => 'number',
            'suffix' => '%',
        ]);

        CRUD::column([
            'label'  => '',
            'name' => 'total',
            'type'  => 'number',
            'prefix' => "Rp.",
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
        ]);

        CRUD::column([
            'label'  => '',
            'name' => 'pph_23',
            'type'  => 'number',
            'suffix' => '%',
        ]);

        CRUD::column([
            'label'  => '',
            'name' => 'discount_pph_23',
            'type'  => 'number',
            'prefix' => "Rp.",
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
        ]);

        CRUD::column([
            'label'  => '',
            'name' => 'pph_4',
            'type'  => 'number',
            'suffix' => '%',
        ]);

        CRUD::column([
            'label'  => '',
            'name' => 'discount_pph_4',
            'type'  => 'number',
            'prefix' => "Rp.",
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
        ]);

        CRUD::column([
            'label'  => '',
            'name' => 'pph_21',
            'type'  => 'number',
            'suffix' => '%',
        ]);

        CRUD::column([
            'label'  => '',
            'name' => 'discount_pph_21',
            'type'  => 'number',
            'prefix' => "Rp.",
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
        ]);

        CRUD::column([
            'label'  => '',
            'name' => 'payment_transfer',
            'type'  => 'number',
            'prefix' => "Rp.",
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
        ]);

        CRUD::column([
            'label'  => '',
            'name' => 'due_date',
            'type'  => 'date',
            'format' => 'D MMM Y'
        ]);

        CRUD::column(
            [
                'label'  => '',
                'name' => 'factur_status',
                'type'  => 'text'
            ],
        );

        CRUD::column(
            [
                'label'  => '',
                'name' => 'no_factur',
                'type'  => 'text'
            ],
        );

        CRUD::column([
            'label'  => '',
            'name' => 'date_factur',
            'type'  => 'date',
            'format' => 'D MMM Y'
        ]);

        CRUD::column(
            [
                'label'  => '',
                'name' => 'bank_name',
                'type'  => 'text'
            ],
        );

        CRUD::column(
            [
                'label'  => '',
                'name' => 'no_account',
                'type'  => 'text'
            ],
        );

        CRUD::column(
            [
                'label'  => '',
                'name' => 'payment_type',
                'type'  => 'text'
            ],
        );

        CRUD::column(
            [
                'label'  => '',
                'name' => 'payment_status',
                'type'  => 'text'
            ],
        );

        CRUD::column(
            [
                'label'  => '',
                'name' => 'priority',
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
        DB::beginTransaction();
        try {
            $this->crud->hasAccessOrFail('delete');

            $item = Voucher::findOrFail($id);

            $voucher_edit = VoucherEdit::where('voucher_id', $id)->get();

            foreach($voucher_edit as $edit_v){
                $approval = Approval::where('model_type', VoucherEdit::class)
                ->where('model_id', $edit_v->id)->delete();
                $edit_v->delete();
            }

            JournalEntry::where('reference_id', $item->id)
            ->where('reference_type', Voucher::class)->delete();

            $item->delete();

            $messages['success'][] = '<strong>'.trans('backpack::crud.delete_confirmation_title').'</strong><br>'.trans('backpack::crud.delete_confirmation_message');
            $messages['events'] = [
                'crudTable-voucher_plugin_load' => true,
                'crudTable-voucher_create_success' => true,
                'crudTable-history_edit_voucher_create_success' => true,
            ];

            DB::commit();
            return response()->json($messages);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'type' => 'errors',
                'message' => $e->getMessage()
            ], 500);
        }
    }

}
