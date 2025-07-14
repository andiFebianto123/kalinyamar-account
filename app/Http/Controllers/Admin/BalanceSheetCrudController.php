<?php
namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Account;
use App\Models\JournalEntry;
use Illuminate\Validation\Rule;
use App\Models\ProjectProfitLost;
use App\Http\Helpers\CustomHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Notifications\Action;
use App\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class BalanceSheetCrudController extends CrudController{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup()
    {
        CRUD::setModel(Account::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/finance-report/balance-sheet');
        CRUD::setEntityNameStrings(trans('backpack::crud.balance_sheet.title_header'), trans('backpack::crud.balance_sheet.title_header'));

        CRUD::allowAccess(['create', 'update', 'delete', 'print']);
    }

    public function listCardComponents(){

        $accountAsset = Account::where('type', 'Assets')
        ->whereIn('level', [2])
        ->where('is_active', 1)->orderBy('code', 'asc')->first();

        $this->card->addCard([
            'name' => 'filter',
            'line' => 'top',
            'view' => 'crud::components.filter-account-balance',
            'parent_view' => 'crud::components.filter-parent',
            'params' => [
            ]
        ]);

        $this->card->addCard([
            'name' => 'account_Assets',
            'line' => 'top',
            'view' => 'crud::components.card-account-balance-sheet',
            'params' => [
                'title' => trans('backpack::crud.balance_sheet.card.asset'),
                'crud' => $this->crud,
                'account' => $accountAsset,
                'route' => url($this->crud->route.'/search?_type=Assets'),
            ]
        ]);

        $accountLiabilities = Account::where('type', 'Liabilities')
        ->whereIn('level', [2])
        ->where('is_active', 1)->orderBy('code', 'asc')->first();

        $this->card->addCard([
            'name' => 'account_Liabilities',
            'line' => 'top',
            'view' => 'crud::components.card-account-balance-sheet',
            'params' => [
                'title' => trans('backpack::crud.balance_sheet.card.liabilities'),
                'crud' => $this->crud,
                'account' => $accountLiabilities,
                'route' => url($this->crud->route.'/search?_type=Liabilities'),
            ]
        ]);

        $accountEquity = Account::where('type', 'Equity')
        ->whereIn('level', [2])
        ->where('is_active', 1)->orderBy('code', 'asc')->first();

        $this->card->addCard([
            'name' => 'account_Equity',
            'line' => 'top',
            'view' => 'crud::components.card-account-balance-sheet',
            'params' => [
                'title' => trans('backpack::crud.balance_sheet.card.equity'),
                'crud' => $this->crud,
                'account' => $accountEquity,
                'route' => url($this->crud->route.'/search?_type=Equity'),
            ]
        ]);

        $this->card->addCard([
            'name' => 'account_total',
            'line' => 'top',
            'view' => 'crud::components.card-asset-total',
            'params' => [
                'route' => url('admin/finance-report/show-total-account'),
            ]
        ]);

    }

    public function index()
    {
        $this->crud->hasAccessOrFail('list');

        $this->data['is_disabled_list'] = true;

        $this->listCardComponents();

        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? mb_ucfirst($this->crud->entity_name_plural);
        $this->data['title_modal_create'] = trans('backpack::crud.balance_sheet.title_modal_create');
        $this->data['title_modal_edit'] = trans('backpack::crud.balance_sheet.title_modal_edit');
        $this->data['title_modal_delete'] = trans('backpack::crud.balance_sheet.title_modal_delete');

        $breadcrumbs = [
            trans('backpack::crud.menu.finance_report') => backpack_url('cash-flow'),
            trans('backpack::crud.menu.balance_sheet') => backpack_url($this->crud->route)
        ];
        $this->data['breadcrumbs'] = $breadcrumbs;

        $this->data['cards'] = $this->card;
        $this->data['modals'] = $this->modal;
        $this->data['scripts'] = $this->script;
        $list = "crud::list-blank" ?? $this->crud->getListView();
        return view($list, $this->data);
    }

    protected function setupListOperation()
    {
        // $this->crud->setFromDb(false);
        CRUD::disableResponsiveTable();

        $request = request();

        // CRUD::removeButton('create');
        CRUD::removeButton('update');
        CRUD::removeButton('delete');

        // CRUD::addButtonFromView('top', 'create', 'create-account-profit-lost', 'begining');
        CRUD::addButtonFromView('line', 'delete', "delete-account", 'beginning');
        CRUD::addButtonFromView('line', 'update', "update-account", 'beginning');
        CRUD::addButtonFromView('top', 'print-all', 'print-all', 'end');
        CRUD::addButtonFromView('top', 'download-pdf-account-balance', 'download-pdf-account-balance', 'end');
        CRUD::addButtonFromView('top', 'download-excel-account-balance', 'download-excel-account-balance', 'end');


        CRUD::column([
            'name' => 'code_',
            'label' => trans('backpack::crud.expense_account.column.code'),
            'type' => 'text',
        ]);

        CRUD::column([
            'name' => 'name_',
            'label' => trans('backpack::crud.expense_account.column.name'),
            'type' => 'custom_html',
            'value' => function($entry){
                if($entry->level_ > 2){
                    $space = str_repeat('&nbsp;', $entry->level_);
                    return $space.'&bull; '.$entry->name_;
                }
                return $entry->name_;
            }
        ]);

        CRUD::column([
            'name' => 'balance',
            'label' => trans('backpack::crud.expense_account.column.balance'),
            'type' => 'custom_html',
            'value' => function($entry) {
                return CustomHelper::formatRupiahWithCurrency($entry->balance);
            },
        ]);

        if(request()->has('_type')){
            $type = request()->_type;
            // $code = Account::find($id);

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

            // if($code->level == 1){
            //     $this->crud->query = $this->crud->query
            //     ->where('code', 'LIKE', "{$code->code}");
            // }else{
            //     $this->crud->query = $this->crud->query
            //     ->where('code', 'LIKE', "{$code->code}%");
            // }

            $this->crud->query = $this->crud->query
            ->where('type', $type)
            ->whereIn('level', [2, 3])
            ->orderBy('code', 'asc')
            ->groupBy('accounts.id');
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

        // $this->data['entry'] = $this->crud->getEntryWithLocale($id);

        $this->data['entry'] = Account::leftJoin('journal_entries', 'journal_entries.account_id', '=', 'accounts.id')
            ->select(DB::raw("
                accounts.id as id,
                MAX(accounts.code) as code,
                MAX(accounts.name) as name,
                MAX(accounts.type) as type,
                MAX(accounts.level) as level,
                (SUM(journal_entries.debit) - SUM(journal_entries.credit)) as balance
            "))->where('accounts.id', $id)
            ->groupBy('accounts.id')
            ->first();

        $this->crud->entry = $this->data['entry'];

        $this->crud->setOperationSetting('fields', $this->crud->getUpdateFields());

        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.edit').' '.$this->crud->entity_name;
        $this->data['id'] = $id;

        return response()->json([
            'html' => view($this->crud->getEditView(), $this->data)->render()
        ]);
    }

    private function ruleAccount(){
        $id = request()->id;
        $type = request()->type;
        $rule = [
            'type' => 'required',
            'name' => 'required|max:100|unique:accounts,name,'.$id,
            'balance' => 'required|numeric|min:0',
            'date' => 'required',
        ];
        $rule['code'] = [
            'required',
            'min:3',
            'max:20',
            Rule::unique('accounts', 'code')->ignore($id),
            function($attribute, $value, $fail) use($id, $type){
                $parent = null;
                for ($i = 1; $i < strlen($value); $i++) {
                    $prefix = substr($value, 0, $i);
                    $account = Account::where('code', $prefix)
                    ->where('level', '>=', 2)->first();
                    if($account && $account->type != $type){
                        $fail(trans('backpack::crud.expense_account.field.code.errors.depedency'));
                    }
                }
                return $parent;
            }
        ];
        if($id){
            $rule['code'] = [
                'required',
                'min:3',
                'max:20',
                Rule::unique('accounts', 'code')->ignore($id),
                function($attribute, $value, $fail) use($id, $type){
                    $old_code = Account::where('id', $id)->first()->code;

                    $parent = null;
                    if($value != $old_code){
                        for ($i = 1; $i < strlen($value); $i++) {
                            $prefix = substr($value, 0, $i);
                            $account = Account::where('code', $prefix)
                            ->where('level', '>=', 2)->first();
                            if($account && $account->type != $type){
                                $fail(trans('backpack::crud.expense_account.field.code.errors.depedency'));
                            }
                        }
                    }

                    if($value != $old_code){
                        $child = Account::where('code', 'LIKE', "$old_code%")
                        // ->where('type', Account::INCOME)
                        ->where('id', '!=', $id)
                        ->count();
                        if($child > 0){
                            $fail(trans('backpack::crud.expense_account.field.code.errors.depedency'));
                        }
                    }
                }
            ];

            $rule['balance'] = [
                'required',
                'numeric',
                'min:0',
                function($attribute, $value, $fail) use($id){
                    $old_balance = Account::leftJoin('journal_entries', 'journal_entries.account_id', '=', 'accounts.id')
                    ->select(DB::raw("
                        (SUM(journal_entries.debit) - SUM(journal_entries.credit)) as balance
                    "))->where('accounts.id', $id)
                    ->groupBy('accounts.id')
                    ->first();

                    $old_balance = $old_balance->balance ?? 0;

                    if($value != $old_balance){
                        $journal = JournalEntry::where('account_id', $id)
                        ->whereNot('reference_type', Account::class)
                        ->count();
                        if($journal > 0){
                            $fail(trans('backpack::crud.expense_account.field.code.errors.not_change_balance'));
                        }
                    }


                }
            ];
        }
        return $rule;
    }

    protected function setupCreateOperation(){
        CRUD::setValidation($this->ruleAccount());
        CRUD::addField([   // select_from_array
            'name'        => 'type',
            'label'       => trans('backpack::crud.balance_sheet.fields.account_type.label'),
            'type'        => 'select_from_array',
            'options'     => [
                '' => trans('backpack::crud.balance_sheet.fields.account_type.placeholder'),
                'Assets' => trans('backpack::crud.balance_sheet.fields.account_type.options.account_asset'),
                'Liabilities' => trans('backpack::crud.balance_sheet.fields.account_type.options.account_liabilities'),
                'Equity' => trans('backpack::crud.balance_sheet.fields.account_type.options.account_equity'),
            ],
            'allows_null' => false,
            // 'default'     => '',
        ]);
        CRUD::addField([
            'name' => 'code',
            'label' => trans('backpack::crud.expense_account.column.code'),
            'type' => 'text',
            'attributes' => [
                'placeholder' => trans('backpack::crud.expense_account.field.code.placeholder')
            ]
        ]);
        CRUD::addField([
            'name' => 'name',
            'label' => trans('backpack::crud.expense_account.column.name'),
            'type' => 'text',
            'attributes' => [
                'placeholder' => trans('backpack::crud.expense_account.field.name.placeholder')
            ]
        ]);

        CRUD::addField([
            'name' => 'balance',
            'label' =>  trans('backpack::crud.expense_account.column.balance'),
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
                'placeholder' => '000.000',
            ]
        ]);

        CRUD::addField([   // date_picker
            'name'  => 'date',
            'type'  => 'date_picker',
            'label' => trans('backpack::crud.balance_sheet.fields.date.label'),
            'date_picker_options' => [
                'language' => App::getLocale(),
            ],
            'suffix' => '<i class="la la-calendar"></i>',
            'wrapper'   => [
                'class' => 'form-group col-md-12'
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.balance_sheet.fields.date.placeholder'),
            ]
        ]);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    public function getRootParentAccount($code){
        $parent = null;
        for ($i = 1; $i <= strlen($code); $i++) {
            $prefix = substr($code, 0, $i);
            $account = Account::where('code', $prefix)
            // ->where('type', Account::INCOME)
            ->whereIn('level', [1, 2])->first();
            if($account){
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
        try{

            $code = $request->code;
            $beforeAccount = null;

            for ($i = 1; $i <= strlen($code); $i++) {
                $prefix = substr($code, 0, $i);
                $account = Account::where('code', $prefix)
                ->first();
                if($account){
                    $beforeAccount = $account;
                }
            }

            $rootParent = $this->getRootParentAccount($code);

            $request->merge([
                'level' => ($beforeAccount) ? $beforeAccount->level + 1 : 2,
                'type' => $request->type,
            ]);

            $item = new Account;
            $item->code = $code;
            $item->name = request()->name;
            $item->type = request()->type;
            $item->level = request()->level;
            $item->save();
            $this->data['entry'] = $this->crud->entry = $item;

            \Alert::success(trans('backpack::crud.insert_success'))->flash();

            $this->crud->setSaveAction();

            CustomHelper::updateOrCreateJournalEntry([
                'account_id' => $item->id,
                'reference_id' => $item->id,
                'reference_type' => Account::class,
                'description' => 'FIRST BALANCE',
                'date' => Carbon::now(),
                'debit' => $request->balance,
                // 'credit' => ($status == CastAccount::OUT) ? $nominal_transaction : 0,
            ], [
                'reference_id' => $item->id,
                'reference_type' => Account::class,
            ]);

            if($rootParent){
                $item->component_name = 'account_'.$rootParent->type;
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'data' => $item,
                'events' => [
                    'account_create_success' => $item,
                ]
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

    public function update()
    {
        $this->crud->hasAccessOrFail('update');

        $request = $this->crud->validateRequest();

        $this->crud->registerFieldEvents();

        DB::beginTransaction();
        try{

            $old_account = Account::find($request->id);
            $rootParent_1 = $this->getRootParentAccount($old_account->code);

            $new_code = $request->code;
            $new_parent = null;
            for ($i = 1; $i <= strlen($new_code); $i++) {
                $prefix = substr($new_code, 0, $i);
                $account = Account::where('code', $prefix)->first();
                if($account){
                    if($account->code != $old_account->code){
                        $new_parent = $account;
                    }
                }
            }

            $rootParant_2 = $this->getRootParentAccount($new_code);

            $item = Account::where('id', $request->id)->first();
            $item->code = $new_code;
            $item->name = $request->name;
            if($new_parent){
                $item->level = $new_parent->level + 1;
            }
            $item->save();

            if($request->balance > 0){
                $journal = JournalEntry::where('account_id', $item->id)
                ->where('reference_type', Account::class)
                ->first();
                if($journal){
                    $journal->debit = $request->balance;
                    $journal->credit = 0;
                    $journal->save();
                }else{
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

            if($rootParent_1){
                $dataEvents[] = $rootParent_1;
                $events['account_'.$rootParent_1->type.'_update_success'] = ($item->level == 2) ? $rootParent_1 : $item;
            }

            if($rootParant_2){
                $dataEvents[] = $rootParant_2;
                $events['account_'.$rootParant_2->type.'_update_success'] = ($item->level == 2) ? $rootParant_2 : $item;
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
        DB::beginTransaction();
        try {
            $this->crud->hasAccessOrFail('delete');

            $item = $this->crud->model::findOrFail($id);
            $parent_account = null;

            if($item){
                $child_exists = Account::where('code', 'LIKE', "{$item->code}%")
                ->where('id', '!=', $item->id)->count();

                if($child_exists > 0){
                    return response()->json([
                        'error' => [
                            trans('backpack::crud.expense_account.field.code.errors.delete')
                        ]
                    ]);
                }
            }

            $parent_account = $this->getRootParentAccount($item->code);

            $events = [];

            if($parent_account){
                $events['account_'.$parent_account->type.'_update_success'] = true;
            }

            JournalEntry::where('account_id', $item->id)->delete();

            $item->delete();

            DB::commit();
            return response()->json([
                'success' => [
                    '<strong>'.trans('backpack::crud.delete_confirmation_title').'</strong><br>'.trans('backpack::crud.delete_confirmation_message'),
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

    public function showTotalAccount(){
        $total_asset = Account::leftJoin('journal_entries', function($q){
            $q->on('accounts.id', '=', 'journal_entries.reference_id')
            ->where('journal_entries.reference_type', Account::class);
        })->where('accounts.type', 'Assets')
        ->select(DB::raw('SUM(journal_entries.debit - journal_entries.credit) as balance'))
        ->groupBy('accounts.type')
        ->get();

        $total_liabilities = Account::leftJoin('journal_entries', function($q){
            $q->on('accounts.id', '=', 'journal_entries.reference_id')
            ->where('journal_entries.reference_type', Account::class);
        })->where('accounts.type', 'Liabilities')
        ->select(DB::raw('SUM(journal_entries.debit - journal_entries.credit) as balance'))
        ->groupBy('accounts.type')
        ->get();

        $total_equity = Account::leftJoin('journal_entries', function($q){
            $q->on('accounts.id', '=', 'journal_entries.reference_id')
            ->where('journal_entries.reference_type', Account::class);
        })->where('accounts.type', 'Equity')
        ->select(DB::raw('SUM(journal_entries.debit - journal_entries.credit) as balance'))
        ->groupBy('accounts.type')
        ->get();

        return response()->json([
            'status' => true,
            'total_asset' => "Rp.".CustomHelper::formatRupiah($total_asset->first()->balance),
            'total_liabilities' => "Rp.".CustomHelper::formatRupiah($total_liabilities->first()->balance),
            'total_equity' => "Rp.".CustomHelper::formatRupiah($total_equity->first()->balance),
        ]);

    }

}
