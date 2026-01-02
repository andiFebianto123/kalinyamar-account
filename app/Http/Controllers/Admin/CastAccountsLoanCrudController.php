<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Account;
use App\Models\Setting;
use App\Models\CastAccount;
use Illuminate\Support\Str;
use App\Models\JournalEntry;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Exports\ExportExcel;
use App\Http\Helpers\CustomHelper;
use App\Models\AccountTransaction;
use Illuminate\Support\Facades\DB;
use App\Models\LoanTransactionFlag;
use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\CrudController;
use App\Http\Controllers\Operation\PermissionAccess;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class CastAccountsCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CastAccountsLoanCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use PermissionAccess;
    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(CastAccount::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/cash-flow/cast-account-loan');
        CRUD::setEntityNameStrings(trans('backpack::crud.cash_account_loan.title_header'), trans('backpack::crud.cash_account_loan.title_header'));

        $allAccess = [
            'AKSES SEMUA MENU ACCOUNTING',
        ];

        $viewMenu = [
            'MENU INDEX ARUS REKENING PINJAMAN',
        ];

        $this->settingPermission([
            'create' => [
                'CREATE INDEX ARUS REKENING PINJAMAN',
                ...$allAccess
            ],
            'update' => [
                'UPDATE INDEX ARUS REKENING PINJAMAN',
                ...$allAccess
            ],
            'delete' => [
                'DELETE INDEX ARUS REKENING PINJAMAN',
                ...$allAccess
            ],
            'list' => $viewMenu,
            'show' => $viewMenu,
            'print' => true,
        ]);
    }

    public function listCardComponents($list = null)
    {
        if ($list && ($list->count() > 0)) {

            foreach ($list as $l) {
                $journal_ = JournalEntry::whereHasMorph('reference', AccountTransaction::class, function ($q) use ($l) {
                    $q->where('cast_account_id', $l->id);
                })->orWhereHasMorph('reference', CastAccount::class, function ($q) use ($l) {
                    $q->where('id', $l->id);
                })
                    ->select(DB::raw('SUM(debit) - SUM(credit) as total'))
                    ->first();

                $l->saldo = ($journal_) ? $journal_->total : 0;
                $l->account = Account::find($l->account_id);

                $this->card->addCard([
                    'name' => 'card_cast_account' . $l->id,
                    'line' => 'top',
                    'view' => 'crud::components.cast_account_card',
                    'params' => [
                        'access' => $l->informations,
                        'detail' => $l,
                        'crud' => $this->crud,
                        'name' => 'card_cast_account' . $l->id,
                        'route_edit' => url($this->crud->route . "/" . $l->id . "/edit?type=cast_account"),
                        'route_update' => url($this->crud->route . "/" . $l->id . "?type=cast_account"),
                    ]
                ]);
            }
        } else {
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
            ->where('cast_accounts.status', CastAccount::LOAN)
            ->groupBy('cast_accounts.id')
            ->orderBy('id', 'ASC')->select(DB::raw(
                '
            cast_accounts.id,
            MAX(cast_accounts.name) as name,
            MAX(cast_accounts.bank_name) as bank_name,
            MAX(cast_accounts.no_account) as no_account,
            MAX(cast_accounts.status) as status,
            MAX(cast_accounts.account_id) as account_id,
            SUM(IF(account_transactions.status = "enter", account_transactions.nominal_transaction, 0)) as total_saldo_enter,
            SUM(IF(account_transactions.status = "out", account_transactions.nominal_transaction, 0)) as total_saldo_out
        '
            ))->get();
        $this->listCardComponents($listCashAccounts);

        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? mb_ucfirst($this->crud->entity_name_plural);
        $this->data['title_modal_create'] = trans('backpack::crud.cash_account_loan.title_modal_create');
        $this->data['title_modal_edit'] = trans('backpack::crud.cash_account_loan.title_modal_edit');
        $this->data['title_modal_delete'] = trans('backpack::crud.cash_account_loan.title_modal_delete');

        $breadcrumbs = [
            trans('backpack::crud.menu.cash_flow') => backpack_url('cash-flow'),
            trans('backpack::crud.menu.cash_flow_loan') => backpack_url($this->crud->route)
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
        $this->crud->file_title_export_pdf = "Laporan_daftar_rekening_pinjaman.pdf";
        $this->crud->file_title_export_excel = "Laporan_daftar_rekening_pinjaman.xlsx";
        $this->crud->param_uri_export = "?export=1";

        CRUD::addButtonFromView('top', 'export-excel-table', 'export-excel-table', 'beginning');
        CRUD::addButtonFromView('top', 'export-pdf-table', 'export-pdf-table', 'beginning');
        CRUD::addButtonFromView('top', 'filter_cash_account_order', 'filter-cash-account-order', 'beginning');
    }

    private function setupListExport()
    {
        $settings = Setting::first();
        $this->crud->query = $this->crud->query->leftJoin('account_transactions', 'account_transactions.cast_account_id', '=', 'cast_accounts.id')
            ->where('cast_accounts.status', CastAccount::LOAN)
            ->groupBy('cast_accounts.id')
            ->orderBy('id', 'ASC')->select(DB::raw('
            cast_accounts.id,
            MAX(cast_accounts.name) as name,
            MAX(cast_accounts.bank_name) as bank_name,
            MAX(cast_accounts.no_account) as no_account,
            MAX(cast_accounts.status) as status,
            MAX(cast_accounts.account_id) as account_id,
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

    private function setuplistExportTrans()
    {
        $id = request()->id;
        CRUD::setModel(AccountTransaction::class);

        $castAccount = CastAccount::where('id', $id)->first();

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

        CRUD::column(
            [
                'label'  => trans('backpack::crud.cash_account.field_transaction.date_transaction.label'),
                'name' => 'date_transaction',
                'type'  => 'export'
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.cash_account.field_transaction.nominal_transaction.label'),
                'name' => 'nominal_transaction',
                'type'  => 'export'
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.cash_account_loan.field.cast_account_destination_id.label'),
                'name' => 'cast_account_destination_id',
                'type'  => 'closure',
                'function' => function ($entry) {
                    return ($entry->cast_account_destination_id) ? $entry->cast_account_destination->name : ($entry->description ?? '-');
                }
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.cash_account.field_transaction.nominal_transaction.label'),
                'name' => 'status',
                'type'  => 'closure',
                'function' => function ($entry) {
                    return ucfirst(strtolower(trans('backpack::crud.cash_account.field_transaction.status.' . $entry->status)));
                }
            ],
        );

        return $castAccount;
    }

    public function exportPdf()
    {

        $this->setupListExport();

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

        $title = "DAFTAR REKENING PINJAMAN";

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

        $this->setupListExport();

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

    public function exportTransPdf()
    {
        $cast = $this->setuplistExportTrans();

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
                $row_items[] = trim($item_value);
            }
            $all_items[] = $row_items;
        }

        $title = "DAFTAR TRANSAKSI REKENING PINJAMAN " . $cast?->name;

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

    public function exportTransExcel()
    {
        $this->setuplistExportTrans();

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

    function ruleValidation()
    {
        $id = request('id') ?? '';
        // 'name' => 'required|min:5|max:255'
        return [
            'name' => 'required|max:100|unique:cast_accounts,name,' . $id,
            'bank_name' => 'required|max:50',
            'no_account' => 'required|max:100|unique:cast_accounts,no_account,' . $id,
            'account_id' => 'required|exists:accounts,id',
            'total_saldo' => 'required|numeric|min:0',
            'status' => 'required|in:loan',
        ];
    }


    function ruleValidationTransaction()
    {
        $cast_account_id = request()->cast_account_id;
        $status = request()->status;
        $cast_account_destination_id = request()->cast_account_destination_id;
        $rule = [
            'date_transaction' => 'required',
            'nominal_transaction' => [
                'required',
                'numeric',
                'min:1000',
                function ($attribute, $value, $fail) use ($cast_account_id, $status, $cast_account_destination_id) {
                    if ($status == CastAccount::ENTER) {
                        $balance = CustomHelper::total_balance_cast_account($cast_account_destination_id, CastAccount::CASH);
                        if ($value > $balance) {
                            $fail(trans("backpack::crud.cash_account.field_transfer.errors.nominal_transfer_to_more"));
                        }
                    } else if ($status == CastAccount::OUT) {
                        $balance = CustomHelper::total_balance_cast_account($cast_account_id, CastAccount::LOAN);
                        if ($value > $balance) {
                            $fail(trans("backpack::crud.cash_account.field_transfer.errors.nominal_transfer_to_more"));
                        }
                    }
                }
            ],
            'cast_account_destination_id' => 'required|exists:cast_accounts,id',
            'kdp' => 'max:50',
            'job_name' => 'max:100',
            'no_invoice' => 'max:100',
            'account_id' => 'exists:accounts,id',
            'status' => [
                'nullable',
                'in:enter,out',
                function ($attr, $value, $fail) use ($cast_account_destination_id) {
                    if ($cast_account_destination_id == AccountTransaction::BANK_LOAN) {
                        if ($value != CastAccount::ENTER) {
                            $fail(trans('backpack::crud.cash_account_loan.field.cast_account_destination_id.bank_loan_alert'));
                        }
                    }
                }
            ],
        ];

        if ($cast_account_destination_id == AccountTransaction::BANK_LOAN) {
            $rule['cast_account_destination_id'] = 'required|in:' . AccountTransaction::BANK_LOAN;
            $rule['nominal_transaction'] = 'required|numeric|min:1000';
        }
        return $rule;
    }


    function ruleValidationMoveTransfer()
    {
        $req = request();
        $loan_transaction_flag_id = $req->loan_transaction_flag_id;
        $cast_account_destination_id = $req->cast_account_destination_id;
        return [
            'loan_transaction_flag_id' => 'required|exists:loan_transaction_flags,id',
            'date_loan_transaction' => 'required|date',
            'cast_account_destination_id' => [
                'required',
                'exists:cast_accounts,id',
            ],
            'payment_price' => [
                'required',
                'numeric',
                'min:1000',
                function ($attribute, $value, $fail) use ($loan_transaction_flag_id, $cast_account_destination_id) {
                    $cast_account_destination = CastAccount::find($cast_account_destination_id);
                    $total_balance_destination = CustomHelper::balanceAccount($cast_account_destination->account->code);

                    if ($total_balance_destination < $value) {
                        $fail(trans("backpack::crud.cash_account.field_transfer.errors.nominal_transfer_to_more_destination"));
                    }

                    $total_loan_transaction = AccountTransaction::where("reference_id", $loan_transaction_flag_id)
                        ->whereNull('cast_account_destination_id')
                        ->where("reference_type", LoanTransactionFlag::class)
                        ->where('status', CastAccount::OUT)
                        ->sum('nominal_transaction');

                    $loan_transaction_flag = LoanTransactionFlag::find($loan_transaction_flag_id);

                    $remaining_balance = $loan_transaction_flag->total_price - $total_loan_transaction - $value;
                    if ($remaining_balance < 0) {
                        $fail(trans("backpack::crud.cash_account.field_transfer.errors.nominal_payment_to_more"));
                    }
                }
            ],
        ];
    }


    function account_select2()
    {
        $this->crud->hasAccessOrFail('create');

        $search = request()->input('q');
        $dataset = \App\Models\Account::select(['id', 'code', 'name'])
            ->where('name', 'LIKE', "%$search%")
            ->orWhere('code', 'LIKE', "%$search%")
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

    function loan_transaction_flag_select2()
    {
        $this->crud->hasAccessOrFail('create');;

        $search = request()->input('q');
        $cast_account_id = request()->input('castaccount');

        $dataset = LoanTransactionFlag::select(['id', 'kode', 'total_price'])
            ->whereExists(function ($q) use ($cast_account_id) {
                $q->selectRaw(1)
                    ->from('account_transactions as at2')
                    ->whereColumn('at2.reference_id', 'loan_transaction_flags.id')
                    ->where('at2.reference_type', LoanTransactionFlag::class)
                    ->where('at2.cast_account_id', $cast_account_id);
            })
            ->where('kode', 'LIKE', "%$search%")
            ->orderBy('id', 'DESC')
            ->paginate(10);

        $results = [];
        foreach ($dataset as $item) {
            $results[] = [
                'id' => $item->id,
                'text' => $item->kode,
            ];
        }
        return response()->json(['results' => $results]);
    }

    function get_loan_balance_ajax()
    {
        $search = request()->input('loan_transaction_flag_id');
        if ($search == null) {
            return response()->json([
                'status' => false,
                'message' => 'Loan transaction flag not found',
            ]);
        }
        $loan_transaction_flag = LoanTransactionFlag::find($search);
        $total_balance_out = AccountTransaction::where("reference_id", $search)
            ->whereNull('cast_account_destination_id')
            ->where("reference_type", LoanTransactionFlag::class)
            ->where('status', CastAccount::OUT)
            ->sum('nominal_transaction');
        $remaining_balance = $loan_transaction_flag->total_price - $total_balance_out;
        return response()->json(['remaining_balance' => CustomHelper::formatRupiahWithCurrency($remaining_balance)]);
    }

    function createTransactionOperation($id = null)
    {

        CRUD::setModel(AccountTransaction::class);
        CRUD::setValidation($this->ruleValidationTransaction());
        $settings = Setting::first();

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
            'name' => 'space_1',
            'type' => 'hidden',
            'value' => 'space_1',
            'wrapper' => [
                'class' => 'form-group col-md-6'
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
            ]
        ]);

        CRUD::addField([
            'name' => 'space_2',
            'type' => 'hidden',
            'value' => 'space_2',
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ]
        ]);

        CRUD::field([  // Select2
            'label'     => trans('backpack::crud.cash_account_loan.field.cast_account_destination_id.label'),
            'type'      => 'select2_array',
            'name'      => 'cast_account_destination_id',
            'options'   => array_replace([
                '' => trans('backpack::crud.cash_account_loan.field.cast_account_destination_id.placeholder'),
            ], CastAccount::where("status", CastAccount::CASH)->pluck('name', 'id')->all()),
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ]
        ]);

        CRUD::addField([
            'name' => 'space_3',
            'type' => 'hidden',
            'value' => 'space_3',
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ]
        ]);

        CRUD::addField([
            'name' => 'description',
            'label' => trans('backpack::crud.cash_account.field_transaction.description.label'),
            'type' => 'textarea',
            'attributes' => [
                'placeholder' => trans('backpack::crud.cash_account.field_transaction.description.placeholder'),
            ],
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ]
        ]);

        CRUD::addField([
            'name' => 'space_4',
            'type' => 'hidden',
            'value' => 'space_4',
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ]
        ]);
    }

    function createMoveTransactionOperation($id = null)
    {
        CRUD::setModel(AccountTransaction::class);
        CRUD::setValidation($this->ruleValidationMoveTransfer());
        $settings = Setting::first();

        CRUD::addField([
            'name' => 'cast_account_id ',
            'type' => 'hidden',
            'value' => $id,
        ]);

        CRUD::addField([
            'name' => 'balance_information',
            'type' => 'balance-information',
            'wrapper' => [
                'class' => 'form-group col-md-12'
            ],
            'value' => 0,
        ]);

        CRUD::addField([
            'label' => trans('backpack::crud.cash_account_loan.field.balance_information.loan_transaction_flag_id'),
            'type'        => "select2_ajax_custom",
            'name'        => 'loan_transaction_flag_id',
            'entity'      => 'account',
            //'model'       => 'App\Models\LoanTransactionFlag',
            'attribute'   => "kode",
            'data_source' => backpack_url('cash-flow/cast-account-loan/loan-transaction-flag-select2?castaccount=' . $id),
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => []
        ]);

        CRUD::addField([
            'name' => 'space_0',
            'type' => 'hidden',
            'value' => 'space_0',
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ]
        ]);

        CRUD::addField([   // date_picker
            'name'  => 'date_loan_transaction',
            'type'  => 'date_picker',
            'label' => trans('backpack::crud.cash_account_loan.field.balance_information.date'),

            // optional:
            'date_picker_options' => [
                'language' => App::getLocale(),
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::addField([
            'name' => 'space_1',
            'type' => 'hidden',
            'value' => 'space_1',
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ]
        ]);

        CRUD::field([  // Select2
            'label'     => trans('backpack::crud.voucher.field.account_source_id.label'),
            'type'      => 'select2_array',
            'name'      => 'cast_account_destination_id',
            'options'   => array_replace([
                '' => trans('backpack::crud.voucher.field.account_source_id.placeholder'),
            ], CastAccount::where('status', CastAccount::CASH)->pluck('name', 'id')->all()),
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ]
        ]);

        CRUD::addField([
            'name' => 'space_2',
            'type' => 'hidden',
            'value' => 'space_2',
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ]
        ]);

        CRUD::addField([
            'name' => 'payment_price',
            'label' =>  trans('backpack::crud.cash_account_loan.field.balance_information.payment_price'),
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
            'name' => 'space_3',
            'type' => 'hidden',
            'value' => 'space_3',
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ]
        ]);

        CRUD::addField([
            'name' => 'description',
            'label' => trans('backpack::crud.cash_account.field_transaction.description.label'),
            'type' => 'textarea',
            'attributes' => [
                'placeholder' => trans('backpack::crud.cash_account.field_transaction.description.placeholder'),
            ],
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ]
        ]);

        CRUD::addField([
            'name' => 'logic_cast_loan',
            'type' => 'logic-cast-loan',
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ],
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
        // CRUD::setFromDb(); // set fields from db columns.
        $settings = Setting::first();

        $request = request();

        if ($request->has('_id') && $request?->type == null) {
            $this->createTransactionOperation($request->_id);
            return;
        }

        if ($request->has('type')) {
            if ($request->has('_id')) {
                if ($request->type == 'move') {
                    $this->createMoveTransactionOperation($request->_id);
                    return;
                }
            }
            if ($request->type == 'cast_account') {
                $id = $request->id ?? '';
                CRUD::setValidation([
                    'name' => 'required|max:100|unique:cast_accounts,name,' . $id,
                ]);
                CRUD::addField([
                    'name' => 'name',
                    'label' => trans('backpack::crud.cash_account.field.name.label'),
                    'type' => 'text',
                    'attributes' => [
                        'placeholder' => trans('backpack::crud.cash_account.field.name.placeholder'),
                    ]
                ]);
                return;
            }
        } else {
            CRUD::setValidation($this->ruleValidation());
            CRUD::addField([
                'name' => 'name',
                'label' => trans('backpack::crud.cash_account_loan.field.name.label'),
                'type' => 'text',
                'attributes' => [
                    'placeholder' => trans('backpack::crud.cash_account_loan.field.name.placeholder'),
                ]
            ]);

            CRUD::field([  // Select2
                'label'     => trans('backpack::crud.cash_account_loan.field.bank_name.label'),
                'type'      => 'select2_array',
                'name'      => 'bank_name',
                'options'   => ['' => trans('backpack::crud.cash_account_loan.field.bank_name.placeholder'), ...CustomHelper::getBanks()], // force the related options to be a custom query, instead of all(); you can use this to filter the results show in the select
                'wrapper' => [
                    'class' => 'form-group col-md-6'
                ]
            ]);

            CRUD::addField([
                'name' => 'no_account',
                'label' => trans('backpack::crud.cash_account_loan.field.no_account.label'),
                'type' => 'text',
                'wrapper'   => [
                    'class' => 'form-group col-md-6',
                ],
                'attributes' => [
                    'placeholder' => trans('backpack::crud.cash_account_loan.field.no_account.placeholder'),
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
                'type' => 'hidden',
                'name' => 'space_1',
                'wrapper' => [
                    'class' => 'form-group col-md-6'
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
                'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : 'Rp.',
                'wrapper'   => [
                    'class' => 'form-group col-md-6',
                ],
                'attributes' => [
                    'placeholder' => '000.000',
                ]
            ]);

            CRUD::addField([
                'type' => 'hidden',
                'name' => 'status',
                'value' => CastAccount::LOAN,
                'wrapper' => [
                    'class' => 'form-group col-md-6'
                ]
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

        $this->crud->setOperationSetting('fields', $this->crud->getUpdateFields());

        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.edit') . ' ' . $this->crud->entity_name;
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
        try {
            $item = $this->crud->create($this->crud->getStrippedSaveRequest($request));

            $this->data['entry'] = $this->crud->entry = $item;

            // \Alert::success(trans('backpack::crud.insert_success'))->flash();

            CustomHelper::updateOrCreateJournalEntry([
                'account_id' => $item->account_id,
                'reference_id' => $item->id,
                'reference_type' => CastAccount::class,
                'description' => '',
                'date' => Carbon::now(),
                'debit' => $item->total_saldo,
                // 'credit' => ($status == CastAccount::OUT) ? $nominal_transaction : 0,
            ], [
                'reference_id' => $item->id,
                'reference_type' => CastAccount::class,
            ]);

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
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function generateCodeLoan()
    {
        do {
            $kode = 'LOAN-' . strtoupper(Str::random(8));
            $check = LoanTransactionFlag::where('kode', $kode)->first();
        } while ($check != null);
        return $kode;
    }

    public function storeTransaction()
    {
        $this->crud->hasAccessOrFail('create');
        CRUD::setValidation($this->ruleValidationTransaction());

        $request = $this->crud->validateRequest();

        $this->crud->registerFieldEvents();

        DB::beginTransaction();
        try {

            $cast_account_id = $request->cast_account_id;
            $date_transaction = $request->date_transaction;
            $nominal_transaction = $request->nominal_transaction;
            $cast_account_destination_id = $request->cast_account_destination_id;
            $status = "enter";
            $codeLoan = $this->generateCodeLoan();
            $description = $request->description;

            $cast_account_loan = CastAccount::find($cast_account_id);
            $cast_account_destination = CastAccount::find($cast_account_destination_id);

            if ($cast_account_destination_id == AccountTransaction::BANK_LOAN) {
                $total_saldo_loan_after = $total_saldo_loan_before + $nominal_transaction;
                $acctTransactionLoan = new AccountTransaction;
                $acctTransactionLoan->cast_account_id = $cast_account_id;
                $acctTransactionLoan->date_transaction = $date_transaction;
                $acctTransactionLoan->nominal_transaction = $nominal_transaction;
                $acctTransactionLoan->total_saldo_before = $total_saldo_loan_before;
                $acctTransactionLoan->total_saldo_after = $total_saldo_loan_after;
                $acctTransactionLoan->status = $status;
                $acctTransactionLoan->account_id = $cast_account_loan->account_id;
                $acctTransactionLoan->description = $request->description;
                $acctTransactionLoan->save();

                $cast_account_loan->total_saldo = $total_saldo_loan_after;
                $cast_account_loan->save();

                CustomHelper::updateOrCreateJournalEntry([
                    'account_id' => $acctTransactionLoan->account_id,
                    'reference_id' => $acctTransactionLoan->id,
                    'reference_type' => AccountTransaction::class,
                    'description' => $request->description,
                    'date' => Carbon::now(),
                    'debit' => $nominal_transaction,
                    'credit' => 0,
                ], [
                    'reference_id' => $acctTransactionLoan->id,
                    'reference_type' => AccountTransaction::class,
                ]);
            } else {
                if ($status == CastAccount::ENTER) {

                    // insert to loan transaction
                    $loan_transaction_flag = new LoanTransactionFlag;
                    $loan_transaction_flag->kode = $codeLoan;
                    $loan_transaction_flag->total_price = $nominal_transaction;
                    $loan_transaction_flag->save();

                    // insert to loan transaction
                    $loan_transaction = new AccountTransaction;
                    $loan_transaction->cast_account_id = $cast_account_id;
                    $loan_transaction->cast_account_destination_id = $cast_account_destination_id;
                    $loan_transaction->date_transaction = $date_transaction;
                    $loan_transaction->nominal_transaction = $nominal_transaction;
                    $loan_transaction->total_saldo_before = $nominal_transaction;
                    $loan_transaction->total_saldo_after = $nominal_transaction;
                    $loan_transaction->status = $status;
                    $loan_transaction->account_id = $cast_account_loan->account_id;
                    $loan_transaction->description = $description;
                    $loan_transaction->reference_type = LoanTransactionFlag::class;
                    $loan_transaction->reference_id = $loan_transaction_flag->id;
                    $loan_transaction->save();

                    // insert to transaction destination
                    $add_transaction_destination = new AccountTransaction;
                    $add_transaction_destination->cast_account_id = $cast_account_destination_id;
                    $add_transaction_destination->cast_account_destination_id = $cast_account_id;
                    $add_transaction_destination->date_transaction = $date_transaction;
                    $add_transaction_destination->nominal_transaction = $nominal_transaction;
                    $add_transaction_destination->total_saldo_before = 0;
                    $add_transaction_destination->total_saldo_after = 0;
                    $add_transaction_destination->status = $status;
                    $add_transaction_destination->account_id = $cast_account_destination->account_id;
                    $add_transaction_destination->description = $description;
                    $add_transaction_destination->save();

                    // insert journal entry transaction loan
                    CustomHelper::updateOrCreateJournalEntry([
                        'account_id' => $cast_account_loan->account_id,
                        'reference_id' => $loan_transaction->id,
                        'reference_type' => AccountTransaction::class,
                        'description' => $description,
                        'date' => Carbon::now(),
                        'debit' => ($status == CastAccount::ENTER) ? $nominal_transaction : 0,
                        'credit' => ($status == CastAccount::OUT) ? $nominal_transaction : 0,
                    ], [
                        'reference_id' => $loan_transaction->id,
                        'reference_type' => AccountTransaction::class,
                    ]);

                    // insert journal entry transaction destination
                    CustomHelper::updateOrCreateJournalEntry([
                        'account_id' => $cast_account_destination->account_id,
                        'reference_id' => $add_transaction_destination->id,
                        'reference_type' => AccountTransaction::class,
                        'description' => $description,
                        'date' => Carbon::now(),
                        'debit' => ($status == CastAccount::ENTER) ? $nominal_transaction : 0,
                        'credit' => ($status == CastAccount::OUT) ? $nominal_transaction : 0,
                    ], [
                        'reference_id' => $add_transaction_destination->id,
                        'reference_type' => AccountTransaction::class,
                    ]);
                }
            }

            $total_saldo = CustomHelper::balanceAccount($cast_account_loan->account->code);

            $item = $cast_account_loan;
            $item->new_saldo = CustomHelper::formatRupiahWithCurrency($total_saldo);

            $this->data['entry'] = $this->crud->entry = $item;

            \Alert::success(trans('backpack::crud.insert_success'))->flash();

            $this->crud->setSaveAction();

            DB::commit();
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $item,
                    'events' => [
                        'card_cast_account' . $cast_account_id . '_create_success' => $item,
                        'card_cast_account' . $cast_account_id . '_store_move_success' => $item
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

    public function update()
    {
        $this->crud->hasAccessOrFail('update');

        $request = $this->crud->validateRequest();

        $this->crud->registerFieldEvents();

        DB::beginTransaction();
        try {

            $event = [];

            if ($request->type == 'cast_account') {
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

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function storeMoveTransaction()
    {
        $this->crud->hasAccessOrFail('create');

        CRUD::setValidation($this->ruleValidationMoveTransfer());

        $request = $this->crud->validateRequest();
        $this->crud->registerFieldEvents();
        DB::beginTransaction();

        try {

            $cast_account_id = $request->cast_account_id;
            $payment_price = $request->payment_price;
            $loan_transaction_flag_id = $request->loan_transaction_flag_id;
            $description = $request->description;
            $date_loan_transaction = $request->date_loan_transaction;
            $cast_account_destination_id = $request->cast_account_destination_id;

            $account_destination = CastAccount::find($cast_account_destination_id)->account;
            $balance_destination_before = CustomHelper::balanceAccount($account_destination->code);
            $balance_destination_after = $balance_destination_before - $payment_price;

            $first_account_transaction = AccountTransaction::where("reference_id", $loan_transaction_flag_id)
                ->where("reference_type", LoanTransactionFlag::class)
                ->first();

            $total_loan_transaction = AccountTransaction::where("reference_id", $loan_transaction_flag_id)
                ->whereNull('cast_account_destination_id')
                ->where("reference_type", LoanTransactionFlag::class)
                ->where('status', CastAccount::OUT)
                ->select(
                    DB::raw("SUM(nominal_transaction) as total_loan_transaction"),
                )->first();

            $loan_transaction_flag = LoanTransactionFlag::find($loan_transaction_flag_id);

            $remaining_balance = $loan_transaction_flag->total_price - $total_loan_transaction->total_loan_transaction - $payment_price;

            $new_cast_transaction = new AccountTransaction;
            $new_cast_transaction->cast_account_id = $cast_account_destination_id;
            $new_cast_transaction->cast_account_destination_id = $cast_account_id;
            $new_cast_transaction->reference_type = LoanTransactionFlag::class;
            $new_cast_transaction->reference_id = $loan_transaction_flag_id;
            $new_cast_transaction->date_transaction = $date_loan_transaction;
            $new_cast_transaction->description = $description;
            $new_cast_transaction->account_id = $account_destination->id;
            $new_cast_transaction->nominal_transaction = $payment_price;
            $new_cast_transaction->total_saldo_before = $balance_destination_before;
            $new_cast_transaction->total_saldo_after = $balance_destination_after;
            $new_cast_transaction->status = CastAccount::OUT;
            $new_cast_transaction->save();

            CustomHelper::updateOrCreateJournalEntry([
                'account_id' => $account_destination->id,
                'reference_id' => $new_cast_transaction->id,
                'reference_type' => AccountTransaction::class,
                'description' => $description,
                'date' => $date_loan_transaction,
                'debit' => 0,
                'credit' => $payment_price,
            ], [
                'reference_id' => $new_cast_transaction->id,
                'reference_type' => AccountTransaction::class,
            ]);

            $new_loan_transaction = new AccountTransaction;
            $new_loan_transaction->cast_account_id = $cast_account_id;
            $new_loan_transaction->cast_account_destination_id = null;
            $new_loan_transaction->reference_type = LoanTransactionFlag::class;
            $new_loan_transaction->reference_id = $loan_transaction_flag_id;
            $new_loan_transaction->date_transaction = $date_loan_transaction;
            $new_loan_transaction->description = $description;
            $new_loan_transaction->account_id = $first_account_transaction->account_id;
            $new_loan_transaction->nominal_transaction = $payment_price;
            $new_loan_transaction->total_saldo_before = $total_loan_transaction->total_loan_transaction ?? 0;
            $new_loan_transaction->total_saldo_after = $remaining_balance;
            $new_loan_transaction->status = CastAccount::OUT;
            $new_loan_transaction->save();

            CustomHelper::updateOrCreateJournalEntry([
                'account_id' => $first_account_transaction->account_id,
                'reference_id' => $new_loan_transaction->id,
                'reference_type' => AccountTransaction::class,
                'description' => $description,
                'date' => $date_loan_transaction,
                'debit' => 0,
                'credit' => $payment_price,
            ], [
                'reference_id' => $new_loan_transaction->id,
                'reference_type' => AccountTransaction::class,
            ]);

            if ($remaining_balance == 0) {
                $loan_transaction_flag->status = 1;
                $loan_transaction_flag->save();
            }

            $item = $new_loan_transaction;
            $total_balance = CustomHelper::balanceAccount(Account::find($first_account_transaction->account_id)->code);
            $item->saldo = CustomHelper::formatRupiahWithCurrency($total_balance);

            $this->data['entry'] = $item;

            $this->crud->setSaveAction();

            DB::commit();
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'events' => [
                        'card_cast_account' . $cast_account_id . '_store_move_success' => $item,
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

    public function destroy($id)
    {
        $this->crud->hasAccessOrFail('delete');

        $id = $this->crud->getCurrentEntryId() ?? $id;

        $journal = JournalEntry::whereHasMorph('reference', AccountTransaction::class, function ($q) use ($id) {
            $q->where('cast_account_id', $id)
                ->orWhere('cast_account_destination_id', $id);
        })->orWhereHasMorph('reference', CastAccount::class, function ($q) use ($id) {
            $q->where('id', $id);
        })->orWhereHasMorph('reference', LoanTransactionFlag::class, function ($q) use ($id) {
            $q->where('id', $id);
        })->delete();

        $accountTransaction = AccountTransaction::where('cast_account_id', $id)
            ->orWhere('cast_account_destination_id', $id)
            ->delete();

        return $this->crud->delete($id);
    }

    public function showTransaction()
    {
        $id = request()->_id;
        $castAccount = CastAccount::where('id', $id)->first();
        $detail = AccountTransaction::select([
            'account_transactions.cast_account_id',
            'loan_transaction_flags.kode as kode',
            'account_transactions.date_transaction',
            'account_transactions.total_saldo_after as loan_remaining',
            'account_transactions.nominal_transaction as nominal',
            'account_transactions.description',
            'loan_transaction_flags.status',
        ])
            ->join("loan_transaction_flags", function ($join) {
                $join->on('loan_transaction_flags.id', '=', 'account_transactions.reference_id')
                    ->where('account_transactions.reference_type', LoanTransactionFlag::class);
            })
            ->where('account_transactions.cast_account_id', $id)
            ->orderByDesc('account_transactions.reference_id')
            ->orderBy('account_transactions.id')
            ->get();
        foreach ($detail as $row => $entry) {
            $row_before = ($row > 0) ? $row - 1 : $row;
            if ($row == 0) {
                $entry->status_str = ($entry->status == 1) ? 'Paid' : 'Unpaid';
                $entry->kode_str = $entry->kode;
                $entry->nominal_str = "-";
            } elseif ($detail[$row_before]->kode != $entry->kode) {
                $entry->status_str = ($entry->status == 1) ? 'Paid' : 'Unpaid';
                $entry->kode_str = $entry->kode;
                $entry->nominal_str = "-";
            } else {
                $entry->status_str = '-';
                $entry->kode_str = '-';
                $entry->nominal_str = CustomHelper::formatRupiahWithCurrency($entry->nominal);
            }
            $entry->loan_str = CustomHelper::formatRupiahWithCurrency($entry->loan_remaining);
            $entry->date_str = Carbon::parse($entry->date_transaction)->translatedFormat('j M Y');
        }
        $castAccount->total_saldo_str = 0;
        return response()->json([
            'status' => true,
            'result' => [
                'cast_account' => $castAccount,
                'detail' => $detail,
            ]
        ]);
    }

    public function getSelectToAccount()
    {
        $castAccounts = CastAccount::whereHas('informations', function ($q) {
            $q->where("additional_informations.id", 2)->select(DB::raw("1"));
        })->where('status', CastAccount::CASH)->get(['id', 'name']);
        return response()->json([
            'status' => true,
            'result' => $castAccounts,
        ]);
    }
}
