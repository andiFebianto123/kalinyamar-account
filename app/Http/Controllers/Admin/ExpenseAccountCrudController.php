<?php
namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Account;
use Illuminate\Validation\Rule;
use App\Http\Helpers\CustomHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Notifications\Action;
use App\Http\Controllers\CrudController;
use App\Models\JournalEntry;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class ExpenseAccountCrudController extends CrudController{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(Account::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/finance-report/expense-account');
        CRUD::setEntityNameStrings(trans('backpack::crud.expense_account.title_header'), trans('backpack::crud.expense_account.title_header'));

        CRUD::allowAccess(['create', 'update', 'delete']);

    }

    public function listCardComponents($type){
        $dataset = Account::where('type', $type)
        ->whereIn('level', [1, 2])
        ->where('is_active', 1)->orderBy('code', 'asc')->get();

        foreach($dataset as $account){
            $this->card->addCard([
                'name' => 'account_'.$account->id,
                'line' => 'top',
                'view' => 'crud::components.card-account',
                'params' => [
                    'crud' => $this->crud,
                    'account' => $account,
                    'route' => url($this->crud->route.'/search?_id='.$account->id),
                ]
            ]);
        }

    }

    public function index()
    {
        $this->crud->hasAccessOrFail('list');

        $this->data['is_disabled_list'] = true;

        $this->listCardComponents(Account::EXPENSE);

        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? mb_ucfirst($this->crud->entity_name_plural);
        $this->data['title_modal_create'] = trans('backpack::crud.expense_account.title_modal_create');
        $this->data['title_modal_edit'] = trans('backpack::crud.expense_account.title_modal_edit');
        $this->data['title_modal_delete'] = trans('backpack::crud.expense_account.title_modal_delete');

        $breadcrumbs = [
            trans('backpack::crud.menu.finance_report') => backpack_url('cash-flow'),
            trans('backpack::crud.menu.expense_account') => backpack_url($this->crud->route)
        ];
        $this->data['breadcrumbs'] = $breadcrumbs;

        $this->data['cards'] = $this->card;
        $this->data['modals'] = $this->modal;
        $this->data['scripts'] = $this->script;
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
        $rule = [
            'code' => 'required|max:20|unique:accounts,code,'.$id,
            'name' => 'required|max:100|unique:accounts,name,'.$id,
            'balance' => 'required|numeric|min:0',
        ];
        if($id){
            $rule['code'] = [
                'required',
                'max:20',
                Rule::unique('accounts', 'code')->ignore($id),
                function($attribute, $value, $fail) use($id){
                    $old_code = Account::where('id', $id)->first()->code;
                    if($value != $old_code){
                        $child = Account::where('code', 'LIKE', "$old_code%")
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
            'label' => trans('backpack::crud.expense_account.column.code'),
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
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => '000.000',
            ]
        ]);

    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    public function update()
    {
        $this->crud->hasAccessOrFail('update');

        $request = $this->crud->validateRequest();

        $this->crud->registerFieldEvents();

        DB::beginTransaction();
        try{

            $old_account = Account::find($request->id);
            $old_parent = null;
            for ($i = 1; $i < strlen($old_account->code); $i++) {
                $prefix = substr($old_account->code, 0, $i);
                $account = Account::where('code', $prefix)->first();
                if($account){
                    $old_parent = $account;
                }
            }

            $new_code = $request->code;
            $new_parent = null;
            for ($i = 1; $i < strlen($new_code); $i++) {
                $prefix = substr($new_code, 0, $i);
                $account = Account::where('code', $prefix)->first();
                if($account){
                    $new_parent = $account;
                }
            }


            $item = Account::where('id', $request->id)->first();
            $item->code = $new_code;
            $item->name = $request->name;
            $item->save();

            if($request->balance > 0){
                $journal = JournalEntry::where('account_id', $item->id)
                ->where('reference_type', Account::class)
                ->first();
                if($journal){
                    $journal->debit = $request->balance;
                    $journal->credit = 0;
                    $journal->save();
                }
            }

            $events = [];

            if($old_parent){
                $events['account_'.$old_parent->id.'_update_success'] = true;
            }

            if($new_parent){
                $events['account_'.$new_parent->id.'_update_success'] = true;
            }

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

    public function store()
    {
        $this->crud->hasAccessOrFail('create');

        $request = $this->crud->validateRequest();

        $this->crud->registerFieldEvents();

        DB::beginTransaction();
        try{

            $code = $request->code;
            $beforeAccount = null;

            for ($i = 1; $i < strlen($code); $i++) {
                $prefix = substr($code, 0, $i);
                $account = Account::where('code', $prefix)->first();
                if($account){
                    $beforeAccount = $account;
                }
            }

            $request->merge([
                'level' => ($beforeAccount) ? $beforeAccount->level + 1 : 2,
                'type' => Account::EXPENSE,
            ]);

            // $item = $this->crud->create($this->crud->getStrippedSaveRequest($request));
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

            if($beforeAccount){
                $item->component_name = 'account_'.$beforeAccount->id;
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

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $this->crud->hasAccessOrFail('delete');

            $item = $this->crud->model::findOrFail($id);
            $parent_account = null;

            if($item->level == 1 || $item->level == 2){
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

            for ($i = 1; $i < strlen($item->code); $i++) {
                $prefix = substr($item->code, 0, $i);
                $account = Account::where('code', $prefix)->first();
                if($account){
                    $parent_account = $account;
                }
            }

            $events = [];

            if($parent_account){
                $events['account_'.$parent_account->id.'_update_success'] = true;
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

    protected function setupListOperation()
    {
        CRUD::disableResponsiveTable();
        CRUD::removeButton('update');
        CRUD::removeButton('delete');

        CRUD::addButtonFromView('line', 'delete', "delete-account", 'beginning');
        CRUD::addButtonFromView('line', 'update', "update-account", 'beginning');


        // CRUD::addButtonFromView('top', 'filter_paid_unpaid', 'filter-paid_unpaid', 'beginning');

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

        CRUD::column(
            [
                'name' => 'balance',
                'label' => trans('backpack::crud.expense_account.column.balance'),
                'type' => 'custom_html',
                'value' => function($entry) {
                    return CustomHelper::formatRupiahWithCurrency($entry->balance);
                },
            ],
        );


        if(request()->has('_id')){
            $id = request()->_id;
            $code = Account::find($id);

            $this->crud->query = $this->crud->query
            ->leftJoin('journal_entries', 'journal_entries.account_id', '=', 'accounts.id')
            ->select(DB::raw("
                accounts.id as id_,
                MAX(accounts.code) as code_,
                MAX(accounts.name) as name_,
                MAX(accounts.level) as level_,
                (SUM(journal_entries.debit) - SUM(journal_entries.credit)) as balance
            "));

            if($code->level == 1){
                $this->crud->query = $this->crud->query
                ->where('code', 'LIKE', "{$code->code}");
            }else{
                $this->crud->query = $this->crud->query
                ->where('code', 'LIKE', "{$code->code}%");
            }


            $this->crud->query = $this->crud->query
            ->orderBy('code', 'asc')
            ->groupBy('accounts.id');
        }

    }

}
