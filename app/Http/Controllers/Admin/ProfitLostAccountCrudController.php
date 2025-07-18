<?php
namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Account;
use App\Models\JournalEntry;
use Illuminate\Validation\Rule;
use App\Models\ProjectProfitLost;
use App\Http\Helpers\CustomHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Notifications\Action;
use App\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class ProfitLostAccountCrudController extends CrudController{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(Account::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/finance-report/profit-lost');
        CRUD::setEntityNameStrings(trans('backpack::crud.profit_lost.title_header'), trans('backpack::crud.profit_lost.title_header'));

        CRUD::allowAccess(['create', 'update', 'delete']);

    }

    public function listCardComponents($type){
        $dataset = Account::where('type', $type)
        ->whereIn('level', [2])
        ->where('is_active', 1)->orderBy('code', 'asc')->get();

        if($dataset->count() > 0){
            foreach($dataset as $account){
                $this->card->addCard([
                    'name' => 'account_'.$account->id,
                    'line' => 'top',
                    'view' => 'crud::components.card-account-profit',
                    'params' => [
                        'crud' => $this->crud,
                        'account' => $account,
                        'route' => url($this->crud->route.'/search?_id='.$account->id),
                    ]
                ]);
            }
        }else{
            $this->card->addCard([
                'name' => 'blank_account',
                'line' => 'top',
                'view' => 'crud::components.blank_card-account-profit',
                'params' => [
                    'message' => 'selamat malam',
                ],
            ]);
        }

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
                        'label' => trans('backpack::crud.profit_lost.column.job_code'),
                        'type'      => 'select',
                        'name'      => 'job_code',
                        'orderable' => true,
                    ],
                    [
                        'label' => trans('backpack::crud.profit_lost.column.no_po'),
                        'type'      => 'text',
                        'name'      => 'no_po',
                        'orderable' => true,
                    ],
                    [
                        'label' => trans('backpack::crud.profit_lost.column.contract_value'),
                        'type'      => 'number',
                        'name'      => 'contract_value',
                        'orderable' => true,
                    ],
                    [
                        'label' => trans('backpack::crud.profit_lost.column.total_project'),
                        'type'      => 'number',
                        'name'      => 'total_project',
                        'orderable' => true,
                    ],
                    [
                        'label' => trans('backpack::crud.profit_lost.column.price_profit_lost_project'),
                        'type'      => 'number',
                        'name'      => 'price_profit_lost_project',
                        'orderable' => true,
                    ],
                    [
                        'name' => 'action',
                        'type' => 'action',
                        'label' =>  trans('backpack::crud.actions'),
                    ]
                ],
                'route' => url($this->crud->route.'/search?type=project'),
            ]
        ]);

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
        return view($list, $this->data);
    }

    public function detail($id){
        $this->crud->hasAccessOrFail('list');
        $this->data['is_disabled_list'] = true;
        $profitLost = ProjectProfitLost::where('id', $id)->first();

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
            ],
        ]);

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
            'name' => 'required|max:100|unique:accounts,name,'.$id,
            'balance' => 'required|numeric|min:0',
        ];
        $rule['code'] = [
            'required',
            'min:3',
            'max:20',
            Rule::unique('accounts', 'code')->ignore($id),
            function($attribute, $value, $fail) use($id){
                $parent = null;
                for ($i = 1; $i < strlen($value); $i++) {
                    $prefix = substr($value, 0, $i);
                    $account = Account::where('code', $prefix)
                    ->where('level', '>=', 2)->first();
                    if($account && $account->type != Account::INCOME){
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
                function($attribute, $value, $fail) use($id){
                    $old_code = Account::where('id', $id)->first()->code;

                    $parent = null;
                    if($value != $old_code){
                        for ($i = 1; $i < strlen($value); $i++) {
                            $prefix = substr($value, 0, $i);
                            $account = Account::where('code', $prefix)
                            ->where('level', '>=', 2)->first();
                            if($account && $account->type != Account::INCOME){
                                $fail(trans('backpack::crud.expense_account.field.code.errors.depedency'));
                            }
                        }
                    }

                    if($value != $old_code){
                        $child = Account::where('code', 'LIKE', "$old_code%")
                        ->where('type', Account::INCOME)
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

    function validationProject(){
        $rule = [];
        $rule['client_po_id'] = 'required|exists:client_po,id';
        return $rule;
    }

    protected function setupCreateOperation(){

        $request = request();

        if($request->has('type')){
            if($request->type == 'project'){

                CRUD::setValidation($this->validationProject());
                CRUD::setModel(ProjectProfitLost::class);

                CRUD::addField([   // 1-n relationship
                    'label'       => trans('backpack::crud.invoice_client.field.client_po_id.label'), // Table column heading
                    'type'        => "select2_ajax_invoice_client",
                    'name'        => 'client_po_id', // the column that contains the ID of that connected entity
                    'entity'      => 'clientPo', // the method that defines the relationship in your Model
                    'attribute'   => 'po_number', // foreign key attribute that is shown to user
                    'data_source' => backpack_url('invoice-client/select2-client-po'), // url to controller search function (with /{id} should return a single entry)
                    'wrapper'   => [
                        'class' => 'form-group col-md-6',
                    ],
                    'placeholder' => trans('backpack::crud.invoice_client.field.client_po_id.placeholder'),
                ]);

                CRUD::addField([
                    'name' => 'job_code',
                    'label' => trans('backpack::crud.profit_lost.fields.job_code.label'),
                    'type' => 'text',
                    'attributes' => [
                        'placeholder' => trans('backpack::crud.profit_lost.fields.job_code.placeholder'),
                        'disabled' => true,
                    ],
                    'wrapper' => ['class' => 'form-group col-md-6'],
                ]);
                CRUD::addField([
                    'name' => 'contract_value',
                    'label' =>  trans('backpack::crud.profit_lost.fields.contract_value.label'),
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
                CRUD::addField([
                    'name' => 'total_project',
                    'label' =>  trans('backpack::crud.profit_lost.fields.total_project.label'),
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

                CRUD::addField([
                    'name' => 'price_material',
                    'label' =>  trans('backpack::crud.profit_lost.fields.price_material.label'),
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

                CRUD::addField([
                    'name' => 'price_subkon',
                    'label' =>  trans('backpack::crud.profit_lost.fields.price_subkon.label'),
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

                CRUD::addField([
                    'name' => 'price_btkl',
                    'label' =>  trans('backpack::crud.profit_lost.fields.price_btkl.label'),
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

                CRUD::addField([
                    'name' => 'price_transport_project',
                    'label' =>  trans('backpack::crud.profit_lost.fields.price_transport_project.label'),
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

                CRUD::addField([
                    'name' => 'price_worker_consumption',
                    'label' =>  trans('backpack::crud.profit_lost.fields.price_worker_consumption.label'),
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

                CRUD::addField([
                    'name' => 'price_project_equipment',
                    'label' =>  trans('backpack::crud.profit_lost.fields.price_project_equipment.label'),
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

                CRUD::addField([
                    'name' => 'price_other',
                    'label' =>  trans('backpack::crud.profit_lost.fields.price_other.label'),
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

                CRUD::addField([
                    'name' => 'price_profit_lost_project',
                    'label' =>  trans('backpack::crud.profit_lost.fields.price_profit_lost_project.label'),
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
        }else{
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
                    'class' => 'form-group col-md-6',
                ],
                'attributes' => [
                    'placeholder' => '000.000',
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
                $events['account_'.$rootParent_1->id.'_update_success'] = ($item->level == 2) ? $rootParent_1 : $item;
            }

            if($rootParant_2){
                $dataEvents[] = $rootParant_2;
                $events['account_'.$rootParant_2->id.'_update_success'] = ($item->level == 2) ? $rootParant_2 : $item;
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
                'type' => Account::INCOME,
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
                $item->component_name = 'account_'.$rootParent->id;
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

    public function storeProject(){
        $this->crud->hasAccessOrFail('create');
        CRUD::setValidation($this->validationProject());

        $request = $this->crud->validateRequest();

        $this->crud->registerFieldEvents();

        DB::beginTransaction();
        try{

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
        // $this->crud->setFromDb(false);
        CRUD::disableResponsiveTable();

        $request = request();

        if($request->has('type')){
            if($request->type == 'project'){
                // CRUD::removeButton('create');
                CRUD::removeButton('update');
                CRUD::removeButton('delete');
                CRUD::removeButton('show');

                CRUD::addButtonFromView('line', 'show-detail-project', "show-detail-project", 'beginning');

                CRUD::setModel(ProjectProfitLost::class);
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
                    'function' => function($entry) {
                        return $entry->clientPo->client->name;
                    }, // the column that contains the ID of that connected entity;
                    'searchLogic' => function ($query, $column, $searchTerm) {
                        $query->orWhereHas('clientPo.client', function ($q) use ($column, $searchTerm) {
                            $q->where('name', 'like', '%'.$searchTerm.'%');
                        });
                    }
                ]);

                CRUD::column([
                    // 1-n relationship
                    'label' => trans('backpack::crud.profit_lost.column.job_code'),
                    'type'      => 'closure',
                    'name'      => 'job_code',
                    'function' => function($entry) {
                        return $entry->clientPo->work_code;
                    }, // the column that contains the ID of that connected entity;
                    'searchLogic' => function($query, $column, $searchTerm){
                        $query->orWhereHas('clientPo', function($q) use($searchTerm){
                            $q->where('work_code', 'like', '%'.$searchTerm.'%');
                        });
                    }
                ]);

                CRUD::column([
                    // 1-n relationship
                    'label' => trans('backpack::crud.profit_lost.column.no_po'),
                    'type'      => 'closure',
                    'name'      => 'no_po',
                    'function' => function($entry) {
                        return $entry->clientPo->po_number;
                    } // the column that contains the ID of that connected entity;
                    // OPTIONAL
                    // 'limit' => 32, // Limit the number of characters shown
                ]);

                CRUD::column([
                    'label'  => trans('backpack::crud.profit_lost.column.contract_value'),
                    'name' => 'contract_value',
                    'type'  => 'number',
                    'prefix' => "Rp.",
                    'decimals'      => 2,
                    'dec_point'     => ',',
                    'thousands_sep' => '.',
                ]);

                CRUD::column([
                    'label'  => trans('backpack::crud.profit_lost.column.total_project'),
                    'name' => 'total_project',
                    'type'  => 'number',
                    'prefix' => "Rp.",
                    'decimals'      => 2,
                    'dec_point'     => ',',
                    'thousands_sep' => '.',
                ]);

                CRUD::column([
                    'label'  => trans('backpack::crud.profit_lost.column.price_profit_lost_project'),
                    'name' => 'price_profit_lost_project',
                    'type'  => 'number',
                    'prefix' => "Rp.",
                    'decimals'      => 2,
                    'dec_point'     => ',',
                    'thousands_sep' => '.',
                ]);

            }
        }else{

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

        // CRUD::addButtonFromView('top', 'filter_paid_unpaid', 'filter-paid_unpaid', 'beginning');


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

}
