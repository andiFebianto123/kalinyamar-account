<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Setting;
use App\Models\CastAccount;
use App\Models\JournalEntry;
use PhpParser\Node\Expr\Cast;
use App\Http\Helpers\CustomHelper;
use App\Models\AccountTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

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
                        'name' => 'card_cast_account'.$l->id
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
        CRUD::addButtonFromView('top', 'filter_cash_account_order', 'filter-cash-account-order', 'beginning');

        // CRUD::setFromDb(); // set columns from db columns.

        /**
         * Columns can be defined using the fluent syntax:
         * - CRUD::column('price')->type('number');
         */
    }

    function ruleValidation(){
        $id = request('id') ?? '';
        // 'name' => 'required|min:5|max:255'
        return [
            'name' => 'required|max:100|unique:cast_accounts,name,'.$id,
            'bank_name' => 'required|max:50',
            'no_account' => 'required|max:100',
            'total_saldo' => 'required|numeric|min:1000',
        ];
    }

    function ruleValidationTransaction(){
        $cast_account_id = request()->cast_account_id;
        $status = request()->status;
        return [
            'date_transaction' => 'required',
            'nominal_transaction' => [
                'required',
                'numeric',
                'min:1000',
                function ($attribute, $value, $fail) use ($cast_account_id, $status) {
                    if($status == CastAccount::OUT){
                        $balance = CustomHelper::total_balance_cast_account($cast_account_id, CastAccount::CASH);
                        if ($value > $balance) {
                            $fail(trans("backpack::crud.cash_account.field_transfer.errors.nominal_transfer_to_more"));
                        }
                    }
                }
            ],
            'kdp' => 'max:50',
            'job_name' => 'max:100',
            'no_invoice' => 'max:100',
            'account_id' => 'exists:accounts,id',
            'status' => 'required|in:enter,out',
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

        $settings = Setting::first();

        CRUD::setModel(AccountTransaction::class);
        CRUD::setValidation($this->ruleValidationTransaction());
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

        CRUD::addField([
            'name' => 'kdp',
            'label' => trans('backpack::crud.cash_account.field_transaction.kdp.label'),
            'type' => 'text',
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.cash_account.field_transaction.kdp.placeholder'),
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

        CRUD::addField([
            'name' => 'no_invoice',
            'label' => trans('backpack::crud.cash_account.field_transaction.no_invoice.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.cash_account.field_transaction.no_invoice.placeholder'),
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

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation($this->ruleValidation());
        // CRUD::setFromDb(); // set fields from db columns.

        $request = request();

        if($request->has('_id')){

            $this->createTransactionOperation($request->_id);

        }else{
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
            $description = $request->description;
            $kdp = $request->kdp;
            $job_name = $request->job_name;
            $no_invoice = $request->no_invoice;
            $status = $request->status;

            $cast_account = CastAccount::where('id', $cast_account_id)->first();
            $before_saldo = $cast_account->total_saldo;
            if($status == CastAccount::ENTER){
                $new_saldo = $before_saldo + $nominal_transaction;
            }else if($status == CastAccount::OUT){
                $new_saldo = $before_saldo - $nominal_transaction;
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

            if($request->has('account_id')){
                $newTransaction->account_id = $request->account_id;
                $newTransaction->save();

                // catat di journal
                CustomHelper::updateOrCreateJournalEntry([
                    'account_id' => $newTransaction->account_id,
                    'reference_id' => $newTransaction->id,
                    'reference_type' => AccountTransaction::class,
                    'description' => $description,
                    'date' => Carbon::now(),
                    'debit' => ($status == CastAccount::ENTER) ? $nominal_transaction : 0,
                    'credit' => ($status == CastAccount::OUT) ? $nominal_transaction : 0,
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

            $newTransaction = new AccountTransaction;
            $newTransaction->cast_account_id = $request->cast_account_id;
            $newTransaction->cast_account_destination_id = $request->to_account;
            $newTransaction->date_transaction = Carbon::now();
            $newTransaction->nominal_transaction = $request->nominal_transfer;
            $newTransaction->total_saldo_before = $old_saldo;
            $newTransaction->total_saldo_after = $new_saldo;
            $newTransaction->status = 'out';
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
            $newTransaction_2->save();

            $otherAccount->total_saldo = $other_new_saldo;
            $otherAccount->save();
            $otherAccount->new_saldo = 'Rp'.CustomHelper::formatRupiah($other_new_saldo);


            $item = $newTransaction_2;
            $this->data['entry'] = $this->crud->entry = $item;

            // \Alert::success(trans('backpack::crud.insert_success'))->flash();

            $this->crud->setSaveAction();

            DB::commit();
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'events' => [
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
                'balance' => CustomHelper::formatRupiahWithCurrency(CustomHelper::total_balance_cast_account($id, CastAccount::CASH)),
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
