<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Spk;
use App\Models\User;
use App\Models\Subkon;
use App\Models\Account;
use App\Models\Setting;
use App\Models\Voucher;
use App\Models\Approval;
use App\Models\ClientPo;
use App\Models\CastAccount;
use App\Models\VoucherEdit;
use App\Models\InvoiceClient;
use App\Models\PurchaseOrder;
use App\Models\PaymentVoucher;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Exports\ExportExcel;
use App\Http\Helpers\CustomHelper;
use App\Models\AccountTransaction;
use App\Models\PaymentVoucherPlan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\CrudController;
use App\Http\Controllers\Operation\PermissionAccess;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class VoucherCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use PermissionAccess;

    public function setup()
    {
        CRUD::setModel(Voucher::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/fa/voucher');
        CRUD::setEntityNameStrings('Voucher', 'Voucher');
        $allAccess = [
            'AKSES SEMUA MENU ACCOUNTING',
            'AKSES MENU FA'
        ];

        $viewMenu = [
            'MENU INDEX FA VOUCHER'
        ];

        $this->settingPermission([
            'create' => [
                ...$allAccess,
                "CREATE INDEX FA VOUCHER",
            ],
            'update' => [...$allAccess, "UPDATE INDEX FA VOUCHER"],
            'delete' => [...$allAccess, "DELETE INDEX FA VOUCHER"],
            'list' => $viewMenu,
            'show' => $viewMenu,
            'print' => true,
        ]);
    }


    function total_voucher()
    {

        $request = request();

        $data = Voucher::selectRaw('
            SUM(bill_value) as jumlah_exclude_ppn,
            SUM(total) as jumlah_include_ppn,
            SUM(payment_transfer) as jumlah_nilai_transfer
        ');

        $v_e = DB::table('voucher_edit')
            ->select(DB::raw('MAX(id) as id'), 'voucher_id')
            ->groupBy('voucher_id');

        $data = $data
            ->leftJoin('accounts', 'accounts.id', '=', 'vouchers.account_id')
            ->leftJoinSub($v_e, 'v_e', function ($join) {
                $join->on('v_e.voucher_id', '=', 'vouchers.id');
            })
            ->leftJoin('voucher_edit', 'voucher_edit.id', '=', 'v_e.id');

        $a_p = DB::table('approvals')
            ->select(DB::raw('MAX(id) as id'), 'model_type', 'model_id')
            ->groupBy('model_type', 'model_id');

        $data = $data
            ->leftJoinSub($a_p, 'a_p', function ($join) {
                $join->on('a_p.model_id', '=', 'voucher_edit.id')
                    ->where('a_p.model_type', '=', DB::raw('"App\\\\Models\\\\VoucherEdit"'));
            })
            ->leftJoin('approvals', 'approvals.id', '=', 'a_p.id');

        $data = $data->leftJoin('cast_accounts', 'cast_accounts.id', 'vouchers.account_source_id');

        if ($request->has('search')) {
            if (isset($request->search[1])) {
                $search = trim($request->search[1]);
                $data = $data->where('no_voucher', 'like', '%' . $search . '%');
            }

            // kolom 2
            if (isset($request->search[2])) {
                $search = trim($request->search[2]);
                $data = $data->where('date_voucher', 'like', '%' . $search . '%');
            }

            // kolom 3 (relasi subkon)
            if (isset($request->search[3])) {
                $search = trim($request->search[3]);
                $data = $data->whereHas('subkon', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });
            }

            // kolom 4
            if (isset($request->search[4])) {
                $search = trim($request->search[4]);
                $data = $data->where('bill_number', 'like', '%' . $search . '%');
            }

            // kolom 5
            if (isset($request->search[5])) {
                $search = trim($request->search[5]);
                $data = $data->where('bill_date', 'like', '%' . $search . '%');
            }

            // kolom 6
            if (isset($request->search[6])) {
                $search = trim($request->search[6]);
                $data = $data->where('payment_description', 'like', '%' . $search . '%');
            }

            // kolom 7
            if (isset($request->search[7])) {
                $search = trim($request->search[7]);
                $data = $data->where('bill_value', 'like', '%' . $search . '%');
            }

            // kolom 8
            if (isset($request->search[8])) {
                $search = trim($request->search[8]);
                $data = $data->where('total', 'like', '%' . $search . '%');
            }

            // kolom 9
            if (isset($request->search[9])) {
                $search = trim($request->search[9]);
                $data = $data->where('payment_transfer', 'like', '%' . $search . '%');
            }

            // kolom 10
            if (isset($request->search[10])) {
                $search = trim($request->search[10]);
                $data = $data->where('factur_status', 'like', $search . '%');
            }

            // kolom 11 (whereHasMorph reference)
            if (isset($request->search[11])) {
                $search = trim($request->search[11]);
                $data = $data->whereHas('client_po', function ($q) use ($search) {
                    $q->where('work_code', 'like', '%' . $search . '%');
                });
            }

            // kolom 12
            if (isset($request->search[12])) {
                $search = trim($request->search[12]);
                $data = $data->where('job_name', 'like', '%' . $search . '%');
            }

            // kolom 13 (relasi accounts)
            if (isset($request->search[13])) {
                $search = trim($request->search[13]);
                $data = $data->where(function ($q) use ($search) {
                    $q->where('accounts.code', 'LIKE', '%' . $search . '%')
                        ->orWhere('accounts.name', 'LIKE', '%' . $search . '%');
                });
            }

            // kolom 14
            if (isset($request->search[14])) {
                $search = trim($request->search[14]);
                $data = $data->where('no_account', 'like', '%' . $search . '%');
            }

            // kolom 15
            if (isset($request->search[15])) {
                $search = trim($request->search[15]);
                $data = $data->where('payment_type', 'like', '%' . $search . '%');
            }

            // kolom 16 (approvals.status)
            if (isset($request->search[16])) {
                $search = trim($request->search[16]);
                $data = $data->where('approvals.status', 'like', '%' . $search . '%');
            }

            if (isset($request->search[17])) {
                $search = trim($request->search[17]);
                $data = $data
                    ->whereExists(function ($query) use ($search) {
                        $query->select(DB::raw(1))
                            ->from('approvals')
                            ->whereColumn('approvals.model_id', 'voucher_edit.id')
                            ->where('approvals.model_type', 'App\\Models\\VoucherEdit')
                            ->whereExists(function ($q) use ($search) {
                                $q->select(DB::raw(1))
                                    ->from('users')
                                    ->whereColumn('users.id', 'approvals.user_id')
                                    ->where('users.name', 'like', '%' . $search . '%');
                            });
                    });
            }

            // kolom 17
            if (isset($request->search[18])) {
                $search = trim($request->search[18]);
                $data = $data->where('payment_status', 'like', $search . '%');
            }
        }

        $data = $data
            ->first();

        return response()->json([
            'total_exclude_ppn' => CustomHelper::formatRupiahWithCurrency($data->jumlah_exclude_ppn),
            'total_include_ppn' => CustomHelper::formatRupiahWithCurrency($data->jumlah_include_ppn),
            'total_nilai_transfer' => CustomHelper::formatRupiahWithCurrency($data->jumlah_nilai_transfer),
        ]);
    }


    function index()
    {
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
                                    'label' => trans('backpack::crud.voucher.column.voucher.status.label'),
                                    'type' => 'text',
                                    'name' => 'status',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.user_approval.label'),
                                    'type' => 'text',
                                    'name' => 'user_approval',
                                    'orderable' => false,
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
                                    'name' => 'action',
                                    'type' => 'action',
                                    'label' =>  trans('backpack::crud.actions'),
                                ]
                            ],
                            'route' => backpack_url('/fa/voucher/search?tab=voucher'),
                            'route_export_pdf' => url($this->crud->route . '/export-pdf?tab=voucher'),
                            'title_export_pdf' => 'Voucher.pdf',
                            'route_export_excel' => url($this->crud->route . '/export-excel?tab=voucher'),
                            'title_export_excel' => 'Voucher.xlsx',
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
                            'route_export_pdf' => url($this->crud->route . '/export-pdf?tab=voucher_edit'),
                            'title_export_pdf' => 'Voucher-edit.pdf',
                            'route_export_excel' => url($this->crud->route . '/export-excel?tab=voucher_edit'),
                            'title_export_excel' => 'Voucher-edit.xlsx',
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
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.add') . ' ' . $this->crud->entity_name;

        return response()->json([
            'html' => view('crud::create', $this->data)->render()
        ]);
    }

    protected function setupListOperation()
    {
        $settings = Setting::first();
        $type = request()->tab;
        CRUD::addButtonFromView('top', 'export-excel', 'export-excel', 'beginning');
        CRUD::addButtonFromView('top', 'export-pdf', 'export-pdf', 'beginning');
        if ($type == 'voucher') {
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

            $a_p = DB::table('approvals')
                ->select(DB::raw('MAX(id) as id'), 'model_type', 'model_id')
                ->groupBy('model_type', 'model_id');

            $this->crud->query = $this->crud->query
                ->leftJoinSub($a_p, 'a_p', function ($join) {
                    $join->on('a_p.model_id', '=', 'voucher_edit.id')
                        ->where('a_p.model_type', '=', DB::raw('"App\\\\Models\\\\VoucherEdit"'));
                })
                ->leftJoin('approvals', 'approvals.id', '=', 'a_p.id');

            if ($user_approval->count() > 0) {
                $this->crud->query = $this->crud->query
                    ->leftJoin('approvals as user_live_approvals', function ($join) use ($user_id) {
                        $join->on('user_live_approvals.model_id', '=', 'voucher_edit.id')
                            ->where('user_live_approvals.model_type', 'App\\Models\\VoucherEdit')
                            ->where('user_live_approvals.user_id', $user_id);
                    });
                CRUD::addClause('select', [
                    DB::raw("
                            vouchers.*,
                            accounts.name as account_name,
                            accounts.code as account_code,
                            voucher_edit.id as voucer_edit_id,
                            approvals.status as approval_status,
                            approvals.user_id as approval_user_id,
                            approvals.no_apprv as approval_no_apprv,
                            cast_accounts.name as cast_account_name,
                            user_live_approvals.no_apprv as user_live_no_apprv,
                            user_live_approvals.status as user_live_status,
                            user_live_approvals.user_id as user_live_user_id
                    ")
                ]);
            } else {
                CRUD::addClause('select', [
                    DB::raw("
                            vouchers.*,
                            accounts.name as account_name,
                            accounts.code as account_code,
                            voucher_edit.id as voucer_edit_id,
                            approvals.status as approval_status,
                            approvals.user_id as approval_user_id,
                            approvals.no_apprv as approval_no_apprv,
                            cast_accounts.name as cast_account_name,
                            '' as user_live_no_apprv,
                            '' as user_live_status,
                            '' as user_live_user_id
                    ")
                ]);
            }


            $request = request();

            if (trim($request->columns[1]['search']['value']) != '') {
                // dd(trim($request->columns[1]['search']['value']));
                $this->crud->query = $this->crud->query
                    ->where('no_voucher', 'like', '%' . $request->columns[1]['search']['value'] . '%');
            }

            if (trim($request->columns[2]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('date_voucher', 'like', '%' . $request->columns[1]['search']['value'] . '%');
            }

            if (trim($request->columns[3]['search']['value']) != '') {
                $search = trim($request->columns[3]['search']['value']);
                $this->crud->query = $this->crud->query
                    ->whereHas('subkon', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    });
                // ->orWhere('bussines_entity_name', 'like', '%'.$request->columns[1]['search']['value'].'%');
            }

            if (trim($request->columns[4]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('bill_number', 'like', $request->columns[4]['search']['value'] . '%');
            }

            if (trim($request->columns[5]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('bill_date', 'like', '%' . $request->columns[5]['search']['value'] . '%');
            }

            if (trim($request->columns[6]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('payment_description', 'like', '%' . $request->columns[6]['search']['value'] . '%');
            }

            if (trim($request->columns[7]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('bill_value', 'like', '%' . $request->columns[7]['search']['value'] . '%');
            }

            if (trim($request->columns[8]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('total', 'like', '%' . $request->columns[8]['search']['value'] . '%');
            }

            if (trim($request->columns[9]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('payment_transfer', 'like', '%' . $request->columns[9]['search']['value'] . '%');
            }

            if (trim($request->columns[10]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('factur_status', 'like', $request->columns[10]['search']['value'] . '%');
            }

            if (trim($request->columns[11]['search']['value']) != '') {
                $search = trim($request->columns[11]['search']['value']);
                $this->crud->query = $this->crud->query
                    ->whereHas('client_po', function ($q) use ($search) {
                        $q->where('work_code', 'like', '%' . $search . '%');
                    });
            }

            if (trim($request->columns[12]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('job_name', 'like', '%' . $request->columns[12]['search']['value'] . '%');
            }

            if (trim($request->columns[13]['search']['value']) != '') {
                $search = trim($request->columns[13]['search']['value']);
                $this->crud->query = $this->crud->query
                    ->where(function ($q) use ($search) {
                        $q->where('accounts.code', 'LIKE', '%' . $search . '%')
                            ->orWhere('accounts.name', 'LIKE', "%" . $search . "%");
                    });
            }

            if (trim($request->columns[14]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('no_account', 'like', '%' . $request->columns[14]['search']['value'] . '%');
            }

            if (trim($request->columns[15]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('payment_type', 'like', '%' . $request->columns[15]['search']['value'] . '%');
            }

            if (trim($request->columns[16]['search']['value']) != '') {
                $status_search = trim($request->columns[16]['search']['value']);
                $this->crud->query = $this->crud->query
                    ->where('approvals.status', 'like', '%' . $status_search . '%');
            }

            if (trim($request->columns[17]['search']['value']) != '') {
                $status_search = trim($request->columns[17]['search']['value']);
                $this->crud->query = $this->crud->query
                    ->whereExists(function ($query) use ($status_search) {
                        $query->select(DB::raw(1))
                            ->from('approvals')
                            ->whereColumn('approvals.model_id', 'voucher_edit.id')
                            ->where('approvals.model_type', 'App\\Models\\VoucherEdit')
                            ->whereExists(function ($q) use ($status_search) {
                                $q->select(DB::raw(1))
                                    ->from('users')
                                    ->whereColumn('users.id', 'approvals.user_id')
                                    ->where('users.name', 'like', '%' . $status_search . '%');
                            });
                    });
            }

            if (trim($request->columns[18]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('payment_status', 'like', $request->columns[18]['search']['value'] . '%');
            }

            if (trim($request->columns[19]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('payment_date', 'like', '%' . $request->columns[19]['search']['value'] . '%');
            }

            $this->crud->query =
                $this->crud->query->leftJoin('cast_accounts', 'cast_accounts.id', 'vouchers.account_source_id');


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
                'type'  => 'wrap_text',
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
                    'name' => 'subkon_id',
                    'type'  => 'closure',
                    'function' => function ($entry) {
                        return $entry?->subkon?->name;
                    }
                ], // BELUM FILTER
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
                    'type'  => 'wrap_text'
                ],
            );
            CRUD::column([
                'label'  => '',
                'name' => 'bill_value',
                'type'  => 'number',
                'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
            ]);
            CRUD::column([
                'label'  => '',
                'name' => 'total',
                'type'  => 'number',
                'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
            ]);
            CRUD::column([
                'label'  => '',
                'name' => 'payment_transfer',
                'type'  => 'number',
                'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
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
                    'name' => 'client_po_id',
                    'type'  => 'closure',
                    'function' => function ($entry) {
                        return $entry?->client_po?->work_code;
                    }
                ], // BELUM FILTER
            );
            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'job_name',
                    'type'  => 'wrap_text'
                ],
            );
            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'account_id',
                    'type'  => 'closure',
                    'function' => function ($entry) {
                        return $entry->account->code . " - " . $entry->account->name;
                    }
                ],
            );
            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'account_source_id',
                    'type'  => 'closure',
                    'function' => function ($entry) {
                        return $entry?->cast_account_name;
                    }
                ], // BELUM FILTER
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
                    'name' => 'status',
                    'type'  => 'approval-voucher',
                ],
            );
            CRUD::addColumn([
                'name'     => 'user_approval',
                'label'    => trans('backpack::crud.voucher.column.voucher.user_approval.label'),
                'type'     => 'custom_html',
                'value' => function ($entry) {
                    $approvals = Approval::where('model_type', VoucherEdit::class)
                        ->where('model_id', $entry->voucer_edit_id)
                        ->orderBy('no_apprv', 'ASC')
                        ->get();
                    return "<ul>" . $approvals->map(function ($item, $key) {
                        if ($item->status == Approval::APPROVED) {
                            return "<li class='text-success'>" . $item->user->name . "</li>";
                        }
                        return "<li>" . $item->user->name . "</li>";
                    })->implode('') . "</ul>";
                },
                'searchLogic' => function ($query, $column, $searchTerm) {
                    // $query->orWhereHas('purchase_orders', function ($q) use ($column, $searchTerm) {
                    //     $q->where('po_number', 'like', '%'.$searchTerm.'%');
                    // });
                }
            ]);
            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'payment_status',
                    'type'  => 'text'
                ],
            );
            CRUD::column([
                'label'  => '',
                'name' => 'payment_date',
                'type'  => 'date',
                'format' => 'D MMM Y'
            ]);
        } else if ($type == 'voucher_edit') {
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
                'function' => function ($entry) {
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
                'type'  => 'wrap_text',
            ]);

            CRUD::column([
                'label'  => '',
                'name' => 'no_apprv',
                'type'  => 'closure',
                'function' => function ($entry) {
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

    private function setupListExport($tab)
    {
        $settings = Setting::first();
        if ($tab == 'voucher') {
            CRUD::setModel(Voucher::class);

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

            $a_p = DB::table('approvals')
                ->select(DB::raw('MAX(id) as id'), 'model_type', 'model_id')
                ->groupBy('model_type', 'model_id');

            $this->crud->query = $this->crud->query
                ->leftJoinSub($a_p, 'a_p', function ($join) {
                    $join->on('a_p.model_id', '=', 'voucher_edit.id')
                        ->where('a_p.model_type', '=', DB::raw('"App\\\\Models\\\\VoucherEdit"'));
                })
                ->leftJoin('approvals', 'approvals.id', '=', 'a_p.id');
            $this->crud->query =
                $this->crud->query->leftJoin('cast_accounts', 'cast_accounts.id', 'vouchers.account_source_id');

            $request = request();

            if (isset($request->columns[1]['search']['value'])) {
                // dd(trim($request->columns[1]['search']['value']));
                $this->crud->query = $this->crud->query
                    ->where('no_voucher', 'like', '%' . $request->columns[1]['search']['value'] . '%');
            }

            if (isset($request->columns[2]['search']['value'])) {
                $search = trim($request->columns[2]['search']['value']);
                $this->crud->query = $this->crud->query
                    ->where('date_voucher', 'like', '%' . $search . '%');
            }

            if (isset($request->columns[3]['search']['value'])) {
                $search = trim($request->columns[3]['search']['value']);
                $this->crud->query = $this->crud->query
                    ->whereHas('subkon', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    });
            }

            if (isset($request->columns[4]['search']['value'])) {
                $search = trim($request->columns[4]['search']['value']);
                $this->crud->query = $this->crud->query
                    ->where('bill_number', 'like', $search . '%');
            }

            if (isset($request->columns[5]['search']['value'])) {
                $search = trim($request->columns[5]['search']['value']);
                $this->crud->query = $this->crud->query
                    ->where('bill_date', 'like', '%' . $search . '%');
            }

            if (isset($request->columns[6]['search']['value'])) {
                $search = trim($request->columns[6]['search']['value']);
                $this->crud->query = $this->crud->query
                    ->where('payment_description', 'like', '%' . $search . '%');
            }

            if (isset($request->columns[7]['search']['value'])) {
                $search = trim($request->columns[7]['search']['value']);
                $this->crud->query = $this->crud->query
                    ->where('bill_value', 'like', '%' . $search . '%');
            }

            if (isset($request->columns[8]['search']['value'])) {
                $search = trim($request->columns[8]['search']['value']);
                $this->crud->query = $this->crud->query
                    ->where('total', 'like', '%' . $search . '%');
            }

            if (isset($request->columns[9]['search']['value'])) {
                $search = trim($request->columns[9]['search']['value']);
                $this->crud->query = $this->crud->query
                    ->where('payment_transfer', 'like', '%' . $search . '%');
            }

            if (isset($request->columns[10]['search']['value'])) {
                $search = trim($request->columns[10]['search']['value']);
                $this->crud->query = $this->crud->query
                    ->where('factur_status', 'like', $search . '%');
            }

            if (isset($request->columns[11]['search']['value'])) {
                $search = trim($request->columns[11]['search']['value']);
                $this->crud->query = $this->crud->query
                    ->whereHas('client_po', function ($q) use ($search) {
                        $q->where('work_code', 'like', '%' . $search . '%');
                    });
            }

            if (isset($request->columns[12]['search']['value'])) {
                $search = trim($request->columns[12]['search']['value']);
                $this->crud->query = $this->crud->query
                    ->where('job_name', 'like', '%' . $search . '%');
            }

            if (isset($request->columns[13]['search']['value'])) {
                $search = trim($request->columns[13]['search']['value']);
                $this->crud->query = $this->crud->query
                    ->where(function ($q) use ($search) {
                        $q->where('accounts.code', 'LIKE', '%' . $search . '%')
                            ->orWhere('accounts.name', 'LIKE', '%' . $search . '%');
                    });
            }

            if (isset($request->columns[14]['search']['value'])) {
                $search = trim($request->columns[14]['search']['value']);
                $this->crud->query = $this->crud->query
                    ->where('no_account', 'like', '%' . $search . '%');
            }

            if (isset($request->columns[15]['search']['value'])) {
                $search = trim($request->columns[15]['search']['value']);
                $this->crud->query = $this->crud->query
                    ->where('payment_type', 'like', '%' . $search . '%');
            }

            if (isset($request->columns[16]['search']['value'])) {
                $search = trim($request->columns[16]['search']['value']);
                $this->crud->query = $this->crud->query
                    ->where('approvals.status', 'like', '%' . $search . '%');
            }

            if (isset($request->columns[17]['search']['value'])) {
                $search = trim($request->columns[17]['search']['value']);
                $this->crud->query = $this->crud->query
                    ->whereExists(function ($query) use ($search) {
                        $query->select(DB::raw(1))
                            ->from('approvals')
                            ->whereColumn('approvals.model_id', 'voucher_edit.id')
                            ->where('approvals.model_type', 'App\\Models\\VoucherEdit')
                            ->whereExists(function ($q) use ($search) {
                                $q->select(DB::raw(1))
                                    ->from('users')
                                    ->whereColumn('users.id', 'approvals.user_id')
                                    ->where('users.name', 'like', '%' . $search . '%');
                            });
                    });
            }

            if (isset($request->columns[18]['search']['value'])) {
                $search = trim($request->columns[18]['search']['value']);
                $this->crud->query = $this->crud->query
                    ->where('payment_status', 'like', $search . '%');
            }

            if (isset($request->columns[19]['search']['value'])) {
                $search = trim($request->columns[19]['search']['value']);
                $this->crud->query = $this->crud->query
                    ->where('payment_date', 'like', '%' . $search . '%');
            }

            CRUD::addClause('select', [
                DB::raw("
                    vouchers.*,
                    accounts.name as account_name,
                    accounts.code as account_code,
                    voucher_edit.id as voucer_edit_id,
                    approvals.status as approval_status,
                    approvals.user_id as approval_user_id,
                    approvals.no_apprv as approval_no_apprv,
                    cast_accounts.name as cast_account_name
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
                'name' => 'no_payment',
                'label' => trans('backpack::crud.voucher.field.no_payment.label'),
                'type' => 'text',
            ]);

            CRUD::column(
                [
                    'label' => trans('backpack::crud.voucher.column.voucher.account_id.label'),
                    'name' => 'account_id',
                    'type'  => 'closure',
                    'function' => function ($entry) {
                        return $entry->account->code . " - " . $entry->account->name;
                    }
                ],
            );

            CRUD::column(
                [
                    'label' => trans('backpack::crud.voucher.column.voucher.bussines_entity_code.label'),
                    'name' => 'client_po_id',
                    'type'  => 'closure',
                    'function' => function ($entry) {
                        return $entry?->client_po?->work_code;
                    }
                ], // BELUM FILTER
            );

            CRUD::column(
                [
                    'label' => trans('backpack::crud.voucher.column.voucher.no_account.label'),
                    'name' => 'account_source_id',
                    'type'  => 'closure',
                    'function' => function ($entry) {
                        return $entry?->cast_account_name;
                    }
                ], // BELUM FILTER
            );

            CRUD::column(
                [
                    'label' => trans('backpack::crud.voucher.column.voucher.job_name.label'),
                    'name' => 'job_name',
                    'type'  => 'wrap_text'
                ],
            );

            CRUD::column([
                'label' => trans('backpack::crud.voucher.field.no_voucher.label'),
                'name' => 'no_voucher',
                'type'  => 'text',
            ]);

            CRUD::column([
                'label' => trans('backpack::crud.voucher.field.date_voucher.label'),
                'name' => 'date_voucher',
                'type'  => 'date',
                'format' => 'D MMM Y'
            ]);

            CRUD::column(
                [
                    'label' => trans('backpack::crud.voucher.column.voucher.bussines_entity_name.label'),
                    'name' => 'subkon_id',
                    'type'  => 'closure',
                    'function' => function ($entry) {
                        return $entry?->subkon?->name;
                    }
                ], // BELUM FILTER
            );

            CRUD::column([
                'label' => trans('backpack::crud.voucher.field.account_holder_name.label'),
                'name' => 'account_holder_name',
                'type'  => 'wrap_text',
            ]);

            CRUD::column(
                [
                    'label' => trans('backpack::crud.voucher.column.voucher.bill_number.label'),
                    'name' => 'bill_number',
                    'type'  => 'closure',
                    'function' => function ($entry) {
                        return str_replace('.00', '', $entry->bill_number);
                    },
                ],
            );
            CRUD::column([
                'label' => trans('backpack::crud.voucher.column.voucher.bill_date.label'),
                'name' => 'bill_date',
                'type'  => 'date',
                'format' => 'D MMM Y'
            ]);
            CRUD::column([
                'label' => trans('backpack::crud.voucher.field.date_receipt_bill.label'),
                'name' => 'date_receipt_bill',
                'type'  => 'date',
                'format' => 'D MMM Y'
            ]);
            CRUD::column(
                [
                    'label' => trans('backpack::crud.voucher.column.voucher.payment_description.label'),
                    'name' => 'payment_description',
                    'type'  => 'wrap_text'
                ],
            );

            CRUD::column(
                [
                    'label' => trans('backpack::crud.voucher.field.no_po_spk.label'),
                    'name' => 'reference_id',
                    'type'  => 'closure',
                    'function' => function ($entry) {
                        if ($entry->reference_type == Spk::class) {
                            return $entry?->reference?->no_spk;
                        }
                        return $entry?->reference?->po_number;
                    }
                ], // BELUM FILTER
            );

            CRUD::column([
                'label' => trans('backpack::crud.voucher.column.voucher.bill_value.label'),
                'name' => 'bill_value',
                // 'type'  => 'bald',
                // 'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
                'type'  => 'closure',
                'function' => function ($entry) {
                    return str_replace('.00', '', $entry->bill_value);
                },
            ]);

            CRUD::column([
                'name' => 'tax_ppn',
                'label' => trans('backpack::crud.voucher.field.tax_ppn.label'),
                'type'  => 'closure',
                'function' => function ($entry) {
                    return str_replace('.00', '', $entry->tax_ppn);
                },
            ]);

            CRUD::column([
                'name' => 'total_price_ppn',
                'label' => trans('backpack::crud.voucher.field.total_price_ppn.label'),
                'type'  => 'closure',
                'function' => function ($entry) {
                    $ppn = (int) $entry->tax_ppn;
                    $total_price_ppn = $entry->bill_value * ($ppn / 100);
                    return $total_price_ppn;
                },
            ]);

            CRUD::column([
                'label' => trans('backpack::crud.voucher.column.voucher.total.label'),
                'name' => 'total',
                'type'  => 'closure',
                'function' => function ($entry) {
                    return str_replace('.00', '', $entry->total);
                },
            ]);

            CRUD::column([
                'name' => 'pph_23',
                'label' => trans('backpack::crud.voucher.field.pph_23.label'),
                'type'  => 'closure',
                'function' => function ($entry) {
                    return str_replace('.00', '', $entry->pph_23);
                },
            ]);

            CRUD::column([
                'name' => 'discount_pph_23',
                'label' => trans('backpack::crud.voucher.field.discount_pph_23.label'),
                'type'  => 'closure',
                'function' => function ($entry) {
                    return str_replace('.00', '', $entry->discount_pph_23);
                },
            ]);

            CRUD::column([
                'name' => 'pph_4',
                'label' => trans('backpack::crud.voucher.field.pph_4.label'),
                'type'  => 'closure',
                'function' => function ($entry) {
                    return str_replace('.00', '', $entry->pph_4);
                },
            ]);

            CRUD::column([
                'name' => 'discount_pph_4',
                'label' =>  trans('backpack::crud.voucher.field.discount_pph_4.label'),
                'type'  => 'closure',
                'function' => function ($entry) {
                    return str_replace('.00', '', $entry->discount_pph_4);
                },
            ]);

            CRUD::column([
                'name' => 'pph_21',
                'label' => trans('backpack::crud.voucher.field.pph_21.label'),
                'type'  => 'closure',
                'function' => function ($entry) {
                    return str_replace('.00', '', $entry->pph_21);
                },
            ]);

            CRUD::column([
                'name' => 'discount_pph_21',
                'label' =>  trans('backpack::crud.voucher.field.discount_pph_21.label'),
                'type'  => 'closure',
                'function' => function ($entry) {
                    return str_replace('.00', '', $entry->discount_pph_21);
                },
            ]);

            CRUD::column([
                'label' => trans('backpack::crud.voucher.column.voucher.payment_transfer.label'),
                'name' => 'payment_transfer',
                'type'  => 'closure',
                'function' => function ($entry) {
                    return str_replace('.00', '', $entry->payment_transfer);
                },
            ]);

            CRUD::column([
                'label' => trans('backpack::crud.voucher.field.due_date.label'),
                'name' => 'due_date',
                'type'  => 'date',
                'format' => 'D MMM Y'
            ]);

            CRUD::column(
                [
                    'label' => trans('backpack::crud.voucher.column.voucher.factur_status.label'),
                    'name' => 'factur_status',
                    'type'  => 'text'
                ],
            );

            CRUD::column(
                [
                    'label' => trans('backpack::crud.voucher.field.no_factur.label'),
                    'name' => 'no_factur',
                    'type'  => 'wrap_text'
                ],
            );

            CRUD::column([
                'label' => trans('backpack::crud.voucher.field.date_factur.label'),
                'name' => 'date_factur',
                'type'  => 'date',
                'format' => 'D MMM Y'
            ]);

            CRUD::column(
                [
                    'label' => trans('backpack::crud.voucher.field.bank_name.label'),
                    'name' => 'bank_name',
                    'type'  => 'text'
                ],
            );

            CRUD::column(
                [
                    'label' => trans('backpack::crud.voucher.field.no_account.label'),
                    'name' => 'no_account',
                    'type'  => 'text'
                ],
            );

            CRUD::column(
                [
                    'label' => trans('backpack::crud.voucher.column.voucher.payment_type.label'),
                    'name' => 'payment_type',
                    'type'  => 'text'
                ],
            );

            CRUD::column(
                [
                    'label' => trans('backpack::crud.voucher.column.voucher.payment_status.label'),
                    'name' => 'payment_status',
                    'type'  => 'text'
                ],
            );

            CRUD::column([
                'label' => trans('backpack::crud.voucher.field.payment_date.label'),
                'name' => 'payment_date',
                'type'  => 'date',
                'format' => 'D MMM Y'
            ]);

            CRUD::column(
                [
                    'label' => trans('backpack::crud.voucher.field.priority.label'),
                    'name' => 'priority',
                    'type'  => 'text'
                ],
            );

            CRUD::column(
                [
                    'label' => trans('backpack::crud.voucher.field.information.label'),
                    'name' => 'information',
                    'type'  => 'wrap_text'
                ],
            );

            CRUD::column(
                [
                    'label' => trans('backpack::crud.voucher.column.voucher.status.label'),
                    'name' => 'status',
                    'type'  => 'approval-voucher',
                ],
            );
        } else if ($tab == 'voucher_edit') {
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
                'label' => trans('backpack::crud.voucher.column.voucher.no_voucher.label'),
                'name' => 'no_voucher',
                'type'  => 'text',
            ]);

            CRUD::column([
                'label' => trans('backpack::crud.voucher.column.voucher_edit.user_id.label'),
                'name' => 'user_id',
                'type'  => 'closure',
                'function' => function ($entry) {
                    return $entry->user->name;
                }
            ]);

            CRUD::column([
                'label' => trans('backpack::crud.voucher.column.voucher_edit.date_update.label'),
                'name' => 'date_update',
                'type'  => 'date',
                'format' => 'DD MMM YYYY HH:mm'
            ]);

            CRUD::column([
                'label' => trans('backpack::crud.voucher.column.voucher_edit.history_update.label'),
                'name' => 'history_update',
                'type'  => 'text',
            ]);

            CRUD::column([
                'label' => trans('backpack::crud.voucher.column.voucher_edit.no_apprv.label'),
                'name' => 'no_apprv',
                'type'  => 'closure',
                'function' => function ($entry) {
                    return 'Final - Approver User';
                }
            ]);

            CRUD::column(
                [
                    'label' => trans('backpack::crud.voucher.column.voucher_edit.status.label'),
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

    public function clientSelectedAjax()
    {
        $id = request()->id;
        $type = request()->type;

        if ($type == 'client') {
            $client = ClientPo::where('id', $id)
                ->select(DB::raw("id, po_number, job_name,
                IF(job_value > 0, job_value, 0) as price_total,
                IF(tax_ppn > 0, tax_ppn, 0) as ppn,
                IF(job_value_include_ppn > 0, job_value_include_ppn, 0) as price_total_include_ppn, work_code, 'Client' as type, status, client_id, '' as date_po"))
                ->first();
            $invoice_exists = InvoiceClient::where('client_po_id', $id)->first();
            $company = null;
        } else if ($type == 'subkon') {
            $client = PurchaseOrder::where('id', $id)
                ->select(DB::raw("id, po_number, job_name,
                IF(job_value > 0, job_value, 0) as price_total,
                IF(tax_ppn > 0, tax_ppn, 0) as ppn,
                IF(total_value_with_tax > 0, total_value_with_tax, 0) as price_total_include_ppn, work_code, 'Subkon' as type, subkon_id, date_po"))
                ->first();
            $company = $client->subkon;
            $invoice_exists = null;
        } else if ($type == 'spk') {
            $client = Spk::where('id', $id)
                ->select(DB::raw("id, no_spk as po_number, job_name,
                IF(job_value > 0, job_value, 0) as price_total,
                IF(tax_ppn > 0, tax_ppn, 0) as ppn,
                IF(total_value_with_tax > 0, total_value_with_tax, 0) as price_total_include_ppn,
                work_code, 'Spk' as type,
                subkon_id, date_spk as date_po"))
                ->first();
            $company = $client->subkon;
            $invoice_exists = null;
        }

        $account_selected = Account::query();

        $account_selected = $account_selected->where('code', 501)->first();

        $data = [
            'invoice_exists' => $invoice_exists,
            'po' => $client,
            'date_po' => ($client->date_po != '') ? Carbon::parse($client->date_po)->format('d/m/Y') : '',
            'account' => $account_selected,
            'company' => $company,
        ];

        return response()->json($data);
    }

    public function castAccountSelectedAjax()
    {
        $id = request()->id;
        $castAccount = Subkon::find($id);
        return response()->json($castAccount);
    }

    public function select2_no_po_spk()
    {
        $q = request()->q;

        $po_subkon = PurchaseOrder::select(DB::raw("id, po_number, 'subkon' as type"));
        $po_spk = Spk::select(DB::raw("id, no_spk as po_number, 'spk' as type"));

        $union = $po_subkon
            ->unionAll($po_spk)
            ->where('po_number', 'like', "%$q%")
            ->paginate(20);

        $results = [];
        foreach ($union as $item) {
            $type = ucfirst($item->type);
            $results[] = [
                'id' => $item->id,
                'text' => $item->po_number . ' (' . $type . ')',
                'type' => $item->type,
            ];
        }
        return response()->json(['results' => $results]);
    }

    public function ruleValidation()
    {

        $id = request()->id ?? null;
        $factur_status = request()->factur_status ?? null;
        $payment_status = request()->payment_status ?? null;

        $rule = [
            'no_payment' => 'required|max:150',
            'account_id' => 'required|exists:accounts,id',
            'no_voucher' => 'required|max:120|unique:vouchers,no_voucher,' . $id,
            // 'work_code' => 'required|max:30',
            // 'for_voucher' => 'required',
            'date_voucher' => 'required|date',
            // 'bussines_entity_code' => 'required|max:30',
            // 'bussines_entity_type' => 'required|max:30',
            'bill_number' => 'required|max:50',
            'bill_date' => 'required|date',
            'date_receipt_bill' => 'required|date',
            'payment_description' => 'required',
            // 'no_po_spk' => 'required|numeric',
            'bill_value' => 'required|numeric',
            'due_date' => 'required|date',
            'factur_status' => 'required',
            'payment_type' => 'required|max:50',
            'payment_status' => 'nullable|max:50',
            'priority' => 'required|max:50',
            'account_source_id' => 'required',
            'reference_id' => 'nullable',
            'subkon_id' => 'required',
            'client_po_id' => 'required',
        ];

        if ($factur_status == 'ADA') {
            $rule['no_factur'] = 'required';
            $rule['date_factur'] = 'required|date';
        } else {
            $rule['no_factur'] = 'nullable';
            $rule['date_factur'] = 'nullable|date';
        }

        if ($payment_status == 'BAYAR') {
            $rule['payment_date'] = 'required|date';
        } else {
            $rule['payment_date'] = 'nullable|date';
        }

        $rule['job_name'] = 'nullable';

        return $rule;
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation($this->ruleValidation());
        $settings = Setting::first();

        $voucher_prefix_value = [];
        $work_code_prefix_value = [];
        $faktur_prefix_value = [];
        if (!$this->crud->getCurrentEntryId()) {
            $voucher_prefix_value = [
                'value' => $this->generateIndexVoucher() . '-' . $settings?->vouhcer_prefix,
            ];
            $work_code_prefix_value = [
                'value' => $settings?->work_code_prefix,
            ];
            $faktur_prefix_value = [
                'value' => $settings?->faktur_prefix,
            ];
        }


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
            'data_source' => backpack_url('account/select2-account-child'),
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.voucher.field.account_id.placeholder'),
            ]
        ]);

        CRUD::addField([
            'label'       => trans('backpack::crud.voucher.field.work_code.label'), // Table column heading
            'type'        => "select2_ajax_custom",
            // 'name'        => 'reference_id',
            'name'        => 'client_po_id',
            'entity'      => 'client_po',
            'model'       => 'App\Models\ClientPo',
            'attribute'   => "work_code",
            'data_source' => backpack_url('fa/voucher/select2-work-code'),
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.voucher.field.work_code.placeholder'),
            ]
        ]);

        $cash_accounts = CastAccount::get();

        $cash_account_options = [
            '' => trans('backpack::crud.voucher.field.account_source_id.placeholder'),
        ];
        foreach ($cash_accounts as $key => $value) {
            $cash_account_options[$value->id] = $value->name;
        }

        CRUD::addField([
            'name' => 'account_source_id',
            'label' => trans('backpack::crud.voucher.field.account_source_id.label'),
            'type' => 'select2_array',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'options' => $cash_account_options,
            'attributes' => [
                // 'disabled' => true,
                'placeholder' => trans('backpack::crud.voucher.field.bussines_entity_name.placeholder'),
            ]
        ]);

        CRUD::addField([
            'name' => 'job_name_disabled',
            'label' => trans('backpack::crud.voucher.field.job_name.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-12',
            ],
            'attributes' => [
                'disabled' => true,
                'placeholder' => trans('backpack::crud.voucher.field.job_name.placeholder'),
            ]
        ]);

        CRUD::addField([
            'name' => 'job_name',
            'type' => 'hidden',
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
            ],
            ...$voucher_prefix_value,
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
            'label' => trans('backpack::crud.voucher.field.bussines_entity_name.label'),
            'type'        => "select2_ajax_custom",
            'name'        => 'subkon_id',
            'entity'      => 'subkon',
            'model'       => 'App\Models\Subkon',
            'attribute'   => "name",
            'data_source' => backpack_url('fa/voucher/select2-subkon'),
            'wrapper'   => [
                'class' => 'form-group col-md-12',
            ],
            'attributes' => [
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
            'type'        => "select2_ajax_custom",
            // 'name'        => 'client_po_id',
            'name'        => 'reference_id',
            'entity'      => 'purchase_order',
            'model'       => 'App\Models\PurchaseOrder',
            'attribute'   => "po_number",
            'data_source' => backpack_url('fa/voucher/select2-po-spk'),
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.voucher.field.no_po_spk.placeholder'),
            ]
        ]);

        CRUD::addField([
            'name' => 'space_1',
            'type' => 'hidden',
            'label' => '',
            'wrapper' => [
                'class' => 'form-group col-md-6'
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
            'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : 'Rp.',
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
            'name' => 'total_price_ppn',
            'label' =>  trans('backpack::crud.voucher.field.total_price_ppn.label'),
            'type' => 'text',
            'mask' => '000.000.000.000.000.000',
            'mask_options' => [
                'reverse' => true
            ],
            'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : 'Rp.',
            'wrapper'   => [
                'class' => 'form-group col-md-4',
            ],
            'attributes' => [
                'disabled' => true,
                'placeholder' => '000.000',
            ]
        ]);

        CRUD::addField([
            'name' => 'total',
            'label' =>  trans('backpack::crud.voucher.field.total.label'),
            'type' => 'text',
            'mask' => '000.000.000.000.000.000',
            'mask_options' => [
                'reverse' => true
            ],
            'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : 'Rp.',
            'wrapper'   => [
                'class' => 'form-group col-md-4',
            ],
            'attributes' => [
                'disabled' => true,
                'placeholder' => '000.000',
            ]
        ]);

        CRUD::addField([
            'name' => 'space_total_price_ppn',
            'type' => 'hidden',
            'label' => '',
            'wrapper' => [
                'class' => 'form-group col-md-8'
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
            'type' => 'text',
            'mask' => '000.000.000.000.000.000',
            'mask_options' => [
                'reverse' => true
            ],
            'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : 'Rp.',
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
            'type' => 'text',
            'mask' => '000.000.000.000.000.000',
            'mask_options' => [
                'reverse' => true
            ],
            'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : 'Rp.',
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
            'type' => 'text',
            'mask' => '000.000.000.000.000.000',
            'mask_options' => [
                'reverse' => true
            ],
            'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : 'Rp.',
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
            'type' => 'text',
            'mask' => '000.000.000.000.000.000',
            'mask_options' => [
                'reverse' => true
            ],
            'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : 'Rp.',
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
                'AKAN ADA' => 'AKAN ADA',
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
            ],
            ...$faktur_prefix_value,
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
            'name' => 'account_holder_name',
            'label' => trans('backpack::crud.voucher.field.account_holder_name.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'disabled' => true,
                'placeholder' => trans('backpack::crud.voucher.field.account_holder_name.placeholder'),
            ]
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
                'SUBKON' => 'SUBKON',
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
                // '' => trans('backpack::crud.voucher.field.payment_status.placeholder'),
                'BELUM BAYAR' => 'BELUM BAYAR',
                'BAYAR' => 'BAYAR',
            ], // force the related options to be a custom query, instead of all(); you can use this to filter the results show in the select
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ]
        ]);

        CRUD::addField([   // date_picker
            'name'  => 'payment_date',
            'type'  => 'date_picker',
            'label' => trans('backpack::crud.voucher.field.payment_date.label'),

            // optional:
            'date_picker_options' => [
                'language' => App::getLocale(),
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::addField([  // Select2
            'label'     => trans('backpack::crud.voucher.field.priority.label'),
            'type'      => 'select2_array',
            'name'      => 'priority',
            'options'   => [
                '' => trans('backpack::crud.voucher.field.priority.placeholder'),
                'HARI INI' => 'HARI INI',
                'MINGGU INI' => 'MINGGU INI',
                'TEMPO' => 'TEMPO'
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

        if (is_numeric($value)) {
            return (float) $value;
        }

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
        try {

            $data = $request->only(['bill_value', 'tax_ppn', 'pph_23', 'pph_4', 'pph_21']);

            $hasilPerhitungan = $this->calculatePayment($data);

            $oldItem = DB::table('vouchers')->where('id', $request->id)->first();

            $hasilPerhitungan = $this->calculatePayment($data);

            $type = strtolower($request->type);

            $item = Voucher::findOrFail($request->id);
            $item->no_payment = $request->no_payment;
            $item->account_id  = $request->account_id;
            $item->account_source_id = $request->account_source_id;
            if ($type == 'client') {
                $item->reference_type = ClientPo::class;
            } else if ($type == 'subkon') {
                $item->reference_type = PurchaseOrder::class;
            } else if ($type == 'spk') {
                $item->reference_type = Spk::class;
            }
            $item->subkon_id = $request->subkon_id;
            $item->client_po_id = $request->client_po_id;

            $item->reference_id = $request->reference_id;
            $item->no_voucher = $request->no_voucher;
            $item->work_code = '';
            $item->job_name = $request->job_name;
            $item->for_voucher = '';
            $item->date_voucher = $request->date_voucher;
            $item->bussines_entity_code = '';
            $item->bussines_entity_type = '';
            $item->bussines_entity_name = '';
            $item->bill_number = $request->bill_number;
            $item->bill_date = $request->bill_date;
            $item->date_receipt_bill = $request->date_receipt_bill;
            $item->payment_description = $request->payment_description;
            $item->no_po_spk = '';
            $item->date_po_spk = null;
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

            $castAccount = Subkon::find($request->subkon_id);
            $item->bank_name = $castAccount->bank_name;
            $item->no_account = $castAccount->bank_account;
            $item->account_holder_name = $castAccount->account_holder_name;
            // $item->bank_name = '';
            // $item->no_account = '';
            $item->payment_type = $request->payment_type;
            $item->payment_status = $request->payment_status;
            $item->payment_date = $request->payment_date;
            $item->priority = $request->priority;
            $item->information = $request->information ?? '';
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
                // 'no_po_spk',
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
                //
                'account_source_id',
                'subkon_id',
                'client_po_id',
                'payment_date',
                'account_holder_name',
            ];

            $edit_flag = 0;

            $edit_field = [];

            foreach ($fieldsToLog as $field) {
                $old = optional($oldItem)->$field;
                $new = $item->$field;

                $normalizedOld = $this->normalize($old);
                $normalizedNew = $this->normalize($new);

                if ($normalizedOld !== $normalizedNew) {
                    $edit_field[] = $field;
                    $edit_flag++;
                }
            }

            if ($edit_flag > 0) {
                $voucher_edit = new VoucherEdit;
                $voucher_edit->voucher_id = $item->id;
                $voucher_edit->user_id  = backpack_auth()->user()->id;
                $voucher_edit->date_update = Carbon::now();
                $voucher_edit->history_update = "Mengedit data voucher";
                $voucher_edit->save();

                $users = User::permission(['APPROVE EDIT VOUCHER'])
                    ->orderBy('no_order', 'ASC')->get();

                foreach ($users as $key => $user) {
                    $approval = new Approval;
                    $approval->model_type = VoucherEdit::class;
                    $approval->model_id = $voucher_edit->id;
                    $approval->no_apprv = $key + 1;
                    $approval->user_id = $user->id;
                    $approval->position = '';
                    $approval->status = Approval::PENDING;
                    $approval->save();
                }

                // $journal_entry = JournalEntry::where('reference_type', Voucher::class)
                // ->where('reference_id', $item->id)->delete();
            }

            $this->data['entry'] = $this->crud->entry = $item;

            $voucher = $item;
            if ($voucher->reference_type == ClientPo::class) {
                if ($voucher->reference->status == 'TANPA PO') {
                    $client = ClientPo::find($voucher->reference_id);
                    $client->job_name = $voucher->job_name;
                    $client->load_general_value = $voucher->payment_transfer;
                    $client->save();
                }
            }

            $field_danger = [
                "account_id",
                "bill_value",
                "total",
                "pph_23",
                "discount_pph_23",
                "pph_4",
                "discount_pph_4",
                "pph_21",
                "discount_pph_21",
                "payment_transfer",
                "payment_status",
                "account_source_id",
                "payment_date"
            ];
            $flag_validation_field = false;

            foreach ($field_danger as $name_field) {
                if (in_array($name_field, $edit_field)) {
                    $flag_validation_field = true;
                    break;
                }
            }

            if ($flag_validation_field) {
                if ($flag_approval_status) {
                    // delete payment
                    $vp = PaymentVoucher::where('voucher_id', $item->id)->first();
                    if ($vp) {
                        $vpp = PaymentVoucherPlan::where('payment_voucher_id', $vp->id)->first();
                        if ($vpp) {
                            Approval::where('model_type', PaymentVoucherPlan::class)
                                ->where('model_id', $vpp->id)->delete();
                            $vpp->delete();
                        }
                        $vp->delete();
                    }
                }
            }

            CustomHelper::rollbackPayment(Voucher::class, $item->id);
            CustomHelper::voucherEntry($item);

            \Alert::success(trans('backpack::crud.update_success'))->flash();


            $this->crud->setSaveAction();

            DB::commit();
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $item,
                    'events' => [
                        'crudTable-filter_voucher_plugin_load' => $item,
                        'crudTable-voucher_updated_success' => $item,
                        'crudTable-history_edit_voucher_updated_success' => $item,
                    ]
                ]);
            }

            return $this->crud->performSaveAction($item->getKey());
        } catch (\Exception $e) {
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

        if ($voucher?->reference_type == ClientPo::class) {
            $voucher->reference->type = 'client';
        } else if ($voucher?->reference_type == PurchaseOrder::class) {
            $voucher->reference->type = 'subkon';
        } else if ($voucher?->reference_type == Spk::class) {
            $voucher->reference->type = 'spk';
        }

        $voucher->client_po = ClientPo::find($voucher->client_po_id);

        $this->data['entry'] = $voucher;

        $this->crud->setOperationSetting('fields', $this->crud->getUpdateFields());

        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.edit') . ' ' . $this->crud->entity_name;
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

    function generateIndexVoucher()
    {
        $total_voucher = Voucher::select('no_voucher')->orderBy('id', 'desc')->first();
        if (!$total_voucher) {
            $numAdd = 1;
            return $numAdd;
        }
        $numAdd = explode('-', $total_voucher->no_voucher)[0];
        $iterasi = 20;
        $countI = 0;
        do {
            $countI++;
            $numAdd++;

            $checkVoucherExists = Voucher::where('no_voucher', 'LIKE', $numAdd . '%')->first();

            if ($countI >= $iterasi) {
                break;
            }
        } while ($checkVoucherExists);

        return $numAdd;
    }

    public function select2WorkCode()
    {
        $this->crud->hasAccessOrFail('create');

        $search = request()->input('q');
        $po_client = ClientPo::select(DB::raw("id, work_code, 'client' as type"))
            ->where('work_code', 'LIKE', "%$search%");

        // $po_subkon = PurchaseOrder::select(DB::raw("id, work_code, 'subkon' as type"))
        // ->where('work_code', 'LIKE', "%$search%");

        $dataset = $po_client->paginate(20);

        $results = [];
        foreach ($dataset as $item) {
            $type = ucfirst($item->type);
            $results[] = [
                'id' => $item->id,
                'text' => $item->work_code . ' (' . $type . ')',
                'type' => $item->type,
            ];
        }
        return response()->json(['results' => $results]);
    }

    public function select2Subkon()
    {
        $this->crud->hasAccessOrFail('create');
        $search = request()->input('q');
        $dataset = Subkon::select(['id', 'name'])
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

        $request = $this->crud->validateRequest();

        $this->crud->registerFieldEvents();

        DB::beginTransaction();
        try {

            $event = [];
            $event['crudTable-filter_voucher_plugin_load'] = true;
            $data = $request->only(['bill_value', 'tax_ppn', 'pph_23', 'pph_4', 'pph_21']);

            $hasilPerhitungan = $this->calculatePayment($data);

            $type = strtolower($request->type);

            $item = new Voucher;
            $item->no_payment = $request->no_payment;
            $item->account_id  = $request->account_id;
            $item->account_source_id = $request->account_source_id;
            if ($type == 'client') {
                $item->reference_type = ClientPo::class;
            } else if ($type == 'subkon') {
                $item->reference_type = PurchaseOrder::class;
            } else if ($type == 'spk') {
                $item->reference_type = Spk::class;
            }
            $item->subkon_id = $request->subkon_id;
            $item->client_po_id = $request->client_po_id;

            $item->reference_id = $request->reference_id;
            $item->no_voucher = $request->no_voucher;
            $item->work_code = '';
            $item->job_name = $request->job_name;
            $item->for_voucher = '';
            $item->date_voucher = $request->date_voucher;
            $item->bussines_entity_code = '';
            $item->bussines_entity_type = '';
            $item->bussines_entity_name = '';
            $item->bill_number = $request->bill_number;
            $item->bill_date = $request->bill_date;
            $item->date_receipt_bill = $request->date_receipt_bill;
            $item->payment_description = $request->payment_description;
            $item->no_po_spk = $request->client_po_id;
            $item->date_po_spk = null;
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

            $castAccount = Subkon::find($request->subkon_id);
            $item->bank_name = $castAccount->bank_name;
            $item->no_account = $castAccount->bank_account;
            $item->account_holder_name = $castAccount->account_holder_name;

            $item->payment_type = $request->payment_type;
            $item->payment_status = "BELUM BAYAR"; // $request->payment_status;
            $item->payment_date = null; // $request->payment_date;
            $item->priority = $request->priority;
            $item->information = $request->information ?? '';
            $item->save();

            $voucher_edit = new VoucherEdit;
            $voucher_edit->voucher_id = $item->id;
            $voucher_edit->user_id  = backpack_auth()->user()->id;
            $voucher_edit->date_update = Carbon::now();
            $voucher_edit->history_update = "Menambahkan data voucher baru";
            $voucher_edit->save();

            $users = User::permission('APPROVE VOUCHER')
                ->orderBy('no_order', 'ASC')->get();

            foreach ($users as $key => $user) {
                $approval = new Approval;
                $approval->model_type = VoucherEdit::class;
                $approval->model_id = $voucher_edit->id;
                $approval->no_apprv = $key + 1;
                $approval->user_id = $user->id;
                $approval->position = '';
                $approval->status = Approval::PENDING;
                $approval->save();
            }

            $voucher = $item;
            if ($voucher->reference_type == ClientPo::class) {
                if ($voucher->reference->status == 'TANPA PO') {
                    $client = ClientPo::find($voucher->reference_id);
                    $client->job_name = $voucher->job_name;
                    $client->load_general_value = $voucher->payment_transfer;
                    $client->save();
                }
            }

            CustomHelper::voucherEntry($item);
            CustomHelper::voucherCreate($item->id);

            $event['crudTable-voucher_create_success'] = $item;
            $event['crudTable-history_edit_voucher_create_success'] = $item;

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
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function addTransaction($id)
    {
        $voucher = Voucher::find($id);
        $transaksi = new AccountTransaction;
        $transaksi->cast_account_id = $voucher->account_source_id;
        $transaksi->reference_type = Voucher::class;
        $transaksi->reference_id = $id;
        $transaksi->date_transaction = Carbon::now()->format('Y-m-d');
        $transaksi->nominal_transaction = $voucher->payment_transfer;
        $transaksi->total_saldo_before = 0;
        $transaksi->total_saldo_after = 0;
        $transaksi->status = CastAccount::ENTER;
        $transaksi->kdp = $voucher?->reference?->work_code;
        $transaksi->job_name = $voucher?->reference?->job_name;
        $transaksi->save();

        $po = $voucher->reference;

        CustomHelper::updateOrCreateJournalEntry([
            'account_id' => $voucher->account_id,
            'reference_id' => $transaksi->id,
            'reference_type' => AccountTransaction::class,
            'description' => $transaksi->kdp,
            'date' => Carbon::now(),
            'debit' => $voucher->payment_transfer,
            // 'credit' => ($status == CastAccount::OUT) ? $nominal_transaction : 0,
        ], [
            'account_id' => $voucher->account_id,
            'reference_id' => $transaksi->id,
            'reference_type' => AccountTransaction::class,
        ]);

        return 1;
    }

    public function approvedStore($id)
    {
        $this->crud->hasAccessOrFail('create');

        $request = $this->crud->validateRequest();

        $this->crud->registerFieldEvents();

        DB::beginTransaction();
        try {

            $user_id = backpack_user()->id;

            $voucher_edit = VoucherEdit::find($id);
            $voucher_id = $voucher_edit->voucher_id;

            $event = [];

            $approval_before = Approval::where('model_type', VoucherEdit::class)
                ->where('model_id', $voucher_edit->id)
                ->where('no_apprv', '<', $request->no_apprv)->first();

            if ($approval_before) {
                if ($approval_before->status != Approval::APPROVED) {
                    throw new \Exception('Approval sebelumnya belum disetujui');
                }
            }

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

            if ($request->action == Approval::APPROVED) {

                if ($final_approval->no_apprv == $request->no_apprv) {
                    // $this->addTransaction($voucher_id);
                }

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

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function setupShowOperation()
    {
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
            'label'       => trans('backpack::crud.voucher.field.work_code.label'), // Table column heading
            'type'        => "select2_ajax_custom",
            'name'        => 'client_po_id',
            'entity'      => 'client_po',
            'model'       => 'App\Models\ClientPo',
            'attribute'   => "work_code",
            'data_source' => backpack_url('fa/voucher/select2-work-code'),
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.voucher.field.work_code.placeholder'),
            ]
        ]);

        CRUD::addField([
            'name' => 'account_source_id',
            'label' => trans('backpack::crud.voucher.field.account_source_id.label'),
            'type' => 'select2_array',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'options' => [],
            'attributes' => [
                // 'disabled' => true,
                'placeholder' => trans('backpack::crud.voucher.field.bussines_entity_name.placeholder'),
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
            'name' => 'no_voucher',
            'label' => trans('backpack::crud.voucher.field.no_voucher.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.voucher.field.no_voucher.placeholder'),
            ],
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
            'label' => trans('backpack::crud.voucher.field.bussines_entity_name.label'),
            'type'        => "select2_ajax_custom",
            'name'        => 'subkon_id',
            'entity'      => 'subkon',
            'model'       => 'App\Models\Subkon',
            'attribute'   => "name",
            'data_source' => backpack_url('fa/voucher/select2-subkon'),
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.voucher.field.bussines_entity_name.placeholder'),
            ]
        ]);

        CRUD::addField([
            'name' => 'account_holder_name',
            'label' => trans('backpack::crud.voucher.field.account_holder_name.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'disabled' => true,
                'placeholder' => trans('backpack::crud.voucher.field.account_holder_name.placeholder'),
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
            'type'        => "select2_ajax_custom",
            'name'        => 'reference_id',
            'entity'      => 'client_po',
            'model'       => 'App\Models\CLientPo',
            'attribute'   => "po_number",
            'data_source' => backpack_url('fa/voucher/select2-po-spk'),
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.voucher.field.no_po_spk.placeholder'),
            ]
        ]);

        CRUD::addField([
            'name' => 'space_1',
            'type' => 'hidden',
            'label' => '',
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ]
        ]);

        // CRUD::addField([   // date_picker
        //     'name'  => 'date_po_spk',
        //     'type'  => 'text',
        //     'label' => trans('backpack::crud.voucher.field.date_po_spk.label'),

        //     // optional:
        //     'date_picker_options' => [
        //         'language' => App::getLocale(),
        //     ],
        //     'wrapper'   => [
        //         'class' => 'form-group col-md-6'
        //     ],
        //     'suffix' => '<span class="la la-calendar"></span>',
        //     'attributes' => [
        //         'disabled' => true,
        //     ]
        // ]);

        CRUD::addField([
            'name' => 'bill_value',
            'label' =>  trans('backpack::crud.voucher.field.bill_value.label'),
            'type' => 'mask',
            'mask' => '000.000.000.000.000.000',
            'mask_options' => [
                'reverse' => true
            ],
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
                'AKAN ADA' => 'AKAN ADA',
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
            ],
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
                // 'disabled' => true,
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
                // 'disabled' => true,
                'placeholder' => trans('backpack::crud.voucher.field.no_account.placeholder'),
            ]
        ]);

        CRUD::addField([  // Select2
            'label'     => trans('backpack::crud.voucher.field.payment_type.label'),
            'type'      => 'select2_array',
            'name'      => 'payment_type',
            'options'   => [
                '' => trans('backpack::crud.voucher.field.payment_type.placeholder'),
                'SUBKON' => 'SUBKON',
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

        CRUD::addField([   // date_picker
            'name'  => 'payment_date',
            'type'  => 'date_picker',
            'label' => trans('backpack::crud.voucher.field.payment_date.label'),

            // optional:
            'date_picker_options' => [
                'language' => App::getLocale(),
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::addField([  // Select2
            'label'     => trans('backpack::crud.voucher.field.priority.label'),
            'type'      => 'select2_array',
            'name'      => 'priority',
            'options'   => [
                '' => trans('backpack::crud.voucher.field.priority.placeholder'),
                'HARI INI' => 'HARI INI',
                'MINGGU INI' => 'MINGGU INI',
                'TEMPO' => 'TEMPO'
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


        // column

        // CRUD::column(
        //     [
        //         'label'  => '',
        //         'name' => 'account_id',
        //         'type'  => 'closure',
        //         'function' => function($entry){
        //             return $entry->account->code ." - ".$entry->account->name;
        //         }
        //     ],
        // );

        //  CRUD::column([
        //     'label'  => '',
        //     'name' => 'payment_transfer',
        //     'type'  => 'number',
        //     'prefix' => "Rp.",
        //     'decimals'      => 2,
        //     'dec_point'     => ',',
        //     'thousands_sep' => '.',
        // ]);

        // CRUD::column([
        //     'label'  => '',
        //     'name' => 'pph_23',
        //     'type'  => 'number',
        //     'suffix' => '%',
        // ]);

        // CRUD::column([
        //     'label'  => '',
        //     'name' => 'due_date',
        //     'type'  => 'date',
        //     'format' => 'D MMM Y'
        // ]);

        CRUD::column([
            'label'  => '',
            'name' => 'no_payment',
            'type'  => 'wrap_text',
        ]);

        CRUD::column(
            [
                'label'  => '',
                'name' => 'account_id',
                'type'  => 'closure',
                'function' => function ($entry) {
                    return $entry->account->code . " - " . $entry->account->name;
                }
            ],
        );

        CRUD::column(
            [
                'label'  => '',
                'name' => 'client_po_id',
                'type'  => 'closure',
                'function' => function ($entry) {
                    return $entry?->client_po?->work_code;
                }
            ],
        );

        CRUD::column(
            [
                'label'  => '',
                'name' => 'account_source_id',
                'type'  => 'closure',
                'function' => function ($entry) {
                    return $entry->account_source->name;
                }
            ],
        );

        CRUD::column([
            'label'  => '',
            'name' => 'job_name',
            'type'  => 'wrap_text',
        ]);

        CRUD::column([
            'label'  => '',
            'name' => 'no_voucher',
            'type'  => 'text',
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
                'name' => 'subkon_id',
                'type'  => 'closure',
                'function' => function ($entry) {
                    return $entry->subkon->name;
                }
            ],
        );

        CRUD::column(
            [
                'label'  => '',
                'name' => 'account_holder_name',
                'type'  => 'closure',
                'function' => function ($entry) {
                    return $entry->account_holder_name ?? $entry->subkon->account_holder_name;
                }
            ],
        );

        CRUD::column([
            'label'  => '',
            'name' => 'bill_number',
            'type'  => 'wrap_text',
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

        CRUD::column([
            'label'  => '',
            'name' => 'payment_description',
            'type'  => 'wrap_text',
        ]);

        CRUD::column(
            [
                'label'  => '',
                'name' => 'reference_id',
                'type'  => 'closure',
                'function' => function ($entry) {
                    if ($entry->reference_type == Spk::class) {
                        return $entry?->reference?->no_spk;
                    }
                    return $entry?->reference?->po_number;
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

        CRUD::column([
            'label'  => '',
            'name' => 'factur_status',
            'type'  => 'text',
        ]);

        CRUD::column([
            'label'  => '',
            'name' => 'no_factur',
            'type'  => 'text',
        ]);

        CRUD::column([
            'label'  => '',
            'name' => 'date_factur',
            'type'  => 'date',
            'format' => 'D MMM Y'
        ]);

        CRUD::column([
            'label'  => '',
            'name' => 'bank_name',
            'type'  => 'text',
        ]);

        CRUD::column([
            'label'  => '',
            'name' => 'no_account',
            'type'  => 'text',
        ]);

        CRUD::column([
            'label'  => '',
            'name' => 'payment_type',
            'type'  => 'text',
        ]);

        CRUD::column([
            'label'  => '',
            'name' => 'payment_status',
            'type'  => 'text',
        ]);

        CRUD::column([
            'label'  => '',
            'name' => 'payment_date',
            'type'  => 'date',
            'format' => 'D MMM Y'
        ]);

        CRUD::column([
            'label'  => '',
            'name' => 'priority',
            'type'  => 'text',
        ]);

        CRUD::column([
            'label'  => '',
            'name' => 'information',
            'type'  => 'wrap_text',
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

        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.preview') . ' ' . $this->crud->entity_name;

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

            foreach ($voucher_edit as $edit_v) {
                $approval = Approval::where('model_type', VoucherEdit::class)
                    ->where('model_id', $edit_v->id)->delete();
                $edit_v->delete();
            }

            // hapus transaksi voucher
            CustomHelper::rollbackPayment(Voucher::class, $id);

            $item->delete();

            $messages['success'][] = trans('backpack::crud.delete_confirmation_message');
            $messages['events'] = [
                'crudTable-filter_voucher_plugin_load' => true,
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

    public function print($id)
    {
        Carbon::setLocale('id');
        $voucher = Voucher::find($id);
        $voucher->total_str = CustomHelper::formatRupiahWithCurrency($voucher->total);
        $voucher->discount_pph_23_str = CustomHelper::formatRupiahWithCurrency($voucher->discount_pph_23);
        $voucher->discount_pph_4_str = CustomHelper::formatRupiahWithCurrency($voucher->discount_pph_4);
        $voucher->bill_value_str = CustomHelper::formatRupiahWithCurrency($voucher->bill_value);
        $voucher->discount_pph_21_str = CustomHelper::formatRupiahWithCurrency($voucher->discount_pph_21);
        $voucher->payment_transfer_str = CustomHelper::formatRupiahWithCurrency($voucher->payment_transfer);

        $voucher->price_ppn_str = ($voucher->bill_value * ($voucher->tax_ppn / 100));
        $voucher->price_ppn_str = CustomHelper::formatRupiahWithCurrency($voucher->price_ppn_str);

        if ($voucher->reference == PurchaseOrder::class) {
            $voucher->reference_date_str = $voucher->reference->date_po;
            $voucher->reference_date_str = Carbon::parse($voucher->reference_date_str)->translatedFormat('d F Y');
        } else {
            $voucher->reference_date_str = $voucher?->reference?->date_spk;
            $voucher->reference_date_str = $voucher->reference_date_str ? Carbon::parse($voucher->reference_date_str)->translatedFormat('d F Y') : '';
        }

        $voucher->date_receipt_bill_str = Carbon::parse($voucher->date_receipt_bill)->translatedFormat('d F Y');
        $voucher->date_voucher_str = Carbon::parse($voucher->date_voucher)->translatedFormat('d F Y');
        $voucher->due_date_str = Carbon::parse($voucher->due_date)->translatedFormat('d F Y');
        $voucher->bill_date_str = Carbon::parse($voucher->bill_date)->translatedFormat('d F Y');

        $voucher->date_factur_str = '';
        if ($voucher->date_factur) {
            $voucher->date_factur_str = Carbon::parse($voucher->date_factur)->translatedFormat('d F Y');
        }

        $voucher->payment_date_str = '';
        if ($voucher->payment_date) {
            $voucher->payment_date_str = Carbon::parse($voucher->payment_date)->translatedFormat('d F Y');
        }

        $numberToWords = new \NumberToWords\NumberToWords();
        $numberTransformer = $numberToWords->getNumberTransformer('id');
        $voucher->payment_transfer_word = ucwords($numberTransformer->toWords($voucher->payment_transfer));

        $voucher->date_now_str = Carbon::now()->translatedFormat('d F Y');

        $pdf = Pdf::loadView('exports.voucher-pdf-origin', [
            'voucher' => $voucher,
        ])
            ->setPaper('A4', 'portrait');
        return $pdf->stream("voucher-$voucher->no_voucher.pdf");
    }

    public function exportPdf()
    {
        $type = request()->tab;

        $this->setupListExport($type);
        // $this->setupListOperation();

        $columns = $this->crud->columns();
        $items =  $this->crud->getEntries();

        $row_number = 0;

        $all_items = [];

        foreach ($items as $item) {
            $row_items = [];
            $row_number++;
            foreach ($columns as $column) {
                $item_value = ($column['name'] == 'row_number') ? $row_number : $this->crud->getCellView($column, $item, $row_number);
                $item_value = str_replace('<span>', '', $item_value);
                $item_value = str_replace('</span>', '', $item_value);
                $item_value = str_replace("\n", '', $item_value);
                $item_value = CustomHelper::clean_html($item_value);
                $row_items[] = trim($item_value);
            }
            $all_items[] = $row_items;
        }

        $title = "VOUCHER";
        if ($type == 'voucher_edit') {
            $title = "VOUCHER EDIT";
        }

        $pdf = Pdf::loadView('exports.table-pdf', [
            'columns' => $columns,
            'items' => $all_items,
            'title' => $title
        ])->setPaper('A4', 'landscape');

        $fileName = 'vendor_po_' . now()->format('Ymd_His') . '.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function exportExcel()
    {
        $type = request()->tab;

        $this->setupListExport($type);
        // $this->setupListOperation();

        $columns = $this->crud->columns();
        $items =  $this->crud->getEntries();

        $row_number = 0;

        $all_items = [];

        foreach ($items as $item) {
            $row_items = [];
            $row_number++;
            foreach ($columns as $column) {
                $item_value = ($column['name'] == 'row_number') ? $row_number : $this->crud->getCellView($column, $item, $row_number);
                $item_value = str_replace('<span>', '', $item_value);
                $item_value = str_replace('</span>', '', $item_value);
                $item_value = str_replace("\n", '', $item_value);
                $item_value = CustomHelper::clean_html($item_value);
                $row_items[] = trim($item_value);
            }
            $all_items[] = $row_items;
        }

        $name = 'VOUCHER';
        if ($type == 'voucher_edit') {
            $name = "VOUCHER EDIT";
        }

        return response()->streamDownload(function () use ($type, $columns, $items, $all_items) {
            echo Excel::raw(new ExportExcel(
                $columns,
                $all_items
            ), \Maatwebsite\Excel\Excel::XLSX);
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
