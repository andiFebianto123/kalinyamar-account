<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Setting;
use App\Models\CastAccount;
use App\Models\JournalEntry;
use PhpParser\Node\Expr\Cast;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Exports\ExportExcel;
use App\Http\Helpers\CustomHelper;
use App\Models\AccountTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Notifications\Action;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\CrudController;
use App\Models\InvoiceClient;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use GuzzleHttp\Psr7\Request;

/**
 * Class CastAccountsCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CastAccountsCrudController extends CrudController
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
        $this->crud->denyAllAccess(['create', 'update', 'delete', 'list', 'show']);
        CRUD::setModel(CastAccount::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/cash-flow/cast-accounts');
        CRUD::setEntityNameStrings(trans('backpack::crud.cash_account.title_header'), trans('backpack::crud.cash_account.title_header'));
        $user = backpack_user();
        $permissions = $user->getAllPermissions();
        if($permissions->whereIn('name', [
            'AKSES SEMUA VIEW ACCOUNTING',
            'AKSES SEMUA MENU ACCOUNTING',
        ])->count() > 0)
        {
            $this->crud->allowAccess(['list', 'show']);
        }

        if($permissions->whereIn('name',[
            'AKSES SEMUA MENU ACCOUNTING',
        ])->count() > 0){
            $this->crud->allowAccess(['create', 'update', 'delete']);
        }
    }

    public function listCardComponents($list = null){
        if($list && ($list->count() > 0)){

            $this->modal->addModal([
                'name' => 'modal_info_cast_account',
                'title' => 'info',
                'title_alignment' => 'center',
                'size' => 'modal-lg',
                'view' => 'crud::components.modal-info-cast-account',
                'params' => [
                    'name' => 'modal_info_cast_account',
                    'crud' => $this->crud,
                ],
                'buttons' => [
                    'footer' => [
                        'right' => [
                            [
                                'class' => 'btn btn-primary btn-sm',
                                'label' => trans('backpack::crud.modal.close'),
                                'action' => "$('#modal_info_cast_account .btn-close').click()",
                            ]
                        ]
                    ]
                ]
            ]);

            $this->modal->addModal([
                'name' => 'modal_transfer_balance',
                'title' => trans('backpack::crud.modal.transfer_balance'),
                'title_alignment' => 'center',
                'size' => 'modal-lg',
                'view' => 'crud::components.modal-transfer-balance-cast-account',
                'params' => [
                    'name' => 'modal_transfer_balance',
                    'crud' => $this->crud,
                    'settings' => Setting::first(),
                ],
                'buttons' => [
                    'footer' => [
                        'right' => [
                            [
                                'class' => 'btn btn-secondary btn-sm',
                                'label' => trans('backpack::crud.modal.cancel'),
                                'action' => "$('#modal_transfer_balance .btn-close').click()",
                            ],
                            [
                                'class' => 'btn btn-primary btn-sm btn-transfer-balance',
                                'label' => trans('backpack::crud.modal.move'),
                            ],
                        ]
                    ]
                ]
            ]);

            foreach($list as $l){
                $l->saldo = ($l->total_saldo_enter - $l->total_saldo_out);
                $this->card->addCard([
                    'name' => 'card_cast_account'.$l->id,
                    'line' => 'top',
                    'view' => 'crud::components.cast_account_card',
                    'params' => [
                        'access' => $l->informations,
                        'detail' => $l,
                        'crud' => $this->crud,
                        'name' => 'card_cast_account'.$l->id,
                        'route_edit' => url($this->crud->route."/".$l->id.'/edit?type=cast_account'),
                        'route_update' => url($this->crud->route."/".$l->id.'?type=cast_account'),
                    ]
                ]);
            }
        }else{
            $this->card->addCard([
                'name' => 'blank_cast_account',
                'line' => 'top',
                'view' => 'crud::components.blank_card',
                'params' => [
                    'message' => trans('backpack::crud.card.blank_cast_account')
                ]
            ]);
        }
    }

    public function index()
    {
        $this->crud->hasAccessOrFail('list');

        $this->data['is_disabled_list'] = true;

        $listCashAccounts = CastAccount::leftJoin('account_transactions', 'account_transactions.cast_account_id', '=', 'cast_accounts.id')
        ->where('cast_accounts.status', CastAccount::CASH)
        ->groupBy('cast_accounts.id')
        ->orderBy('id', 'ASC')->select(DB::raw('
            cast_accounts.id,
            MAX(cast_accounts.name) as name,
            MAX(cast_accounts.bank_name) as bank_name,
            MAX(cast_accounts.no_account) as no_account,
            MAX(cast_accounts.status) as status,
            SUM(IF(account_transactions.status = "enter", account_transactions.nominal_transaction, 0)) as total_saldo_enter,
            SUM(IF(account_transactions.status = "out", account_transactions.nominal_transaction, 0)) as total_saldo_out
        '
        ))->get();

        $this->listCardComponents($listCashAccounts);

        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? mb_ucfirst($this->crud->entity_name_plural);
        $this->data['title_modal_create'] = trans('backpack::crud.cash_account.title_modal_create');
        $this->data['title_modal_edit'] = trans('backpack::crud.cash_account.title_modal_edit');
        $this->data['title_modal_delete'] = trans('backpack::crud.cash_account.title_modal_delete');

        $breadcrumbs = [
            trans('backpack::crud.menu.cash_flow') => backpack_url('cash-flow'),
            trans('backpack::crud.menu.cash_flow_cash') => backpack_url($this->crud->route)
        ];
        $this->data['breadcrumbs'] = $breadcrumbs;

        $this->data['cards'] = $this->card;
        $this->data['modals'] = $this->modal;
        $this->data['scripts'] = $this->script;
        $list = "crud::list-blank" ?? $this->crud->getListView();
        return view($list, $this->data);
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        // filter-cash-account-order
        $this->crud->file_title_export_pdf = "Laporan_daftar_rekening_kas.pdf";
        $this->crud->file_title_export_excel = "Laporan_daftar_rekening_kas.xlsx";
        $this->crud->param_uri_export = "?export=1";

        CRUD::addButtonFromView('top', 'export-excel-table', 'export-excel-table', 'beginning');
        CRUD::addButtonFromView('top', 'export-pdf-table', 'export-pdf-table', 'beginning');
        CRUD::addButtonFromView('top', 'filter_cash_account_order', 'filter-cash-account-order', 'beginning');

        // CRUD::setFromDb(); // set columns from db columns.

        /**
         * Columns can be defined using the fluent syntax:
         * - CRUD::column('price')->type('number');
         */
    }

    private function setupListExport(){
        $settings = Setting::first();
        $this->crud->query = $this->crud->query->leftJoin('account_transactions', 'account_transactions.cast_account_id', '=', 'cast_accounts.id')
        ->where('cast_accounts.status', CastAccount::CASH)
        ->groupBy('cast_accounts.id')
        ->orderBy('id', 'ASC')
        ->select(DB::raw('
            cast_accounts.id,
            MAX(cast_accounts.name) as name,
            MAX(cast_accounts.bank_name) as bank_name,
            MAX(cast_accounts.no_account) as no_account,
            MAX(cast_accounts.status) as status,
            (SUM(IF(account_transactions.status = "enter", account_transactions.nominal_transaction, 0)) - SUM(IF(account_transactions.status = "out", account_transactions.nominal_transaction, 0))) as saldo
        '));

        $this->crud->addColumn([
            'name'      => 'row_number',
            'type'      => 'export',
            'label'     => 'No',
            'orderable' => false,
            'wrapper' => [
                'element' => 'strong',
            ]
        ])->makeFirstColumn();

        CRUD::column(
            [
                'label'  => trans('backpack::crud.cash_account.field.name.label'),
                'name' => 'name',
                'type'  => 'export'
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.cash_account.field.bank_name.label'),
                'name' => 'bank_name',
                'type'  => 'export'
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.cash_account.field.no_account.label'),
                'name' => 'no_account',
                'type'  => 'export'
            ],
        );

        // CRUD::column(
        //     [
        //         'label'  => trans('backpack::crud.cash_account.field.total_saldo.label'),
        //         'name' => 'saldo',
        //         'type'  => ''
        //     ],
        // );
        CRUD::column([
                'label'  => trans('backpack::crud.cash_account.field.total_saldo.label'),
                'name' => 'saldo',
                'type'  => 'number',
                'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
            ]);

    }

    private function setuplistExportTrans(){
        $id = request()->id;
        $settings = Setting::first();
        CRUD::setModel(AccountTransaction::class);
        $castAccount = CastAccount::where('id', $id)->first();
        // $detail = AccountTransaction::where('cast_account_id', $id)
        // ->where('is_first', 0)
        // ->orderBy('date_transaction', 'ASC')->get();
        $this->crud->query = $this->crud->query
        ->where('cast_account_id', $id)
        ->where('is_first', 0)
        ->orderBy('date_transaction', 'ASC');

        $this->crud->addColumn([
            'name'      => 'row_number',
            'type'      => 'export',
            'label'     => 'No',
            'orderable' => false,
            'wrapper' => [
                'element' => 'strong',
            ]
        ])->makeFirstColumn();

        CRUD::column([
                'label'  => trans('backpack::crud.cash_account.field_transaction.date_transaction.label'),
            'name' => 'date_transaction',
            'type'  => 'date',
            'format' => 'D MMM Y'
        ]);

        // CRUD::column(
        //     [
        //         'label'  => trans('backpack::crud.cash_account.field_transaction.nominal.label'),
        //         'name' => 'nominal_transaction',
        //         'type'  => 'export'
        //     ],
        // );

        CRUD::column([
                'label'  => trans('backpack::crud.cash_account.field_transaction.nominal.label'),
            'name' => 'nominal_transaction',
            'type'  => 'number',
            'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
        ]);

        CRUD::column(
            [
                'label'  => trans('backpack::crud.cash_account.field_transaction.description.label'),
                'name' => 'description',
                'type'  => 'export'
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.cash_account.field_transaction.kdp.label'),
                'name' => 'kdp',
                'type'  => 'export'
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.cash_account.field_transaction.job_name.label'),
                'name' => 'job_name',
                'type'  => 'export'
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.cash_account.field_transaction.account.label'),
                'name' => 'account',
                'type'  => 'closure',
                'function' => function($row){
                    return ($row->account_id) ? $row->account->code.' - '.$row->account->name : '-';
                }
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.cash_account.field_transaction.no_invoice.label'),
                'name' => 'no_invoice',
                'type'  => 'closure',
                'function' => function($row){
                    return ($row->no_invoice) ? $row->no_invoice : '-';
                }
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.cash_account.field_transaction.status.label'),
                'name' => 'status',
                'type'  => 'closure',
                'function' => function($row){
                    return ucfirst(strtolower(trans('backpack::crud.cash_account.field_transaction.status.'.$row->status)));
                }
            ],
        );

        return $castAccount;
    }

    public function exportPdf(){

        $this->setupListExport();

        $columns = $this->crud->columns();
        $items =  $this->crud->getEntries();

        $row_number = 0;

        $all_items = [];

        foreach($items as $item){
            $row_items = [];
            $row_number++;
            foreach($columns as $column){
                $item_value = ($column['name'] == 'row_number') ? $row_number : $this->crud->getCellView($column, $item, $row_number);
                $item_value = str_replace('<span>', '', $item_value);
                $item_value = str_replace('</span>', '', $item_value);
                $item_value = str_replace("\n", '', $item_value);
                $item_value = CustomHelper::clean_html($item_value);
                $row_items[] = trim($item_value);
            }
            $all_items[] = $row_items;
        }

        $title = "DAFTAR REKENING KAS";

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
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    public function exportExcel(){

        $this->setupListExport();

        $columns = $this->crud->columns();
        $items =  $this->crud->getEntries();

        $row_number = 0;

        $all_items = [];

        foreach($items as $item){
            $row_items = [];
            $row_number++;
            foreach($columns as $column){
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

        return response()->streamDownload(function () use($columns, $items, $all_items){
            echo Excel::raw(new ExportExcel(
                $columns, $all_items), \Maatwebsite\Excel\Excel::XLSX);
        }, $name, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $name . '"',
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Download Failure',
        ], 400);
    }


    public function exportTransPdf(){
        $cast = $this->setuplistExportTrans();

        $columns = $this->crud->columns();
        $items =  $this->crud->getEntries();
        $row_number = 0;

        $all_items = [];

        foreach($items as $item){
            $row_items = [];
            $row_number++;
            foreach($columns as $column){
                $item_value = ($column['name'] == 'row_number') ? $row_number : $this->crud->getCellView($column, $item, $row_number);
                $item_value = str_replace('<span>', '', $item_value);
                $item_value = str_replace('</span>', '', $item_value);
                $item_value = str_replace("\n", '', $item_value);
                $item_value = CustomHelper::clean_html($item_value);
                $row_items[] = trim($item_value);
            }
            $all_items[] = $row_items;
        }

        $title = "DAFTAR TRANSAKSI KAS REKENING ".$cast?->name;

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
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    public function exportTransExcel(){
        $this->setuplistExportTrans();

        $columns = $this->crud->columns();
        $items =  $this->crud->getEntries();

        $row_number = 0;

        $all_items = [];

        foreach($items as $item){
            $row_items = [];
            $row_number++;
            foreach($columns as $column){
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

        return response()->streamDownload(function () use($columns, $items, $all_items){
            echo Excel::raw(new ExportExcel(
                $columns, $all_items), \Maatwebsite\Excel\Excel::XLSX);
        }, $name, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $name . '"',
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Download Failure',
        ], 400);
    }

    function ruleValidation(){
        $id = request('id') ?? '';
        $no_account = request('no_account') ?? '';
        // 'name' => 'required|min:5|max:255'
        return [
            'name' => 'required|max:100|unique:cast_accounts,name,'.$id,
            'bank_name' => 'required|max:50',
            'no_account' => 'required|max:100,unique:cast_accounts,no_account,'.$id,
            'total_saldo' => 'required|numeric|min:0',
            'account_id' => [
                'required',
                'exists:accounts,id',
                function ($attribute, $value, $fail) use($no_account){
                    $castAccount = CastAccount::where('account_id', $value)
                    ->where('status', CastAccount::CASH)
                    ->where('no_account', $no_account)
                    ->first();
                    if($castAccount){
                        $fail(trans('backpack::crud.cash_account.field_transfer.errors.account_id_exist'));
                    }
                    return true;
                }
            ]
        ];
    }

    function ruleValidationTransaction(){
        $cast_account_id = request()->cast_account_id;
        $has_access_primary = $this->accessAccount($cast_account_id);
        $has_access_payment = $this->accessPaymentAccount($cast_account_id);

        $rule = [
            'date_transaction' => 'required',
            'nominal_transaction' => [
                'required',
                'numeric',
                'min:0'
            ],
            'kdp' => 'max:50',
            'job_name' => 'max:100',
            'no_invoice' => 'max:100',
            'account_id' => 'required|exists:accounts,id',
        ];

        if($has_access_primary == 0){
            $rule['nominal_transaction'] = [
                'required',
                'numeric',
                'min:1000',
                function ($attribute, $value, $fail) use($cast_account_id, $has_access_payment){
                    // total_balance_cast_account_edit
                    if($has_access_payment > 0){
                        // tidak ada validasi apapun
                    }else{
                        $balance = CustomHelper::total_balance_cast_account($cast_account_id, CastAccount::CASH);
                        if ($value > $balance) {
                            $fail(trans("backpack::crud.cash_account.field_transfer.errors.nominal_transfer_to_more"));
                        }
                    }
                }
            ];
        }

        return $rule;
    }

    function ruleValidationEditTransactionAccountPrimary(){
        return [
            'date_transaction' => 'required',
            'kdp' => 'max:50',
            'job_name' => 'max:100',
            'no_invoice' => 'max:100',
            'account_id' => 'exists:accounts,id',
        ];
    }

    function ruleValidationEditTransactionAccountSecondary(){
        $cast_account_id = request()->cast_account_id;
        $id = request()->id;
        $has_access_payment = $this->accessPaymentAccount($cast_account_id);
        return [
            'date_transaction' => 'required',
            'kdp' => 'max:50',
            'job_name' => 'max:100',
            'no_invoice' => 'max:100',
            'account_id' => 'exists:accounts,id',
            'nominal_transaction' => [
                'required',
                'numeric',
                'min:1000',
                function ($attribute, $value, $fail) use ($cast_account_id, $id, $has_access_payment) {
                    if($has_access_payment > 0){
                    }else{
                        if($id){
                            $balance = CustomHelper::total_balance_cast_account_edit($cast_account_id, $id, CastAccount::CASH);
                        }else{
                            $balance = CustomHelper::total_balance_cast_account($cast_account_id, CastAccount::CASH);
                        }
                        if ($value > $balance) {
                            $fail(trans("backpack::crud.cash_account.field_transfer.errors.nominal_transfer_to_more"));
                        }
                    }
                }
            ]
        ];
    }

    function ruleValidationStoreTransactionAccountPrimary(){
        return [
            'date_transaction' => 'required',
            'kdp' => 'max:50',
            'job_name' => 'max:100',
            'no_invoice' => 'max:100',
            'account_id' => 'required|exists:accounts,id',
            // 'status' => 'required|in:enter,out',
        ];
    }

    function account_select2(){
        $this->crud->hasAccessOrFail('create');

        $search = request()->input('q');
        $dataset = \App\Models\Account::select(['id', 'code', 'name'])
            ->where('level', ">", 2)
            ->where(function($q) use($search){
                $q->where('name', 'LIKE', "%$search%")
                ->orWhere('code', 'LIKE', "%$search%");
            })
            ->orderBy('code', 'ASC')
            ->paginate(10);

        $results = [];
        foreach ($dataset as $item) {
            $results[] = [
                'id' => $item->id,
                'text' => $item->code.' - '.$item->name,
            ];
        }
        return response()->json(['results' => $results]);
    }

    function account_child_select2(){
        $this->crud->hasAccessOrFail('create');

        $search = request()->input('q');
        $dataset = \App\Models\Account::select(['id', 'code', 'name'])
            ->where('level', '>', 1)
            ->where(function($q) use($search){
                $q->where('name', 'LIKE', "%$search%")
                ->orWhere('code', 'LIKE', "%$search%");
            })
            ->orderBy('code', 'ASC')
            ->paginate(10);

        $results = [];
        foreach ($dataset as $item) {
            $results[] = [
                'id' => $item->id,
                'text' => $item->code.' - '.$item->name,
            ];
        }
        return response()->json(['results' => $results]);
    }

    public function select2Invoice()
    {
        $this->crud->hasAccessOrFail('create');

        $search = request()->input('q');
        $r_type = request()->type;
        $invoices = InvoiceClient::where('status', 'Unpaid')
        ->where(function($q) use($search){
            $q->where('invoice_number', 'LIKE', "%$search%")
            ->orWhere('name', 'LIKE', "%$search%")
            ->orWhere('kdp', 'LIKE', "%$search%");
        });

        $dataset = $invoices->paginate(20);

        $results = [];
        foreach ($dataset as $item) {
            $type = ucfirst($item->type);
            $results[] = [
                'id' => $item->id,
                'text' => ($r_type == 'kdp') ? $item->kdp : $item->invoice_number,
            ];
        }
        return response()->json(['results' => $results]);
    }

    public function get_invoice_ajax(){
        // url : {{ url($crud->route) }}/get-invoice
        // method : GET
        $req = request();
        $id = $req->id;
        $invoice = InvoiceClient::leftJoin('client_po', 'client_po.id', 'invoice_clients.client_po_id')
        ->where('invoice_clients.id', $id)
        ->select(DB::raw('
            invoice_clients.id,
            invoice_clients.kdp,
            invoice_clients.invoice_number,
            client_po.job_name
        '))
        ->first();
        return response()->json($invoice);

    }

    function accessAccount($id_account){
        $account = CastAccount::whereHas('informations', function($q){
            $q->where('name', 'Jadikan rekening utama');
        })->where('id', $id_account)->get();
        return $account->count();
    }

    function accessPaymentAccount($id_account){
        $account = CastAccount::whereHas('informations', function($q){
            $q->where('name', 'Digunakan sebagai pembayaran langsung');
        })->where('id', $id_account)->get();
        return $account->count();
    }

    function createTransactionOperation($id = null){

        $settings = Setting::first();

        CRUD::setModel(AccountTransaction::class);
        CRUD::setValidation($this->ruleValidationTransaction());

        $attribute_form = [];
        $invoice_disabled = [];

        $has_access_primary = $this->accessAccount($id);

        if(request()->has('type')){
            // edit
            if($has_access_primary > 0){
                $attribute_form = [
                    'disabled' => true,
                ];
            }

            $invoice_disabled = [
                'disabled' => true,
            ];
        }


        CRUD::addField([
            'name' => 'cast_account_id ',
            'type' => 'hidden',
            'value' => $id,
        ]);

        CRUD::addField([   // date_picker
            'name'  => 'date_transaction',
            'type'  => 'date_picker',
            'label' => trans('backpack::crud.cash_account.field_transaction.date_transaction.label'),

            // optional:
            'date_picker_options' => [
                'language' => App::getLocale(),
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.cash_account.field_transaction.date_transaction.placeholder')
            ]
        ]);

        CRUD::addField([
            'name' => 'nominal_transaction',
            'label' =>  trans('backpack::crud.cash_account.field_transaction.nominal_transaction.label'),
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
                ...$attribute_form,
            ]
        ]);

        CRUD::addField([
            'name' => 'description',
            'label' => trans('backpack::crud.cash_account.field_transaction.description.label'),
            'type' => 'textarea',
            'attributes' => [
                'placeholder' => trans('backpack::crud.cash_account.field_transaction.description.placeholder'),
            ]
        ]);

        // CRUD::addField([
        //     'name' => 'kdp',
        //     'label' => trans('backpack::crud.cash_account.field_transaction.kdp.label'),
        //     'type' => 'text',
        //     'wrapper' => [
        //         'class' => 'form-group col-md-6'
        //     ],
        //     'attributes' => [
        //         'placeholder' => trans('backpack::crud.cash_account.field_transaction.kdp.placeholder'),
        //     ]
        // ]);

        CRUD::addField([
            'label'       => trans('backpack::crud.cash_account.field_transaction.kdp.label'), // Table column heading
            'type'        => "select2_ajax_custom",
            'name'        => 'kdp',
            'model'       => 'App\Models\ClientPo',
            'attribute'   => "work_code",
            'data_source' => backpack_url('cash-flow/cast-accounts-select-to-invoice?type=kdp'),
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.voucher.field.work_code.placeholder'),
                ...$invoice_disabled,
            ]
        ]);

        CRUD::addField([
            'name' => 'space_1',
            'type' => 'hidden',
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ]
        ]);

        CRUD::addField([
            'name' => 'job_name',
            'label' => trans('backpack::crud.cash_account.field_transaction.job_name.label'),
            'type' => 'text',
            'attributes' => [
                'placeholder' => trans('backpack::crud.cash_account.field_transaction.job_name.placeholder'),
            ]
        ]);

        CRUD::addField([
            'label'       => trans('backpack::crud.cash_account.field_transaction.account_id.label'), // Table column heading
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
                'placeholder' => trans('backpack::crud.cash_account.field_transaction.account_id.placeholder'),
            ]
        ]);

        // CRUD::addField([
        //     'name' => 'no_invoice',
        //     'label' => trans('backpack::crud.cash_account.field_transaction.no_invoice.label'),
        //     'type' => 'text',
        //     'wrapper'   => [
        //         'class' => 'form-group col-md-6',
        //     ],
        //     'attributes' => [
        //         'placeholder' => trans('backpack::crud.cash_account.field_transaction.no_invoice.placeholder'),
        //     ]
        // ]);

        CRUD::addField([
            'label' => trans('backpack::crud.cash_account.field_transaction.no_invoice.label'),
            'type'        => "select2_ajax_custom",
            'name'        => 'no_invoice',
            'model'       => 'App\Models\ClientPo',
            'attribute'   => "work_code",
            'data_source' => backpack_url('cash-flow/cast-accounts-select-to-invoice?type=invoice'),
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.cash_account.field_transaction.no_invoice.placeholder'),
                ...$invoice_disabled,
            ]
        ]);

        CRUD::addField([
            'name' => 'logic_account_transaction',
            'type' => 'logic-account-transaction',
        ]);

        if($has_access_primary == 0){
            // CRUD::addField([
            //     'name'        => 'status',
            //     'label'       => trans('backpack::crud.cash_account.field_transaction.status.label'),
            //     'type'        => 'select_from_array',
            //     'options'     => [
            //         '' => trans('backpack::crud.cash_account.field_transaction.status.placeholder'),
            //         'enter' => trans('backpack::crud.cash_account.field_transaction.status.enter'),
            //         'out' => trans('backpack::crud.cash_account.field_transaction.status.out')
            //     ],
            //     'allows_null' => false,
            //     'wrapper'   => [
            //         'class' => 'form-group col-md-6',
            //     ],
            //     'attributes' => [
            //         ...$attribute_form,
            //     ]
            //     // 'allows_multiple' => true, // OPTIONAL; needs you to cast this to array in your model;
            // ]);
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
        // CRUD::setFromDb(); // set fields from db columns.

        $request = request();

        // CRUD::setValidation($this->ruleValidation());

        if($request->has('type')){

            if($request->type == 'cast_account'){
                $id = $request->id ?? '';
                CRUD::setValidation([
                    'name' => 'required|max:100|unique:cast_accounts,name,'.$id,
                ]);
                CRUD::addField([
                    'name' => 'name',
                    'label' => trans('backpack::crud.cash_account.field.name.label'),
                    'type' => 'text',
                    'attributes' => [
                        'placeholder' => trans('backpack::crud.cash_account.field.name.placeholder'),
                    ]
                ]);
            }else if($request->type == 'transaction'){
                $this->createTransactionOperation($request->_id);
                return;
            }
        }else if($request->has('_id')){
            $this->createTransactionOperation($request->_id);
        }else{
            CRUD::setValidation($this->ruleValidation());
            CRUD::addField([
                'name' => 'name',
                'label' => trans('backpack::crud.cash_account.field.name.label'),
                'type' => 'text',
                // 'wrapper'   => [
                //     'class' => 'form-group col-md-12',
                // ],
                'attributes' => [
                    'placeholder' => trans('backpack::crud.cash_account.field.name.placeholder'),
                ]
            ]);

            CRUD::field([  // Select2
                'label'     => trans('backpack::crud.cash_account.field.bank_name.label'),
                'type'      => 'select2_array',
                'name'      => 'bank_name',
                'options'   => ['' => trans('backpack::crud.cash_account.field.bank_name.placeholder'), ...CustomHelper::getBanks()], // force the related options to be a custom query, instead of all(); you can use this to filter the results show in the select
                'wrapper' => [
                    'class' => 'form-group col-md-6'
                ]
            ]);

            CRUD::addField([
                'name' => 'no_account',
                'label' => trans('backpack::crud.cash_account.field.no_account.label'),
                'type' => 'text',
                'wrapper'   => [
                    'class' => 'form-group col-md-12',
                ],
                'attributes' => [
                    'placeholder' => trans('backpack::crud.cash_account.field.no_account.placeholder'),
                ]
            ]);

            CRUD::addField([
                'label'       => trans('backpack::crud.cash_account_loan.field.account.label'), // Table column heading
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
                    'placeholder' => trans('backpack::crud.cash_account_loan.field.account.placeholder'),
                ]
            ]);

            CRUD::addField([
                'name' => 'total_saldo',
                'label' => trans('backpack::crud.cash_account.field.total_saldo.label'),
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

            CRUD::field([   // Checklist
                'label'     => trans('backpack::crud.cash_account.field.additional_information.label'),
                'type'      => 'checklist-custom',
                'name'      => 'informations',
                'entity'    => 'informations',
                'attribute' => 'name',
                // 'model'     => "app\Models\CastAccount",
                'pivot'     => true,
                'wrapper'   => [
                    'class' => 'form-group col-md-6',
                ],
                // 'show_select_all' => true, // default false
                // 'number_of_columns' => 3,
            ]);
        }
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
        $request = request();

        if($request->has('type')){
            if($request->type == 'transaction'){
                CRUD::setModel(AccountTransaction::class);
            }
        }

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

    public function store()
    {
        $this->crud->hasAccessOrFail('create');

        $request = $this->crud->validateRequest();

        $this->crud->registerFieldEvents();

        DB::beginTransaction();
        try{

            $item = $this->crud->create($this->crud->getStrippedSaveRequest($request));

            $this->data['entry'] = $this->crud->entry = $item;

            $acctTransaction = new AccountTransaction;
            $acctTransaction->cast_account_id = $item->id;
            $acctTransaction->date_transaction = Carbon::now()->format('Y-m-d');
            $acctTransaction->nominal_transaction = $item->total_saldo ?? 0;
            $acctTransaction->total_saldo_before = 0;
            $acctTransaction->total_saldo_after = $item->total_saldo ?? 0;
            $acctTransaction->status = CastAccount::ENTER;
            $acctTransaction->is_first = 1;
            $acctTransaction->save();

            CustomHelper::updateOrCreateJournalEntry([
                'account_id' => $item->account_id,
                'reference_id' => $item->id,
                'reference_type' => CastAccount::class,
                'description' => 'Start Saldo',
                'date' => Carbon::now(),
                'debit' => $item->total_saldo,
            ], [
                'reference_id' => $item->id,
                'reference_type' => CastAccount::class,
            ]);

            // \Alert::success(trans('backpack::crud.insert_success'))->flash();

            $this->crud->setSaveAction();

            DB::commit();
            // return $this->crud->performSaveAction($item->getKey());

            return response()->json([
                'success' => true,
                'data' => $item,
                'events' => [
                    'cast_account_store_success' => $item,
                ]
            ]);

        }catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function updateTransaction(){
        $request = request();

        CRUD::setModel(AccountTransaction::class);

        $old_item = AccountTransaction::find($request->id);

        $has_access_primary = $this->accessAccount($old_item->cast_account_id);
        $status_account = AccountTransaction::OUT;
        if(request()->has('type')){
            // edit
            if($has_access_primary > 0){
                $request->validate($this->ruleValidationStoreTransactionAccountPrimary());
            }else{
                $has_payment_access = $this->accessPaymentAccount($request->cast_account_id);
                $request->validate($this->ruleValidationEditTransactionAccountSecondary());
                if($has_payment_access > 0){
                    $status_account = AccountTransaction::ENTER;
                }
            }
        }

        $this->crud->registerFieldEvents();

        DB::beginTransaction();

        try {

            $event = [];

            $journal = JournalEntry::whereHasMorph('reference', AccountTransaction::class, function($q) use($request){
                $q->where('id', $request->id);
            })->first();

            $item = AccountTransaction::find($request->id);
            $item->date_transaction = $request->date_transaction;
            $item->description = $request->description;
            $item->account_id = $request->account_id;
            $item->no_invoice = $request->no_invoice;
            $item->nominal_transaction = $request->nominal_transaction;

            $item->save();

            $journal->description = $item->description;
            $journal->account_id = $item->account_id;
            $journal->date = $item->date_transaction;
            if($status_account == AccountTransaction::OUT){
                $journal->credit = $item->nominal_transaction;
                $journal->debit = 0;
            }else{
                $journal->debit = $item->nominal_transaction;
                $journal->credit = 0;
            }
            $journal->save();


            $this->data['entry'] = $item;
            $event['cast_account_store_success'] = true;

            \Alert::success(trans('backpack::crud.update_success'))->flash();

            $this->crud->setSaveAction();

            DB::commit();
            return response()->json([
                'success' => true,
                'data' => $item,
                'events' => $event
            ]);

        }catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }

    }

    public function update()
    {
        $this->crud->hasAccessOrFail('update');

        if(request()->has('type')){
            if(request()->type == 'transaction'){
                return $this->updateTransaction();
            }
        }

        CRUD::setValidation([
            'name' => 'required|max:100|unique:cast_accounts,name,'.request('id'),
        ]);

        $request = $this->crud->validateRequest();

        $this->crud->registerFieldEvents();

        DB::beginTransaction();
        try{

            $event = [];

            if($request->type == 'cast_account'){
                $item = CastAccount::find($request->id);
                $item->name = $request->name;
                $item->save();
                $event['cast_account_store_success'] = true;
            }

            // $type = $request->_type;
            // if($type == 'category_project'){
            //     $event['setup_category_project_create_success'] = true;
            //     $item = CategoryProject::find($request->id);
            //     $item->name = $request->name;
            //     $item->save();
            // }else if($type == 'status_project'){
            //     $event['setup_status_project_create_success'] = true;
            //     $item = SetupStatusProject::find($request->id);
            //     $item->name = $request->name;
            //     $item->save();
            // }else if($type == 'status_offering'){
            //     $event['setup_status_offering_create_success'] = true;
            //     $item = SetupOffering::find($request->id);
            //     $item->name = $request->name;
            //     $item->save();
            // }else if($type == 'client'){
            //     $event['setup_client_create_success'] = true;
            //     $item = SetupClient::find($request->id);
            //     $item->name = $request->name;
            //     $item->save();
            // }else if($type == 'ppn'){
            //     $event['setup_ppn_create_success'] = true;
            //     $item = SetupPpn::find($request->id);
            //     $item->name = $request->name;
            //     $item->save();
            // }

            $this->data['entry'] = $item;

            \Alert::success(trans('backpack::crud.update_success'))->flash();

            $this->crud->setSaveAction();

            DB::commit();
            return response()->json([
                'success' => true,
                'data' => $item,
                'events' => $event
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

    public function storeTransaction()
    {
        $this->crud->hasAccessOrFail('create');
        $request = request();
        $request->validate($this->ruleValidationTransaction());

        $has_access_primary = $this->accessAccount($request->cast_account_id);

        if($has_access_primary > 0){
            $status_account = AccountTransaction::ENTER;
        }else{
            $has_payment_access = $this->accessPaymentAccount($request->cast_account_id);
            if($has_payment_access > 0){
                $status_account = AccountTransaction::ENTER;
            }else{
                $status_account = AccountTransaction::OUT;
            }
        }

        $this->crud->registerFieldEvents();

        DB::beginTransaction();
        try{
            $cast_account_id = $request->cast_account_id;
            $date_transaction = $request->date_transaction;
            $nominal_transaction = $request->nominal_transaction;
            $description = $request->description;
            $kdp = $request->kdp;
            $job_name = $request->job_name;
            $no_invoice = $request->no_invoice;
            $status = $status_account;

            $cast_account = CastAccount::where('id', $cast_account_id)->first();
            $before_saldo = $cast_account->total_saldo;

            if($status == AccountTransaction::ENTER){
                $new_saldo = $before_saldo + $nominal_transaction;
            }else{
                $new_saldo = $before_saldo - $nominal_transaction;
            }

            $invoice = null;

            if($request->has('kdp') || $request->has('no_invoice')){
                $id = $request->kdp ?? $request->no_invoice;
                $invoice = InvoiceClient::find($id);
                $kdp = $invoice->kdp;
                $no_invoice = $invoice->no_invoice;
                $invoice->status = 'Paid';
                $invoice->save();
            }

            $newTransaction = new AccountTransaction;
            $newTransaction->cast_account_id = $cast_account_id;
            $newTransaction->date_transaction = $date_transaction;
            $newTransaction->no_invoice = $no_invoice;
            $newTransaction->nominal_transaction = $nominal_transaction;
            $newTransaction->total_saldo_before = $before_saldo;
            $newTransaction->total_saldo_after = $new_saldo;
            $newTransaction->status = $status;
            $newTransaction->description = $description;
            $newTransaction->kdp = $kdp;
            $newTransaction->job_name = $job_name;

            if($kdp != null && $kdp != ''){
                $newTransaction->reference_type = InvoiceClient::class;
                $newTransaction->reference_id = $invoice->id;
            }

            if($request->has('account_id')){
                $newTransaction->account_id = $request->account_id;
                $newTransaction->save();

                // catat di journal
                CustomHelper::invoicePaymentTransaction($newTransaction, $invoice);
                CustomHelper::updateOrCreateJournalEntry([
                    'account_id' => $newTransaction->account_id,
                    'reference_id' => $newTransaction->id,
                    'reference_type' => AccountTransaction::class,
                    'description' => $description,
                    'date' => Carbon::now(),
                    'debit' => ($status == AccountTransaction::ENTER) ? $nominal_transaction : 0,
                    'credit' => ($status == AccountTransaction::OUT) ? $nominal_transaction : 0,
                ], [
                    'reference_id' => $newTransaction->id,
                    'reference_type' => AccountTransaction::class,
                ]);
            }else{
                $newTransaction->save();
            }


            $updateAccount = CastAccount::where('id', $cast_account_id)->first();
            $updateAccount->total_saldo = $new_saldo;
            $updateAccount->save();

            $item = $newTransaction;
            $item->new_saldo = 'Rp'.CustomHelper::formatRupiah($item->total_saldo_after);

            $this->data['entry'] = $this->crud->entry = $item;

            // \Alert::success(trans('backpack::crud.insert_success'))->flash();

            $this->crud->setSaveAction();

            DB::commit();
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $item,
                    'events' => [
                        'card_cast_account'.$cast_account_id.'_create_success' => $item,
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

    public function storeMoveTransfer()
    {
        $this->crud->hasAccessOrFail('create');

        $castAccount = CastAccount::where('id', request()->cast_account_id)->first();
        $balance = CustomHelper::total_balance_cast_account(request()->cast_account_id, CastAccount::CASH);

        CRUD::setValidation([
            'cast_account_id' => ['required', 'exists:cast_accounts,id'],
            'to_account' => [
                'required', 'exists:cast_accounts,id',
                function($attr, $value, $fail) use($castAccount){
                    if($value == $castAccount->id){
                        $fail(trans('backpack::crud.cash_account.field_transfer.errors.to_account_is_same'));
                    }
                }
            ],
            'nominal_transfer' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) use ($balance) {
                    if ($value > $balance) {
                        $fail(trans("backpack::crud.cash_account.field_transfer.errors.nominal_transfer_to_more"));
                    }
                }
            ],
        ]);

        $request = $this->crud->validateRequest();
        $this->crud->registerFieldEvents();
        DB::beginTransaction();

        try{
            $old_saldo = $balance;
            $new_saldo = $balance - $request->nominal_transfer;
            $description = $request->description;

            $newTransaction = new AccountTransaction;
            $newTransaction->cast_account_id = $request->cast_account_id;
            $newTransaction->cast_account_destination_id = $request->to_account;
            $newTransaction->date_transaction = Carbon::now();
            $newTransaction->nominal_transaction = $request->nominal_transfer;
            $newTransaction->total_saldo_before = $old_saldo;
            $newTransaction->total_saldo_after = $new_saldo;
            $newTransaction->status = 'out';
            $newTransaction->description = $description;
            $newTransaction->save();

            $castAccount->total_saldo = $new_saldo;
            $castAccount->save();
            $castAccount->new_saldo = 'Rp'.CustomHelper::formatRupiah($new_saldo);

            // other account
            $otherAccount = CastAccount::where('id', $request->to_account)->first();
            $other_old_saldo = $otherAccount->total_saldo;
            $other_new_saldo = $other_old_saldo + $newTransaction->nominal_transaction;

            $newTransaction_2 = new AccountTransaction;
            $newTransaction_2->cast_account_id = $otherAccount->id;
            $newTransaction_2->cast_account_destination_id = $newTransaction->cast_account_id;
            $newTransaction_2->date_transaction = Carbon::now();
            $newTransaction_2->nominal_transaction = $request->nominal_transfer;
            $newTransaction_2->total_saldo_before = $other_old_saldo;
            $newTransaction_2->total_saldo_after = $other_new_saldo;
            $newTransaction_2->status = 'enter';
            $newTransaction_2->description = $description;
            $newTransaction_2->save();

            $otherAccount->total_saldo = $other_new_saldo;
            $otherAccount->save();
            $otherAccount->new_saldo = 'Rp'.CustomHelper::formatRupiah($other_new_saldo);


            $item = $newTransaction_2;
            $this->data['entry'] = $this->crud->entry = $item;

            \Alert::success(trans('backpack::crud.insert_success'))->flash();

            $this->crud->setSaveAction();

            DB::commit();
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'events' => [
                        'cast_account_store_success' => true,
                        'card_cast_account'.$newTransaction->cast_account_id.'_create_success' => $castAccount,
                        'card_cast_account'.$newTransaction_2->cast_account_id.'_create_success' => $otherAccount,
                    ]
                ]);
            }
            return $this->crud->performSaveAction($newTransaction_2->getKey());

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

        $id = $this->crud->getCurrentEntryId() ?? $id;

        // delete journal entry
        JournalEntry::whereHasMorph('reference', AccountTransaction::class, function($q) use($id){
            $q->where('cast_account_id', $id)
            ->orWhere('cast_account_destination_id', $id);
        })->orWhereHasMorph('reference', CastAccount::class, function($q) use($id){
            $q->where('id', $id);
        })
        ->delete();

        return $this->crud->delete($id);
    }

    public function destroyTransaction($id){
        $this->crud->hasAccessOrFail('delete');
        DB::beginTransaction();
        try {
            $event = [];
            $request = request();

            $at = AccountTransaction::find($request->id);
            JournalEntry::whereHasMorph('reference', AccountTransaction::class, function($q) use($id){
                $q->where('id', $id);
            })->delete();

            $at->delete();

            $event['cast_account_store_success'] = true;

            $messages['success'][] = trans('backpack::crud.delete_confirmation_message');
            $messages['events'] = $event;

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

    public function showTransaction(){
        $id = request()->_id;
        $castAccount = CastAccount::where('id', $id)->first();
        $detail = AccountTransaction::where('cast_account_id', $id)
        ->where('is_first', 0)
        ->orderBy('date_transaction', 'ASC')->get();
        $has_access_primary = $this->accessAccount($id);

        foreach($detail as $entry){
            $entry->date_transaction_str = Carbon::parse($entry->date_transaction)->translatedFormat('j M Y');
            $entry->nominal_transaction_str = CustomHelper::formatRupiahWithCurrency($entry->nominal_transaction);
            $entry->description_str = $entry->description ?? '-';
            $entry->kdp_str = $entry->kdp ?? '-';
            $entry->job_name_str = $entry->job_name ?? '-';
            $entry->account_id_str = ($entry->account_id) ? $entry->account->code.' - '.$entry->account->name : '-';
            $entry->no_invoice_str = ($entry->no_invoice) ? $entry->no_invoice : '-';
            $entry->status_str = ucfirst(strtolower(trans('backpack::crud.cash_account.field_transaction.status.'.$entry->status)));
            $entry->url_edit = url($this->crud->route.'/'.$entry->id.'/edit?type=transaction&_id='.$entry->cast_account_id);
            $entry->url_update = url($this->crud->route).'/'.$entry->id.'?type=transaction&_id='.$entry->cast_account_id;
            $entry->url_delete = url($this->crud->route."/delete-transaction/".$entry->id);
            $entry->is_primary = $has_access_primary;
            $entry->is_transfer = $entry->cast_account_destination_id;
        }
        $castAccount->total_saldo_str = CustomHelper::formatRupiahWithCurrency($castAccount->total_saldo);
        return response()->json([
            'status' => true,
            'result'=> [
                'cast_account' => $castAccount,
                'balance' => CustomHelper::formatRupiahWithCurrency(CustomHelper::total_balance_cast_account($id, CastAccount::CASH)),
                'detail' => $detail,
            ]
        ]);
    }

    public function getSelectToAccount(){
        $castAccounts = CastAccount::whereHas('informations', function($q){
            $q->where("additional_informations.id", 2);
        })->where('status', CastAccount::CASH)->get(['id', 'name']);
        return response()->json([
            'status' => true,
            'result' => $castAccounts,
        ]);
    }

}
