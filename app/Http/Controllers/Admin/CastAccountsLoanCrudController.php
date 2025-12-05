<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Account;
use App\Models\Setting;
use App\Models\CastAccount;
use App\Models\JournalEntry;
use PhpParser\Node\Expr\Cast;
use App\Imports\AccountImport;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Exports\ExportExcel;
use App\Http\Helpers\CustomHelper;
use App\Models\AccountTransaction;
use Illuminate\Support\Facades\DB;
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

    public function listCardComponents($list = null){
        if($list && ($list->count() > 0)){

            foreach($list as $l){
                $journal_ = JournalEntry::whereHasMorph('reference', AccountTransaction::class, function($q) use($l){
                    $q->where('cast_account_id', $l->id);
                })->orWhereHasMorph('reference', CastAccount::class, function($q) use($l){
                    $q->where('id', $l->id);
                })
                ->select(DB::raw('SUM(debit) - SUM(credit) as total'))
                ->first();

                $l->saldo = ($journal_) ? $journal_->total : 0;
                $l->account = Account::find($l->account_id);

                $this->card->addCard([
                    'name' => 'card_cast_account'.$l->id,
                    'line' => 'top',
                    'view' => 'crud::components.cast_account_card',
                    'params' => [
                        'access' => $l->informations,
                        'detail' => $l,
                        'crud' => $this->crud,
                        'name' => 'card_cast_account'.$l->id,
                        'route_edit' => url($this->crud->route."/".$l->id."/edit?type=cast_account"),
                        'route_update' => url($this->crud->route."/".$l->id."?type=cast_account"),
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
        ->where('cast_accounts.status', CastAccount::LOAN)
        ->groupBy('cast_accounts.id')
        ->orderBy('id', 'ASC')->select(DB::raw('
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

    private function setupListExport(){
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

    private function setuplistExportTrans(){
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
                'function' => function($entry) {
                    return ($entry->cast_account_destination_id) ? $entry->cast_account_destination->name : ($entry->description ?? '-');
                }
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.cash_account.field_transaction.nominal_transaction.label'),
                'name' => 'status',
                'type'  => 'closure',
                'function' => function($entry) {
                    return ucfirst(strtolower(trans('backpack::crud.cash_account.field_transaction.status.'.$entry->status)));
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
                $row_items[] = trim($item_value);
            }
            $all_items[] = $row_items;
        }

        $title = "DAFTAR TRANSAKSI REKENING PINJAMAN ".$cast?->name;

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
        // 'name' => 'required|min:5|max:255'
        return [
            'name' => 'required|max:100|unique:cast_accounts,name,'.$id,
            'bank_name' => 'required|max:50',
            'no_account' => 'required|max:100|unique:cast_accounts,no_account,'.$id,
            'account_id' => 'required|exists:accounts,id',
            'total_saldo' => 'required|numeric|min:0',
            'status' => 'required|in:loan',
        ];
    }


    function ruleValidationTransaction(){
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
                    if($status == CastAccount::ENTER)
                    {
                        $balance = CustomHelper::total_balance_cast_account($cast_account_destination_id, CastAccount::CASH);
                        if($value > $balance){
                            $fail(trans("backpack::crud.cash_account.field_transfer.errors.nominal_transfer_to_more"));
                        }
                    }else if($status == CastAccount::OUT){
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
                'required',
                'in:enter,out',
                function($attr, $value, $fail) use($cast_account_destination_id){
                    if($cast_account_destination_id == AccountTransaction::BANK_LOAN){
                        if($value != CastAccount::ENTER){
                            $fail(trans('backpack::crud.cash_account_loan.field.cast_account_destination_id.bank_loan_alert'));
                        }
                    }
                }
            ],
        ];

        if($cast_account_destination_id == AccountTransaction::BANK_LOAN){
            $rule['cast_account_destination_id'] = 'required|in:'.AccountTransaction::BANK_LOAN;
            $rule['nominal_transaction'] = 'required|numeric|min:1000';
        }
        return $rule;
    }


    function ruleValidationMoveTransfer(){
        $cast_account_id = request()->cast_account_id;
        $cast_account_destination_id = request()->cast_account_destination_id;
        return [
            'cast_account_id' => 'required|exists:cast_accounts,id',
            'cast_account_destination_id' => 'required|exists:cast_accounts,id',
            'nominal_move' => [
                'required',
                'numeric',
                'min:1000',
                function ($attribute, $value, $fail) use ($cast_account_id) {
                    $balance = CustomHelper::total_balance_cast_account($cast_account_id, CastAccount::LOAN);
                    if ($value > $balance) {
                        $fail(trans("backpack::crud.cash_account.field_transfer.errors.nominal_transfer_to_more"));
                    }
                }
            ],
        ];
    }


    function account_select2(){
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
                'text' => $item->code.' - '.$item->name,
            ];
        }
        return response()->json(['results' => $results]);
    }

    function createTransactionOperation($id = null){

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
                trans('backpack::crud.cash_account_loan.field.cast_account_destination_id.bank_loan') => trans('backpack::crud.cash_account_loan.field.cast_account_destination_id.bank_loan_placeholder'),
                ], CastAccount::whereHas('informations', function($q){
                            $q->where('additional_informations.id', 2);
                        })->pluck('name', 'id')->all()),
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
            'name'        => 'status',
            'label'       => trans('backpack::crud.cash_account.field_transaction.status.label'),
            'type'        => 'select_from_array',
            'options'     => [
                '' => trans('backpack::crud.cash_account.field_transaction.status.placeholder'),
                'enter' => trans('backpack::crud.cash_account.field_transaction.status.enter'),
                'out' => trans('backpack::crud.cash_account.field_transaction.status.out')
            ],
            'allows_null' => false,
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            // 'allows_multiple' => true, // OPTIONAL; needs you to cast this to array in your model;
        ]);
    }

    function createMoveTransactionOperation($id = null){
        CRUD::setModel(AccountTransaction::class);
        CRUD::setValidation($this->ruleValidationMoveTransfer());
        $settings = Setting::first();

        CRUD::addField([
            'name' => 'cast_account_id ',
            'type' => 'hidden',
            'value' => $id,
        ]);

        $balance = CustomHelper::formatRupiahWithCurrency(CustomHelper::total_balance_cast_account($id, CastAccount::LOAN));

        CRUD::addField([
            'name' => 'balance_information',
            'type' => 'balance-information',
            'value' => $balance,
            'wrapper' => [
                'class' => 'form-group col-md-12 mb-3'
            ]
        ]);

        CRUD::addField([
            'name' => 'nominal_move',
            'label' =>  trans('backpack::crud.cash_account_loan.field.balance_information.placeholder'),
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
            'label'     => trans('backpack::crud.cash_account.field_transfer.to_account.label'),
            'type'      => 'select2_array',
            'name'      => 'cast_account_destination_id',
            'options'   => array_replace([
                '' => trans('backpack::crud.cash_account.field_transfer.to_account.placeholder'),
                ], CastAccount::whereHas('informations', function($q){
                            $q->where('additional_informations.id', 2);
                        })->pluck('name', 'id')->all()),
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ]
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

        if($request->has('_id') && $request?->type == null){
            $this->createTransactionOperation($request->_id);
            return;
        }

        if($request->has('type')){
            if($request->has('_id')){
                if($request->type == 'move'){
                    $this->createMoveTransactionOperation($request->_id);
                    return;
                }
            }
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
                return;
            }
        }else{
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
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.add').' '.$this->crud->entity_name;

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
        CRUD::setValidation($this->ruleValidationTransaction());

        $request = $this->crud->validateRequest();

        $this->crud->registerFieldEvents();

        DB::beginTransaction();
        try{

            $cast_account_id = $request->cast_account_id;
            $date_transaction = $request->date_transaction;
            $nominal_transaction = $request->nominal_transaction;
            $cast_account_destination_id = $request->cast_account_destination_id;
            $status = $request->status;

            $cast_account_loan = CastAccount::find($cast_account_id);
            $total_saldo_loan_before = $cast_account_loan->total_saldo;

            if($cast_account_destination_id == AccountTransaction::BANK_LOAN){
                $total_saldo_loan_after = $total_saldo_loan_before + $nominal_transaction;
                $acctTransactionLoan = new AccountTransaction;
                $acctTransactionLoan->cast_account_id = $cast_account_id;
                $acctTransactionLoan->date_transaction = $date_transaction;
                $acctTransactionLoan->nominal_transaction = $nominal_transaction;
                $acctTransactionLoan->total_saldo_before = $total_saldo_loan_before;
                $acctTransactionLoan->total_saldo_after = $total_saldo_loan_after;
                $acctTransactionLoan->status = $status;
                $acctTransactionLoan->account_id = $cast_account_loan->account_id;
                $acctTransactionLoan->description = $cast_account_destination_id;
                $acctTransactionLoan->save();

                $cast_account_loan->total_saldo = $total_saldo_loan_after;
                $cast_account_loan->save();

                CustomHelper::updateOrCreateJournalEntry([
                    'account_id' => $acctTransactionLoan->account_id,
                    'reference_id' => $acctTransactionLoan->id,
                    'reference_type' => AccountTransaction::class,
                    'description' => '',
                    'date' => Carbon::now(),
                    'debit' => $nominal_transaction,
                    'credit' => 0,
                ], [
                    'reference_id' => $acctTransactionLoan->id,
                    'reference_type' => AccountTransaction::class,
                ]);

            }else{
                if($status == CastAccount::ENTER){
                    $total_saldo_loan_after = $total_saldo_loan_before + $nominal_transaction;
                    $acctTransactionLoan = new AccountTransaction;
                    $acctTransactionLoan->cast_account_id = $cast_account_id;
                    $acctTransactionLoan->cast_account_destination_id = $cast_account_destination_id;
                    $acctTransactionLoan->date_transaction = $date_transaction;
                    $acctTransactionLoan->nominal_transaction = $nominal_transaction;
                    $acctTransactionLoan->total_saldo_before = $total_saldo_loan_before;
                    $acctTransactionLoan->total_saldo_after = $total_saldo_loan_after;
                    $acctTransactionLoan->status = $status;
                    $acctTransactionLoan->account_id = $cast_account_loan->account_id;
                    $acctTransactionLoan->save();

                    $cast_account_loan->total_saldo = $total_saldo_loan_after;
                    $cast_account_loan->save();

                    $cast_account_destination = CastAccount::find($cast_account_destination_id);
                    $total_saldo_before = $cast_account_destination->total_saldo;
                    $total_saldo_after = $total_saldo_before - $nominal_transaction;

                    $acctTransaction = new AccountTransaction;
                    $acctTransaction->cast_account_id = $cast_account_destination_id;
                    $acctTransaction->cast_account_destination_id = $cast_account_id;
                    $acctTransaction->date_transaction = $date_transaction;
                    $acctTransaction->nominal_transaction = $nominal_transaction;
                    $acctTransaction->total_saldo_before = $total_saldo_before;
                    $acctTransaction->total_saldo_after = $total_saldo_after;
                    $acctTransaction->status = CastAccount::OUT;
                    $acctTransaction->save();

                    $cast_account_destination->total_saldo = $total_saldo_after;
                    $cast_account_destination->save();

                    CustomHelper::updateOrCreateJournalEntry([
                        'account_id' => $acctTransactionLoan->account_id,
                        'reference_id' => $acctTransactionLoan->id,
                        'reference_type' => AccountTransaction::class,
                        'description' => '',
                        'date' => Carbon::now(),
                        'debit' => ($status == CastAccount::ENTER) ? $nominal_transaction : 0,
                        'credit' => ($status == CastAccount::OUT) ? $nominal_transaction : 0,
                    ], [
                        'reference_id' => $acctTransactionLoan->id,
                        'reference_type' => AccountTransaction::class,
                    ]);
                }else if($status == CastAccount::OUT){
                    $total_saldo_loan_after = $total_saldo_loan_before - $nominal_transaction;
                    $acctTransactionLoan = new AccountTransaction;
                    $acctTransactionLoan->cast_account_id = $cast_account_id;
                    $acctTransactionLoan->cast_account_destination_id = $cast_account_destination_id;
                    $acctTransactionLoan->date_transaction = $date_transaction;
                    $acctTransactionLoan->nominal_transaction = $nominal_transaction;
                    $acctTransactionLoan->total_saldo_before = $total_saldo_loan_before;
                    $acctTransactionLoan->total_saldo_after = $total_saldo_loan_after;
                    $acctTransactionLoan->status = $status;
                    $acctTransactionLoan->account_id = $cast_account_loan->account_id;
                    $acctTransactionLoan->save();

                    $cast_account_loan->total_saldo = $total_saldo_loan_after;
                    $cast_account_loan->save();

                    $cast_account_destination = CastAccount::find($cast_account_destination_id);
                    $total_saldo_before = $cast_account_destination->total_saldo;
                    $total_saldo_after = $total_saldo_before + $nominal_transaction;

                    $acctTransaction = new AccountTransaction;
                    $acctTransaction->cast_account_id = $cast_account_destination_id;
                    $acctTransaction->cast_account_destination_id = $cast_account_id;
                    $acctTransaction->date_transaction = $date_transaction;
                    $acctTransaction->nominal_transaction = $nominal_transaction;
                    $acctTransaction->total_saldo_before = $total_saldo_before;
                    $acctTransaction->total_saldo_after = $total_saldo_after;
                    $acctTransaction->status = CastAccount::ENTER;
                    $acctTransaction->save();

                    $cast_account_destination->total_saldo = $total_saldo_after;
                    $cast_account_destination->save();

                    CustomHelper::updateOrCreateJournalEntry([
                        'account_id' => $acctTransactionLoan->account_id,
                        'reference_id' => $acctTransactionLoan->id,
                        'reference_type' => AccountTransaction::class,
                        'description' => '',
                        'date' => Carbon::now(),
                        'debit' => ($status == CastAccount::ENTER) ? $nominal_transaction : 0,
                        'credit' => ($status == CastAccount::OUT) ? $nominal_transaction : 0,
                    ], [
                        'reference_id' => $acctTransactionLoan->id,
                        'reference_type' => AccountTransaction::class,
                    ]);
                }
            }


            $item = $cast_account_loan;
            $item->new_saldo = CustomHelper::formatRupiahWithCurrency($item->total_saldo);

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
                        'card_cast_account'.$cast_account_id.'_store_move_success' => $item
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

    public function update()
    {
        $this->crud->hasAccessOrFail('update');

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

    public function storeMoveTransaction()
    {
        $this->crud->hasAccessOrFail('create');

        CRUD::setValidation($this->ruleValidationMoveTransfer());

        $request = $this->crud->validateRequest();
        $this->crud->registerFieldEvents();
        DB::beginTransaction();

        try{

            $cast_account_id = $request->cast_account_id;
            $nominal_move = $request->nominal_move;
            $cast_account_destination_id = $request->cast_account_destination_id;

            $castAccount = CastAccount::find($cast_account_id);
            $before_salde = $castAccount->total_saldo;
            $new_saldo = $before_salde - $nominal_move;

            $accountTransaction = new AccountTransaction;
            $accountTransaction->cast_account_id = $cast_account_id;
            $accountTransaction->cast_account_destination_id = $cast_account_destination_id;
            $accountTransaction->date_transaction = Carbon::now();
            $accountTransaction->nominal_transaction = $nominal_move;
            $accountTransaction->total_saldo_before = $before_salde;
            $accountTransaction->total_saldo_after = $new_saldo;
            $accountTransaction->status = CastAccount::OUT;
            $accountTransaction->account_id = $castAccount->account_id;
            $accountTransaction->save();

            $castAccount->total_saldo = $new_saldo;
            $castAccount->save();

            $cast_account_destination = CastAccount::find($cast_account_destination_id);
            $saldo_before = $cast_account_destination->total_saldo;
            $new_saldo = $saldo_before + $nominal_move;

            $accountTransactionDestination = new AccountTransaction;
            $accountTransactionDestination->cast_account_id = $cast_account_destination_id;
            $accountTransactionDestination->cast_account_destination_id = $cast_account_id;
            $accountTransactionDestination->date_transaction = Carbon::now();
            $accountTransactionDestination->nominal_transaction = $nominal_move;
            $accountTransactionDestination->total_saldo_before = $saldo_before;
            $accountTransactionDestination->total_saldo_after = $new_saldo;
            $accountTransactionDestination->status = CastAccount::ENTER;
            $accountTransactionDestination->save();

            $cast_account_destination->total_saldo = $new_saldo;
            $cast_account_destination->save();

            CustomHelper::updateOrCreateJournalEntry([
                'account_id' => $castAccount->account_id,
                'reference_id' => $accountTransaction->id,
                'reference_type' => AccountTransaction::class,
                'description' => '',
                'date' => Carbon::now(),
                'debit' => 0,
                'credit' => $accountTransaction->nominal_transaction,
            ], [
                'reference_id' => $accountTransaction->id,
                'reference_type' => AccountTransaction::class,
            ]);

            $item = $accountTransaction;
            $item->saldo = CustomHelper::formatRupiahWithCurrency(CustomHelper::total_balance_cast_account($castAccount->id, CastAccount::LOAN));

            $this->data['entry'] = $item;

            $this->crud->setSaveAction();

            DB::commit();
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'events' => [
                        'card_cast_account'.$castAccount->id.'_store_move_success' => $item,
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

    public function destroy($id)
    {
        $this->crud->hasAccessOrFail('delete');

        $id = $this->crud->getCurrentEntryId() ?? $id;

        $journal = JournalEntry::whereHasMorph('reference', AccountTransaction::class, function($q) use($id){
            $q->where('cast_account_id', $id)
            ->orWhere('cast_account_destination_id', $id);
        })->orWhereHasMorph('reference', CastAccount::class, function($q) use($id){
            $q->where('id', $id);
        })->delete();

        return $this->crud->delete($id);
    }

    public function showTransaction(){
        $id = request()->_id;
        $castAccount = CastAccount::where('id', $id)->first();
        $detail = AccountTransaction::where('cast_account_id', $id)
        ->where('is_first', 0)
        ->orderBy('date_transaction', 'ASC')->get();
        foreach($detail as $entry){
            $entry->date_transaction_str = Carbon::parse($entry->date_transaction)->translatedFormat('j M Y');
            $entry->nominal_transaction_str = CustomHelper::formatRupiahWithCurrency($entry->nominal_transaction);
            $entry->description_str = ($entry->cast_account_destination_id) ? $entry->cast_account_destination->name : ($entry->description ?? '-');
            $entry->kdp_str = $entry->kdp ?? '-';
            $entry->job_name_str = $entry->job_name ?? '-';
            $entry->account_id_str = ($entry->account_id) ? $entry->account->code.' - '.$entry->account->name : '-';
            $entry->no_invoice_str = ($entry->no_invoice) ? $entry->no_invoice : '-';
            $entry->status_str = ucfirst(strtolower(trans('backpack::crud.cash_account.field_transaction.status.'.$entry->status)));
        }
        $castAccount->total_saldo_str = CustomHelper::formatRupiahWithCurrency($castAccount->total_saldo);
        return response()->json([
            'status' => true,
            'result'=> [
                'cast_account' => $castAccount,
                'detail' => $detail,
            ]
        ]);
    }

    public function getSelectToAccount(){
        $castAccounts = CastAccount::whereHas('informations', function($q){
            $q->where("additional_informations.id", 2)->select(DB::raw("1"));
        })->where('status', CastAccount::CASH)->get(['id', 'name']);
        return response()->json([
            'status' => true,
            'result' => $castAccounts,
        ]);
    }

}
