<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Account;
use App\Models\Setting;
use App\Models\Voucher;
use App\Models\ClientPo;
use App\Models\JournalEntry;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\Rule;
use App\Http\Exports\ExportExcel;
use App\Models\ProjectProfitLost;
use App\Http\Helpers\CustomHelper;
use App\Models\AccountTransaction;
use Illuminate\Support\Facades\DB;
use App\Models\ProjectProfitLostLog;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Exports\ProfitLostExcel;
use App\Models\ConsolidateIncomeItem;
use App\Http\Controllers\CrudController;
use App\Http\Controllers\Operation\FormaterExport;
use App\Http\Exports\ExportProfitLostConsolidation;
use App\Http\Controllers\Operation\PermissionAccess;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class ProfitLostAccountCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use PermissionAccess;
    use FormaterExport;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(Account::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/finance-report/profit-lost');
        CRUD::setEntityNameStrings(trans('backpack::crud.profit_lost.title_header'), trans('backpack::crud.profit_lost.title_header'));

        $base = 'INDEX LAPORAN KEUANGAN LABA RUGI';
        $allAccess = ['AKSES SEMUA MENU ACCOUNTING'];
        $viewMenu  = ["MENU $base"];

        $this->settingPermission([
            'create' => ["CREATE $base", ...$allAccess],
            'update' => ["UPDATE $base", ...$allAccess],
            'delete' => ["DELETE $base", ...$allAccess],
            'list'   => $viewMenu,
            'show'   => $viewMenu,
            'print'  => true,
        ]);
    }

    public function total_report_account_profit_lost_ajax_old()
    {
        $acct_1 = Account::where('accounts.code', 109)
            ->leftJoin('journal_entries', 'journal_entries.account_id', '=', 'accounts.id')
            ->select(DB::raw('SUM(journal_entries.debit - journal_entries.credit) as balance'))
            ->first();

        $acct_2 = Account::where('accounts.code', 401)
            ->leftJoin('journal_entries', 'journal_entries.account_id', '=', 'accounts.id')
            ->select(DB::raw('SUM(journal_entries.debit - journal_entries.credit) as balance'))
            ->first();

        $acct_3 = Account::where('accounts.code', 402)
            ->leftJoin('journal_entries', 'journal_entries.account_id', '=', 'accounts.id')
            ->select(DB::raw('SUM(journal_entries.debit - journal_entries.credit) as balance'))
            ->first();

        $acct_4 = Account::where('accounts.code', 110)
            ->leftJoin('journal_entries', 'journal_entries.account_id', '=', 'accounts.id')
            ->select(DB::raw('SUM(journal_entries.debit - journal_entries.credit) as balance'))
            ->first();

        $acct_5 = Account::where('accounts.code', 11001)
            ->leftJoin('journal_entries', 'journal_entries.account_id', '=', 'accounts.id')
            ->select(DB::raw('SUM(journal_entries.debit - journal_entries.credit) as balance'))
            ->first();

        $acct_6 = Account::where('accounts.code', 11002)
            ->leftJoin('journal_entries', 'journal_entries.account_id', '=', 'accounts.id')
            ->select(DB::raw('SUM(journal_entries.debit - journal_entries.credit) as balance'))
            ->first();

        $acct_7 = Account::where('accounts.code', 111)
            ->leftJoin('journal_entries', 'journal_entries.account_id', '=', 'accounts.id')
            ->select(DB::raw('SUM(journal_entries.debit - journal_entries.credit) as balance'))
            ->first();

        $acct_8 = Account::where('accounts.code', 112)
            ->leftJoin('journal_entries', 'journal_entries.account_id', '=', 'accounts.id')
            ->select(DB::raw('SUM(journal_entries.debit - journal_entries.credit) as balance'))
            ->first();

        $acct_9 = Account::where('accounts.code', 40201)
            ->leftJoin('journal_entries', 'journal_entries.account_id', '=', 'accounts.id')
            ->select(DB::raw('SUM(journal_entries.debit - journal_entries.credit) as balance'))
            ->first();

        $acct_10 = Account::where('accounts.code', 113)
            ->leftJoin('journal_entries', 'journal_entries.account_id', '=', 'accounts.id')
            ->select(DB::raw('SUM(journal_entries.debit - journal_entries.credit) as balance'))
            ->first();

        $acct_11 = Account::where('accounts.code', 11301)
            ->leftJoin('journal_entries', 'journal_entries.account_id', '=', 'accounts.id')
            ->select(DB::raw('SUM(journal_entries.debit - journal_entries.credit) as balance'))
            ->first();

        $acct_12 = Account::where('accounts.code', 108)
            ->leftJoin('journal_entries', 'journal_entries.account_id', '=', 'accounts.id')
            ->select(DB::raw('SUM(journal_entries.debit - journal_entries.credit) as balance'))
            ->first();

        $total_acct_1 = $acct_1->balance + $acct_2->balance;
        $total_acct_4 =  $acct_5->balance + $acct_6->balance;
        $total_acct_8 = $acct_9->balance;
        $total_acct_10 = $acct_11->balance;

        return response()->json([
            'total_acct_1' => CustomHelper::formatRupiahWithCurrency($total_acct_1),
            'total_acct_2' => CustomHelper::formatRupiahWithCurrency($acct_2->balance),
            'total_acct_3' => CustomHelper::formatRupiahWithCurrency($acct_3->balance),
            'total_acct_4' => CustomHelper::formatRupiahWithCurrency($total_acct_4),
            'total_acct_5' => CustomHelper::formatRupiahWithCurrency($acct_5->balance),
            'total_acct_6' => CustomHelper::formatRupiahWithCurrency($acct_6->balance),
            'total_acct_7' => CustomHelper::formatRupiahWithCurrency($acct_7->balance),
            'total_acct_8' => CustomHelper::formatRupiahWithCurrency($total_acct_8),
            'total_acct_9' => CustomHelper::formatRupiahWithCurrency($acct_9->balance),
            'total_acct_10' => CustomHelper::formatRupiahWithCurrency($total_acct_10),
            'total_acct_11' => CustomHelper::formatRupiahWithCurrency($acct_11->balance),
            'total_acct_12' => CustomHelper::formatRupiahWithCurrency($acct_12->balance)
        ]);
    }

    public function total_report_account_profit_lost_ajax()
    {
        return $this->consolidate_formula();
    }

    public function listCardComponents($type)
    {
        $dataset = Account::where('type', $type)
            ->whereIn('level', [2])
            ->where('is_active', 1)->orderBy('code', 'asc')->get();

        $this->card->addCard([
            'name' => 'report_profit_lost',
            'line' => 'top',
            'view' => 'crud::components.card-report-account-profit',
            'params' => [
                'crud' => $this->crud,
                'route' => url($this->crud->route . '/report-total'),
            ]
        ]);

        if ($dataset->count() > 0) {
            // foreach($dataset as $account){
            //     $this->card->addCard([
            //         'name' => 'account_'.$account->id,
            //         'line' => 'top',
            //         'view' => 'crud::components.card-account-profit',
            //         'params' => [
            //             'crud' => $this->crud,
            //             'account' => $account,
            //             'route' => url($this->crud->route.'/search?_id='.$account->id),
            //         ]
            //     ]);
            // }
        } else {
            // $this->card->addCard([
            //     'name' => 'blank_account',
            //     'line' => 'top',
            //     'view' => 'crud::components.blank_card-account-profit',
            //     'params' => [
            //         'message' => 'selamat malam',
            //     ],
            // ]);
        }

        $this->crud->filter('category77crudTable-project')
            ->label('Kategori')
            ->type('select2')
            ->values([
                'RUTIN' => 'RUTIN',
                'NON RUTIN' => 'NON RUTIN',
            ]);

        $this->card->addCard([
            'name' => 'project',
            'line' => 'bottom',
            'view' => 'crud::components.datatable-origin',
            'params' => [
                'title' => trans('backpack::crud.profit_lost.project_income_statement'),
                'crud_custom' => $this->crud,
                'columns' => [
                    [
                        'name'      => 'row_number',
                        'type'      => 'row_number',
                        'label'     => 'No',
                        'orderable' => false,
                    ],
                    [
                        'label' => trans('backpack::crud.profit_lost.column.client_po_id'),
                        'type'      => 'select',
                        'name'      => 'client_po_id',
                        'orderable' => true,
                    ],
                    [
                        'label'  => trans('backpack::crud.client_po.column.reimburse_type'),
                        'type'      => 'text',
                        'name'      => 'reimburse_type',
                        'orderable' => true,
                    ],
                    [
                        'label'  => trans('backpack::crud.client_po.column.work_code'),
                        'type'      => 'text',
                        'name'      => 'work_code',
                        'orderable' => true,
                    ],
                    [
                        'label'  => trans('backpack::crud.client_po.column.po_number'),
                        'type'      => 'text',
                        'name'      => 'po_number',
                        'orderable' => true,
                    ],
                    [
                        'label'  => trans('backpack::crud.client_po.column.job_name'),
                        'type'      => 'text',
                        'name'      => 'job_name',
                        'orderable' => true,
                    ],
                    [
                        'label'  => trans('backpack::crud.client_po.column.job_value_exclude_ppn'),
                        'type'      => 'text',
                        'name'      => 'job_value',
                        'orderable' => true,
                    ],
                    [
                        'label'  => trans('backpack::crud.client_po.column.job_value_include_ppn'),
                        'type'      => 'text',
                        'name'      => 'job_value_include_ppn',
                        'orderable' => true,
                    ],
                    [
                        'label'  => trans('backpack::crud.client_po.column.price_after_year'),
                        'type'      => 'text',
                        'name'      => 'price_after_year',
                        'orderable' => true,
                    ],
                    [
                        'label'  => trans('backpack::crud.profit_lost.column.price_voucher'),
                        'type'      => 'text',
                        'name'      => 'price_voucher',
                        'orderable' => true,
                    ],
                    [
                        'label'  => trans('backpack::crud.profit_lost.column.price_small_cash'),
                        'type'      => 'text',
                        'name'      => 'price_small_cash',
                        'orderable' => true,
                    ],
                    [
                        'label'  => trans('backpack::crud.profit_lost.column.price_total'),
                        'type'      => 'text',
                        'name'      => 'price_total',
                        'orderable' => true,
                    ],
                    [
                        'label'  => trans('backpack::crud.profit_lost.column.profit_lost_po'),
                        'type'      => 'text',
                        'name'      => 'profit_lost_po',
                        'orderable' => true,
                    ],
                    [
                        'label'  => trans('backpack::crud.profit_lost.column.load_general_value'),
                        'type'      => 'text',
                        'name'      => 'load_general_value',
                        'orderable' => true,
                    ],
                    [
                        'label'  => trans('backpack::crud.profit_lost.column.profit_lost_final'),
                        'type'      => 'text',
                        'name'      => 'profit_lost_final',
                        'orderable' => true,
                    ],
                    [
                        'label'  => trans('backpack::crud.profit_lost.column.category'),
                        'type'      => 'text',
                        'name'      => 'category',
                        'orderable' => true,
                    ],
                    [
                        'label'  => trans('backpack::crud.profit_lost.column.invoice_date'),
                        'type'      => 'text',
                        'name'      => 'invoice_date',
                        'orderable' => true,
                    ],
                    [
                        'name' => 'action',
                        'type' => 'action',
                        'label' =>  trans('backpack::crud.actions'),
                    ]
                ],
                'filter_table' => collect($this->crud->filters())->slice(0, 2),
                'route' => url($this->crud->route . '/search?type=project'),
            ]
        ]);

        $this->card->addCard([
            'name' => 'profit-lost-plugin',
            'line' => 'top',
            'view' => 'crud::components.profit-lost-plugin',
            'parent_view' => 'crud::components.filter-parent',
            'params' => [],
        ]);
    }

    public function get_total_excl_ppn_final_profit()
    {
        $request = request();
        $referenceType = 'App\\Models\\AccountTransaction';      // isi param kamu
        $castNameLike  = '%kas kecil%';
        $category      = ($request->has('category')) ? $request->category : null;

        // ============ SUBQUERY: vouchers ===============
        $vouchers = DB::table('vouchers')
            ->select(
                'client_po_id',
                DB::raw('SUM(payment_transfer) AS payment_transfer'),
                DB::raw('SUM(total) AS voucher_biaya')
            )
            ->groupBy('client_po_id');

        // ============ SUBQUERY: invoice_clients ===============
        $invoiceClients = DB::table('invoice_clients')
            ->select(
                'client_po_id',
                DB::raw("GROUP_CONCAT(invoice_date SEPARATOR ',') AS invoice_date"),
                DB::raw("SUM(price_total_exclude_ppn) AS price_job_exlude_ppn"),
                DB::raw("SUM(price_total_include_ppn) AS price_job_include_ppn")
            )
            ->groupBy('client_po_id');

        $client_po_query_exclude_ppn = DB::table("client_po")
            ->leftJoinSub($invoiceClients, 'invoice', function ($join) {
                $join->on('invoice.client_po_id', '=', 'client_po.id');
            })->select(
                "client_po.id as client_po_id",
                'client_po.work_code',
                'client_po.po_number',
                'client_po.reimburse_type',
                'client_po.job_name',
                'client_po.job_value',
                "client_po.category",
                DB::raw("IF(invoice.invoice_date IS NULL, client_po.job_value, invoice.price_job_exlude_ppn) as price_job_exlude_ppn_logic")
            );

        // ============ QUERY UTAMA ===============
        $mainQuery = DB::table('project_profit_lost')
            ->select([
                'vouchers_logic.voucher_biaya as voucher_biaya',
                'client_po.price_job_exlude_ppn_logic',
                DB::raw('IFNULL(project_profit_lost.price_small_cash, 0) as total_small_cash'),
                DB::raw('(IFNULL(project_profit_lost.price_after_year,0)
                        + IFNULL(vouchers_logic.voucher_biaya,0)
                        + IFNULL(project_profit_lost.price_small_cash,0)
                    ) AS price_total_str'),
                DB::raw('(client_po.price_job_exlude_ppn_logic -
                        (IFNULL(project_profit_lost.price_after_year,0)
                        + IFNULL(vouchers_logic.voucher_biaya,0)
                        + IFNULL(project_profit_lost.price_small_cash,0))
                    ) AS price_profit_lost_str'),
                DB::raw('
                    ((client_po.price_job_exlude_ppn_logic -
                        (IFNULL(project_profit_lost.price_after_year,0)
                        + IFNULL(vouchers_logic.voucher_biaya,0)
                        + IFNULL(project_profit_lost.price_small_cash,0)))
                    - IFNULL(project_profit_lost.price_general, 0)
                ) AS price_prift_lost_final_str')
            ])
            ->leftJoinSub($client_po_query_exclude_ppn, 'client_po', function ($join) {
                $join->on('client_po.client_po_id', '=', 'project_profit_lost.client_po_id');
            })
            ->leftJoinSub($vouchers, 'vouchers_logic', 'vouchers_logic.client_po_id', 'client_po.client_po_id');

        $total_excl_ppn_logic = DB::table('project_profit_lost')
            ->leftJoinSub($client_po_query_exclude_ppn, 'dummy_query', function ($join) {
                $join->on('dummy_query.client_po_id', '=', 'project_profit_lost.client_po_id');
            })
            ->select(
                DB::raw("SUM(dummy_query.price_job_exlude_ppn_logic) as total_excl_ppn_logic")
            );


        if ($category) {
            $mainQuery = $mainQuery->where('client_po.category', $category);
            $total_excl_ppn_logic = $total_excl_ppn_logic->where('dummy_query.category', $category);
        }

        $total_excl_ppn_logic = $total_excl_ppn_logic->first();

        // ============ BUNGKUS SUBQUERY + SUM ===============
        $result = DB::query()
            ->fromSub($mainQuery, 't')
            ->selectRaw('SUM(t.price_prift_lost_final_str) AS total_price_prift_lost_final_str')
            ->first();

        return response()->json([
            'total_price_exlude_ppn' => CustomHelper::formatRupiahWithCurrency($total_excl_ppn_logic->total_excl_ppn_logic ?? 0),
            'total_price_prift_lost_finals' => CustomHelper::formatRupiahWithCurrency($result->total_price_prift_lost_final_str ?? 0)
        ]);
    }

    public function select2Account()
    {
        $search = request()->input('q');
        $dataset = \App\Models\Account::select(['id', 'code', 'name'])
            // ->where('level', ">", 2)
            ->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%")
                    ->orWhere('code', 'LIKE', "%$search%");
            })
            ->orderBy('code', 'ASC')
            ->paginate(10);

        $results = [];
        foreach ($dataset as $item) {
            $results[] = [
                'id' => $item->id,
                'text' => $item->code . ' - ' . $item->name,
            ];
        }
        return response()->json(['results' => $results]);
    }

    public function index()
    {
        $this->crud->hasAccessOrFail('list');

        $this->data['is_disabled_list'] = true;

        $this->listCardComponents(Account::INCOME);

        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? mb_ucfirst($this->crud->entity_name_plural);
        $this->data['title_modal_create'] = trans('backpack::crud.profit_lost.title_modal_create');
        $this->data['title_modal_edit'] = trans('backpack::crud.profit_lost.title_modal_edit_consolidation');
        $this->data['title_modal_delete'] = trans('backpack::crud.profit_lost.title_modal_delete_consolidation');

        $breadcrumbs = [
            trans('backpack::crud.menu.finance_report') => backpack_url('cash-flow'),
            trans('backpack::crud.menu.profit_lost') => backpack_url($this->crud->route)
        ];
        $this->data['breadcrumbs'] = $breadcrumbs;

        $this->data['cards'] = $this->card;
        $this->data['modals'] = $this->modal;
        $this->data['scripts'] = $this->script;
        $list = "crud::list-blank" ?? $this->crud->getListView();
        $this->consolidate_formula();

        return view($list, $this->data);
    }

    public function total_detail_project($id, $pure = 0)
    {
        $profitLost = ProjectProfitLost::where('id', $id)->first();
        $po = $profitLost->clientPo;
        $account_material = Account::where('code', 50101)->first();
        $price_po_excl_ppn = $po->job_value; //

        $profit_lost_all_price = CustomHelper::profitLostRepository()
            ->where('project_profit_lost.client_po_id', $po->id)
            ->first();

        // $material_data = AccountTransaction::leftJoin('journal_entries', function ($join) {
        //     $join->on('journal_entries.reference_id', '=', 'account_transactions.id')
        //         ->where('journal_entries.reference_type', '=', 'App\\Models\\AccountTransaction');
        // })
        //     ->where('journal_entries.account_id', $account_material->id)
        //     ->where('account_transactions.kdp', $po->work_code)
        //     ->selectRaw("(SUM(journal_entries.debit) - SUM(journal_entries.credit)) as total_material")
        //     ->get()->sum('total_material'); //
        $material_data = Voucher::where('client_po_id', $po->id)
            ->where('account_id', $account_material->id)
            ->select(DB::raw("SUM(total) as biaya"))->groupBy('client_po_id')->get()->sum('biaya'); //


        $account_subkon = Account::whereIn('code', [50102, 50103])->get()
            ->pluck('id')->toArray();

        // $subkon_data = AccountTransaction::leftJoin('journal_entries', function ($join) {
        //     $join->on('journal_entries.reference_id', '=', 'account_transactions.id')
        //         ->where('journal_entries.reference_type', '=', 'App\\Models\\AccountTransaction');
        // })
        //     ->whereIn('journal_entries.account_id', $account_subkon)
        //     ->where('account_transactions.kdp', $po->work_code)
        //     ->selectRaw("(SUM(journal_entries.debit) - SUM(journal_entries.credit)) as total_subkon")
        //     ->get()->sum('total_subkon'); //

        $subkon_data = Voucher::where('client_po_id', $po->id)
            ->whereIn('account_id', $account_subkon)
            ->select(DB::raw("SUM(total) as subkon"))->groupBy('client_po_id')->get()->sum('subkon'); //


        $account_btkl = Account::where('code', 50104)->first();

        // $btkl_data = AccountTransaction::leftJoin('journal_entries', function ($join) {
        //     $join->on('journal_entries.reference_id', '=', 'account_transactions.id')
        //         ->where('journal_entries.reference_type', '=', 'App\\Models\\AccountTransaction');
        // })
        //     ->where('journal_entries.account_id', $account_btkl->id)
        //     ->where('account_transactions.kdp', $po->work_code)
        //     ->selectRaw("(SUM(journal_entries.debit) - SUM(journal_entries.credit)) as total_material")
        //     ->get()->sum('total_material'); //

        $btkl_data = Voucher::where('client_po_id', $po->id)
            ->where('account_id', $account_btkl->id)
            ->select(DB::raw("SUM(total) as btkl"))->groupBy('client_po_id')->get()->sum('btkl'); //

        $account_other = Account::whereIn('code', [50101, 50102, 50103, 50104])->get()
            ->pluck('id')->toArray();

        $price_other_data = Voucher::where('client_po_id', $po->id)
            ->whereNotIn('account_id', $account_other)
            ->select(DB::raw("SUM(total) as price_other"))->groupBy('client_po_id')->get()->sum('price_other'); //

        $price_profit_lost = $profitLost->price_after_year; //

        $price_total = $material_data + $subkon_data + $btkl_data + $price_other_data + $price_profit_lost; //

        $price_profit_lost_po = $profit_lost_all_price->price_profit_lost_str; //

        $price_general = $profit_lost_all_price->price_general; //

        $price_profit_final = $profit_lost_all_price->price_prift_lost_final_str; //

        if ($pure) {
            return [
                'price_po_excl_ppn' => CustomHelper::formatRupiahWithCurrency($price_po_excl_ppn),
                'price_material' => CustomHelper::formatRupiahWithCurrency($material_data),
                'price_subkon' => CustomHelper::formatRupiahWithCurrency($subkon_data),
                'price_btkl' => CustomHelper::formatRupiahWithCurrency($btkl_data),
                'price_other' => CustomHelper::formatRupiahWithCurrency($price_other_data),
                'price_profit_lost_project' => CustomHelper::formatRupiahWithCurrency($price_profit_lost),
                'price_total' => CustomHelper::formatRupiahWithCurrency($price_total),
                'price_profit_lost_po' => CustomHelper::formatRupiahWithCurrency($price_profit_lost_po),
                'price_general' => CustomHelper::formatRupiahWithCurrency($price_general),
                'price_profit_final' => CustomHelper::formatRupiahWithCurrency($price_profit_final)
            ];
        }

        return [
            'price_po_excl_ppn' => CustomHelper::formatRupiah($price_po_excl_ppn),
            'price_material' => CustomHelper::formatRupiah($material_data),
            'price_subkon' => CustomHelper::formatRupiah($subkon_data),
            'price_btkl' => CustomHelper::formatRupiah($btkl_data),
            'price_other' => CustomHelper::formatRupiah($price_other_data),
            'price_profit_lost_project' => CustomHelper::formatRupiah($price_profit_lost),
            'price_total' => CustomHelper::formatRupiah($price_total),
            'price_profit_lost_po' => CustomHelper::formatRupiah($price_profit_lost_po),
            'price_general' => CustomHelper::formatRupiah($price_general),
            'price_profit_final' => CustomHelper::formatRupiah($price_profit_final)
        ];
    }

    public function consolidate_formula()
    {

        $dataset = [];
        $consolidate_income_header = DB::table('consolidate_income_headers')
            ->orderBy('id', 'asc')->get();
        // dd($consolidate_income_header);

        // $contract_income = JournalEntry::whereExists(function ($query) {
        //     $query->select(DB::raw(1))
        //         ->from('accounts')
        //         ->whereColumn('journal_entries.account_id', '=', 'accounts.id')
        //         ->where('accounts.code', 'like', "401%");
        // })
        // ->select(DB::raw('SUM(debit) - SUM(credit) as total'))
        // ->first();
        // pendapatan kontrak
        $contract_income_number = 0;

        if ($consolidate_income_header[0]) {
            $data = [
                'name' => $consolidate_income_header[0]->name,
                'total' => CustomHelper::formatRupiahWithCurrency($contract_income_number),
                'item' => [],
            ];
            $totalAll = 0;
            $items = ConsolidateIncomeItem::leftJoin('accounts', 'accounts.id', 'consolidate_income_account_items.account_id')
                ->where('consolidate_income_account_items.header_id', $consolidate_income_header[0]->id)
                ->select(DB::raw("accounts.*"))->get();
            foreach ($items as $item) {
                $total_account = CustomHelper::balanceAccount($item->code);
                $totalAll += $total_account;
                $item->total = CustomHelper::formatRupiahWithCurrency($total_account);
            }
            $data['total'] = CustomHelper::formatRupiahWithCurrency($totalAll);
            $data['item'] = $items;
            $dataset[] = $data;
            $contract_income_number = $totalAll;
        }


        // $cost_expense = JournalEntry::whereExists(function ($query) {
        //     $query->select(DB::raw(1))
        //         ->from('accounts')
        //         ->whereColumn('journal_entries.account_id', '=', 'accounts.id')
        //         ->where('accounts.code', 'like', "501%");
        // })
        // ->select(DB::raw('SUM(debit) - SUM(credit) as total'))
        // ->first();
        // beban usaha
        $cost_expense_number = 0;
        if ($consolidate_income_header[1]) {
            $data = [
                'name' => $consolidate_income_header[1]->name,
                'total' => CustomHelper::formatRupiahWithCurrency($cost_expense_number),
                'item' => [],
            ];
            $totalAll = 0;
            $items = ConsolidateIncomeItem::leftJoin('accounts', 'accounts.id', 'consolidate_income_account_items.account_id')
                ->where('consolidate_income_account_items.header_id', $consolidate_income_header[1]->id)
                ->select(DB::raw("accounts.*"))->get();
            foreach ($items as $item) {
                $total_account = CustomHelper::balanceAccount($item->code);
                $totalAll += $total_account;
                $item->total = CustomHelper::formatRupiahWithCurrency($total_account);
            }
            $data['item'] = $items;
            $data['total'] = CustomHelper::formatRupiahWithCurrency($totalAll);
            $dataset[] = $data;
            $cost_expense_number = $totalAll;
        }
        // laba usaha
        $cost_profit_expense_number = $contract_income_number - $cost_expense_number;
        if ($consolidate_income_header[2]) {
            $data = [
                'name' => $consolidate_income_header[2]->name,
                'total' => CustomHelper::formatRupiahWithCurrency($cost_profit_expense_number),
                'item' => [],
            ];
            $items = ConsolidateIncomeItem::leftJoin('accounts', 'accounts.id', 'consolidate_income_account_items.account_id')
                ->where('consolidate_income_account_items.header_id', $consolidate_income_header[2]->id)
                ->select(DB::raw("accounts.*"))->get();
            foreach ($items as $item) {
                $total_account = CustomHelper::balanceAccount($item->code);
                $item->total = CustomHelper::formatRupiahWithCurrency($total_account);
            }
            $data['item'] = $items;
            $dataset[] = $data;
        }

        // pendapatan lain - lain
        $cost_other_number = 0;
        if ($consolidate_income_header[3]) {
            $data = [
                'name' => $consolidate_income_header[3]->name,
                'total' => CustomHelper::formatRupiahWithCurrency($cost_other_number),
                'item' => [],
            ];
            $totalAll = 0;
            $items = ConsolidateIncomeItem::leftJoin('accounts', 'accounts.id', 'consolidate_income_account_items.account_id')
                ->where('consolidate_income_account_items.header_id', $consolidate_income_header[3]->id)
                ->select(DB::raw("accounts.*"))->get();
            foreach ($items as $item) {
                $total_account = CustomHelper::balanceAccount($item->code);
                $totalAll += $total_account;
                $item->total = CustomHelper::formatRupiahWithCurrency($total_account);
            }
            $data['total'] = CustomHelper::formatRupiahWithCurrency($totalAll);
            $data['item'] = $items;
            $dataset[] = $data;
            $cost_other_number = $totalAll;
        }

        // beban lain - lain
        $expense_other_number = 0;
        if ($consolidate_income_header[4]) {
            $data = [
                'name' => $consolidate_income_header[4]->name,
                'total' => CustomHelper::formatRupiahWithCurrency($expense_other_number),
                'item' => [],
            ];
            $totalAll = 0;
            $items = ConsolidateIncomeItem::leftJoin('accounts', 'accounts.id', 'consolidate_income_account_items.account_id')
                ->where('consolidate_income_account_items.header_id', $consolidate_income_header[4]->id)
                ->select(DB::raw("accounts.*"))->get();
            foreach ($items as $item) {
                $total_account = CustomHelper::balanceAccount($item->code);
                $totalAll += $total_account;
                $item->total = CustomHelper::formatRupiahWithCurrency($total_account);
            }
            $data['total'] = CustomHelper::formatRupiahWithCurrency($totalAll);
            $data['item'] = $items;
            $dataset[] = $data;
            $expense_other_number = $totalAll;
        }
        // laba sebelum pajak
        $profit_before_tax = $cost_profit_expense_number + $cost_other_number - $expense_other_number;

        if ($consolidate_income_header[5]) {
            $data = [
                'name' => $consolidate_income_header[5]->name,
                'total' => CustomHelper::formatRupiahWithCurrency($profit_before_tax),
                'item' => [],
            ];
            $totalAll = 0;
            $items = ConsolidateIncomeItem::leftJoin('accounts', 'accounts.id', 'consolidate_income_account_items.account_id')
                ->where('consolidate_income_account_items.header_id', $consolidate_income_header[5]->id)
                ->select(DB::raw("accounts.*"))->get();
            foreach ($items as $item) {
                $total_account = CustomHelper::balanceAccount($item->code);
                $item->total = CustomHelper::formatRupiahWithCurrency($total_account);
            }
            $data['item'] = $items;
            $dataset[] = $data;
        }

        // beban pajak
        $expense_tax_number = 0;
        if ($consolidate_income_header[6]) {
            $data = [
                'name' => $consolidate_income_header[6]->name,
                'total' => CustomHelper::formatRupiahWithCurrency(0),
                'item' => [],
            ];
            $totalAll = 0;
            $items = ConsolidateIncomeItem::leftJoin('accounts', 'accounts.id', 'consolidate_income_account_items.account_id')
                ->where('consolidate_income_account_items.header_id', $consolidate_income_header[6]->id)
                ->select(DB::raw("accounts.*"))->get();
            foreach ($items as $item) {
                $total_account = CustomHelper::balanceAccount($item->code);
                $totalAll += $total_account;
                $item->total = CustomHelper::formatRupiahWithCurrency($total_account);
            }
            $data['total'] = CustomHelper::formatRupiahWithCurrency($totalAll);
            $data['item'] = $items;
            $dataset[] = $data;
            $expense_tax_number = $totalAll;
        }

        // laba bersih
        $net_profit = $profit_before_tax - $expense_tax_number;
        if ($consolidate_income_header[7]) {
            $data = [
                'name' => $consolidate_income_header[7]->name,
                'total' => CustomHelper::formatRupiahWithCurrency($net_profit),
                'item' => [],
            ];
            $totalAll = 0;
            $items = ConsolidateIncomeItem::leftJoin('accounts', 'accounts.id', 'consolidate_income_account_items.account_id')
                ->where('consolidate_income_account_items.header_id', $consolidate_income_header[7]->id)
                ->select(DB::raw("accounts.*"))->get();
            foreach ($items as $item) {
                $total_account = CustomHelper::balanceAccount($item->code);
                $item->total = CustomHelper::formatRupiahWithCurrency($total_account);
            }
            $data['item'] = $items;
            $dataset[] = $data;
        }

        return $dataset;
    }

    public function detail($id)
    {
        $this->crud->hasAccessOrFail('list');
        $this->data['is_disabled_list'] = true;
        $profitLost = ProjectProfitLost::where('id', $id)->first();

        $this->crud->id_profit_lost = $id;

        CRUD::addButtonFromView('top', 'export-excel-profit-lost', 'export-excel-profit-lost', 'beginning');
        CRUD::addButtonFromView('top', 'export-pdf-profit-lost', 'export-pdf-profit-lost', 'beginning');

        $breadcrumbs = [
            trans('backpack::crud.menu.finance_report') => backpack_url('cash-flow'),
            trans('backpack::crud.menu.profit_lost') => url($this->crud->route),
            $profitLost->clientPo->po_number =>  $profitLost->clientPo->po_number,
        ];
        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? mb_ucfirst($this->crud->entity_name_plural);
        $this->data['title_modal_create'] = trans('backpack::crud.profit_lost.title_modal_create');
        $this->data['title_modal_edit'] = trans('backpack::crud.profit_lost.title_modal_edit_consolidation');
        $this->data['title_modal_delete'] = trans('backpack::crud.profit_lost.title_modal_delete_consolidation');

        $this->card->addCard([
            'name' => 'detail-project',
            'line' => 'top',
            'view' => 'crud::components.detail-project-profit-lost',
            'params' => [
                'data' => $profitLost,
                'report' => $this->total_detail_project($id),
            ],
            'wrapper' => [
                'class' => 'col-md-6'
            ]
        ]);

        $this->data['breadcrumbs'] = $breadcrumbs;
        $this->data['cards'] = $this->card;
        $this->data['modals'] = $this->modal;
        $this->data['scripts'] = $this->script;
        $this->data['id_profit_lost'] = $id;

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

    public function edit($id)
    {
        $this->crud->hasAccessOrFail('update');

        $id = $this->crud->getCurrentEntryId() ?? $id;

        $this->crud->registerFieldEvents();

        $this->data['entry'] = $this->crud->getEntryWithLocale($id);

        $this->crud->entry = $this->data['entry'];
        $client_po = $this->data['entry']->clientPo;

        $this->crud->entry->po_number = $client_po->po_number;

        $this->crud->setOperationSetting('fields', $this->crud->getUpdateFields());

        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.edit') . ' ' . $this->crud->entity_name;
        $this->data['id'] = $id;

        return response()->json([
            'html' => view($this->crud->getEditView(), $this->data)->render()
        ]);
    }

    function select2Po()
    {
        $q = request()->q;
        // $po = ClientPo::select(DB::raw("id, po_number, 'client' as type"))->where('po_number', 'like', "%$q%");

        // $po_subkon = PurchaseOrder::select(DB::raw("id, po_number, 'subkon' as type"))
        // ->where('po_number', 'like', "%$q%");

        // $union = $po->unionAll($po_subkon)
        // ->paginate(20);
        $union = ClientPo::where('work_code', 'like', "%$q%")
            ->get();

        $results = [];
        foreach ($union as $item) {
            $results[] = [
                'data' => $item,
                'voucher_id' => null,
                'id' => $item->id,
                'po_number' => $item->po_number,
                'text' => $item->work_code,
                'work_code' => $item->work_code,
            ];
        }
        return response()->json(['results' => $results]);
    }

    function get_client_selected_ajax()
    {
        $id = request()->id;

        $voucher = Voucher::select(
            'client_po_id',
            DB::raw("SUM(payment_transfer) as payment_transfer"),
            DB::raw("SUM(total) as biaya")
        )->where('client_po_id', $id)
            ->groupBy('client_po_id')->first();

        // $price_excl_ppn_po = $voucher?->client_po?->job_value;

        $invoiceClients = DB::table('invoice_clients')
            ->select(
                'client_po_id',
                DB::raw("GROUP_CONCAT(invoice_date SEPARATOR ',') AS invoice_date"),
                DB::raw("SUM(price_total_exclude_ppn) AS price_job_exlude_ppn"),
                DB::raw("SUM(price_total_include_ppn) AS price_job_include_ppn")
            )
            ->groupBy('client_po_id');

        $client_po_query_exclude_ppn = DB::table("client_po")
            ->leftJoinSub($invoiceClients, 'invoice', function ($join) {
                $join->on('invoice.client_po_id', '=', 'client_po.id');
            })->select(
                "client_po.id as client_po_id",
                "client_po.category as category",
                DB::raw("IF(invoice.invoice_date IS NULL, client_po.job_value, invoice.price_job_exlude_ppn) as price_job_exlude_ppn_logic")
            )->where('client_po.id', $id)->first();



        return response()->json([
            'price_voucher' => (int) $voucher?->biaya,
            // 'price_small_cash' => $total_small_cash,
            'price_excl_ppn_po' => (int) $client_po_query_exclude_ppn?->price_job_exlude_ppn_logic,
        ]);
    }

    private function ruleAccount()
    {
        $id = request()->id;
        $rule = [
            'name' => 'required|max:100|unique:accounts,name,' . $id,
            'balance' => 'required|numeric|min:0',
        ];
        $rule['code'] = [
            'required',
            'min:3',
            'max:20',
            Rule::unique('accounts', 'code')->ignore($id),
            function ($attribute, $value, $fail) use ($id) {
                $parent = null;
                for ($i = 1; $i < strlen($value); $i++) {
                    $prefix = substr($value, 0, $i);
                    $account = Account::where('code', $prefix)
                        ->where('level', '>=', 2)->first();
                    if ($account && $account->type != Account::INCOME) {
                        $fail(trans('backpack::crud.expense_account.field.code.errors.depedency'));
                    }
                }
                return $parent;
            }
        ];
        if ($id) {
            $rule['code'] = [
                'required',
                'min:3',
                'max:20',
                Rule::unique('accounts', 'code')->ignore($id),
                function ($attribute, $value, $fail) use ($id) {
                    $old_code = Account::where('id', $id)->first()->code;

                    $parent = null;
                    if ($value != $old_code) {
                        for ($i = 1; $i < strlen($value); $i++) {
                            $prefix = substr($value, 0, $i);
                            $account = Account::where('code', $prefix)
                                ->where('level', '>=', 2)->first();
                            if ($account && $account->type != Account::INCOME) {
                                $fail(trans('backpack::crud.expense_account.field.code.errors.depedency'));
                            }
                        }
                    }

                    if ($value != $old_code) {
                        $child = Account::where('code', 'LIKE', "$old_code%")
                            ->where('type', Account::INCOME)
                            ->where('id', '!=', $id)
                            ->count();
                        if ($child > 0) {
                            $fail(trans('backpack::crud.expense_account.field.code.errors.depedency'));
                        }
                    }
                }
            ];

            $rule['balance'] = [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($id) {
                    $old_balance = Account::leftJoin('journal_entries', 'journal_entries.account_id', '=', 'accounts.id')
                        ->select(DB::raw("
                        (SUM(journal_entries.debit) - SUM(journal_entries.credit)) as balance
                    "))->where('accounts.id', $id)
                        ->groupBy('accounts.id')
                        ->first();

                    $old_balance = $old_balance->balance ?? 0;

                    if ($value != $old_balance) {
                        $journal = JournalEntry::where('account_id', $id)
                            ->whereNot('reference_type', Account::class)
                            ->count();
                        if ($journal > 0) {
                            $fail(trans('backpack::crud.expense_account.field.code.errors.not_change_balance'));
                        }
                    }
                }
            ];
        }
        return $rule;
    }

    private function ruleConsolidateItem()
    {
        $rule = [
            'account_id' => [
                'required',
                'exists:accounts,id',
                Rule::unique('consolidate_income_account_items', 'account_id')->ignore(request()->id)
            ],
            'header_id' => 'required|exists:consolidate_income_headers,id',
        ];
        return $rule;
    }

    function validationProject()
    {
        $rule = [];
        $rule['id_client_po'] = 'required|unique:project_profit_lost,client_po_id,' . request()->id;
        $rule['category'] = 'required';
        return $rule;
    }

    protected function setupCreateOperation()
    {

        $request = request();

        $settings = Setting::first();
        $job_code_prefix_value = [];
        $after_year_attribute = [];
        $work_code_attribute = [];
        $price_voucher_attribute = [];
        $price_small_cash_attribute = [];
        $price_total_attribute = [];
        $price_profit_lost_po_attribute = [];
        $price_prift_lost_final_attribute = [];
        $category_attribute = [];
        if (!$this->crud->getCurrentEntryId()) {
            $job_code_prefix_value = [
                'value' => $settings?->work_code_prefix,
            ];
        } else {
            // edit
            $job_code_prefix_value = [
                'disabled' => true,
            ];
            $price_voucher_attribute = [
                'disabled' => true,
            ];
            // $price_small_cash_attribute = [
            //     'disabled' => true,
            // ];
            $category_attribute = [
                'disabled' => true,
            ];
        }

        $price_total_attribute = [
            'disabled' => true,
        ];
        $price_profit_lost_po_attribute = [
            'disabled' => true,
        ];
        $price_prift_lost_final_attribute = [
            'disabled' => true,
        ];

        if ($request->has('type')) {
            if ($request->type == 'project') {

                CRUD::setValidation($this->validationProject());
                CRUD::setModel(ProjectProfitLost::class);

                CRUD::addField([
                    'name' => 'work_code',
                    'label' => trans('backpack::crud.profit_lost.fields.job_code.label'),
                    'type' => 'select2_ajax_custom',
                    'attribute' => 'work_code',
                    'entity' => 'clientPo',
                    'data_source' => backpack_url('finance-report/profit-lost/select2-po'), // url to controller search function (with /{id} should return a single entry)
                    'wrapper' => ['class' => 'form-group col-md-6'],
                    'attributes' => [
                        ...$job_code_prefix_value,
                    ]
                ]);

                CRUD::addField([   // 1-n relationship
                    'label'       => trans('backpack::crud.invoice_client.field.client_po_id.label'), // Table column heading
                    'type'        => "text",
                    'name'        => 'po_number', // the column that contains the ID of that connected entity
                    'wrapper'   => [
                        'class' => 'form-group col-md-6',
                    ],
                    'attributes' => [
                        'placeholder' => trans('backpack::crud.profit_lost.fields.no_po.placeholder'),
                        'disabled' => true,
                    ],
                ]);

                CRUD::addField([
                    'name' => 'price_after_year',
                    'label' =>  trans('backpack::crud.profit_lost.fields.price_after_year.label'),
                    'type' => 'mask',
                    'mask' => '000.000.000.000.000.000',
                    'mask_options' => [
                        'reverse' => true
                    ],
                    'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : 'Rp.',
                    'wrapper'   => [
                        'class' => 'form-group col-md-6',
                    ],
                    'attributes' => [
                        'placeholder' => '000.000',
                        ...$after_year_attribute,
                    ]
                ]);
                CRUD::addField([
                    'name' => 'price_voucher',
                    'label' =>  trans('backpack::crud.profit_lost.fields.price_voucher.label'),
                    'type' => 'mask',
                    'mask' => '000.000.000.000.000.000',
                    'mask_options' => [
                        'reverse' => true
                    ],
                    'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : 'Rp.',
                    'wrapper'   => [
                        'class' => 'form-group col-md-6',
                    ],
                    'attributes' => [
                        'placeholder' => '000.000',
                        ...$price_voucher_attribute,
                    ]
                ]);

                CRUD::addField([
                    'name' => 'price_small_cash',
                    'label' =>  trans('backpack::crud.profit_lost.fields.price_small_cash.label'),
                    'type' => 'mask',
                    'mask' => '000.000.000.000.000.000',
                    'mask_options' => [
                        'reverse' => true
                    ],
                    'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : 'Rp.',
                    'wrapper'   => [
                        'class' => 'form-group col-md-6',
                    ],
                    'attributes' => [
                        'placeholder' => '000.000',
                        ...$price_small_cash_attribute,
                    ]
                ]);

                CRUD::addField([
                    'name' => 'price_total',
                    'label' =>  trans('backpack::crud.profit_lost.fields.price_total.label'),
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
                        'placeholder' => '000.000',
                        ...$price_total_attribute,
                    ]
                ]);

                CRUD::addField([
                    'name' => 'price_profit_lost_po',
                    'label' =>  trans('backpack::crud.profit_lost.fields.price_profit_lost_po.label'),
                    'type' => 'text',
                    'mask' => 'Z000.000.000.000.000.000',
                    'mask_options' => [
                        'reverse' => true,
                    ],
                    'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : 'Rp.',
                    'wrapper'   => [
                        'class' => 'form-group col-md-6',
                    ],
                    'attributes' => [
                        'placeholder' => '000.000',
                        ...$price_profit_lost_po_attribute,
                    ]
                ]);

                CRUD::addField([
                    'name' => 'price_general',
                    'label' =>  trans('backpack::crud.profit_lost.fields.price_general.label'),
                    'type' => 'mask',
                    'mask' => '000.000.000.000.000.000',
                    'mask_options' => [
                        'reverse' => true
                    ],
                    'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : 'Rp.',
                    'wrapper'   => [
                        'class' => 'form-group col-md-6',
                    ],
                    'attributes' => [
                        'placeholder' => '000.000',
                    ]
                ]);

                CRUD::addField([
                    'name' => 'price_prift_lost_final',
                    'label' =>  trans('backpack::crud.profit_lost.fields.price_prift_lost_final.label'),
                    'type' => 'text',
                    'mask' => 'Z000.000.000.000.000.000',
                    'mask_options' => [
                        'reverse' => true
                    ],
                    'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : 'Rp.',
                    'wrapper'   => [
                        'class' => 'form-group col-md-6',
                    ],
                    'attributes' => [
                        'placeholder' => '000.000',
                        ...$price_prift_lost_final_attribute,
                    ]
                ]);

                CRUD::addField([
                    'label'     => trans('backpack::crud.client_po.column.category'),
                    'type'      => 'select2_array',
                    'name'      => 'category',
                    'options'   => [
                        '' => trans('backpack::crud.voucher.field.payment_type.placeholder'),
                        'RUTIN' => 'RUTIN',
                        'NON RUTIN' => 'NON RUTIN',
                    ],
                    'wrapper' => [
                        'class' => 'form-group col-md-6'
                    ],
                    'attributes' => [
                        ...$category_attribute,
                    ]
                ]);

                CRUD::addField([
                    'name' => 'logic_profit_lost',
                    'type' => 'logic_profit_lost'
                ]);
            }
        } else {
            CRUD::setValidation($this->ruleConsolidateItem());
            CRUD::setModel(ConsolidateIncomeItem::class);
            $consolidate_header = DB::table('consolidate_income_headers')
                ->get();
            $optionHeader = [];
            foreach ($consolidate_header as $header) {
                $optionHeader[$header->id] = $header->name;
            }

            CRUD::addField([  // Select2
                'label'     => trans('backpack::crud.profit_lost.fields.header_id.label'),
                'type'      => 'select2_array',
                'name'      => 'header_id',
                'options'   =>  $optionHeader,
                'wrapper' => [
                    'class' => 'form-group col-md-12'
                ]
            ]);

            CRUD::addField([
                'label'       => trans('backpack::crud.voucher.field.account_id.label'), // Table column heading
                'type'        => "select2_ajax_custom",
                'name'        => 'account_id',
                'entity'      => 'account',
                'model'       => 'App\Models\Account',
                'attribute'   => "name",
                'data_source' => backpack_url('finance-report/profit-lost/select2-account'),
                'wrapper'   => [
                    'class' => 'form-group col-md-12',
                ],
                'attributes' => [
                    'placeholder' => trans('backpack::crud.voucher.field.account_id.placeholder'),
                ]
            ]);
        }
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    public function update()
    {
        $this->crud->hasAccessOrFail('update');
        $request = request();

        $request->validate([
            'price_after_year' => 'nullable',
            'price_general' => 'nullable',
        ]);

        $this->crud->registerFieldEvents();

        DB::beginTransaction();
        try {
            $events = [];

            $flag_update = 0;

            $project_profit_lost = ProjectProfitLost::find($request->id);
            $price_small_cash_old = price_normalize($project_profit_lost->price_small_cash);
            $price_small_cash_new = price_normalize($request->price_small_cash);
            if ($price_small_cash_old != $price_small_cash_new) {
                $flag_update++;
                $project_profit_lost->price_small_cash = $request->price_small_cash;
            }

            $price_after_year_old = price_normalize($project_profit_lost->price_after_year);
            $price_after_year_new = price_normalize($request->price_after_year);
            if ($price_after_year_old != $price_after_year_new) {
                $flag_update++;
                $project_profit_lost->price_after_year = $request->price_after_year;
            }

            $price_general_old = price_normalize($project_profit_lost->price_general);
            $price_general_new = price_normalize($request->price_general);
            if ($price_general_old != $price_general_new) {
                $flag_update++;
                $project_profit_lost->price_general = $request->price_general;
            }
            if ($flag_update > 0) {
                $project_profit_lost->save();
                // store profit log
                $new_profit_log = new ProjectProfitLostLog;
                $new_profit_log->project_profit_lost_id = $project_profit_lost->id;
                $new_profit_log->user_id = backpack_user()->id;
                $new_profit_log->price_after_year = $request->price_after_year;
                $new_profit_log->price_general = $request->price_general;
                $new_profit_log->save();

                $events['crudTable-filter_profit_lost_plugin_load'] = true;
                $events['project_create_success'] = true;
            }

            $item = $project_profit_lost;
            $this->data['entry'] = $this->crud->entry = $item;

            \Alert::success(trans('backpack::crud.update_success'))->flash();

            $this->crud->setSaveAction();

            DB::commit();
            return response()->json([
                'success' => true,
                'data' => $item,
                'events' => $events
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function updateOld()
    {
        $this->crud->hasAccessOrFail('update');

        $request = $this->crud->validateRequest();

        $this->crud->registerFieldEvents();

        DB::beginTransaction();
        try {

            $old_account = Account::find($request->id);
            $rootParent_1 = $this->getRootParentAccount($old_account->code);

            $new_code = $request->code;
            $new_parent = null;
            for ($i = 1; $i <= strlen($new_code); $i++) {
                $prefix = substr($new_code, 0, $i);
                $account = Account::where('code', $prefix)->first();
                if ($account) {
                    if ($account->code != $old_account->code) {
                        $new_parent = $account;
                    }
                }
            }

            $rootParant_2 = $this->getRootParentAccount($new_code);

            $item = Account::where('id', $request->id)->first();
            $item->code = $new_code;
            $item->name = $request->name;
            if ($new_parent) {
                $item->level = $new_parent->level + 1;
            }
            $item->save();

            if ($request->balance > 0) {
                $journal = JournalEntry::where('account_id', $item->id)
                    ->where('reference_type', Account::class)
                    ->first();
                if ($journal) {
                    $journal->debit = $request->balance;
                    $journal->credit = 0;
                    $journal->save();
                } else {
                    CustomHelper::updateOrCreateJournalEntry([
                        'account_id' => $item->id,
                        'reference_id' => $item->id,
                        'reference_type' => Account::class,
                        'description' => 'FIRST BALANCE',
                        'date' => Carbon::now(),
                        'debit' => $request->balance,
                    ], [
                        'reference_id' => $item->id,
                        'reference_type' => Account::class,
                    ]);
                }
            }

            $events = [];
            $dataEvents = [];

            if ($rootParent_1) {
                $dataEvents[] = $rootParent_1;
                $events['account_' . $rootParent_1->id . '_update_success'] = ($item->level == 2) ? $rootParent_1 : $item;
            }

            if ($rootParant_2) {
                $dataEvents[] = $rootParant_2;
                $events['account_' . $rootParant_2->id . '_update_success'] = ($item->level == 2) ? $rootParant_2 : $item;
            }

            // if(count($dataEvents) > 0){
            //     $events['accounts_update_success'] = $dataEvents;
            // }

            $this->data['entry'] = $this->crud->entry = $item;

            \Alert::success(trans('backpack::crud.update_success'))->flash();

            $this->crud->setSaveAction();

            DB::commit();
            return response()->json([
                'success' => true,
                'data' => $item,
                'events' => $events
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

    public function getRootParentAccount($code)
    {
        $parent = null;
        for ($i = 1; $i <= strlen($code); $i++) {
            $prefix = substr($code, 0, $i);
            $account = Account::where('code', $prefix)
                // ->where('type', Account::INCOME)
                ->whereIn('level', [1, 2])->first();
            if ($account) {
                $parent = $account;
            }
        }
        return $parent;
    }

    public function store()
    {
        $this->crud->hasAccessOrFail('create');

        $request = $this->crud->validateRequest();

        $this->crud->registerFieldEvents();

        DB::beginTransaction();
        try {

            $header_id = $request->header_id;
            $account_id = $request->account_id;

            $newItemConsolidate = new ConsolidateIncomeItem;
            $newItemConsolidate->header_id = $header_id;
            $newItemConsolidate->account_id = $account_id;
            $newItemConsolidate->save();

            DB::commit();
            return response()->json([
                'success' => true,
                'data' => $newItemConsolidate,
                'events' => [
                    'account_create_success' => $newItemConsolidate,
                ]
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

    public function storeProjectOld()
    {
        $this->crud->hasAccessOrFail('create');
        CRUD::setValidation($this->validationProject());

        $request = $this->crud->validateRequest();

        $this->crud->registerFieldEvents();

        DB::beginTransaction();
        try {

            $voucher_id = $request->voucher_id;
            $client_po_id = $request->client_po_id;
            $contract_value = $request->contract_value;
            $total_project = $request->total_project;
            $price_material = $request->price_material;
            $price_subkon = $request->price_subkon;
            $price_btkl = $request->price_btkl;
            $price_transport_project = $request->price_transport_project;
            $price_worker_consumption = $request->price_worker_consumption;
            $price_project_equipment = $request->price_project_equipment;
            $price_other = $request->price_other;
            $price_profit_lost_project = $request->price_profit_lost_project;

            $item = new ProjectProfitLost;
            $item->voucher_id = $voucher_id;
            $item->client_po_id = $client_po_id;
            $item->contract_value = $contract_value;
            $item->total_project = $total_project;
            $item->price_material = $price_material;
            $item->price_subkon = $price_subkon;
            $item->price_btkl = $price_btkl;
            $item->price_transport_project = $price_transport_project;
            $item->price_worker_consumption = $price_worker_consumption;
            $item->price_project_equipment = $price_project_equipment;
            $item->price_other = $price_other;
            $item->price_profit_lost_project = $price_profit_lost_project;
            $item->save();

            $this->data['entry'] = $this->crud->entry = $item;

            \Alert::success(trans('backpack::crud.insert_success'))->flash();

            $this->crud->setSaveAction();

            DB::commit();
            return response()->json([
                'success' => true,
                'data' => $item,
                'events' => [
                    'project_create_success' => $item,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function storeProject()
    {
        $this->crud->hasAccessOrFail('create');
        CRUD::setValidation($this->validationProject());

        $request = $this->crud->validateRequest();

        $this->crud->registerFieldEvents();

        DB::beginTransaction();
        try {

            $item = new ProjectProfitLost;
            $item->voucher_id = $request->voucher_id;
            $item->client_po_id = $request->id_client_po;
            $item->price_after_year = $request->price_after_year;
            $item->price_voucher = $request->price_voucher;
            $item->price_small_cash = $request->price_small_cash;
            $item->price_total = $request->price_total;
            $item->price_profit_lost_po = $request->price_profit_lost_po;
            $item->price_general = $request->price_general;
            $item->price_prift_lost_final = $request->price_prift_lost_final;
            $item->category = $request->category;
            $item->contract_value = 0;
            $item->total_project = 0;
            $item->save();

            $po = ClientPo::find($request->id_client_po);
            $po->price_after_year = $item->price_after_year;
            $po->price_total = $item->price_total;
            $po->profit_and_loss = $item->price_profit_lost_po;
            $po->load_general_value = $item->price_general;
            $po->profit_and_lost_final = $item->price_prift_lost_final;
            $po->category = $item->category;
            $po->save();

            $this->data['entry'] = $this->crud->entry = $item;

            \Alert::success(trans('backpack::crud.insert_success'))->flash();

            $this->crud->setSaveAction();

            DB::commit();
            return response()->json([
                'success' => true,
                'data' => $item,
                'events' => [
                    'project_create_success' => $item,
                ]
            ]);
        } catch (\Exception $e) {
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
        if (request('type') == 'project') {
            CRUD::setModel(ProjectProfitLost::class);
        }

        DB::beginTransaction();
        try {
            $this->crud->hasAccessOrFail('delete');

            $item = $this->crud->model::findOrFail($id);

            if ($item instanceof ProjectProfitLost) {
                $item->delete();
                DB::commit();
                return response()->json([
                    'events' => ['crudTable-project_updated_success' => true]
                ]);
            }

            $parent_account = null;

            if ($item) {
                $child_exists = Account::where('code', 'LIKE', "{$item->code}%")
                    ->where('id', '!=', $item->id)->count();

                if ($child_exists > 0) {
                    return response()->json([
                        'error' => [
                            trans('backpack::crud.expense_account.field.code.errors.delete')
                        ]
                    ]);
                }
            }

            $parent_account = $this->getRootParentAccount($item->code);

            $events = [];

            if ($parent_account) {
                $events['account_' . $parent_account->id . '_update_success'] = true;
            }

            JournalEntry::where('account_id', $item->id)->delete();

            $item->delete();

            DB::commit();
            return response()->json([
                'success' => [
                    '<strong>' . trans('backpack::crud.delete_confirmation_title') . '</strong><br>' . trans('backpack::crud.delete_confirmation_message'),
                ],
                'events' => $events,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'type' => 'errors',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    protected function setupListOperation()
    {
        // $this->crud->setFromDb(false);
        $settings = Setting::first();
        CRUD::disableResponsiveTable();

        $request = request();
        $this->crud->file_title_export_pdf = "Laporan_daftar_laba_rugi_proyek.pdf";
        $this->crud->file_title_export_excel = "Laporan_daftar_laba_rugi_proyek.xlsx";
        $this->crud->param_uri_export = "?type=project";

        CRUD::addButtonFromView('top', 'export-excel-table', 'export-excel-table', 'beginning');
        CRUD::addButtonFromView('top', 'export-pdf-table', 'export-pdf-table', 'beginning');

        if ($request->has('type')) {
            if ($request->type == 'project') {
                // CRUD::removeButton('create');
                CRUD::removeButton('update');
                CRUD::removeButton('delete');
                CRUD::removeButton('show');

                CRUD::addButtonFromView('line', 'delete-profit-lost-project', 'delete-profit-lost-project', 'beginning');
                CRUD::addButtonFromView('line', 'update-profit-lost', 'update-profit-lost', 'beginning');
                CRUD::addButtonFromView('line', 'show-detail-project', "show-detail-project", 'end');

                CRUD::setModel(ProjectProfitLost::class);

                $voucher = DB::table('vouchers')->select(
                    'client_po_id',
                    DB::raw("SUM(payment_transfer) as payment_transfer"),
                    DB::raw("SUM(total) as biaya")
                )->groupBy('client_po_id');

                $invoice = DB::table('invoice_clients')
                    ->select(
                        'invoice_clients.client_po_id',
                        DB::raw("GROUP_CONCAT(invoice_clients.invoice_date SEPARATOR ',') AS invoice_date"),
                        DB::raw("SUM(invoice_clients.price_total_exclude_ppn) as price_job_exlude_ppn"),
                        DB::raw("SUM(invoice_clients.price_total_include_ppn) as price_job_include_ppn")
                    )
                    ->groupBy('invoice_clients.client_po_id');

                $client_po_query_exclude_ppn = DB::table("client_po")
                    ->leftJoinSub($invoice, 'invoice', function ($join) {
                        $join->on('invoice.client_po_id', '=', 'client_po.id');
                    })->select(
                        "client_po.id as client_po_id",
                        "client_po.work_code",
                        "client_po.po_number",
                        "client_po.reimburse_type",
                        "client_po.job_name",
                        "client_po.job_value",
                        "client_po.category",
                        "client_po.job_value_include_ppn",
                        "invoice.invoice_date",
                        "invoice.price_job_exlude_ppn as invoice_price_job_exlude_ppn",
                        "invoice.price_job_include_ppn as invoice_price_job_include_ppn",
                        DB::raw("IF(invoice.invoice_date IS NULL, client_po.job_value, invoice.price_job_exlude_ppn) as price_job_exlude_ppn_logic")
                    );

                $this->crud->query = $this->crud->query
                    ->leftJoinSub($client_po_query_exclude_ppn, 'client_po', function ($join) {
                        $join->on('client_po.client_po_id', '=', 'project_profit_lost.client_po_id');
                    });

                $this->crud->query = $this->crud->query
                    ->leftJoinSub($voucher, 'vouchers', function ($join) {
                        $join->on('vouchers.client_po_id', '=', 'project_profit_lost.client_po_id');
                    });

                if ($request->has('category')) {
                    $this->crud->query = $this->crud->query->where('client_po.category', $request->category);
                }

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
                    'label' => '',
                    'type'      => 'closure',
                    'name'      => 'client_po_id',
                    'function' => function ($entry) {
                        return $entry->clientPo->client->name;
                    }, // the column that contains the ID of that connected entity;
                    'searchLogic' => function ($query, $column, $searchTerm) {
                        $query->orWhereHas('clientPo.client', function ($q) use ($column, $searchTerm) {
                            $q->where('name', 'like', '%' . $searchTerm . '%');
                        });
                    }
                ]);

                CRUD::column([
                    'label'  => trans('backpack::crud.client_po.column.reimburse_type'),
                    'name' => 'reimburse_type',
                    'type' => 'text',
                    'searchLogic' => function ($query, $column, $searchTerm) {
                        $query->orWhere('client_po.reimburse_type', 'like', '%' . $searchTerm . '%');
                    }
                ]);

                CRUD::column([
                    'label'  => trans('backpack::crud.client_po.column.work_code'),
                    'name' => 'work_code',
                    'type' => 'text',
                    'searchLogic' => function ($query, $column, $searchTerm) {
                        $query->orWhere('client_po.work_code', 'like', '%' . $searchTerm . '%');
                    }
                ]);

                CRUD::column([
                    'label'  => trans('backpack::crud.client_po.column.po_number'),
                    'name' => 'po_number',
                    'type' => 'text',
                    'searchLogic' => function ($query, $column, $searchTerm) {
                        $query->orWhere('client_po.po_number', 'like', '%' . $searchTerm . '%');
                    }
                ]);

                CRUD::column([
                    'label'  => trans('backpack::crud.client_po.column.job_name'),
                    'name' => 'job_name',
                    'type' => 'text',
                    'searchLogic' => function ($query, $column, $searchTerm) {
                        $query->orWhere('client_po.job_name', 'like', '%' . $searchTerm . '%');
                    }
                ]);

                CRUD::column([
                    'label'  => trans('backpack::crud.client_po.column.job_value_exclude_ppn'),
                    'name' => 'price_job_exlude_ppn_logic',
                    'type'  => 'closure',
                    'function' => function ($entry) use ($settings) {
                        $currency = ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.";
                        return $currency . number_format($entry->price_job_exlude_ppn_logic, 2, ",", ".");
                    },
                    'orderLogic' => function ($query, $column, $columnDirection) {
                        $query->orderBy('client_po.price_job_exlude_ppn_logic', $columnDirection);
                    }
                ]);

                CRUD::column([
                    'label'  => trans('backpack::crud.client_po.column.job_value_include_ppn'),
                    'name' => 'job_value_include_ppn',
                    'type'  => 'closure',
                    'function' => function ($entry) use ($settings) {
                        $currency = ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.";
                        return $currency . number_format($entry->job_value_include_ppn, 2, ",", ".");
                    },
                    'orderLogic' => function ($query, $column, $columnDirection) {
                        $query->orderBy('client_po.job_value_include_ppn', $columnDirection);
                    }
                ]);

                CRUD::column([
                    'label'  => trans('backpack::crud.client_po.column.price_after_year'),
                    'name' => 'price_after_year',
                    'type'  => 'number',
                    'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
                    'decimals'      => 2,
                    'dec_point'     => ',',
                    'thousands_sep' => '.',
                ]);

                CRUD::column([
                    'label'  => trans('backpack::crud.profit_lost.column.price_voucher'),
                    'name' => 'voucher_biaya',
                    'type'  => 'number',
                    'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
                    'decimals'      => 2,
                    'dec_point'     => ',',
                    'thousands_sep' => '.',
                    'orderLogic' => function ($query, $column, $columnDirection) {
                        $query->orderBy('voucher_biaya', $columnDirection);
                    }
                ]);

                CRUD::column([
                    'label'  => trans('backpack::crud.profit_lost.column.price_small_cash'),
                    'name' => 'total_small_cash',
                    'type'  => 'number',
                    'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
                    'decimals'      => 2,
                    'dec_point'     => ',',
                    'thousands_sep' => '.',
                    'orderLogic' => function ($query, $column, $columnDirection) {
                        $query->orderBy('total_small_cash', $columnDirection);
                    }
                ]);

                CRUD::column([
                    'label'  => trans('backpack::crud.profit_lost.column.price_total'),
                    'name' => 'price_total_str',
                    'type'  => 'number',
                    'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
                    'decimals'      => 2,
                    'dec_point'     => ',',
                    'thousands_sep' => '.',
                    'orderLogic' => function ($query, $column, $columnDirection) {
                        $query->orderBy('price_total_str', $columnDirection);
                    }
                ]);

                CRUD::column([
                    'label'  => trans('backpack::crud.profit_lost.column.profit_lost_po'),
                    'name' => 'price_profit_lost_str',
                    'type'  => 'number',
                    'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
                    'decimals'      => 2,
                    'dec_point'     => ',',
                    'thousands_sep' => '.',
                    'orderLogic' => function ($query, $column, $columnDirection) {
                        $query->orderBy('price_profit_lost_str', $columnDirection);
                    }
                ]);

                CRUD::column([
                    'label'  => trans('backpack::crud.profit_lost.column.load_general_value'),
                    'name' => 'price_general',
                    'type'  => 'number',
                    'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
                    'decimals'      => 2,
                    'dec_point'     => ',',
                    'thousands_sep' => '.',
                    'orderLogic' => function ($query, $column, $columnDirection) {
                        $query->orderBy('price_general', $columnDirection);
                    }
                ]);

                CRUD::column([
                    'label'  => trans('backpack::crud.profit_lost.column.profit_lost_final'),
                    'name' => 'price_prift_lost_final_str',
                    'type'  => 'number',
                    'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
                    'decimals'      => 2,
                    'dec_point'     => ',',
                    'thousands_sep' => '.',
                    'orderLogic' => function ($query, $column, $columnDirection) {
                        $query->orderBy('price_prift_lost_final_str', $columnDirection);
                    }
                ]);

                CRUD::column([
                    'label'  => trans('backpack::crud.profit_lost.column.category'),
                    'type'      => 'text',
                    'name'      => 'category',
                ]);

                CRUD::column([
                    'label' => trans('backpack::crud.profit_lost.column.invoice_date'),
                    'type' => 'date-multiple',
                    'name' => 'invoice_date',
                    'format' => 'DD MMM YYYY',
                    // 'function' => function ($entry) {
                    //     $tgl = explode(",", $entry->invoice_date);
                    //     return implode(", ", $tgl);
                    // },
                    'orderLogic' => function ($query, $column, $columnDirection) {
                        $query->orderBy('client_po.invoice_date', $columnDirection);
                    }
                ]);

                CRUD::addClause('select', [
                    DB::raw("
                        project_profit_lost.*,
                        vouchers.payment_transfer as payment_voucher,
                        vouchers.biaya as voucher_biaya,
                        client_po.invoice_date,
                        client_po.price_job_exlude_ppn_logic,
                        client_po.invoice_price_job_include_ppn,
                        client_po.work_code as work_code,
                        client_po.po_number as po_number,
                        client_po.reimburse_type as reimburse_type,
                        client_po.job_name as job_name,
                        client_po.job_value as job_value,
                        client_po.job_value_include_ppn as job_value_include_ppn,
                        IFNULL(project_profit_lost.price_small_cash, 0) as total_small_cash,
                        (IFNULL(project_profit_lost.price_after_year, 0) + IFNULL(vouchers.biaya, 0) + IFNULL(project_profit_lost.price_small_cash, 0)) as price_total_str,
                        (client_po.price_job_exlude_ppn_logic - (IFNULL(project_profit_lost.price_after_year, 0) + IFNULL(vouchers.biaya, 0) + IFNULL(project_profit_lost.price_small_cash, 0))) as price_profit_lost_str,
                        ((client_po.price_job_exlude_ppn_logic - (IFNULL(project_profit_lost.price_after_year, 0) + IFNULL(vouchers.biaya, 0) + IFNULL(project_profit_lost.price_small_cash, 0))) - IFNULL(project_profit_lost.price_general, 0)) as price_prift_lost_final_str
                   ")
                ]);
            }
        } else {

            CRUD::removeButton('create');
            CRUD::removeButton('update');
            CRUD::removeButton('delete');

            CRUD::addButtonFromView('top', 'create', 'create-account-profit-lost', 'begining');
            CRUD::addButtonFromView('line', 'delete', "delete-account", 'beginning');
            CRUD::addButtonFromView('line', 'update', "update-account", 'beginning');

            CRUD::column([
                'name' => 'code_',
                'label' => trans('backpack::crud.expense_account.column.code'),
                'type' => 'text',
            ]);

            CRUD::column([
                'name' => 'name_',
                'label' => trans('backpack::crud.expense_account.column.name'),
                'type' => 'custom_html',
                'value' => function ($entry) {
                    if ($entry->level_ > 2) {
                        $space = str_repeat('&nbsp;', $entry->level_);
                        return $space . '&bull; ' . $entry->name_;
                    }
                    return $entry->name_;
                }
            ]);

            CRUD::column(
                [
                    'name' => 'balance',
                    'label' => trans('backpack::crud.expense_account.column.balance'),
                    'type' => 'custom_html',
                    'value' => function ($entry) {
                        return CustomHelper::formatRupiahWithCurrency($entry->balance);
                    },
                ],
            );

            if (request()->has('_id')) {
                $id = request()->_id;
                $code = Account::find($id);

                $this->crud->query = $this->crud->query
                    ->leftJoin('journal_entries', 'journal_entries.account_id', '=', 'accounts.id');

                CRUD::addClause('select', [
                    DB::raw("
                        accounts.id as id,
                        accounts.id as id_,
                        MAX(accounts.code) as code_,
                        MAX(accounts.name) as name_,
                        MAX(accounts.level) as level_,
                        (SUM(journal_entries.debit) - SUM(journal_entries.credit)) as balance
                    ")
                ]);


                if ($code->level == 1) {
                    $this->crud->query = $this->crud->query
                        ->where('code', 'LIKE', "{$code->code}");
                } else {
                    $this->crud->query = $this->crud->query
                        ->where('code', 'LIKE', "{$code->code}%");
                }


                $this->crud->query = $this->crud->query
                    ->orderBy('code', 'asc')
                    ->groupBy('accounts.id');
            }
        }

        // CRUD::addButtonFromView('top', 'filter_paid_unpaid', 'filter-paid_unpaid', 'beginning');


    }

    protected function setupListExportOperation()
    {
        $settings = Setting::first();
        $request = request();
        if ($request->has('type')) {
            if ($request->type == 'project') {

                $status_file = '';
                if (strpos(url()->current(), 'excel')) {
                    $status_file = 'excel';
                } else {
                    $status_file = 'pdf';
                }

                CRUD::setModel(ProjectProfitLost::class);

                $voucher = DB::table('vouchers')->select(
                    'client_po_id',
                    DB::raw("SUM(payment_transfer) as payment_transfer"),
                    DB::raw("SUM(total) as biaya")
                )->groupBy('client_po_id');

                $invoice = DB::table('invoice_clients')
                    ->select(
                        'invoice_clients.client_po_id',
                        DB::raw("GROUP_CONCAT(invoice_clients.invoice_date SEPARATOR ',') AS invoice_date"),
                        DB::raw("SUM(invoice_clients.price_total_exclude_ppn) as price_job_exlude_ppn"),
                        DB::raw("SUM(invoice_clients.price_total_include_ppn) as price_job_include_ppn")
                    )
                    ->groupBy('invoice_clients.client_po_id');

                $client_po_query_exclude_ppn = DB::table("client_po")
                    ->leftJoinSub($invoice, 'invoice', function ($join) {
                        $join->on('invoice.client_po_id', '=', 'client_po.id');
                    })->select(
                        "client_po.id as client_po_id",
                        "client_po.work_code",
                        "client_po.po_number",
                        "client_po.reimburse_type",
                        "client_po.job_name",
                        "client_po.job_value",
                        "client_po.category",
                        "client_po.job_value_include_ppn",
                        "invoice.invoice_date",
                        "invoice.price_job_exlude_ppn as invoice_price_job_exlude_ppn",
                        "invoice.price_job_include_ppn as invoice_price_job_include_ppn",
                        DB::raw("IF(invoice.invoice_date IS NULL, client_po.job_value, invoice.price_job_exlude_ppn) as price_job_exlude_ppn_logic")
                    );

                $this->crud->query = $this->crud->query
                    ->leftJoinSub($client_po_query_exclude_ppn, 'client_po', function ($join) {
                        $join->on('client_po.client_po_id', '=', 'project_profit_lost.client_po_id');
                    });

                $this->crud->query = $this->crud->query
                    ->leftJoinSub($voucher, 'vouchers', function ($join) {
                        $join->on('vouchers.client_po_id', '=', 'project_profit_lost.client_po_id');
                    });

                if ($request->has('category')) {
                    $this->crud->query = $this->crud->query->where('client_po.category', $request->category);
                }

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
                    'label' => '',
                    'type'      => 'closure',
                    'name'      => 'client_po_id',
                    'function' => function ($entry) {
                        return $entry->clientPo->client->name;
                    }, // the column that contains the ID of that connected entity;
                    'searchLogic' => function ($query, $column, $searchTerm) {
                        $query->orWhereHas('clientPo.client', function ($q) use ($column, $searchTerm) {
                            $q->where('name', 'like', '%' . $searchTerm . '%');
                        });
                    }
                ]);

                CRUD::column([
                    'label'  => trans('backpack::crud.client_po.column.reimburse_type'),
                    'name' => 'reimburse_type',
                    'type' => 'text',
                    'searchLogic' => function ($query, $column, $searchTerm) {
                        $query->orWhere('client_po.reimburse_type', 'like', '%' . $searchTerm . '%');
                    }
                ]);

                CRUD::column([
                    'label'  => trans('backpack::crud.client_po.column.work_code'),
                    'name' => 'work_code',
                    'type' => 'text',
                    'searchLogic' => function ($query, $column, $searchTerm) {
                        $query->orWhere('client_po.work_code', 'like', '%' . $searchTerm . '%');
                    }
                ]);

                CRUD::column([
                    'label'  => trans('backpack::crud.client_po.column.po_number'),
                    'name' => 'po_number',
                    'type' => 'text',
                    'searchLogic' => function ($query, $column, $searchTerm) {
                        $query->orWhere('client_po.po_number', 'like', '%' . $searchTerm . '%');
                    }
                ]);

                CRUD::column([
                    'label'  => trans('backpack::crud.client_po.column.job_name'),
                    'name' => 'job_name',
                    'type' => 'wrap_text',
                    'searchLogic' => function ($query, $column, $searchTerm) {
                        $query->orWhere('client_po.job_name', 'like', '%' . $searchTerm . '%');
                    }
                ]);

                CRUD::column([
                    'label'  => trans('backpack::crud.client_po.column.job_value_exclude_ppn'),
                    'name' => 'job_value',
                    'type'  => 'closure',
                    'function' => function ($entry) use ($status_file) {
                        return $this->priceFormatExport($status_file, $entry->price_job_exlude_ppn_logic);
                    },
                    'orderLogic' => function ($query, $column, $columnDirection) {
                        $query->orderBy('client_po.price_job_exlude_ppn_logic', $columnDirection);
                    }
                ]);

                CRUD::column([
                    'label'  => trans('backpack::crud.client_po.column.job_value_include_ppn'),
                    'name' => 'job_value_include_ppn',
                    'type'  => 'closure',
                    'function' => function ($entry) use ($status_file) {
                        return $this->priceFormatExport($status_file, $entry->job_value_include_ppn);
                    },
                    'orderLogic' => function ($query, $column, $columnDirection) {
                        $query->orderBy('client_po.job_value_include_ppn', $columnDirection);
                    }
                ]);

                CRUD::column([
                    'label'  => trans('backpack::crud.client_po.column.price_after_year'),
                    'name' => 'price_after_year',
                    'type'  => 'closure',
                    'decimals'      => 2,
                    'dec_point'     => ',',
                    'thousands_sep' => '.',
                    'function' => function ($entry) use ($status_file) {
                        return $this->priceFormatExport($status_file, $entry->price_after_year);
                    }
                ]);

                CRUD::column([
                    'label'  => trans('backpack::crud.profit_lost.column.price_voucher'),
                    'name' => 'voucher_biaya',
                    'type'  => 'closure',
                    'function' => function ($entry) use ($status_file) {
                        return $this->priceFormatExport($status_file, $entry->voucher_biaya);
                    },
                    'orderLogic' => function ($query, $column, $columnDirection) {
                        $query->orderBy('voucher_biaya', $columnDirection);
                    }
                ]);

                CRUD::column([
                    'label'  => trans('backpack::crud.profit_lost.column.price_small_cash'),
                    'name' => 'total_small_cash',
                    'type'  => 'closure',
                    'function' => function ($entry) use ($status_file) {
                        return $this->priceFormatExport($status_file, $entry->total_small_cash);
                    },
                    'orderLogic' => function ($query, $column, $columnDirection) {
                        $query->orderBy('total_small_cash', $columnDirection);
                    }
                ]);

                CRUD::column([
                    'label'  => trans('backpack::crud.profit_lost.column.price_total'),
                    'name' => 'price_total_str',
                    'type'  => 'closure',
                    'function' => function ($entry) use ($status_file) {
                        return $this->priceFormatExport($status_file, $entry->price_total_str);
                    },
                    'orderLogic' => function ($query, $column, $columnDirection) {
                        $query->orderBy('price_total_str', $columnDirection);
                    }
                ]);

                CRUD::column([
                    'label'  => trans('backpack::crud.profit_lost.column.profit_lost_po'),
                    'name' => 'price_profit_lost_str',
                    'type'  => 'closure',
                    'function' => function ($entry) use ($status_file) {
                        return $this->priceFormatExport($status_file, $entry->price_profit_lost_str);
                    },
                    'orderLogic' => function ($query, $column, $columnDirection) {
                        $query->orderBy('price_profit_lost_str', $columnDirection);
                    }
                ]);

                CRUD::column([
                    'label'  => trans('backpack::crud.profit_lost.column.load_general_value'),
                    'name' => 'price_general',
                    'type'  => 'closure',
                    'function' => function ($entry) use ($status_file) {
                        return $this->priceFormatExport($status_file, $entry->price_general);
                    },
                    'orderLogic' => function ($query, $column, $columnDirection) {
                        $query->orderBy('price_general', $columnDirection);
                    }
                ]);

                CRUD::column([
                    'label'  => trans('backpack::crud.profit_lost.column.profit_lost_final'),
                    'name' => 'price_prift_lost_final_str',
                    'type'  => 'closure',
                    'function' => function ($entry) use ($status_file) {
                        return $this->priceFormatExport($status_file, $entry->price_prift_lost_final_str);
                    },
                    'orderLogic' => function ($query, $column, $columnDirection) {
                        $query->orderBy('price_prift_lost_final_str', $columnDirection);
                    }
                ]);

                CRUD::column([
                    'label'  => trans('backpack::crud.profit_lost.column.category'),
                    'type'      => 'text',
                    'name'      => 'category',
                ]);

                CRUD::column([
                    'label' => trans('backpack::crud.profit_lost.column.invoice_date'),
                    'type' => 'date-multiple',
                    'name' => 'invoice_date',
                    'format' => 'DD MMM YYYY',
                    // 'function' => function ($entry) {
                    //     return $entry->invoice_date;
                    // },
                    'orderLogic' => function ($query, $column, $columnDirection) {
                        $query->orderBy('client_po.invoice_date', $columnDirection);
                    }
                ]);

                CRUD::addClause('select', [
                    DB::raw("
                        project_profit_lost.*,
                        vouchers.payment_transfer as payment_voucher,
                        vouchers.biaya as voucher_biaya,
                        client_po.invoice_date,
                        client_po.price_job_exlude_ppn_logic,
                        client_po.invoice_price_job_include_ppn,
                        client_po.work_code as work_code,
                        client_po.po_number as po_number,
                        client_po.reimburse_type as reimburse_type,
                        client_po.job_name as job_name,
                        client_po.job_value as job_value,
                        client_po.job_value_include_ppn as job_value_include_ppn,
                        IFNULL(project_profit_lost.price_small_cash, 0) as total_small_cash,
                        (IFNULL(project_profit_lost.price_after_year, 0) + IFNULL(vouchers.biaya, 0) + IFNULL(project_profit_lost.price_small_cash, 0)) as price_total_str,
                        (client_po.price_job_exlude_ppn_logic - (IFNULL(project_profit_lost.price_after_year, 0) + IFNULL(vouchers.biaya, 0) + IFNULL(project_profit_lost.price_small_cash, 0))) as price_profit_lost_str,
                        ((client_po.price_job_exlude_ppn_logic - (IFNULL(project_profit_lost.price_after_year, 0) + IFNULL(vouchers.biaya, 0) + IFNULL(project_profit_lost.price_small_cash, 0))) - IFNULL(project_profit_lost.price_general, 0)) as price_prift_lost_final_str
                   ")
                ]);
            }
        }
    }

    private function setupListExport()
    {
        $settings = Setting::first();
        CRUD::setModel(ProjectProfitLost::class);
        $this->crud->query = $this->crud->query
            ->leftJoin('vouchers', function ($join) {
                $join->on('vouchers.reference_id', '=', 'project_profit_lost.client_po_id')
                    ->where('vouchers.reference_type', 'App\\Models\\ClientPo');
            })->leftJoin('client_po', 'client_po.id', '=', 'vouchers.reference_id');

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
            'label' => trans('backpack::crud.profit_lost.column.client_po_id'),
            'type'      => 'closure',
            'name'      => 'client_po_id',
            'function' => function ($entry) {
                return $entry->clientPo->client->name;
            }, // the column that contains the ID of that connected entity;
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhereHas('clientPo.client', function ($q) use ($column, $searchTerm) {
                    $q->where('name', 'like', '%' . $searchTerm . '%');
                });
            }
        ]);

        CRUD::column([
            'label'  => trans('backpack::crud.client_po.column.reimburse_type'),
            'name' => 'reimburse_type',
            'type' => 'text',
        ]);

        CRUD::column([
            'label'  => trans('backpack::crud.client_po.column.work_code'),
            'name' => 'work_code',
            'type' => 'text',
        ]);

        CRUD::column([
            'label'  => trans('backpack::crud.client_po.column.po_number'),
            'name' => 'po_number',
            'type' => 'text',
        ]);

        CRUD::column([
            'label'  => trans('backpack::crud.client_po.column.job_name'),
            'name' => 'job_name',
            'type' => 'text',
        ]);

        CRUD::column([
            'label'  => trans('backpack::crud.client_po.column.job_value_exclude_ppn'),
            'name' => 'job_value',
            'type'  => 'number',
            'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
        ]);

        CRUD::column([
            'label'  => trans('backpack::crud.client_po.column.job_value_include_ppn'),
            'name' => 'job_value_include_ppn',
            'type'  => 'number',
            'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
        ]);

        CRUD::column([
            'label'  => trans('backpack::crud.client_po.column.price_after_year'),
            'name' => 'price_after_year',
            'type'  => 'number',
            'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
        ]);

        CRUD::column([
            'label'  => trans('backpack::crud.profit_lost.column.price_voucher'),
            'name' => 'price_voucher',
            'type'  => 'number',
            'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
        ]);

        CRUD::column([
            'label'  => trans('backpack::crud.profit_lost.column.price_small_cash'),
            'name' => 'price_small_cash',
            'type'  => 'number',
            'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
        ]);

        CRUD::column([
            'label'  => trans('backpack::crud.profit_lost.column.price_total'),
            'name' => 'price_total',
            'type'  => 'number',
            'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
        ]);

        CRUD::column([
            'label'  => trans('backpack::crud.profit_lost.column.profit_lost_po'),
            'name' => 'price_profit_lost_po',
            'type'  => 'number',
            'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
        ]);

        CRUD::column([
            'label'  => trans('backpack::crud.profit_lost.column.load_general_value'),
            'name' => 'price_general',
            'type'  => 'number',
            'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
        ]);

        CRUD::column([
            'label'  => trans('backpack::crud.profit_lost.column.profit_lost_final'),
            'name' => 'price_prift_lost_final',
            'type'  => 'number',
            'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
        ]);

        CRUD::column([
            'label'  => trans('backpack::crud.profit_lost.column.category'),
            'type'      => 'text',
            'name'      => 'category',
        ]);

        CRUD::addClause('select', [
            DB::raw("
                project_profit_lost.*,
                client_po.work_code as work_code,
                client_po.po_number as po_number,
                client_po.reimburse_type as reimburse_type,
                client_po.job_name as job_name,
                client_po.job_value as job_value,
                client_po.job_value_include_ppn as job_value_include_ppn
            ")
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

    public function exportPdf()
    {

        // $this->setupListExport();
        $this->setupListExportOperation();

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

        $title = "DAFTAR LAPORAN LABA RUGI PROYEK";

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

        $this->setupListExportOperation();
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

        $name = 'DAFTAR SPK';

        return response()->streamDownload(function () use ($columns, $items, $all_items) {
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

    public function exportDetailPdf()
    {

        $id = request()->id;
        $profitLost = ProjectProfitLost::where('id', $id)->first();

        $pdf = Pdf::loadView('exports.profit-lost-detail', [
            'profit_lost' => $profitLost,
            'report' => $this->total_detail_project($id)
        ])->setPaper('A4', 'portrait');

        $fileName = 'laporan-laba-rugi_' . now()->format('Ymd_His') . '.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function exportDetailExcel()
    {
        $id = request()->id;
        $profitLost = ProjectProfitLost::where('id', $id)->first();
        $report = $this->total_detail_project($id, 1);

        $name = "Laporan-laba-rugi.xlsx";

        return response()->streamDownload(function () use ($profitLost, $report, $name) {
            echo Excel::raw(new ProfitLostExcel(
                $profitLost,
                $report
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

    public function total_report_account_profit_lost()
    {
        $acct_1 = Account::where('accounts.code', 109)
            ->leftJoin('journal_entries', 'journal_entries.account_id', '=', 'accounts.id')
            ->select(DB::raw('SUM(journal_entries.debit - journal_entries.credit) as balance'))
            ->first();

        $acct_2 = Account::where('accounts.code', 401)
            ->leftJoin('journal_entries', 'journal_entries.account_id', '=', 'accounts.id')
            ->select(DB::raw('SUM(journal_entries.debit - journal_entries.credit) as balance'))
            ->first();

        $acct_3 = Account::where('accounts.code', 402)
            ->leftJoin('journal_entries', 'journal_entries.account_id', '=', 'accounts.id')
            ->select(DB::raw('SUM(journal_entries.debit - journal_entries.credit) as balance'))
            ->first();

        $acct_4 = Account::where('accounts.code', 110)
            ->leftJoin('journal_entries', 'journal_entries.account_id', '=', 'accounts.id')
            ->select(DB::raw('SUM(journal_entries.debit - journal_entries.credit) as balance'))
            ->first();

        $acct_5 = Account::where('accounts.code', 11001)
            ->leftJoin('journal_entries', 'journal_entries.account_id', '=', 'accounts.id')
            ->select(DB::raw('SUM(journal_entries.debit - journal_entries.credit) as balance'))
            ->first();

        $acct_6 = Account::where('accounts.code', 11002)
            ->leftJoin('journal_entries', 'journal_entries.account_id', '=', 'accounts.id')
            ->select(DB::raw('SUM(journal_entries.debit - journal_entries.credit) as balance'))
            ->first();

        $acct_7 = Account::where('accounts.code', 111)
            ->leftJoin('journal_entries', 'journal_entries.account_id', '=', 'accounts.id')
            ->select(DB::raw('SUM(journal_entries.debit - journal_entries.credit) as balance'))
            ->first();

        $acct_8 = Account::where('accounts.code', 112)
            ->leftJoin('journal_entries', 'journal_entries.account_id', '=', 'accounts.id')
            ->select(DB::raw('SUM(journal_entries.debit - journal_entries.credit) as balance'))
            ->first();

        $acct_9 = Account::where('accounts.code', 40201)
            ->leftJoin('journal_entries', 'journal_entries.account_id', '=', 'accounts.id')
            ->select(DB::raw('SUM(journal_entries.debit - journal_entries.credit) as balance'))
            ->first();

        $acct_10 = Account::where('accounts.code', 113)
            ->leftJoin('journal_entries', 'journal_entries.account_id', '=', 'accounts.id')
            ->select(DB::raw('SUM(journal_entries.debit - journal_entries.credit) as balance'))
            ->first();

        $acct_11 = Account::where('accounts.code', 11301)
            ->leftJoin('journal_entries', 'journal_entries.account_id', '=', 'accounts.id')
            ->select(DB::raw('SUM(journal_entries.debit - journal_entries.credit) as balance'))
            ->first();

        $acct_12 = Account::where('accounts.code', 108)
            ->leftJoin('journal_entries', 'journal_entries.account_id', '=', 'accounts.id')
            ->select(DB::raw('SUM(journal_entries.debit - journal_entries.credit) as balance'))
            ->first();

        $total_acct_1 = $acct_1->balance + $acct_2->balance;
        $total_acct_4 =  $acct_5->balance + $acct_6->balance;
        $total_acct_8 = $acct_9->balance;
        $total_acct_10 = $acct_11->balance;

        return [
            'total_acct_1' => ($total_acct_1 > 0) ? CustomHelper::formatRupiahWithCurrency($total_acct_1) : 0,
            'total_acct_2' => ($acct_2->balance > 0) ? CustomHelper::formatRupiahWithCurrency($acct_2->balance) : 0,
            'total_acct_3' => ($acct_3->balance > 0) ? CustomHelper::formatRupiahWithCurrency($acct_3->balance) : 0,
            'total_acct_4' => ($total_acct_4 > 0) ? CustomHelper::formatRupiahWithCurrency($total_acct_4) : 0,
            'total_acct_5' => ($acct_5->balance > 0) ? CustomHelper::formatRupiahWithCurrency($acct_5->balance) : 0,
            'total_acct_6' => ($acct_6->balance > 0) ? CustomHelper::formatRupiahWithCurrency($acct_6->balance) : 0,
            'total_acct_7' => ($acct_7->balance > 0) ? CustomHelper::formatRupiahWithCurrency($acct_7->balance) : 0,
            'total_acct_8' => ($total_acct_8 > 0) ? CustomHelper::formatRupiahWithCurrency($total_acct_8) : 0,
            'total_acct_9' => ($acct_9->balance > 0) ? CustomHelper::formatRupiahWithCurrency($acct_9->balance) : 0,
            'total_acct_10' => ($total_acct_10 > 0) ? CustomHelper::formatRupiahWithCurrency($total_acct_10) : 0,
            'total_acct_11' => ($acct_11->balance > 0) ? CustomHelper::formatRupiahWithCurrency($acct_11->balance) : 0,
            'total_acct_12' => ($acct_12->balance > 0) ? CustomHelper::formatRupiahWithCurrency($acct_12->balance) : 0,
        ];
    }

    public function exportConsolidationPdf()
    {

        $pdf = Pdf::loadView('exports.profit-lost-consolidation-pdf', [
            'data' => $this->total_report_account_profit_lost_ajax(),
        ])->setPaper('A4', 'portrait');

        $fileName = 'laporan-laba-rugi_' . now()->format('Ymd_His') . '.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function exportConsolidationExcel()
    {
        $data = $this->total_report_account_profit_lost_ajax();

        $name = "Laporan-laba-rugi.xlsx";

        return response()->streamDownload(function () use ($data, $name) {
            echo Excel::raw(new ExportProfitLostConsolidation($data), \Maatwebsite\Excel\Excel::XLSX);
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
