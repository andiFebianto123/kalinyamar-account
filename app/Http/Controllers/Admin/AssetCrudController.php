<?php
namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Asset;
use App\Models\Account;
use App\Models\Setting;
use App\Models\JournalEntry;
use Illuminate\Validation\Rule;
use App\Models\ProjectProfitLost;
use App\Http\Helpers\CustomHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Notifications\Action;
use App\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class AssetCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        $this->crud->denyAllAccess(['create', 'update', 'delete', 'list', 'show']);
        $this->crud->setModel(Asset::class);
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/finance-report/list-asset');
        $this->crud->setEntityNameStrings(trans('backpack::crud.asset.title_header'), trans('backpack::crud.asset.title_header'));
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

    public function index()
    {
        $this->crud->hasAccessOrFail('list');

        // $this->listCardComponents();

        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? mb_ucfirst($this->crud->entity_name_plural);
        $this->data['title_modal_create'] = trans('backpack::crud.asset.title_modal_create');
        $this->data['title_modal_edit'] = trans('backpack::crud.asset.title_modal_edit');
        $this->data['title_modal_delete'] = trans('backpack::crud.asset.title_modal_delete');

        $breadcrumbs = [
            trans('backpack::crud.menu.finance_report') => backpack_url('finance-report'),
            trans('backpack::crud.menu.asset') => backpack_url($this->crud->route)
        ];
        $this->data['breadcrumbs'] = $breadcrumbs;

        $this->data['cards'] = $this->card;
        $this->data['modals'] = $this->modal;
        $this->data['scripts'] = $this->script;
        $list = "crud::list-custom" ?? $this->crud->getListView();
        return view($list, $this->data);
    }

    protected function setupListOperation(){
        CRUD::disableResponsiveTable();

        CRUD::addButtonFromView('top', 'filter-year-asset', 'filter-year-asset', 'beginning');

        $settings = Setting::first();
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
            'label' => trans('backpack::crud.asset.column.account_id'),
            'type'      => 'select',
            'name'      => 'account_id', // the column that contains the ID of that connected entity;
            'entity'    => 'account', // the method that defines the relationship in your Model
            'attribute' => 'name', // foreign key attribute that is shown to user
            'model'     => "App\Models\Account", // foreign key model
            // OPTIONAL
            // 'limit' => 32, // Limit the number of characters shown
        ]);

        CRUD::column([
            // 1-n relationship
            'label' => trans('backpack::crud.asset.column.depreciation_account_id'),
            'type'      => 'select',
            'name'      => 'depreciation_account_id', // the column that contains the ID of that connected entity;
            'entity'    => 'account_depreciation', // the method that defines the relationship in your Model
            'attribute' => 'name', // foreign key attribute that is shown to user
            'model'     => "App\Models\Account", // foreign key model
            // OPTIONAL
            // 'limit' => 32, // Limit the number of characters shown
        ]);

        CRUD::column([
            // 1-n relationship
            'label' => trans('backpack::crud.asset.column.expense_account_id'),
            'type'      => 'select',
            'name'      => 'expense_account_id', // the column that contains the ID of that connected entity;
            'entity'    => 'account_expense', // the method that defines the relationship in your Model
            'attribute' => 'name', // foreign key attribute that is shown to user
            'model'     => "App\Models\Account", // foreign key model
            // OPTIONAL
            // 'limit' => 32, // Limit the number of characters shown
        ]);

        CRUD::column(
            [
                'label'  => trans('backpack::crud.asset.column.description'),
                'name' => 'description',
                'type'  => 'text'
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.asset.column.year_acquisition'),
                'name' => 'year_acquisition',
                'type'  => 'date',
                'format' => 'MMM Y'
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.asset.column.price_acquisition'),
                'name' => 'price_acquisition',
                'type'  => 'number',
                'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : 'Rp.',
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.asset.column.economic_age'),
                'name' => 'economic_age',
                'type'  => 'text'
            ],
        );

        CRUD::addColumn([
            'label'  => trans('backpack::crud.asset.column.tarif'),
            'name' => 'tarif',
            'type'  => 'text',
            'suffix' => '%',
        ]);

        CRUD::column(
            [
                'label'  => trans('backpack::crud.asset.column.price_rate_per_year'),
                'name' => 'price_rate_per_year',
                'type'  => 'number',
                'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : 'Rp.',
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.asset.column.price_rate_year_ago'),
                'name' => 'price_rate_year_ago',
                'type'  => 'number',
                'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : 'Rp.',
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.asset.column.accumulated_until_december_last_year'),
                'name' => 'accumulated_until_december_last_year',
                'type'  => 'number',
                'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : 'Rp.',
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.asset.column.book_value_last_december'),
                'name' => 'book_value_last_december',
                'type'  => 'number',
                'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : 'Rp.',
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.asset.column.this_year_depreciation_rate'),
                'name' => 'this_year_depreciation_rate',
                'type'  => 'number',
                'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : 'Rp.',
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.asset.column.accumulated_until_december_this_year'),
                'name' => 'accumulated_until_december_this_year',
                'type'  => 'number',
                'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : 'Rp.',
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.asset.column.book_value_this_december'),
                'name' => 'book_value_this_december',
                'type'  => 'number',
                'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : 'Rp.',
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
            ],
        );

        if(request()->has('filter_year')){
            $filter_year = request()->get('filter_year');
            if($filter_year != 'all'){
                $this->crud->query = $this->crud->query
                ->where(DB::raw("YEAR(year_acquisition)"), $filter_year);
            }
        }

    }

    function ruleAsset(){
        return [
            'account_id' => 'required|exists:accounts,id',
            'depreciation_account_id' => 'required|exists:accounts,id',
            'expense_account_id' => 'required|exists:accounts,id',
            'description' => 'required|max:150',
            'year_acquisition' => 'required',
            'price_acquisition' => 'required|numeric|min:1000',
            'economic_age' => 'required|numeric',
        ];
    }

    public function select2account()
    {
        $this->crud->hasAccessOrFail('create');

        $search = request()->input('q');
        $dataset = \App\Models\Account::select(['id', 'name']);

        if(request()->has('type')){
            if(request()->get('type') == 'beban'){
                $dataset = $dataset->where('type', 'Expense');
            }
        }else{
            $dataset = $dataset->where('type', 'Assets')
            ->where('code', 'LIKE', "105%");
        }
        $dataset = $dataset
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

    protected function setupCreateOperation()
    {
        CRUD::setValidation($this->ruleAsset());
        $settings = Setting::first();
        CRUD::field([   // 1-n relationship
            'label'       => trans('backpack::crud.asset.field.account_id.label'), // Table column heading
            'type'        => "select2_ajax_custom",
            'name'        => 'account_id', // the column that contains the ID of that connected entity
            'entity'      => 'account', // the method that defines the relationship in your Model
            'attribute'   => "name", // foreign key attribute that is shown to user
            'data_source' => backpack_url('finance-report/select2-account-id'), // url to controller search function (with /{id} should return a single entry)
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::field([   // 1-n relationship
            'label'       => trans('backpack::crud.asset.field.account_depreciation.label'), // Table column heading
            'type'        => "select2_ajax_custom",
            'name'        => 'depreciation_account_id', // the column that contains the ID of that connected entity
            'entity'      => 'account_depreciation', // the method that defines the relationship in your Model
            'attribute'   => "name", // foreign key attribute that is shown to user
            'data_source' => backpack_url('finance-report/select2-account-id'), // url to controller search function (with /{id} should return a single entry)
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::field([   // 1-n relationship
            'label'       => trans('backpack::crud.asset.field.expense_account_id.label'), // Table column heading
            'type'        => "select2_ajax_custom",
            'name'        => 'expense_account_id', // the column that contains the ID of that connected entity
            'entity'      => 'account_expense', // the method that defines the relationship in your Model
            'attribute'   => "name", // foreign key attribute that is shown to user
            'data_source' => backpack_url('finance-report/select2-account-id?type=beban'), // url to controller search function (with /{id} should return a single entry)
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::addField([
            'name' => 'description',
            'label' => trans('backpack::crud.asset.column.description'),
            'type' => 'text',
            'attributes' => [
                'placeholder' => trans('backpack::crud.asset.field.description.placeholder'),
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::field([   // date_picker
            'name'  => 'year_acquisition',
            'type'  => 'date_picker',
            'label' => trans('backpack::crud.asset.field.year_acquisition.label'),

            // optional:
            'date_picker_options' => [
                'language' => App::getLocale(),
                'format'   => 'mm-yyyy',
                'startView' => "months",
                'minViewMode' => "months",
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::addField([
            'name' => 'price_acquisition',
            'label' => trans('backpack::crud.asset.field.price_acquisition.label'),
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
            'name' => 'economic_age',
            'label' => trans('backpack::crud.asset.field.economic_age.label'),
            'type' => 'number',
             // optionals
            'attributes' => ["step" => "any"], // allow decimals
            // 'suffix'     => ".00",
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.asset.field.economic_age.placeholder'),
            ]
        ]);

        CRUD::addField([
            'name' => 'tarif',
            'label' => trans('backpack::crud.asset.field.tarif.label'),
            'type' => 'number',
             // optionals
            'attributes' => ["step" => "any"], // allow decimals
            'prefix'     => "%",
            // 'suffix'     => ".00",
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => '0',
                'disabled' => true,
            ]
        ]);

        CRUD::addField([
            'name' => 'price_rate_per_year',
            'label' => trans('backpack::crud.asset.field.price_rate_per_year.label'),
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
                'disabled' => true
            ]
        ]);

        CRUD::addField([
            'name' => 'price_rate_year_ago',
            'label' => trans('backpack::crud.asset.field.price_rate_year_ago.label'),
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
            'name' => 'accumulated_until_december_last_year',
            'label' => trans('backpack::crud.asset.field.accumulated_until_december_last_year.label'),
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
            'name' => 'book_value_last_december',
            'label' => trans('backpack::crud.asset.field.book_value_last_december.label'),
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
            'name' => 'this_year_depreciation_rate',
            'label' => trans('backpack::crud.asset.field.this_year_depreciation_rate.label'),
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
                'disabled' => true
            ]
        ]);


        CRUD::addField([
            'name' => 'accumulated_until_december_this_year',
            'label' => trans('backpack::crud.asset.field.accumulated_until_december_this_year.label'),
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
                'disabled' => true
            ]
        ]);

        CRUD::addField([
            'name' => 'book_value_this_december',
            'label' => trans('backpack::crud.asset.field.book_value_this_december.label'),
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
                'disabled' => true
            ]
        ]);

        CRUD::addField([
            'name' => 'logic_asset',
            'type' => 'logic_asset',
        ]);

    }

    function getInclusiveMonthDiff($fromDateStr)
    {
        $fromDate = Carbon::parse($fromDateStr);
        $toDate = Carbon::create(date('Y'), 12, 1); // Desember tahun sekarang

        return $fromDate->diffInMonths($toDate) + 1; // +1 untuk inklusif
    }

    public function store()
    {
        $this->crud->hasAccessOrFail('create');

        $request = $this->crud->validateRequest();

        $this->crud->registerFieldEvents();

        DB::beginTransaction();
        try{

            $price_acquisition = (float) str_replace('.', '', $request->price_acquisition); // contoh: 1000000
            $economic_age = (int) $request->economic_age; // contoh: 5
            $year_acquisition = $request->year_acquisition; // contoh: '2022-03-01'

            $period = $this->getInclusiveMonthDiff($year_acquisition);

            $penyusutan_per_tahun = $economic_age == 0 ? 0 : ($price_acquisition / $economic_age);

            $tarif = $price_acquisition == 0 ? 0 : ($penyusutan_per_tahun / $price_acquisition) * 100;

            $tarif_penyusutan_tahun_ini = $price_acquisition - $penyusutan_per_tahun;

            $akumulasi_desember_tahun_ini = round(($penyusutan_per_tahun / 12 * $period) / 10) * 10;

            $nilai_buku_desember = $price_acquisition - $akumulasi_desember_tahun_ini;

            $aset = new Asset;
            $aset->account_id = $request->account_id;
            $aset->depreciation_account_id = $request->depreciation_account_id;
            $aset->expense_account_id = $request->expense_account_id;
            $aset->description = $request->description;
            $aset->year_acquisition = $request->year_acquisition;
            $aset->price_acquisition = $request->price_acquisition;
            $aset->economic_age = $request->economic_age;
            $aset->tarif = $tarif;
            $aset->price_rate_per_year = $penyusutan_per_tahun;
            $aset->price_rate_year_ago = $request->price_rate_year_ago;
            $aset->accumulated_until_december_last_year = $request->accumulated_until_december_last_year;
            $aset->book_value_last_december = $request->book_value_last_december;
            $aset->this_year_depreciation_rate = $tarif_penyusutan_tahun_ini;
            $aset->accumulated_until_december_this_year = $akumulasi_desember_tahun_ini;
            $aset->book_value_this_december = $nilai_buku_desember;
            $aset->save();

            CustomHelper::updateOrCreateJournalEntry([
                'account_id' => $aset->account_id,
                'reference_id' => $aset->id,
                'reference_type' => Asset::class,
                'description' => 'FIRST BALANCE',
                'date' => Carbon::now(),
                'debit' => $aset->price_acquisition,
                // 'credit' => ($status == CastAccount::OUT) ? $nominal_transaction : 0,
            ], [
                'account_id' => $aset->account_id,
                'reference_id' => $aset->id,
                'reference_type' => Asset::class,
            ]);

            CustomHelper::updateOrCreateJournalEntry([
                'account_id' => $aset->depreciation_account_id,
                'reference_id' => $aset->id,
                'reference_type' => Asset::class,
                'description' => 'FIRST BALANCE',
                'date' => Carbon::now(),
                'debit' => $aset->price_rate_per_year,
                // 'credit' => ($status == CastAccount::OUT) ? $nominal_transaction : 0,
            ], [
                'account_id' => $aset->depreciation_account_id,
                'reference_id' => $aset->id,
                'reference_type' => Asset::class,
            ]);

            CustomHelper::updateOrCreateJournalEntry([
                'account_id' => $aset->expense_account_id,
                'reference_id' => $aset->id,
                'reference_type' => Asset::class,
                'description' => 'FIRST BALANCE',
                'date' => Carbon::now(),
                'debit' => $aset->book_value_this_december,
                // 'credit' => ($status == CastAccount::OUT) ? $nominal_transaction : 0,
            ], [
                'account_id' => $aset->expense_account_id,
                'reference_id' => $aset->id,
                'reference_type' => Asset::class,
            ]);


            $item = $aset;
            $this->data['entry'] = $this->crud->entry = $item;

            \Alert::success(trans('backpack::crud.insert_success'))->flash();

            $this->crud->setSaveAction();

            DB::commit();
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $item,
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

            $price_acquisition = (float) str_replace('.', '', $request->price_acquisition); // contoh: 1000000
            $economic_age = (int) $request->economic_age; // contoh: 5
            $year_acquisition = $request->year_acquisition; // contoh: '2022-03-01'

            $period = $this->getInclusiveMonthDiff($year_acquisition);

            $penyusutan_per_tahun = $economic_age == 0 ? 0 : ($price_acquisition / $economic_age);

            $tarif = $price_acquisition == 0 ? 0 : ($penyusutan_per_tahun / $price_acquisition) * 100;

            $tarif_penyusutan_tahun_ini = $price_acquisition - $penyusutan_per_tahun;

            $akumulasi_desember_tahun_ini = round(($penyusutan_per_tahun / 12 * $period) / 10) * 10;

            $nilai_buku_desember = $price_acquisition - $akumulasi_desember_tahun_ini;

            $aset = Asset::where('id', $request->id)->first();
            $aset->account_id = $request->account_id;
            $aset->depreciation_account_id = $request->depreciation_account_id;
            $aset->expense_account_id = $request->expense_account_id;
            $aset->description = $request->description;
            $aset->year_acquisition = $request->year_acquisition;
            $aset->price_acquisition = $request->price_acquisition;
            $aset->economic_age = $request->economic_age;
            $aset->tarif = $tarif;
            $aset->price_rate_per_year = $penyusutan_per_tahun;
            $aset->price_rate_year_ago = $request->price_rate_year_ago;
            $aset->accumulated_until_december_last_year = $request->accumulated_until_december_last_year;
            $aset->book_value_last_december = $request->book_value_last_december;
            $aset->this_year_depreciation_rate = $tarif_penyusutan_tahun_ini;
            $aset->accumulated_until_december_this_year = $akumulasi_desember_tahun_ini;
            $aset->book_value_this_december = $nilai_buku_desember;
            $aset->save();

            JournalEntry::where('reference_id', $request->id)
            ->where('reference_type', Asset::class)->delete();

            CustomHelper::updateOrCreateJournalEntry([
                'account_id' => $aset->account_id,
                'reference_id' => $aset->id,
                'reference_type' => Asset::class,
                'description' => 'FIRST BALANCE',
                'date' => Carbon::now(),
                'debit' => $aset->price_acquisition,
                // 'credit' => ($status == CastAccount::OUT) ? $nominal_transaction : 0,
            ], [
                'account_id' => $aset->account_id,
                'reference_id' => $aset->id,
                'reference_type' => Asset::class,
            ]);

            CustomHelper::updateOrCreateJournalEntry([
                'account_id' => $aset->depreciation_account_id,
                'reference_id' => $aset->id,
                'reference_type' => Asset::class,
                'description' => 'FIRST BALANCE',
                'date' => Carbon::now(),
                'debit' => $aset->price_rate_per_year,
                // 'credit' => ($status == CastAccount::OUT) ? $nominal_transaction : 0,
            ], [
                'account_id' => $aset->depreciation_account_id,
                'reference_id' => $aset->id,
                'reference_type' => Asset::class,
            ]);

            CustomHelper::updateOrCreateJournalEntry([
                'account_id' => $aset->expense_account_id,
                'reference_id' => $aset->id,
                'reference_type' => Asset::class,
                'description' => 'FIRST BALANCE',
                'date' => Carbon::now(),
                'debit' => $aset->book_value_this_december,
                // 'credit' => ($status == CastAccount::OUT) ? $nominal_transaction : 0,
            ], [
                'account_id' => $aset->expense_account_id,
                'reference_id' => $aset->id,
                'reference_type' => Asset::class,
            ]);


            $this->data['entry'] = $this->crud->entry = $aset;


            \Alert::success(trans('backpack::crud.update_success'))->flash();


            $this->crud->setSaveAction();

            DB::commit();
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $aset,
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

    protected function setupShowOperation(){
        $this->setupCreateOperation();
        CRUD::field('logic_asset')->remove();

        $this->setupListOperation();
        CRUD::column('row_number')->remove();
    }

    // public function destroy($id)
    // {
    //     $this->crud->hasAccessOrFail('delete');

    //     // get entry ID from Request (makes sure its the last ID for nested resources)
    //     $id = $this->crud->getCurrentEntryId() ?? $id;

    //     return $this->crud->delete($id);
    // }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $this->crud->hasAccessOrFail('delete');
            $item = $this->crud->model::findOrFail($id);
            JournalEntry::where('reference_id', $id)
            ->where('reference_type', Asset::class)->delete();
            $delete = $item->delete();
            DB::commit();
            return $delete;
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'type' => 'errors',
                'message' => $e->getMessage()
            ], 500);
        }
    }


}
