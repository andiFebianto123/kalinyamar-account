<?php
namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Spk;
use App\Models\User;
use App\Models\Setting;

use App\Models\Voucher;
use App\Models\Approval;
use App\Models\PurchaseOrder;
use App\Models\PaymentVoucher;
use Faker\Provider\ar_EG\Payment;
use App\Http\Helpers\CustomHelper;
use App\Models\PaymentVoucherPlan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class VoucherPaymentCrudController extends CrudController {
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        $this->crud->denyAllAccess(['create', 'update', 'delete', 'list', 'show']);
        CRUD::setModel(PaymentVoucher::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/fa/voucher-payment');
        CRUD::setEntityNameStrings(trans('backpack::crud.voucher_payment.title_header'), trans('backpack::crud.voucher_payment.title_header'));
        $user = backpack_user();
        $permissions = $user->getAllPermissions();
        if($permissions->whereIn('name', [
            'AKSES SEMUA VIEW ACCOUNTING',
            'AKSES SEMUA MENU ACCOUNTING',
            'AKSES MENU FA'
        ])->count() > 0)
        {
            $this->crud->allowAccess(['list', 'show']);
        }

        if($permissions->whereIn('name',[
            'AKSES SEMUA MENU ACCOUNTING',
            'AKSES MENU FA'
        ])->count() > 0){
            $this->crud->allowAccess(['create', 'update', 'delete']);
        }
    }

    function total_voucher(){
        $total_voucher_data_non_rutin = Voucher::leftJoin('payment_vouchers', 'payment_vouchers.voucher_id', '=', 'vouchers.id')
        ->where('payment_vouchers.payment_type', 'NON RUTIN')
        ->groupBy('payment_vouchers.payment_type')
        ->select(DB::raw('SUM(payment_transfer) as jumlah_nilai_transfer'))
        ->first();

        // pisah

        $p_v_p = DB::table('payment_voucher_plan')
        ->select(DB::raw('MAX(id) as id'), 'payment_voucher_id')
        ->groupBy('payment_voucher_id');
        $total_voucher_plan_data_non_rutin = new PaymentVoucher();
        $total_voucher_plan_data_non_rutin = $total_voucher_plan_data_non_rutin
        ->leftJoin('vouchers', 'vouchers.id', '=', 'payment_vouchers.voucher_id')
            ->leftJoinSub($p_v_p, 'p_v_p', function ($join) {
                $join->on('p_v_p.payment_voucher_id', '=', 'payment_vouchers.id');
            })
            ->leftJoin('payment_voucher_plan', 'payment_voucher_plan.id', '=', 'p_v_p.id');

        $a_p = DB::table('approvals')
        ->select(DB::raw('MAX(id) as id'), 'model_type', 'model_id')
        ->groupBy('model_type', 'model_id');

        $total_voucher_plan_data_non_rutin = $total_voucher_plan_data_non_rutin
        ->leftJoinSub($a_p, 'a_p', function ($join) {
            $join->on('a_p.model_id', '=', 'payment_voucher_plan.id')
            ->where('a_p.model_type', '=', DB::raw('"App\\\\Models\\\\PaymentVoucherPlan"'));
        })
        ->leftJoin('approvals', 'approvals.id', '=', 'a_p.id')
        ->where('payment_vouchers.payment_type', 'NON RUTIN')
        ->where('approvals.status', Approval::APPROVED)
        ->select(DB::raw('SUM(vouchers.payment_transfer) as jumlah_nilai_transfer'))
        ->first();

        $total_voucher_data_rutin = Voucher::leftJoin('payment_vouchers', 'payment_vouchers.voucher_id', '=', 'vouchers.id')
        ->where('payment_vouchers.payment_type', 'RUTIN')
        ->groupBy('payment_vouchers.payment_type')
        ->select(DB::raw('SUM(payment_transfer) as jumlah_nilai_transfer'))
        ->first();

        $p_v_p = DB::table('payment_voucher_plan')
        ->select(DB::raw('MAX(id) as id'), 'payment_voucher_id')
        ->groupBy('payment_voucher_id');
        $total_voucher_plan_data_rutin = new PaymentVoucher();
        $total_voucher_plan_data_rutin = $total_voucher_plan_data_rutin
        ->leftJoin('vouchers', 'vouchers.id', '=', 'payment_vouchers.voucher_id')
            ->leftJoinSub($p_v_p, 'p_v_p', function ($join) {
                $join->on('p_v_p.payment_voucher_id', '=', 'payment_vouchers.id');
            })
            ->leftJoin('payment_voucher_plan', 'payment_voucher_plan.id', '=', 'p_v_p.id');

        $a_p = DB::table('approvals')
        ->select(DB::raw('MAX(id) as id'), 'model_type', 'model_id')
        ->groupBy('model_type', 'model_id');

        $total_voucher_plan_data_rutin = $total_voucher_plan_data_rutin
        ->leftJoinSub($a_p, 'a_p', function ($join) {
            $join->on('a_p.model_id', '=', 'payment_voucher_plan.id')
            ->where('a_p.model_type', '=', DB::raw('"App\\\\Models\\\\PaymentVoucherPlan"'));
        })
        ->leftJoin('approvals', 'approvals.id', '=', 'a_p.id')
        ->where('payment_vouchers.payment_type', 'RUTIN')
        ->where('approvals.status', Approval::APPROVED)
        ->select(DB::raw('SUM(vouchers.payment_transfer) as jumlah_nilai_transfer'))
        ->first();

        return response()->json([
            'voucher_payment_non_rutin_total' => CustomHelper::formatRupiah(($total_voucher_data_non_rutin != null) ? $total_voucher_data_non_rutin->jumlah_nilai_transfer : 0),
            'voucher_payment_plan_non_rutin_total' => CustomHelper::formatRupiah(($total_voucher_plan_data_non_rutin != null) ? $total_voucher_plan_data_non_rutin->jumlah_nilai_transfer : 0),
            'voucher_payment_rutin_total' => CustomHelper::formatRupiah(($total_voucher_data_rutin != null) ? $total_voucher_data_rutin->jumlah_nilai_transfer : 0),
            'voucher_payment_plan_rutin_total' => CustomHelper::formatRupiah(($total_voucher_plan_data_rutin != null) ? $total_voucher_plan_data_rutin->jumlah_nilai_transfer : 0),
        ]);

    }

    function index(){
        $this->crud->hasAccessOrFail('list');

        $this->card->addCard([
            'name' => 'payment_non_rutin',
            'line' => 'top',
            'view' => 'crud::components.card-tab',
            'title' => 'Non Rutin',
            'params' => [
                'tabs' => [
                    [
                        'name' => 'voucher_payment_non_rutin',
                        'label' => trans('backpack::crud.voucher_payment.tab.title_voucher_payment'),
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
                                    'type'      => 'text',
                                    'name'      => 'date_voucher',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.bussines_entity_name.label'),
                                    'type'      => 'text',
                                    'name'      => 'bussines_entity_name',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.bill_date.label'),
                                    'type'      => 'text',
                                    'name'      => 'bill_date',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.no_po_spk.label'),
                                    'type'      => 'text',
                                    'name'      => 'no_po_spk',
                                    'orderable' => false,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.payment_transfer.label_2'),
                                    'type'      => 'text',
                                    'name'      => 'payment_transfer',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.due_date.label_2'),
                                    'type'      => 'text',
                                    'name'      => 'due_date',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.factur_status.label'),
                                    'type'      => 'text',
                                    'name'      => 'factur_status',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.due_date.label'),
                                    'type'      => 'text',
                                    'name'      => 'due_date_payment',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.payment_status.label'),
                                    'type'      => 'text',
                                    'name'      => 'payment_status',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.approved_at.label'),
                                    'type'      => 'text',
                                    'name'      => 'approved_at',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.status.label'),
                                    'type'      => 'text',
                                    'name'      => 'status',
                                    'orderable' => false,
                                ],
                                [
                                    'name' => 'action',
                                    'type' => 'action',
                                    'label' =>  '',
                                ]
                            ],
                            'route' => backpack_url('/fa/voucher-payment/search?tab=voucher_payment&type=NON RUTIN'),
                        ]
                    ],
                    [
                        'name' => 'voucher_payment_plan_non_rutin',
                        'label' => trans('backpack::crud.voucher_payment.tab.title_voucher_payment_plan'),
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
                                    'type'      => 'text',
                                    'name'      => 'date_voucher',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.bussines_entity_name.label'),
                                    'type'      => 'text',
                                    'name'      => 'bussines_entity_name',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.bill_date.label'),
                                    'type'      => 'text',
                                    'name'      => 'bill_date',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.no_po_spk.label'),
                                    'type'      => 'text',
                                    'name'      => 'no_po_spk',
                                    'orderable' => false,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.payment_transfer.label_2'),
                                    'type'      => 'text',
                                    'name'      => 'payment_transfer',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.due_date.label_2'),
                                    'type'      => 'text',
                                    'name'      => 'due_date',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.factur_status.label'),
                                    'type'      => 'text',
                                    'name'      => 'factur_status',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.due_date.label'),
                                    'type'      => 'text',
                                    'name'      => 'due_date_payment',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.payment_status.label'),
                                    'type'      => 'text',
                                    'name'      => 'payment_status',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.approved_at.label'),
                                    'type'      => 'text',
                                    'name'      => 'approved_at',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.status.label'),
                                    'type'      => 'text',
                                    'name'      => 'status',
                                    'orderable' => false,
                                ],
                                [
                                    'name' => 'action',
                                    'type' => 'action',
                                    'label' =>  '',
                                ]
                            ],
                            'route' => backpack_url('/fa/voucher-payment/search?tab=voucher_payment_plan&type=NON RUTIN'),
                        ]
                    ]
                ]
            ]
        ]);

        $this->card->addCard([
            'name' => 'payment_rutin',
            'line' => 'top',
            'view' => 'crud::components.card-tab',
            'title' => 'Subkon',
            'params' => [
                'tabs' => [
                    [
                        'name' => 'voucher_payment_rutin',
                        'label' => trans('backpack::crud.voucher_payment.tab.title_voucher_payment'),
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
                                    'type'      => 'text',
                                    'name'      => 'date_voucher',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.bussines_entity_name.label'),
                                    'type'      => 'text',
                                    'name'      => 'bussines_entity_name',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.bill_date.label'),
                                    'type'      => 'text',
                                    'name'      => 'bill_date',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.no_po_spk.label'),
                                    'type'      => 'text',
                                    'name'      => 'no_po_spk',
                                    'orderable' => false,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.payment_transfer.label_2'),
                                    'type'      => 'text',
                                    'name'      => 'payment_transfer',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.due_date.label_2'),
                                    'type'      => 'text',
                                    'name'      => 'due_date',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.factur_status.label'),
                                    'type'      => 'text',
                                    'name'      => 'factur_status',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.due_date.label'),
                                    'type'      => 'text',
                                    'name'      => 'due_date_payment',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.payment_status.label'),
                                    'type'      => 'text',
                                    'name'      => 'payment_status',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.approved_at.label'),
                                    'type'      => 'text',
                                    'name'      => 'approved_at',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.status.label'),
                                    'type'      => 'text',
                                    'name'      => 'status',
                                    'orderable' => false,
                                ],
                                [
                                    'name' => 'action',
                                    'type' => 'action',
                                    'label' =>  '',
                                ]
                            ],
                            'route' => backpack_url('/fa/voucher-payment/search?tab=voucher_payment&type=RUTIN'),
                        ]
                    ],
                    [
                        'name' => 'voucher_payment_plan_rutin',
                        'label' => trans('backpack::crud.voucher_payment.tab.title_voucher_payment_plan'),
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
                                    'type'      => 'text',
                                    'name'      => 'date_voucher',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.bussines_entity_name.label'),
                                    'type'      => 'text',
                                    'name'      => 'bussines_entity_name',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.bill_date.label'),
                                    'type'      => 'text',
                                    'name'      => 'bill_date',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.no_po_spk.label'),
                                    'type'      => 'text',
                                    'name'      => 'no_po_spk',
                                    'orderable' => false,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.payment_transfer.label_2'),
                                    'type'      => 'text',
                                    'name'      => 'payment_transfer',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.due_date.label_2'),
                                    'type'      => 'text',
                                    'name'      => 'due_date',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.factur_status.label'),
                                    'type'      => 'text',
                                    'name'      => 'factur_status',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.due_date.label'),
                                    'type'      => 'text',
                                    'name'      => 'due_date_payment',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.payment_status.label'),
                                    'type'      => 'text',
                                    'name'      => 'payment_status',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.approved_at.label'),
                                    'type'      => 'text',
                                    'name'      => 'approved_at',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.status.label'),
                                    'type'      => 'text',
                                    'name'      => 'status',
                                    'orderable' => false,
                                ],
                                [
                                    'name' => 'action',
                                    'type' => 'action',
                                    'label' =>  '',
                                ]
                            ],
                            'route' => backpack_url('/fa/voucher-payment/search?tab=voucher_payment_plan&type=RUTIN'),
                        ]
                    ]
                ]
            ]
        ]);

        $this->card->addCard([
            'name' => 'voucher-payment-plugin',
            'line' => 'top',
            'view' => 'crud::components.voucher-payment-plugin',
            'parent_view' => 'crud::components.filter-parent',
            'params' => [],
        ]);

        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? mb_ucfirst($this->crud->entity_name_plural);
        $this->data['title_modal_create'] = trans('backpack::crud.voucher_payment.title_modal_create');
        $this->data['title_modal_edit'] = trans('backpack::crud.voucher_payment.title_modal_edit');
        $this->data['title_modal_delete'] = trans('backpack::crud.voucher_payment.title_modal_delete');
        $this->data['cards'] = $this->card;

        $breadcrumbs = [
            'FA' => backpack_url('fa'),
            trans('backpack::crud.voucher_payment.title_header') => backpack_url($this->crud->route)
        ];

        $this->data['breadcrumbs'] = $breadcrumbs;

        $list = "crud::list-blank" ?? $this->crud->getListView();
        return view($list, $this->data);

    }

    protected function setupListOperation()
    {
        $tab = request()->tab;
        $type = request()->type;

        $settings = Setting::first();

        if($tab == 'voucher_payment' && $type == 'NON RUTIN'){
            CRUD::setModel(PaymentVoucher::class);
            CRUD::disableResponsiveTable();

            $user_id = backpack_user()->id;
            $user_approval = \App\Models\User::permission(['APPROVE RENCANA BAYAR'])
            ->where('id', $user_id)
            ->get();

            $p_v_p = DB::table('payment_voucher_plan')
            ->select(DB::raw('MAX(id) as id'), 'payment_voucher_id')
            ->groupBy('payment_voucher_id');

            $this->crud->query = $this->crud->query
            ->leftJoin('vouchers', 'vouchers.id', '=', 'payment_vouchers.voucher_id')
            ->leftJoinSub($p_v_p, 'p_v_p', function ($join) {
                $join->on('p_v_p.payment_voucher_id', '=', 'payment_vouchers.id');
            })
            ->leftJoin('payment_voucher_plan', 'payment_voucher_plan.id', '=', 'p_v_p.id');

            if($user_approval->count() > 0){
                $this->crud->query = $this->crud->query
                ->leftJoin('approvals', function ($join) use($user_id){
                    $join->on('approvals.model_id', '=', 'payment_voucher_plan.id')
                        ->where('approvals.model_type', 'App\\Models\\PaymentVoucherPlan')
                        ->where('approvals.user_id', $user_id);
                });
            }else{
                $a_p = DB::table('approvals')
                ->select(DB::raw('MAX(id) as id'), 'model_type', 'model_id')
                ->groupBy('model_type', 'model_id');

                $this->crud->query = $this->crud->query
                ->leftJoinSub($a_p, 'a_p', function ($join) {
                    $join->on('a_p.model_id', '=', 'payment_voucher_plan.id')
                    ->where('a_p.model_type', '=', DB::raw('"App\\\\Models\\\\PaymentVoucherPlan"'));
                })
                ->leftJoin('approvals', 'approvals.id', '=', 'a_p.id');
            }

            $this->crud->query = $this->crud->query
            ->leftJoin('spk', function($join){
                $join->on('spk.id', '=', 'vouchers.reference_id')
                ->where('vouchers.reference_type', '=', DB::raw('"App\\\\Models\\\\Spk"'));
            })
            ->leftJoin('purchase_orders', function($join){
                $join->on('purchase_orders.id', '=', 'vouchers.reference_id')
                ->where('vouchers.reference_type', '=', DB::raw('"App\\\\Models\\\\PurchaseOrder"'));
            })
            ->where('payment_vouchers.payment_type', 'NON RUTIN');

            CRUD::addClause('select', [
                DB::raw("
                    vouchers.*,
                    spk.no_spk as spk_no,
                    purchase_orders.po_number as po_no,
                    approvals.approved_at as approval_approved_at,
                    approvals.status as approval_status,
                    approvals.user_id as approval_user_id,
                    approvals.no_apprv as approval_no_apprv,
                    payment_voucher_plan.id as voucer_edit_id
                ")
            ]);

            $request = request();

            if(trim($request->columns[1]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('no_voucher', 'like', '%'.$request->columns[1]['search']['value'].'%');
            }

            if(trim($request->columns[2]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('date_voucher', 'like', '%'.$request->columns[2]['search']['value'].'%');
            }

            if(trim($request->columns[3]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('bussines_entity_name', 'like', '%'.$request->columns[3]['search']['value'].'%');
            }

            if(trim($request->columns[4]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('bill_date', 'like', '%'.$request->columns[4]['search']['value'].'%');
            }

            if(trim($request->columns[5]['search']['value']) != ''){
                $search = $request->columns[5]['search']['value'];
                $this->crud->query = $this->crud->query
                ->where(function($q) use($search){
                    $q->where('spk.no_spk', 'LIKE', "%$search%")
                    ->orWhere('purchase_orders.po_number', 'LIKE', "%$search%");
                });
            }

            if(trim($request->columns[6]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('payment_transfer', 'like', '%'.$request->columns[6]['search']['value'].'%');
            }

            if(trim($request->columns[7]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('due_date', 'like', '%'.$request->columns[7]['search']['value'].'%');
            }

            if(trim($request->columns[8]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('factur_status', 'like', '%'.$request->columns[8]['search']['value'].'%');
            }

            if(trim($request->columns[9]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('due_date', 'like', '%'.$request->columns[9]['search']['value'].'%');
            }

            if(trim($request->columns[10]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('payment_status', 'like', '%'.$request->columns[10]['search']['value'].'%');
            }

            if(trim($request->columns[11]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('approvals.approved_at', 'like', '%'.$request->columns[11]['search']['value'].'%');
            }

            if(trim($request->columns[12]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('approvals.status', 'like', '%'.$request->columns[12]['search']['value'].'%');
            }

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
                'searchLogic' => function ($query, $column, $searchTerm) {

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
                    'name' => 'bussines_entity_name',
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
                    'name' => 'no_po_spk',
                    'type'  => 'closure',
                    'function' => function($entry){
                        return ($entry->spk_no) ? $entry->spk_no : $entry->po_no;
                    }
                ],
            );

            CRUD::column([
                'label'  => '',
                'name' => 'payment_transfer',
                'type'  => 'number',
                'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
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

            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'factur_status',
                    'type'  => 'text'
                ],
            );

            CRUD::column([
                'label'  => '',
                'name' => 'due_date_payment',
                'type'  => 'closure',
                'function' => function($entry){
                    $datePayment = Carbon::parse($entry->due_date)->isoFormat('D MMM Y');
                    return $datePayment;
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
                'name' => 'approval_approved_at',
                'type'  => 'date',
                'format' => 'D MMM Y'
            ]);

            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'status',
                    'type'  => 'approval-voucher',
                ],
            );

            CRUD::column([
                'label'  => '',
                'name' => 'action',
                'type'  => 'closure',
                'function' => function($entry){
                    return '';
                }
            ]);

        }else if($tab == 'voucher_payment_plan' && $type == 'NON RUTIN'){
            CRUD::setModel(PaymentVoucher::class);
            CRUD::disableResponsiveTable();
            CRUD::addButtonFromView('line', 'approve_payment', 'approve_payment', 'end');

            $user_id = backpack_user()->id;
            $user_approval = \App\Models\User::permission(['APPROVE RENCANA BAYAR'])
            ->where('id', $user_id)
            ->get();

            $p_v_p = DB::table('payment_voucher_plan')
            ->select(DB::raw('MAX(id) as id'), 'payment_voucher_id')
            ->groupBy('payment_voucher_id');

            $this->crud->query = $this->crud->query
            ->leftJoin('vouchers', 'vouchers.id', '=', 'payment_vouchers.voucher_id')
            ->leftJoinSub($p_v_p, 'p_v_p', function ($join) {
                $join->on('p_v_p.payment_voucher_id', '=', 'payment_vouchers.id');
            })
            ->leftJoin('payment_voucher_plan', 'payment_voucher_plan.id', '=', 'p_v_p.id');

            if($user_approval->count() > 0){
                $this->crud->query = $this->crud->query
                ->leftJoin('approvals', function ($join) use($user_id){
                    $join->on('approvals.model_id', '=', 'payment_voucher_plan.id')
                        ->where('approvals.model_type', 'App\\Models\\PaymentVoucherPlan')
                        ->where('approvals.user_id', $user_id);
                });
            }else{
                $a_p = DB::table('approvals')
                ->select(DB::raw('MAX(id) as id'), 'model_type', 'model_id')
                ->groupBy('model_type', 'model_id');

                $this->crud->query = $this->crud->query
                ->leftJoinSub($a_p, 'a_p', function ($join) {
                    $join->on('a_p.model_id', '=', 'payment_voucher_plan.id')
                    ->where('a_p.model_type', '=', DB::raw('"App\\\\Models\\\\PaymentVoucherPlan"'));
                })
                ->leftJoin('approvals', 'approvals.id', '=', 'a_p.id');
            }

            $this->crud->query = $this->crud->query
            ->leftJoin('spk', function($join){
                $join->on('spk.id', '=', 'vouchers.reference_id')
                ->where('vouchers.reference_type', '=', DB::raw('"App\\\\Models\\\\Spk"'));
            })
            ->leftJoin('purchase_orders', function($join){
                $join->on('purchase_orders.id', '=', 'vouchers.reference_id')
                ->where('vouchers.reference_type', '=', DB::raw('"App\\\\Models\\\\PurchaseOrder"'));
            })
            ->where('payment_vouchers.payment_type', 'NON RUTIN');

            CRUD::addClause('select', [
                DB::raw("
                    vouchers.*,
                    spk.no_spk as spk_no,
                    purchase_orders.po_number as po_no,
                    approvals.approved_at as approval_approved_at,
                    approvals.status as approval_status,
                    approvals.user_id as approval_user_id,
                    approvals.no_apprv as approval_no_apprv,
                    payment_voucher_plan.id as voucer_edit_id
                ")
            ]);

            $request = request();

            if(trim($request->columns[1]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('no_voucher', 'like', '%'.$request->columns[1]['search']['value'].'%');
            }

            if(trim($request->columns[2]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('date_voucher', 'like', '%'.$request->columns[2]['search']['value'].'%');
            }

            if(trim($request->columns[3]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('bussines_entity_name', 'like', '%'.$request->columns[3]['search']['value'].'%');
            }

            if(trim($request->columns[4]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('bill_date', 'like', '%'.$request->columns[4]['search']['value'].'%');
            }

            if(trim($request->columns[5]['search']['value']) != ''){
                $search = $request->columns[5]['search']['value'];
                $this->crud->query = $this->crud->query
                ->where(function($q) use($search){
                    $q->where('spk.no_spk', 'LIKE', "%$search%")
                    ->orWhere('purchase_orders.po_number', 'LIKE', "%$search%");
                });
            }

            if(trim($request->columns[6]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('payment_transfer', 'like', '%'.$request->columns[6]['search']['value'].'%');
            }

            if(trim($request->columns[7]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('due_date', 'like', '%'.$request->columns[7]['search']['value'].'%');
            }

            if(trim($request->columns[8]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('factur_status', 'like', '%'.$request->columns[8]['search']['value'].'%');
            }

            if(trim($request->columns[9]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('due_date', 'like', '%'.$request->columns[9]['search']['value'].'%');
            }

            if(trim($request->columns[10]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('payment_status', 'like', '%'.$request->columns[10]['search']['value'].'%');
            }

            if(trim($request->columns[11]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('approvals.approved_at', 'like', '%'.$request->columns[11]['search']['value'].'%');
            }

            if(trim($request->columns[12]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('approvals.status', 'like', '%'.$request->columns[12]['search']['value'].'%');
            }

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
                'searchLogic' => function ($query, $column, $searchTerm) {

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
                    'name' => 'bussines_entity_name',
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
                    'name' => 'no_po_spk',
                    'type'  => 'closure',
                    'function' => function($entry){
                        return ($entry->spk_no) ? $entry->spk_no : $entry->po_no;
                    }
                ],
            );

            CRUD::column([
                'label'  => '',
                'name' => 'payment_transfer',
                'type'  => 'number',
                'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
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

            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'factur_status',
                    'type'  => 'text'
                ],
            );

            CRUD::column([
                'label'  => '',
                'name' => 'due_date_payment',
                'type'  => 'closure',
                'function' => function($entry){
                    $datePayment = Carbon::parse($entry->due_date)->isoFormat('D MMM Y');
                    return $datePayment;
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
                'name' => 'approval_approved_at',
                'type'  => 'date',
                'format' => 'D MMM Y'
            ]);

            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'status',
                    'type'  => 'approval-voucher',
                ],
            );

        }else if($tab == 'voucher_payment' && $type == 'RUTIN'){
            CRUD::setModel(PaymentVoucher::class);
            CRUD::disableResponsiveTable();

            $user_id = backpack_user()->id;
            $user_approval = \App\Models\User::permission(['APPROVE RENCANA BAYAR'])
            ->where('id', $user_id)
            ->get();

            $p_v_p = DB::table('payment_voucher_plan')
            ->select(DB::raw('MAX(id) as id'), 'payment_voucher_id')
            ->groupBy('payment_voucher_id');

            $this->crud->query = $this->crud->query
            ->leftJoin('vouchers', 'vouchers.id', '=', 'payment_vouchers.voucher_id')
            ->leftJoinSub($p_v_p, 'p_v_p', function ($join) {
                $join->on('p_v_p.payment_voucher_id', '=', 'payment_vouchers.id');
            })
            ->leftJoin('payment_voucher_plan', 'payment_voucher_plan.id', '=', 'p_v_p.id');

            if($user_approval->count() > 0){
                $this->crud->query = $this->crud->query
                ->leftJoin('approvals', function ($join) use($user_id){
                    $join->on('approvals.model_id', '=', 'payment_voucher_plan.id')
                        ->where('approvals.model_type', 'App\\Models\\PaymentVoucherPlan')
                        ->where('approvals.user_id', $user_id);
                });
            }else{
                $a_p = DB::table('approvals')
                ->select(DB::raw('MAX(id) as id'), 'model_type', 'model_id')
                ->groupBy('model_type', 'model_id');

                $this->crud->query = $this->crud->query
                ->leftJoinSub($a_p, 'a_p', function ($join) {
                    $join->on('a_p.model_id', '=', 'payment_voucher_plan.id')
                    ->where('a_p.model_type', '=', DB::raw('"App\\\\Models\\\\PaymentVoucherPlan"'));
                })
                ->leftJoin('approvals', 'approvals.id', '=', 'a_p.id');
            }

            $this->crud->query = $this->crud->query
            ->leftJoin('spk', function($join){
                $join->on('spk.id', '=', 'vouchers.reference_id')
                ->where('vouchers.reference_type', '=', DB::raw('"App\\\\Models\\\\Spk"'));
            })
            ->leftJoin('purchase_orders', function($join){
                $join->on('purchase_orders.id', '=', 'vouchers.reference_id')
                ->where('vouchers.reference_type', '=', DB::raw('"App\\\\Models\\\\PurchaseOrder"'));
            })
            ->where('payment_vouchers.payment_type', 'RUTIN');

            CRUD::addClause('select', [
                DB::raw("
                    vouchers.*,
                    spk.no_spk as spk_no,
                    purchase_orders.po_number as po_no,
                    approvals.approved_at as approval_approved_at,
                    approvals.status as approval_status,
                    approvals.user_id as approval_user_id,
                    approvals.no_apprv as approval_no_apprv,
                    payment_voucher_plan.id as voucer_edit_id
                ")
            ]);

            $request = request();

            if(trim($request->columns[1]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('no_voucher', 'like', '%'.$request->columns[1]['search']['value'].'%');
            }

            if(trim($request->columns[2]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('date_voucher', 'like', '%'.$request->columns[2]['search']['value'].'%');
            }

            if(trim($request->columns[3]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('bussines_entity_name', 'like', '%'.$request->columns[3]['search']['value'].'%');
            }

            if(trim($request->columns[4]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('bill_date', 'like', '%'.$request->columns[4]['search']['value'].'%');
            }

            if(trim($request->columns[5]['search']['value']) != ''){
                $search = $request->columns[5]['search']['value'];
                $this->crud->query = $this->crud->query
                ->where(function($q) use($search){
                    $q->where('spk.no_spk', 'LIKE', "%$search%")
                    ->orWhere('purchase_orders.po_number', 'LIKE', "%$search%");
                });
            }

            if(trim($request->columns[6]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('payment_transfer', 'like', '%'.$request->columns[6]['search']['value'].'%');
            }

            if(trim($request->columns[7]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('due_date', 'like', '%'.$request->columns[7]['search']['value'].'%');
            }

            if(trim($request->columns[8]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('factur_status', 'like', '%'.$request->columns[8]['search']['value'].'%');
            }

            if(trim($request->columns[9]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('due_date', 'like', '%'.$request->columns[9]['search']['value'].'%');
            }

            if(trim($request->columns[10]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('payment_status', 'like', '%'.$request->columns[10]['search']['value'].'%');
            }

            if(trim($request->columns[11]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('approvals.approved_at', 'like', '%'.$request->columns[11]['search']['value'].'%');
            }

            if(trim($request->columns[12]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('approvals.status', 'like', '%'.$request->columns[12]['search']['value'].'%');
            }

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
                'searchLogic' => function ($query, $column, $searchTerm) {

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
                    'name' => 'bussines_entity_name',
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
                    'name' => 'no_po_spk',
                    'type'  => 'closure',
                    'function' => function($entry){
                        return ($entry->spk_no) ? $entry->spk_no : $entry->po_no;
                    }
                ],
            );

            CRUD::column([
                'label'  => '',
                'name' => 'payment_transfer',
                'type'  => 'number',
                'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
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

            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'factur_status',
                    'type'  => 'text'
                ],
            );

            CRUD::column([
                'label'  => '',
                'name' => 'due_date_payment',
                'type'  => 'closure',
                'function' => function($entry){
                    $datePayment = Carbon::parse($entry->due_date)->isoFormat('D MMM Y');
                    return $datePayment;
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
                'name' => 'approval_approved_at',
                'type'  => 'date',
                'format' => 'D MMM Y'
            ]);

            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'status',
                    'type'  => 'approval-voucher',
                ],
            );

            CRUD::column([
                'label'  => '',
                'name' => 'action',
                'type'  => 'closure',
                'function' => function($entry){
                    return '';
                }
            ]);
        }else if($tab == 'voucher_payment_plan' && $type == 'RUTIN'){
            CRUD::setModel(PaymentVoucher::class);
            CRUD::disableResponsiveTable();
            CRUD::addButtonFromView('line', 'approve_payment', 'approve_payment', 'end');

            $user_id = backpack_user()->id;
            $user_approval = \App\Models\User::permission(['APPROVE RENCANA BAYAR'])
            ->where('id', $user_id)
            ->get();

            $p_v_p = DB::table('payment_voucher_plan')
            ->select(DB::raw('MAX(id) as id'), 'payment_voucher_id')
            ->groupBy('payment_voucher_id');

            $this->crud->query = $this->crud->query
            ->leftJoin('vouchers', 'vouchers.id', '=', 'payment_vouchers.voucher_id')
            ->leftJoinSub($p_v_p, 'p_v_p', function ($join) {
                $join->on('p_v_p.payment_voucher_id', '=', 'payment_vouchers.id');
            })
            ->leftJoin('payment_voucher_plan', 'payment_voucher_plan.id', '=', 'p_v_p.id');

            if($user_approval->count() > 0){
                $this->crud->query = $this->crud->query
                ->leftJoin('approvals', function ($join) use($user_id){
                    $join->on('approvals.model_id', '=', 'payment_voucher_plan.id')
                        ->where('approvals.model_type', 'App\\Models\\PaymentVoucherPlan')
                        ->where('approvals.user_id', $user_id);
                });
            }else{
                $a_p = DB::table('approvals')
                ->select(DB::raw('MAX(id) as id'), 'model_type', 'model_id')
                ->groupBy('model_type', 'model_id');

                $this->crud->query = $this->crud->query
                ->leftJoinSub($a_p, 'a_p', function ($join) {
                    $join->on('a_p.model_id', '=', 'payment_voucher_plan.id')
                    ->where('a_p.model_type', '=', DB::raw('"App\\\\Models\\\\PaymentVoucherPlan"'));
                })
                ->leftJoin('approvals', 'approvals.id', '=', 'a_p.id');
            }

            $this->crud->query = $this->crud->query
            ->leftJoin('spk', function($join){
                $join->on('spk.id', '=', 'vouchers.reference_id')
                ->where('vouchers.reference_type', '=', DB::raw('"App\\\\Models\\\\Spk"'));
            })
            ->leftJoin('purchase_orders', function($join){
                $join->on('purchase_orders.id', '=', 'vouchers.reference_id')
                ->where('vouchers.reference_type', '=', DB::raw('"App\\\\Models\\\\PurchaseOrder"'));
            })
            ->where('payment_vouchers.payment_type', 'RUTIN');

            CRUD::addClause('select', [
                DB::raw("
                    vouchers.*,
                    spk.no_spk as spk_no,
                    purchase_orders.po_number as po_no,
                    approvals.approved_at as approval_approved_at,
                    approvals.status as approval_status,
                    approvals.user_id as approval_user_id,
                    approvals.no_apprv as approval_no_apprv,
                    payment_voucher_plan.id as voucer_edit_id
                ")
            ]);

            $request = request();

            if(trim($request->columns[1]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('no_voucher', 'like', '%'.$request->columns[1]['search']['value'].'%');
            }

            if(trim($request->columns[2]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('date_voucher', 'like', '%'.$request->columns[2]['search']['value'].'%');
            }

            if(trim($request->columns[3]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('bussines_entity_name', 'like', '%'.$request->columns[3]['search']['value'].'%');
            }

            if(trim($request->columns[4]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('bill_date', 'like', '%'.$request->columns[4]['search']['value'].'%');
            }

            if(trim($request->columns[5]['search']['value']) != ''){
                $search = $request->columns[5]['search']['value'];
                $this->crud->query = $this->crud->query
                ->where(function($q) use($search){
                    $q->where('spk.no_spk', 'LIKE', "%$search%")
                    ->orWhere('purchase_orders.po_number', 'LIKE', "%$search%");
                });
            }

            if(trim($request->columns[6]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('payment_transfer', 'like', '%'.$request->columns[6]['search']['value'].'%');
            }

            if(trim($request->columns[7]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('due_date', 'like', '%'.$request->columns[7]['search']['value'].'%');
            }

            if(trim($request->columns[8]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('factur_status', 'like', '%'.$request->columns[8]['search']['value'].'%');
            }

            if(trim($request->columns[9]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('due_date', 'like', '%'.$request->columns[9]['search']['value'].'%');
            }

            if(trim($request->columns[10]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('payment_status', 'like', '%'.$request->columns[10]['search']['value'].'%');
            }

            if(trim($request->columns[11]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('approvals.approved_at', 'like', '%'.$request->columns[11]['search']['value'].'%');
            }

            if(trim($request->columns[12]['search']['value']) != ''){
                $this->crud->query = $this->crud->query
                ->where('approvals.status', 'like', '%'.$request->columns[12]['search']['value'].'%');
            }

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
                'searchLogic' => function ($query, $column, $searchTerm) {

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
                    'name' => 'bussines_entity_name',
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
                    'name' => 'no_po_spk',
                    'type'  => 'closure',
                    'function' => function($entry){
                        return ($entry->spk_no) ? $entry->spk_no : $entry->po_no;
                    }
                ],
            );

            CRUD::column([
                'label'  => '',
                'name' => 'payment_transfer',
                'type'  => 'number',
                'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
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

            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'factur_status',
                    'type'  => 'text'
                ],
            );

            CRUD::column([
                'label'  => '',
                'name' => 'due_date_payment',
                'type'  => 'closure',
                'function' => function($entry){
                    $datePayment = Carbon::parse($entry->due_date)->isoFormat('D MMM Y');
                    return $datePayment;
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
                'name' => 'approval_approved_at',
                'type'  => 'date',
                'format' => 'D MMM Y'
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

    private function ruleValidation(){
        return [
            'voucher' => [
                'required',
                'array',
                'min:1',
                function($attr, $value, $fail){
                    foreach($value as $id_voucher){
                        $payment_voucher = PaymentVoucher::find(request()->id);
                        if($payment_voucher != null){
                            $fail(trans('backpack::crud.voucher_payment.voucher_payment_exists'));
                        }
                    }
                }
            ],
        ];
    }

    protected function setupCreateOperation(){
        CRUD::setValidation($this->ruleValidation());
        $settings = Setting::first();


        $v_e = DB::table('voucher_edit')
            ->select(DB::raw('MAX(id) as id'), 'voucher_id')
            ->groupBy('voucher_id');

        $a_p = DB::table('approvals')
                ->select(DB::raw('MAX(id) as id'), 'model_type', 'model_id')
                ->groupBy('model_type', 'model_id');


        $voucherList = Voucher::leftJoin('accounts', 'accounts.id', '=', 'vouchers.account_id')
        ->leftJoinSub($v_e, 'v_e', function ($join) {
            $join->on('v_e.voucher_id', '=', 'vouchers.id');
        })
        ->leftJoin('voucher_edit', 'voucher_edit.id', '=', 'v_e.id')
        ->leftJoinSub($a_p, 'a_p', function ($join) {
            $join->on('a_p.model_id', '=', 'voucher_edit.id')
            ->where('a_p.model_type', '=', DB::raw('"App\\\\Models\\\\VoucherEdit"'));
        })
        ->leftJoin('approvals', 'approvals.id', '=', 'a_p.id')
        ->whereNotExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('payment_vouchers')
                ->whereColumn('payment_vouchers.voucher_id', 'vouchers.id');
        })
        ->where('approvals.status', Approval::APPROVED)
        ->select(DB::raw("
            vouchers.*,
            accounts.name as account_name,
            accounts.code as account_code,
            voucher_edit.id as voucer_edit_id,
            approvals.status as approval_status,
            approvals.user_id as approval_user_id,
            approvals.no_apprv as approval_no_apprv
        "))
        ->get();

        foreach($voucherList as $list){
            $list->date_voucher_str = Carbon::parse($list->date_voucher)->format('d M Y');
            $list->bill_date_str = Carbon::parse($list->bill_date)->format('d M Y');
            $list->due_date_str = Carbon::parse($list->due_date)->format('d M Y');
            $list->payment_transfer_str = ($settings?->currency_symbol) ? $settings->currency_symbol.' '.CustomHelper::formatRupiah($list->payment_transfer) : "Rp.".CustomHelper::formatRupiah($list->payment_transfer);
            if($list->reference_type == PurchaseOrder::class){
                $data_po_spk = PurchaseOrder::leftJoin('subkons', 'subkons.id', '=', 'purchase_orders.subkon_id')
                ->select(DB::raw("
                    subkons.name as name_company,
                    subkons.bank_name as bank_name,
                    subkons.bank_account as bank_account,
                    purchase_orders.id as id,
                    purchase_orders.po_number as no_po_spk,
                    purchase_orders.date_po as date_po_spk,
                    'po' as type
                "))->where('purchase_orders.id', $list->reference_id)->first();
            }else if($list->reference_type == Spk::class){
                $data_po_spk = Spk::leftJoin('subkons', 'subkons.id', '=', 'spk.subkon_id')
                ->select(DB::raw("
                    subkons.name as name_company,
                    subkons.bank_name as bank_name,
                    subkons.bank_account as bank_account,
                    spk.id as id,
                    spk.no_spk as no_po_spk,
                    spk.date_spk as date_po_spk,
                    'spk' as type
                "))->where('spk.id', $list->reference_id)->first();
            }

            $list->no_po_spk_str = $data_po_spk->no_po_spk;
        }

        CRUD::addField([
            'name' => 'voucher',
            'label' => '',
            'type' => 'voucher-list',
            'value' => $voucherList,
        ]);
    }



    public function store()
    {
        $this->crud->hasAccessOrFail('create');

        $request = $this->crud->validateRequest();

        $this->crud->registerFieldEvents();

        DB::beginTransaction();
        try{

            $event = [];
            $event['crudTable-voucher_payment_plugin_load'] = true;

            $voucher = $request->voucher;
            foreach($voucher as $id_v){
                $voucher = Voucher::find($id_v);
                $type = '';
                if($voucher->payment_type == 'NON RUTIN'){
                    $type = 'NON RUTIN';
                    $event['crudTable-voucher_payment_non_rutin_create_success'] = true;
                    $event['crudTable-voucher_payment_plan_non_rutin_create_success'] = true;
                }else{
                    $type = 'RUTIN';
                    $event['crudTable-voucher_payment_rutin_create_success'] = true;
                    $event['crudTable-voucher_payment_plan_rutin_create_success'] = true;
                }
                $payment_voucher = new PaymentVoucher();
                $payment_voucher->voucher_id = $id_v;
                $payment_voucher->payment_type = $type;
                $payment_voucher->save();

                $payment_voucher_plan = new PaymentVoucherPlan();
                $payment_voucher_plan->payment_voucher_id  = $payment_voucher->id;
                $payment_voucher_plan->save();

                $user_approval = User::permission('APPROVE RENCANA BAYAR')
                ->orderBy('no_order', 'ASC')->get();

                foreach($user_approval as $key => $user){
                    $approval = new Approval;
                    $approval->model_type = PaymentVoucherPlan::class;
                    $approval->model_id = $payment_voucher_plan->id;
                    $approval->no_apprv = $key + 1;
                    $approval->user_id = $user->id;
                    $approval->position = '';
                    $approval->status = Approval::PENDING;
                    $approval->save();
                }
            }

            \Alert::success(trans('backpack::crud.insert_success'))->flash();

            $this->crud->setSaveAction();

            DB::commit();
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $payment_voucher,
                    'events' => $event,
                ]);
            }
            return $this->crud->performSaveAction($payment_voucher->getKey());
        }catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function approvedStore($id){
        $this->crud->hasAccessOrFail('create');

        $request = $this->crud->validateRequest();

        $this->crud->registerFieldEvents();

        DB::beginTransaction();
        try{

            $event = [];

            $event['crudTable-voucher_payment_plugin_load'] = true;

            $user_id = backpack_user()->id;
            $voucher_payment_plan = PaymentVoucherPlan::find($id);
            $voucher_payment = PaymentVoucher::where('id', $voucher_payment_plan->payment_voucher_id)->first();

            if($voucher_payment->payment_type == 'NON RUTIN'){
                $event['crudTable-voucher_payment_non_rutin_create_success'] = true;
                $event['crudTable-voucher_payment_plan_non_rutin_create_success'] = true;
            }else if($voucher_payment->payment_type == 'RUTIN'){
                $event['crudTable-voucher_payment_rutin_create_success'] = true;
                $event['crudTable-voucher_payment_plan_rutin_create_success'] = true;
            }

            $approval = Approval::where('model_type', PaymentVoucherPlan::class)
            ->where('model_id', $voucher_payment_plan->id)
            ->where('user_id', $user_id)
            ->where('no_apprv', $request->no_apprv)
            ->first();

            $final_approval = Approval::where('model_type', PaymentVoucherPlan::class)
            ->where('model_id', $voucher_payment_plan->id)
            ->orderBy('no_apprv', 'DESC')->first();

            $approval->status = $request->action;
            $approval->approved_at = Carbon::now();
            $approval->save();

            $voucher = Voucher::find($voucher_payment->voucher_id);

            if($request->action == Approval::APPROVED){
                if($final_approval->no_apprv == $request->no_apprv){
                    // CustomHelper::updateOrCreateJournalEntry([
                    //     'account_id' => $voucher->account_id,
                    //     'reference_id' => $voucher_payment->id,
                    //     'reference_type' => PaymentVoucher::class,
                    //     'description' => 'FIRST BALANCE',
                    //     'date' => Carbon::now(),
                    //     'debit' => $voucher->payment_transfer,
                    //     // 'credit' => ($status == CastAccount::OUT) ? $nominal_transaction : 0,
                    // ], [
                    //     'account_id' => $voucher->account_id,
                    //     'reference_id' => $voucher_payment->id,
                    //     'reference_type' => PaymentVoucher::class,
                    // ]);
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'data' => $voucher,
                'events' => $event,
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

}
