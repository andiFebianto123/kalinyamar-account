<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Account;
use App\Models\Setting;
use App\Models\Voucher;
use App\Models\Approval;
use App\Models\CastAccount;
use App\Models\InvoiceClient;
use App\Models\PaymentVoucher;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Helpers\CustomVoid;
use App\Http\Exports\ExportExcel;
use App\Http\Helpers\CustomHelper;
use App\Models\AccountTransaction;
use App\Models\PaymentVoucherPlan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\CrudController;
use App\Http\Controllers\Operation\FormaterExport;
use App\Http\Controllers\Operation\PermissionAccess;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class VoucherPaymentPlanCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use PermissionAccess;
    use FormaterExport;

    public function setup()
    {
        CRUD::setModel(PaymentVoucher::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/fa/voucher-payment-plan');
        CRUD::setEntityNameStrings(trans('backpack::crud.menu.voucher_payment_plan'), trans('backpack::crud.menu.voucher_payment_plan'));

        $allAccess = [
            'AKSES SEMUA MENU ACCOUNTING',
            'AKSES MENU FA'
        ];

        $viewMenu = [
            'MENU INDEX FA RENCANA PEMBAYARAN',
        ];

        $this->settingPermission([
            'create' => [
                'CREATE INDEX FA RENCANA PEMBAYARAN',
                'APPROVE RENCANA BAYAR',
                ...$allAccess
            ],
            'update' => [
                'UPDATE INDEX FA RENCANA PEMBAYARAN',
                ...$allAccess
            ],
            'delete' => [
                'DELETE INDEX FA RENCANA PEMBAYARAN',
                ...$allAccess
            ],
            'list' => $viewMenu,
            'show' => $viewMenu,
            'print' => true,
        ]);
    }

    function total_voucher()
    {

        $request = request();
        // pisah
        $p_v_p = DB::table('payment_voucher_plan')
            ->select(DB::raw('MAX(id) as id'), 'payment_voucher_id')
            ->groupBy('payment_voucher_id');

        $total_voucher_plan_data_non_rutin = PaymentVoucher::select(DB::raw('SUM(vouchers.payment_transfer) as jumlah_nilai_transfer'));

        $p_v_p = DB::table('payment_voucher_plan')
            ->select(DB::raw('MAX(id) as id'), 'payment_voucher_id')
            ->groupBy('payment_voucher_id');

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
            ->leftJoin('approvals', 'approvals.id', '=', 'a_p.id');

        $total_voucher_plan_data_non_rutin = $total_voucher_plan_data_non_rutin
            ->leftJoin('spk', function ($join) {
                $join->on('spk.id', '=', 'vouchers.reference_id')
                    ->where('vouchers.reference_type', '=', DB::raw('"App\\\\Models\\\\Spk"'));
            })
            ->leftJoin('purchase_orders', function ($join) {
                $join->on('purchase_orders.id', '=', 'vouchers.reference_id')
                    ->where('vouchers.reference_type', '=', DB::raw('"App\\\\Models\\\\PurchaseOrder"'));
            })
            ->leftJoin('cast_accounts', 'cast_accounts.id', 'vouchers.account_source_id')
            ->where('vouchers.payment_status', 'BELUM BAYAR')
            ->where('approvals.status', Approval::APPROVED);

        if ($request->has('search')) {
            // Kolom 1 - no_voucher
            if (isset($request->search[1]) && trim($request->search[1]) !== '') {
                $search = trim($request->search[1]);
                $total_voucher_plan_data_non_rutin = $total_voucher_plan_data_non_rutin
                    ->where('vouchers.no_voucher', 'like', "%{$search}%");
            }

            // Kolom 2 - subkon.name
            if (isset($request->search[2]) && trim($request->search[2]) !== '') {
                $search = trim($request->search[2]);
                $total_voucher_plan_data_non_rutin = $total_voucher_plan_data_non_rutin
                    ->whereHas('voucher.subkon', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            }

            // Kolom 3 - subkon.bank_name
            if (isset($request->search[3]) && trim($request->search[3]) !== '') {
                $search = trim($request->search[3]);
                $total_voucher_plan_data_non_rutin = $total_voucher_plan_data_non_rutin
                    ->whereHas('voucher.subkon', function ($q) use ($search) {
                        $q->where('bank_name', 'like', "%{$search}%");
                    });
            }

            // Kolom 4 - subkon.bank_account
            if (isset($request->search[4]) && trim($request->search[4]) !== '') {
                $search = trim($request->search[4]);
                $total_voucher_plan_data_non_rutin = $total_voucher_plan_data_non_rutin
                    ->whereHas('voucher.subkon', function ($q) use ($search) {
                        $q->where('bank_account', 'like', "%{$search}%");
                    });
            }

            // Kolom 5 - bill_number
            if (isset($request->search[5]) && trim($request->search[5]) !== '') {
                $search = trim($request->search[5]);
                $total_voucher_plan_data_non_rutin = $total_voucher_plan_data_non_rutin
                    ->where('vouchers.bill_number', 'like', "%{$search}%");
            }

            // Kolom 6 - payment_description
            if (isset($request->search[6]) && trim($request->search[6]) !== '') {
                $search = trim($request->search[6]);
                $total_voucher_plan_data_non_rutin = $total_voucher_plan_data_non_rutin
                    ->where('vouchers.payment_description', 'like', "%{$search}%");
            }

            // Kolom 7 - reference.po_number
            if (isset($request->search[7]) && trim($request->search[7]) !== '') {
                $search = trim($request->search[7]);
                $total_voucher_plan_data_non_rutin = $total_voucher_plan_data_non_rutin
                    ->whereHas('voucher', function ($q) use ($search) {
                        $q->whereHasMorph('reference', '*', function ($query) use ($search) {
                            $query->where('po_number', 'like', "%{$search}%")
                                ->orWhere('no_spk', 'like', '%' . $search . '%');
                        });
                    });
            }

            // Kolom 8 - payment_transfer
            if (isset($request->search[8]) && trim($request->search[8]) !== '') {
                $search = trim($request->search[8]);
                $total_voucher_plan_data_non_rutin = $total_voucher_plan_data_non_rutin
                    ->where('vouchers.payment_transfer', 'like', "%{$search}%");
            }

            // Kolom 9 - factur_status
            if (isset($request->search[9]) && trim($request->search[9]) !== '') {
                $search = trim($request->search[9]);
                $total_voucher_plan_data_non_rutin = $total_voucher_plan_data_non_rutin
                    ->where('vouchers.factur_status', 'like', "{$search}%");
            }

            // Kolom 10 - reference.job_name
            if (isset($request->search[10]) && trim($request->search[10]) !== '') {
                $search = trim($request->search[10]);
                $total_voucher_plan_data_non_rutin = $total_voucher_plan_data_non_rutin
                    ->where('vouchers.job_name', 'like', "%{$search}%");
                // ->whereHas('voucher', function($q) use($search){
                //     $q->whereHasMorph('reference', '*', function ($query) use ($search) {
                //         $query->where('job_name', 'like', "%{$search}%");
                //     });
                // });
            }

            // Kolom 11 - due_date
            if (isset($request->search[11]) && trim($request->search[11]) !== '') {
                $search = trim($request->search[11]);
                $total_voucher_plan_data_non_rutin = $total_voucher_plan_data_non_rutin
                    ->where('vouchers.due_date', 'like', "%{$search}%");
            }

            // Kolom 12 - payment_type
            if (isset($request->search[12]) && trim($request->search[12]) !== '') {
                $search = trim($request->search[12]);
                $total_voucher_plan_data_non_rutin = $total_voucher_plan_data_non_rutin
                    ->where('vouchers.payment_type', 'like', "%{$search}%");
            }
        }

        $total_voucher_plan_data_non_rutin = $total_voucher_plan_data_non_rutin
            ->first();

        $total_voucher_data_rutin = Voucher::leftJoin('payment_vouchers', 'payment_vouchers.voucher_id', '=', 'vouchers.id')
            ->where('payment_vouchers.payment_type', 'SUBKON')
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
            ->where('payment_vouchers.payment_type', 'SUBKON')
            // ->where('approvals.status', Approval::APPROVED)
            ->select(DB::raw('SUM(vouchers.payment_transfer) as jumlah_nilai_transfer'))
            ->first();

        return response()->json([
            'voucher_payment_plan_non_rutin_total' => CustomHelper::formatRupiahWithCurrency(($total_voucher_plan_data_non_rutin != null) ? $total_voucher_plan_data_non_rutin->jumlah_nilai_transfer : 0),
            'voucher_payment_rutin_total' => CustomHelper::formatRupiahWithCurrency(($total_voucher_data_rutin != null) ? $total_voucher_data_rutin->jumlah_nilai_transfer : 0),
            'voucher_payment_plan_rutin_total' => CustomHelper::formatRupiahWithCurrency(($total_voucher_plan_data_rutin != null) ? $total_voucher_plan_data_rutin->jumlah_nilai_transfer : 0),
        ]);
    }

    function index()
    {
        $this->crud->hasAccessOrFail('list');

        $this->card->addCard([
            'name' => 'payment_non_rutin',
            'line' => 'top',
            'view' => 'crud::components.card-tab',
            // 'title' => 'Non Rutin',
            'params' => [
                'tabs' => [
                    [
                        'name' => 'voucher_payment_plan_non_rutin',
                        // 'label' => trans('backpack::crud.voucher_payment.tab.title_voucher_payment_plan'),
                        'label' => 'Data',
                        'view' => 'crud::components.datatable',
                        'active' => true,
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
                                    'label' => trans('backpack::crud.voucher.column.voucher.bussines_entity_name.label'),
                                    'type'      => 'text',
                                    'name'      => 'bussines_entity_name',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.bank.label'),
                                    'type'      => 'text',
                                    'name'      => 'bank',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.number_account.label'),
                                    'type'      => 'text',
                                    'name'      => 'number_account',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.bill_number.label'),
                                    'type'      => 'text',
                                    'name'      => 'bill_number',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.payment_description.label'),
                                    'type'      => 'text',
                                    'name'      => 'payment_description',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.no_po_spk.label'),
                                    'type'      => 'text',
                                    'name'      => 'no_po_spk',
                                    'orderable' => false,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.payment_transfer.label'),
                                    'type'      => 'text',
                                    'name'      => 'payment_transfer',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.factur_status.label'),
                                    'type'      => 'text',
                                    'name'      => 'factur_status',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.job_name.label'),
                                    'type'      => 'text',
                                    'name'      => 'job_name',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.due_date.label_2'),
                                    'type'      => 'text',
                                    'name'      => 'due_date',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.payment_type.label'),
                                    'type'      => 'text',
                                    'name'      => 'payment_type',
                                    'orderable' => true,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.status.label'),
                                    'type'      => 'text',
                                    'name'      => 'status',
                                    'orderable' => false,
                                ],
                                [
                                    'label' => trans('backpack::crud.voucher.column.voucher.user_approval.label'),
                                    'type' => 'text',
                                    'name' => 'user_approval',
                                    'orderable' => false,
                                ],
                                [
                                    'name' => 'action',
                                    'type' => 'action',
                                    'label' =>  '',
                                ]
                            ],
                            'route' => backpack_url('/fa/voucher-payment-plan/search?tab=voucher_payment_plan&type=NON RUTIN'),
                            'route_export_pdf' => url($this->crud->route . '/export-pdf?tab=voucher_payment_plan_all'),
                            'title_export_pdf' => 'Voucher-payment-plan.pdf',
                            'route_export_excel' => url($this->crud->route . '/export-excel?tab=voucher_payment_plan_all'),
                            'title_export_excel' => 'Voucher-payment-plan.xlsx',
                        ]
                    ]
                ]
            ]
        ]);

        $this->card->addCard([
            'name' => 'voucher-payment-plugin',
            'line' => 'top',
            'view' => 'crud::components.voucher-payment-plan-plugin',
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

    protected function setupListOperationOld()
    {
        $tab = request()->tab;
        $type = request()->type;

        $settings = Setting::first();

        CRUD::removeButton('delete');

        CRUD::addButtonFromView('top', 'export-excel', 'export-excel', 'beginning');
        CRUD::addButtonFromView('top', 'export-pdf', 'export-pdf', 'beginning');

        CRUD::addButtonFromView('line', 'delete-payment-plan', 'delete-payment-plan', 'beginning');

        if ($tab == 'voucher_payment' && $type == 'NON RUTIN') {
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

            if ($user_approval->count() > 0) {
                $this->crud->query = $this->crud->query
                    ->leftJoin('approvals', function ($join) use ($user_id) {
                        $join->on('approvals.model_id', '=', 'payment_voucher_plan.id')
                            ->where('approvals.model_type', 'App\\Models\\PaymentVoucherPlan')
                            ->where('approvals.user_id', $user_id);
                    });
            } else {
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
                ->leftJoin('spk', function ($join) {
                    $join->on('spk.id', '=', 'vouchers.reference_id')
                        ->where('vouchers.reference_type', '=', DB::raw('"App\\\\Models\\\\Spk"'));
                })
                ->leftJoin('purchase_orders', function ($join) {
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
                    payment_voucher_plan.id as voucer_edit_id,
                    payment_vouchers.voucher_id
                ")
            ]);

            $request = request();

            if (trim($request->columns[1]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('no_voucher', 'like', '%' . $request->columns[1]['search']['value'] . '%');
            }

            if (trim($request->columns[2]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('date_voucher', 'like', '%' . $request->columns[2]['search']['value'] . '%');
            }

            if (trim($request->columns[3]['search']['value']) != '') {
                $search = $request->columns[3]['search']['value'];
                $this->crud->query = $this->crud->query
                    ->whereHas('voucher.subkon', function ($q) use ($search) {
                        $q->where('name', 'like', "%$search%");
                    });
            }

            if (trim($request->columns[4]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('bill_date', 'like', '%' . $request->columns[4]['search']['value'] . '%');
            }

            if (trim($request->columns[5]['search']['value']) != '') {
                $search = $request->columns[5]['search']['value'];
                $this->crud->query = $this->crud->query
                    ->whereHas('voucher', function ($q) use ($search) {
                        $q->whereHasMorph('reference', '*', function ($query) use ($search) {
                            $query->where('po_number', 'like', '%' . $search . '%');
                        });
                    });
            }

            if (trim($request->columns[6]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('payment_transfer', 'like', '%' . $request->columns[6]['search']['value'] . '%');
            }

            if (trim($request->columns[7]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('due_date', 'like', '%' . $request->columns[7]['search']['value'] . '%');
            }

            if (trim($request->columns[8]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('factur_status', 'like', '%' . $request->columns[8]['search']['value'] . '%');
            }

            if (trim($request->columns[9]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('due_date', 'like', '%' . $request->columns[9]['search']['value'] . '%');
            }

            if (trim($request->columns[10]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('payment_status', 'like', '%' . $request->columns[10]['search']['value'] . '%');
            }

            if (trim($request->columns[11]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('approvals.approved_at', 'like', '%' . $request->columns[11]['search']['value'] . '%');
            }

            if (trim($request->columns[12]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('approvals.status', 'like', '%' . $request->columns[12]['search']['value'] . '%');
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
                'orderLogic' => function ($query, $column, $order) {
                    return $query->orderBy('vouchers.no_voucher', $order);
                }
            ]);

            CRUD::column([
                'label'  => '',
                'name' => 'date_voucher',
                'type'  => 'date',
                'format' => 'D MMM Y',
                'orderLogic' => function ($query, $column, $order) {
                    return $query->orderBy('vouchers.date_voucher', $order);
                }
            ]);

            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'subkon_id',
                    'type'  => 'closure',
                    'function' => function ($entry) {
                        return $entry?->voucher?->subkon?->name;
                    },
                    'orderLogic' => function ($query, $column, $order) {
                        return $query->leftJoin('subkons', 'subkons.id', '=', 'vouchers.subkon_id')
                            ->orderBy('subkons.name', $order);
                    }
                ],
            );

            CRUD::column([
                'label'  => '',
                'name' => 'bill_date',
                'type'  => 'date',
                'format' => 'D MMM Y',
                'orderLogic' => function ($query, $column, $order) {
                    return $query->orderBy('vouchers.bill_date', $order);
                }
            ]);

            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'reference_id',
                    'type'  => 'closure',
                    'function' => function ($entry) {
                        return $entry?->voucher?->reference?->po_number;
                    }
                ], // BELUM FILTER
            );

            CRUD::column([
                'label'  => '',
                'name' => 'payment_transfer',
                'type'  => 'number',
                'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
                'orderLogic' => function ($query, $column, $order) {
                    return $query->orderBy('vouchers.payment_transfer', $order);
                }
            ]);

            CRUD::column([
                'label'  => '',
                'name' => 'due_date',
                'type'  => 'date',
                'format' => 'D MMM Y',
                'orderLogic' => function ($query, $column, $order) {
                    return $query->orderBy('vouchers.due_date', $order);
                }
            ]);

            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'factur_status',
                    'type'  => 'text',
                    'orderLogic' => function ($query, $column, $order) {
                        return $query->orderBy('vouchers.factur_status', $order);
                    }
                ],
            );

            CRUD::column([
                'label'  => '',
                'name' => 'payment_date',
                'type'  => 'date',
                'format' => 'D MMM Y',
                'orderLogic' => function ($query, $column, $order) {
                    return $query->orderBy('vouchers.payment_date', $order);
                }
            ]);

            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'payment_status',
                    'type'  => 'text',
                    'orderLogic' => function ($query, $column, $order) {
                        return $query->orderBy('vouchers.payment_status', $order);
                    }
                ],
            );

            CRUD::column([
                'label'  => '',
                'name' => 'approval_approved_at',
                'type'  => 'date',
                'format' => 'D MMM Y',
                'orderLogic' => function ($query, $column, $order) {
                    return $query->orderBy('approvals.approved_at', $order);
                }
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
                'function' => function ($entry) {
                    return '';
                }
            ]);
        } else if ($tab == 'voucher_payment_plan' && $type == 'NON RUTIN') {
            CRUD::setModel(PaymentVoucher::class);
            CRUD::disableResponsiveTable();
            CRUD::addButtonFromView('line', 'approve_payment', 'approve_payment', 'end');

            $p_v_p = DB::table('payment_voucher_plan')
                ->select(DB::raw('MAX(id) as id'), 'payment_voucher_id')
                ->groupBy('payment_voucher_id');

            $this->crud->query = $this->crud->query
                ->leftJoin('vouchers', 'vouchers.id', '=', 'payment_vouchers.voucher_id')
                ->leftJoinSub($p_v_p, 'p_v_p', function ($join) {
                    $join->on('p_v_p.payment_voucher_id', '=', 'payment_vouchers.id');
                })
                ->leftJoin('payment_voucher_plan', 'payment_voucher_plan.id', '=', 'p_v_p.id');


            $a_p = DB::table('approvals')
                ->select(DB::raw('MAX(id) as id'), 'model_type', 'model_id')
                ->groupBy('model_type', 'model_id');

            $this->crud->query = $this->crud->query
                ->leftJoinSub($a_p, 'a_p', function ($join) {
                    $join->on('a_p.model_id', '=', 'payment_voucher_plan.id')
                        ->where('a_p.model_type', '=', DB::raw('"App\\\\Models\\\\PaymentVoucherPlan"'));
                })
                ->leftJoin('approvals', 'approvals.id', '=', 'a_p.id');

            $this->crud->query = $this->crud->query
                ->leftJoin('spk', function ($join) {
                    $join->on('spk.id', '=', 'vouchers.reference_id')
                        ->where('vouchers.reference_type', '=', DB::raw('"App\\\\Models\\\\Spk"'));
                })
                ->leftJoin('purchase_orders', function ($join) {
                    $join->on('purchase_orders.id', '=', 'vouchers.reference_id')
                        ->where('vouchers.reference_type', '=', DB::raw('"App\\\\Models\\\\PurchaseOrder"'));
                })
                // ->where('payment_vouchers.payment_type', 'NON RUTIN');
                ->where('approvals.status', Approval::APPROVED);

            CRUD::addClause('select', [
                DB::raw("
                    vouchers.*,
                    spk.no_spk as spk_no,
                    purchase_orders.po_number as po_no,
                    approvals.approved_at as approval_approved_at,
                    approvals.status as approval_status,
                    approvals.user_id as approval_user_id,
                    approvals.no_apprv as approval_no_apprv,
                    payment_voucher_plan.id as voucer_edit_id,
                    payment_vouchers.voucher_id,
                    payment_vouchers.id as payment_voucher_id
                ")
            ]);

            $request = request();

            if (trim($request->columns[1]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('vouchers.no_voucher', 'like', '%' . $request->columns[1]['search']['value'] . '%');
            }

            if (trim($request->columns[2]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->whereHas('voucher.subkon', function ($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->columns[2]['search']['value'] . '%');
                    });
            }

            if (trim($request->columns[3]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->whereHas('voucher.subkon', function ($q) use ($request) {
                        $q->where('bank_name', 'like', '%' . $request->columns[3]['search']['value'] . '%');
                    });
            }

            if (trim($request->columns[4]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->whereHas('voucher.subkon', function ($q) use ($request) {
                        $q->where('bank_account', 'like', '%' . $request->columns[4]['search']['value'] . '%');
                    });
            }

            if (trim($request->columns[5]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('vouchers.bill_number', 'like', '%' . $request->columns[5]['search']['value'] . '%');
            }

            if (trim($request->columns[6]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('vouchers.payment_description', 'like', '%' . $request->columns[6]['search']['value'] . '%');
            }

            if (trim($request->columns[7]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->whereHas('voucher', function ($q) use ($request) {
                        $q->whereHasMorph('reference', '*', function ($query) use ($request) {
                            $query->where('po_number', 'like', '%' . $request->columns[7]['search']['value'] . '%');
                        });
                    });
            }

            if (trim($request->columns[8]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('vouchers.payment_transfer', 'like', '%' . $request->columns[8]['search']['value'] . '%');
            }

            if (trim($request->columns[9]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('vouchers.factur_status', 'like', '%' . $request->columns[9]['search']['value'] . '%');
            }

            if (trim($request->columns[10]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->whereHas('voucher', function ($q) use ($request) {
                        $q->whereHasMorph('reference', '*', function ($query) use ($request) {
                            $query->where('job_name', 'like', '%' . $request->columns[10]['search']['value'] . '%');
                        });
                    });
            }

            if (trim($request->columns[11]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('vouchers.due_date', 'like', '%' . $request->columns[11]['search']['value'] . '%');
            }

            if (trim($request->columns[12]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('vouchers.payment_type', 'like', '%' . $request->columns[12]['search']['value'] . '%');
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
                'orderLogic' => function ($query, $column, $order) {
                    return $query->orderBy('vouchers.no_voucher', $order);
                }
            ]);

            CRUD::column(
                [
                    'label' => trans('backpack::crud.voucher.column.voucher.bussines_entity_name.label'),
                    'name' => 'subkon_id',
                    'type'  => 'closure',
                    'function' => function ($entry) {
                        return $entry?->voucher?->subkon?->name;
                    },
                    'orderLogic' => function ($query, $column, $order) {
                        return $query->leftJoin('subkons', 'subkons.id', '=', 'vouchers.subkon_id')
                            ->orderBy('subkons.name', $order);
                    }
                ],
            );

            CRUD::column(
                [
                    'label' => trans('backpack::crud.voucher.column.voucher.bank.label'),
                    'name' => 'bank_name',
                    'type'  => 'closure',
                    'function' => function ($entry) {
                        return $entry?->voucher?->subkon?->bank_name;
                    },
                    'orderLogic' => function ($query, $column, $order) {
                        return $query->leftJoin('subkons', 'subkons.id', '=', 'vouchers.subkon_id')
                            ->orderBy('subkons.bank_name', $order);
                    }
                ],
            );

            CRUD::column(
                [
                    'label' => trans('backpack::crud.voucher.column.voucher.number_account.label'),
                    'name' => 'bank_account',
                    'type'  => 'closure',
                    'function' => function ($entry) {
                        return $entry?->voucher?->subkon?->bank_account;
                    },
                    'orderLogic' => function ($query, $column, $order) {
                        return $query->leftJoin('subkons', 'subkons.id', '=', 'vouchers.subkon_id')
                            ->orderBy('subkons.bank_account', $order);
                    }
                ],
            );

            CRUD::column(
                [
                    'label' => trans('backpack::crud.voucher.column.voucher.bill_number.label'),
                    'name' => 'bill_number',
                    'type'  => 'text',
                ],
            );

            CRUD::column(
                [
                    'label' => trans('backpack::crud.voucher.column.voucher.payment_description.label'),
                    'name' => 'payment_description',
                    'type'  => 'text',
                ],
            );

            CRUD::column(
                [
                    'label' => trans('backpack::crud.voucher.column.voucher.no_po_spk.label'),
                    'name' => 'reference_id',
                    'type'  => 'closure',
                    'function' => function ($entry) {
                        return $entry?->voucher?->reference?->po_number;
                    },
                ], // BELUM FILTER
            );

            CRUD::column([
                'label' => trans('backpack::crud.voucher.column.voucher.payment_transfer.label_2'),
                'name' => 'payment_transfer',
                'type'  => 'number',
                'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
                'orderLogic' => function ($query, $column, $order) {
                    return $query->orderBy('vouchers.payment_transfer', $order);
                }
            ]);

            CRUD::column(
                [
                    'label' => trans('backpack::crud.voucher.column.voucher.factur_status.label'),
                    'name' => 'factur_status',
                    'type'  => 'text',
                    'orderLogic' => function ($query, $column, $order) {
                        return $query->orderBy('vouchers.factur_status', $order);
                    }
                ],
            );

            CRUD::column(
                [
                    'label' => trans('backpack::crud.voucher.column.voucher.job_name.label'),
                    'name' => 'job_name',
                    'type'  => 'closure',
                    'function' => function ($entry) {
                        return $entry?->voucher?->reference?->job_name;
                    },
                ], // BELUM FILTER
            );

            CRUD::column([
                'label' => trans('backpack::crud.voucher.column.voucher.due_date.label_2'),
                'name' => 'due_date',
                'type'  => 'date',
                'format' => 'D MMM Y',
                'orderLogic' => function ($query, $column, $order) {
                    return $query->orderBy('vouchers.due_date', $order);
                }
            ]);

            CRUD::column(
                [
                    'label' => trans('backpack::crud.voucher.column.voucher.payment_type.label'),
                    'name' => 'payment_type',
                    'type'  => 'text',
                    'orderLogic' => function ($query, $column, $order) {
                        return $query->orderBy('vouchers.payment_type', $order);
                    }
                ],
            );
        } else if ($tab == 'voucher_payment' && $type == 'SUBKON') {
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

            if ($user_approval->count() > 0) {
                $this->crud->query = $this->crud->query
                    ->leftJoin('approvals', function ($join) use ($user_id) {
                        $join->on('approvals.model_id', '=', 'payment_voucher_plan.id')
                            ->where('approvals.model_type', 'App\\Models\\PaymentVoucherPlan')
                            ->where('approvals.user_id', $user_id);
                    });
            } else {
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
                ->leftJoin('spk', function ($join) {
                    $join->on('spk.id', '=', 'vouchers.reference_id')
                        ->where('vouchers.reference_type', '=', DB::raw('"App\\\\Models\\\\Spk"'));
                })
                ->leftJoin('purchase_orders', function ($join) {
                    $join->on('purchase_orders.id', '=', 'vouchers.reference_id')
                        ->where('vouchers.reference_type', '=', DB::raw('"App\\\\Models\\\\PurchaseOrder"'));
                })
                ->where('payment_vouchers.payment_type', 'SUBKON');


            CRUD::addClause('select', [
                DB::raw("
                    vouchers.*,
                    spk.no_spk as spk_no,
                    purchase_orders.po_number as po_no,
                    approvals.approved_at as approval_approved_at,
                    approvals.status as approval_status,
                    approvals.user_id as approval_user_id,
                    approvals.no_apprv as approval_no_apprv,
                    payment_voucher_plan.id as voucer_edit_id,
                    payment_vouchers.voucher_id
                ")
            ]);

            $request = request();

            if (trim($request->columns[1]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('no_voucher', 'like', '%' . $request->columns[1]['search']['value'] . '%');
            }

            if (trim($request->columns[2]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('date_voucher', 'like', '%' . $request->columns[2]['search']['value'] . '%');
            }

            if (trim($request->columns[3]['search']['value']) != '') {
                $search = $request->columns[3]['search']['value'];
                $this->crud->query = $this->crud->query
                    ->whereHas('voucher.subkon', function ($q) use ($search) {
                        $q->where('name', 'like', "%$search%");
                    });
            }

            if (trim($request->columns[4]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('bill_date', 'like', '%' . $request->columns[4]['search']['value'] . '%');
            }

            if (trim($request->columns[5]['search']['value']) != '') {
                $search = $request->columns[5]['search']['value'];
                $this->crud->query = $this->crud->query
                    ->whereHas('voucher', function ($q) use ($search) {
                        $q->whereHasMorph('reference', '*', function ($query) use ($search) {
                            $query->where('po_number', 'like', '%' . $search . '%');
                        });
                    });
            }

            if (trim($request->columns[6]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('payment_transfer', 'like', '%' . $request->columns[6]['search']['value'] . '%');
            }

            if (trim($request->columns[7]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('due_date', 'like', '%' . $request->columns[7]['search']['value'] . '%');
            }

            if (trim($request->columns[8]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('factur_status', 'like', '%' . $request->columns[8]['search']['value'] . '%');
            }

            if (trim($request->columns[9]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('due_date', 'like', '%' . $request->columns[9]['search']['value'] . '%');
            }

            if (trim($request->columns[10]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('payment_status', 'like', '%' . $request->columns[10]['search']['value'] . '%');
            }

            if (trim($request->columns[11]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('approvals.approved_at', 'like', '%' . $request->columns[11]['search']['value'] . '%');
            }

            if (trim($request->columns[12]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('approvals.status', 'like', '%' . $request->columns[12]['search']['value'] . '%');
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
                'orderLogic' => function ($query, $column, $order) {
                    return $query->orderBy('vouchers.no_voucher', $order);
                }
            ]);

            CRUD::column([
                'label'  => '',
                'name' => 'date_voucher',
                'type'  => 'date',
                'format' => 'D MMM Y',
                'orderLogic' => function ($query, $column, $order) {
                    return $query->orderBy('vouchers.date_voucher', $order);
                }
            ]);

            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'subkon_id',
                    'type'  => 'closure',
                    'function' => function ($entry) {
                        return $entry?->voucher?->subkon?->name;
                    },
                    'orderLogic' => function ($query, $column, $order) {
                        return $query->leftJoin('subkons', 'subkons.id', '=', 'vouchers.subkon_id')
                            ->orderBy('subkons.name', $order);
                    }
                ],
            );

            CRUD::column([
                'label'  => '',
                'name' => 'bill_date',
                'type'  => 'date',
                'format' => 'D MMM Y',
                'orderLogic' => function ($query, $column, $order) {
                    return $query->orderBy('vouchers.bill_date', $order);
                }
            ]);

            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'reference_id',
                    'type'  => 'closure',
                    'function' => function ($entry) {
                        return $entry?->voucher?->reference?->po_number;
                    },
                ], // BELUM FILTER
            );

            CRUD::column([
                'label'  => '',
                'name' => 'payment_transfer',
                'type'  => 'number',
                'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
                'orderLogic' => function ($query, $column, $order) {
                    return $query->orderBy('vouchers.payment_transfer', $order);
                }
            ]);

            CRUD::column([
                'label'  => '',
                'name' => 'due_date',
                'type'  => 'date',
                'format' => 'D MMM Y',
                'orderLogic' => function ($query, $column, $order) {
                    return $query->orderBy('vouchers.due_date', $order);
                }
            ]);

            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'factur_status',
                    'type'  => 'text',
                    'orderLogic' => function ($query, $column, $order) {
                        return $query->orderBy('vouchers.factur_status', $order);
                    }
                ],
            );

            CRUD::column([
                'label'  => '',
                'name' => 'payment_date',
                'type'  => 'date',
                'format' => 'D MMM Y',
                'orderLogic' => function ($query, $column, $order) {
                    return $query->orderBy('vouchers.payment_date', $order);
                }
            ]);

            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'payment_status',
                    'type'  => 'text',
                    'orderLogic' => function ($query, $column, $order) {
                        return $query->orderBy('vouchers.payment_status', $order);
                    }
                ],
            );

            CRUD::column([
                'label'  => '',
                'name' => 'approval_approved_at',
                'type'  => 'date',
                'format' => 'D MMM Y',
                'orderLogic' => function ($query, $column, $order) {
                    return $query->orderBy('approvals.approved_at', $order);
                }
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
                'function' => function ($entry) {
                    return '';
                }
            ]);
        } else if ($tab == 'voucher_payment_plan' && $type == 'SUBKON') {
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

            if ($user_approval->count() > 0) {
                $this->crud->query = $this->crud->query
                    ->leftJoin('approvals', function ($join) use ($user_id) {
                        $join->on('approvals.model_id', '=', 'payment_voucher_plan.id')
                            ->where('approvals.model_type', 'App\\Models\\PaymentVoucherPlan')
                            ->where('approvals.user_id', $user_id);
                    });
            } else {
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
                ->leftJoin('spk', function ($join) {
                    $join->on('spk.id', '=', 'vouchers.reference_id')
                        ->where('vouchers.reference_type', '=', DB::raw('"App\\\\Models\\\\Spk"'));
                })
                ->leftJoin('purchase_orders', function ($join) {
                    $join->on('purchase_orders.id', '=', 'vouchers.reference_id')
                        ->where('vouchers.reference_type', '=', DB::raw('"App\\\\Models\\\\PurchaseOrder"'));
                })
                ->where('payment_vouchers.payment_type', 'SUBKON');


            CRUD::addClause('select', [
                DB::raw("
                    vouchers.*,
                    spk.no_spk as spk_no,
                    purchase_orders.po_number as po_no,
                    approvals.approved_at as approval_approved_at,
                    approvals.status as approval_status,
                    approvals.user_id as approval_user_id,
                    approvals.no_apprv as approval_no_apprv,
                    payment_voucher_plan.id as voucer_edit_id,
                    payment_vouchers.voucher_id,
                    payment_vouchers.id as payment_voucher_id
                ")
            ]);

            $request = request();

            if (trim($request->columns[1]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('no_voucher', 'like', '%' . $request->columns[1]['search']['value'] . '%');
            }

            if (trim($request->columns[2]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('date_voucher', 'like', '%' . $request->columns[2]['search']['value'] . '%');
            }

            if (trim($request->columns[3]['search']['value']) != '') {
                $search = $request->columns[3]['search']['value'];
                $this->crud->query = $this->crud->query
                    ->whereHas('voucher.subkon', function ($q) use ($search) {
                        $q->where('name', 'like', "%$search%");
                    });
            }

            if (trim($request->columns[4]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('bill_date', 'like', '%' . $request->columns[4]['search']['value'] . '%');
            }

            if (trim($request->columns[5]['search']['value']) != '') {
                $search = $request->columns[5]['search']['value'];
                $this->crud->query = $this->crud->query
                    ->whereHas('voucher', function ($q) use ($search) {
                        $q->whereHasMorph('reference', '*', function ($query) use ($search) {
                            $query->where('po_number', 'like', '%' . $search . '%');
                        });
                    });
            }

            if (trim($request->columns[6]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('payment_transfer', 'like', '%' . $request->columns[6]['search']['value'] . '%');
            }

            if (trim($request->columns[7]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('due_date', 'like', '%' . $request->columns[7]['search']['value'] . '%');
            }

            if (trim($request->columns[8]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('factur_status', 'like', '%' . $request->columns[8]['search']['value'] . '%');
            }

            if (trim($request->columns[9]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('due_date', 'like', '%' . $request->columns[9]['search']['value'] . '%');
            }

            if (trim($request->columns[10]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('payment_status', 'like', '%' . $request->columns[10]['search']['value'] . '%');
            }

            if (trim($request->columns[11]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('approvals.approved_at', 'like', '%' . $request->columns[11]['search']['value'] . '%');
            }

            if (trim($request->columns[12]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('approvals.status', 'like', '%' . $request->columns[12]['search']['value'] . '%');
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
                'orderLogic' => function ($query, $column, $order) {
                    return $query->orderBy('vouchers.no_voucher', $order);
                }
            ]);

            CRUD::column([
                'label'  => '',
                'name' => 'date_voucher',
                'type'  => 'date',
                'format' => 'D MMM Y',
                'orderLogic' => function ($query, $column, $order) {
                    return $query->orderBy('vouchers.date_voucher', $order);
                }
            ]);

            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'subkon_id',
                    'type'  => 'closure',
                    'function' => function ($entry) {
                        return $entry?->voucher?->subkon?->name;
                    },
                    'orderLogic' => function ($query, $column, $order) {
                        return $query->leftJoin('subkons', 'subkons.id', '=', 'vouchers.subkon_id')
                            ->orderBy('subkons.name', $order);
                    }
                ], // BELUM FILTER
            );

            CRUD::column([
                'label'  => '',
                'name' => 'bill_date',
                'type'  => 'date',
                'format' => 'D MMM Y',
                'orderLogic' => function ($query, $column, $order) {
                    return $query->orderBy('vouchers.bill_date', $order);
                }
            ]);

            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'reference_id',
                    'type'  => 'closure',
                    'function' => function ($entry) {
                        return $entry?->voucher?->reference?->po_number;
                    }
                ], // BELUM FILTER
            );

            CRUD::column([
                'label'  => '',
                'name' => 'payment_transfer',
                'type'  => 'number',
                'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
                'orderLogic' => function ($query, $column, $order) {
                    return $query->orderBy('vouchers.payment_transfer', $order);
                }
            ]);

            CRUD::column([
                'label'  => '',
                'name' => 'due_date',
                'type'  => 'date',
                'format' => 'D MMM Y',
                'orderLogic' => function ($query, $column, $order) {
                    return $query->orderBy('vouchers.due_date', $order);
                }
            ]);

            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'factur_status',
                    'type'  => 'text',
                    'orderLogic' => function ($query, $column, $order) {
                        return $query->orderBy('vouchers.factur_status', $order);
                    }
                ],
            );

            CRUD::column([
                'label'  => '',
                'name' => 'payment_date',
                'type'  => 'date',
                'format' => 'D MMM Y',
                'orderLogic' => function ($query, $column, $order) {
                    return $query->orderBy('vouchers.payment_date', $order);
                }
            ]);

            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'payment_status',
                    'type'  => 'text',
                    'orderLogic' => function ($query, $column, $order) {
                        return $query->orderBy('vouchers.payment_status', $order);
                    }
                ],
            );

            CRUD::column([
                'label'  => '',
                'name' => 'approval_approved_at',
                'type'  => 'date',
                'format' => 'D MMM Y',
                'orderLogic' => function ($query, $column, $order) {
                    return $query->orderBy('approvals.approved_at', $order);
                }
            ]);

            CRUD::column(
                [
                    'label'  => '',
                    'name' => 'status',
                    'type'  => 'approval-voucher',
                ],
            );
        } else if ($tab == 'voucher_payment_plan_all') {
            $request = request();
            CRUD::setModel(PaymentVoucher::class);
            CRUD::disableResponsiveTable();
            CRUD::addButtonFromView('line', 'approve_payment', 'approve_payment', 'end');

            $p_v_p = DB::table('payment_voucher_plan')
                ->select(DB::raw('MAX(id) as id'), 'payment_voucher_id')
                ->groupBy('payment_voucher_id');

            $this->crud->query = $this->crud->query
                ->leftJoin('vouchers', 'vouchers.id', '=', 'payment_vouchers.voucher_id')
                ->leftJoinSub($p_v_p, 'p_v_p', function ($join) {
                    $join->on('p_v_p.payment_voucher_id', '=', 'payment_vouchers.id');
                })
                ->leftJoin('payment_voucher_plan', 'payment_voucher_plan.id', '=', 'p_v_p.id');

            $a_p = DB::table('approvals')
                ->select(DB::raw('MAX(id) as id'), 'model_type', 'model_id')
                ->groupBy('model_type', 'model_id');

            $this->crud->query = $this->crud->query
                ->leftJoinSub($a_p, 'a_p', function ($join) {
                    $join->on('a_p.model_id', '=', 'payment_voucher_plan.id')
                        ->where('a_p.model_type', '=', DB::raw('"App\\\\Models\\\\PaymentVoucherPlan"'));
                })
                ->leftJoin('approvals', 'approvals.id', '=', 'a_p.id');

            $this->crud->query = $this->crud->query
                ->leftJoin('spk', function ($join) {
                    $join->on('spk.id', '=', 'vouchers.reference_id')
                        ->where('vouchers.reference_type', '=', DB::raw('"App\\\\Models\\\\Spk"'));
                })
                ->leftJoin('purchase_orders', function ($join) {
                    $join->on('purchase_orders.id', '=', 'vouchers.reference_id')
                        ->where('vouchers.reference_type', '=', DB::raw('"App\\\\Models\\\\PurchaseOrder"'));
                })
                ->where('approvals.status', Approval::APPROVED);

            if ($request->has('columns')) {
                if (isset($request->columns[1]['search']['value'])) {
                    $this->crud->query = $this->crud->query
                        ->where('vouchers.no_voucher', 'like', '%' . $request->columns[1]['search']['value'] . '%');
                }

                if (isset($request->columns[2]['search']['value'])) {
                    $this->crud->query = $this->crud->query
                        ->whereHas('voucher.subkon', function ($q) use ($request) {
                            $q->where('name', 'like', '%' . $request->columns[2]['search']['value'] . '%');
                        });
                }

                if (isset($request->columns[3]['search']['value'])) {
                    $this->crud->query = $this->crud->query
                        ->whereHas('voucher.subkon', function ($q) use ($request) {
                            $q->where('bank_name', 'like', '%' . $request->columns[3]['search']['value'] . '%');
                        });
                }

                if (isset($request->columns[4]['search']['value'])) {
                    $this->crud->query = $this->crud->query
                        ->whereHas('voucher.subkon', function ($q) use ($request) {
                            $q->where('bank_account', 'like', '%' . $request->columns[4]['search']['value'] . '%');
                        });
                }

                if (isset($request->columns[5]['search']['value'])) {
                    $this->crud->query = $this->crud->query
                        ->where('vouchers.bill_number', 'like', '%' . $request->columns[5]['search']['value'] . '%');
                }

                if (isset($request->columns[6]['search']['value'])) {
                    $this->crud->query = $this->crud->query
                        ->where('vouchers.payment_description', 'like', '%' . $request->columns[6]['search']['value'] . '%');
                }

                if (isset($request->columns[7]['search']['value'])) {
                    $this->crud->query = $this->crud->query
                        ->whereHas('voucher', function ($q) use ($request) {
                            $q->whereHasMorph('reference', '*', function ($query) use ($request) {
                                $query->where('po_number', 'like', '%' . $request->columns[7]['search']['value'] . '%');
                            });
                        });
                }

                if (isset($request->columns[8]['search']['value'])) {
                    $this->crud->query = $this->crud->query
                        ->where('vouchers.payment_transfer', 'like', '%' . $request->columns[8]['search']['value'] . '%');
                }

                if (isset($request->columns[9]['search']['value'])) {
                    $this->crud->query = $this->crud->query
                        ->where('vouchers.factur_status', 'like', '%' . $request->columns[9]['search']['value'] . '%');
                }

                if (isset($request->columns[10]['search']['value'])) {
                    $this->crud->query = $this->crud->query
                        ->whereHas('voucher', function ($q) use ($request) {
                            $q->whereHasMorph('reference', '*', function ($query) use ($request) {
                                $query->where('job_name', 'like', '%' . $request->columns[10]['search']['value'] . '%');
                            });
                        });
                }

                if (isset($request->columns[11]['search']['value'])) {
                    $this->crud->query = $this->crud->query
                        ->where('vouchers.due_date', 'like', '%' . $request->columns[11]['search']['value'] . '%');
                }

                if (isset($request->columns[12]['search']['value'])) {
                    $this->crud->query = $this->crud->query
                        ->where('vouchers.payment_type', 'like', '%' . $request->columns[12]['search']['value'] . '%');
                }
            }


            CRUD::addClause('select', [
                DB::raw("
                    vouchers.*,
                    spk.no_spk as spk_no,
                    purchase_orders.po_number as po_no,
                    approvals.approved_at as approval_approved_at,
                    approvals.status as approval_status,
                    approvals.user_id as approval_user_id,
                    approvals.no_apprv as approval_no_apprv,
                    payment_voucher_plan.id as voucer_edit_id,
                    payment_vouchers.voucher_id
                ")
            ]);

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
                'label'  => 'No. Voucher',
                'name' => 'no_voucher',
                'type'  => 'text',
                'orderLogic' => function ($query, $column, $order) {
                    return $query->orderBy('vouchers.no_voucher', $order);
                }
            ]);

            CRUD::column(
                [
                    'label' => trans('backpack::crud.voucher.column.voucher.bussines_entity_name.label'),
                    'name' => 'subkon_id',
                    'type'  => 'closure',
                    'function' => function ($entry) {
                        return $entry?->voucher?->subkon?->name;
                    },
                    'orderLogic' => function ($query, $column, $order) {
                        return $query->leftJoin('subkons', 'subkons.id', '=', 'vouchers.subkon_id')
                            ->orderBy('subkons.name', $order);
                    }
                ],
            );

            CRUD::column(
                [
                    'label' => trans('backpack::crud.voucher.column.voucher.bank.label'),
                    'name' => 'bank_name',
                    'type'  => 'closure',
                    'function' => function ($entry) {
                        return $entry?->voucher?->subkon?->bank_name;
                    },
                    'orderLogic' => function ($query, $column, $order) {
                        return $query->leftJoin('subkons', 'subkons.id', '=', 'vouchers.subkon_id')
                            ->orderBy('subkons.bank_name', $order);
                    }
                ],
            );

            CRUD::column(
                [
                    'label' => trans('backpack::crud.voucher.column.voucher.number_account.label'),
                    'name' => 'bank_account',
                    'type'  => 'closure',
                    'function' => function ($entry) {
                        return $entry?->voucher?->subkon?->bank_account;
                    },
                    'orderLogic' => function ($query, $column, $order) {
                        return $query->leftJoin('subkons', 'subkons.id', '=', 'vouchers.subkon_id')
                            ->orderBy('subkons.bank_account', $order);
                    }
                ],
            );

            CRUD::column(
                [
                    'label' => trans('backpack::crud.voucher.column.voucher.bill_number.label'),
                    'name' => 'bill_number',
                    'type'  => 'text',
                ],
            );

            CRUD::column(
                [
                    'label' => trans('backpack::crud.voucher.column.voucher.payment_description.label'),
                    'name' => 'payment_description',
                    'type'  => 'text',
                ],
            );

            CRUD::column(
                [
                    'label' => trans('backpack::crud.voucher.column.voucher.no_po_spk.label'),
                    'name' => 'reference_id',
                    'type'  => 'closure',
                    'function' => function ($entry) {
                        return $entry?->voucher?->reference?->po_number;
                    },
                ], // BELUM FILTER
            );

            CRUD::column([
                'label' => trans('backpack::crud.voucher.column.voucher.payment_transfer.label_2'),
                'name' => 'payment_transfer',
                'type'  => 'number',
                'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
                'orderLogic' => function ($query, $column, $order) {
                    return $query->orderBy('vouchers.payment_transfer', $order);
                }
            ]);

            CRUD::column(
                [
                    'label' => trans('backpack::crud.voucher.column.voucher.factur_status.label'),
                    'name' => 'factur_status',
                    'type'  => 'text',
                    'orderLogic' => function ($query, $column, $order) {
                        return $query->orderBy('vouchers.factur_status', $order);
                    }
                ],
            );

            CRUD::column(
                [
                    'label' => trans('backpack::crud.voucher.column.voucher.job_name.label'),
                    'name' => 'job_name',
                    'type'  => 'closure',
                    'function' => function ($entry) {
                        return $entry?->voucher?->reference?->job_name;
                    },
                ], // BELUM FILTER
            );

            CRUD::column([
                'label' => trans('backpack::crud.voucher.column.voucher.due_date.label_2'),
                'name' => 'due_date',
                'type'  => 'date',
                'format' => 'D MMM Y',
                'orderLogic' => function ($query, $column, $order) {
                    return $query->orderBy('vouchers.due_date', $order);
                }
            ]);

            CRUD::column(
                [
                    'label' => trans('backpack::crud.voucher.column.voucher.payment_type.label'),
                    'name' => 'payment_type',
                    'type'  => 'text',
                    'orderLogic' => function ($query, $column, $order) {
                        return $query->orderBy('vouchers.payment_type', $order);
                    }
                ],
            );
        }
    }

    protected function setupListOperation()
    {
        CRUD::setModel(PaymentVoucher::class);
        $request = request();
        $tab = $request->tab;
        $type = $request->type;
        $settings = Setting::first();
        CRUD::removeButton('delete');
        // CRUD::removeButton('create');
        CRUD::addButtonFromView('top', 'export-excel', 'export-excel', 'beginning');
        CRUD::addButtonFromView('top', 'export-pdf', 'export-pdf', 'beginning');
        CRUD::addButtonFromView('line', 'delete-payment-plan', 'delete-payment-plan', 'beginning');
        CRUD::addButtonFromView('line', 'approve_button', 'approve_button', 'end');

        $status_file = '';
        if (strpos(url()->current(), 'excel')) {
            $status_file = 'excel';
        } else if (strpos(url()->current(), 'pdf')) {
            $status_file = 'pdf';
        }

        $wrap_length = [];

        if ($status_file == 'excel' || $status_file == 'pdf') {
            $wrap_length = [
                'width_box' => '100%',
            ];
        }


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

        if ($user_approval->count() > 0) {
            $this->crud->query = $this->crud->query
                ->leftJoin('approvals as user_live_approvals', function ($join) use ($user_id) {
                    $join->on('user_live_approvals.model_id', '=', 'payment_voucher_plan.id')
                        ->where('user_live_approvals.model_type', 'App\\Models\\PaymentVoucherPlan')
                        ->where('user_live_approvals.user_id', $user_id);
                });
            CRUD::addClause('select', [
                DB::raw("
                    vouchers.*,
                    spk.no_spk as spk_no,
                    purchase_orders.po_number as po_no,
                    approvals.approved_at as approval_approved_at,
                    approvals.status as approval_status,
                    approvals.user_id as approval_user_id,
                    approvals.no_apprv as approval_no_apprv,
                    payment_voucher_plan.id as voucer_edit_id,
                    payment_vouchers.voucher_id,
                    user_live_approvals.no_apprv as user_live_no_apprv,
                    user_live_approvals.status as user_live_status,
                    user_live_approvals.user_id as user_live_user_id
                ")
            ]);
        } else {
            CRUD::addClause('select', [
                DB::raw("
                    vouchers.*,
                    spk.no_spk as spk_no,
                    purchase_orders.po_number as po_no,
                    approvals.approved_at as approval_approved_at,
                    approvals.status as approval_status,
                    approvals.user_id as approval_user_id,
                    approvals.no_apprv as approval_no_apprv,
                    payment_voucher_plan.id as voucer_edit_id,
                    payment_vouchers.voucher_id,
                    '' as user_live_no_apprv,
                    '' as user_live_status,
                    '' as user_live_user_id
                ")
            ]);
        }

        $a_p = DB::table('approvals')
            ->select(DB::raw('MAX(id) as id'), 'model_type', 'model_id')
            ->groupBy('model_type', 'model_id');

        $this->crud->query = $this->crud->query
            ->leftJoinSub($a_p, 'a_p', function ($join) {
                $join->on('a_p.model_id', '=', 'payment_voucher_plan.id')
                    ->where('a_p.model_type', '=', DB::raw('"App\\\\Models\\\\PaymentVoucherPlan"'));
            })
            ->leftJoin('approvals', 'approvals.id', '=', 'a_p.id');

        $this->crud->query = $this->crud->query
            ->leftJoin('spk', function ($join) {
                $join->on('spk.id', '=', 'vouchers.reference_id')
                    ->where('vouchers.reference_type', '=', DB::raw('"App\\\\Models\\\\Spk"'));
            })
            ->leftJoin('purchase_orders', function ($join) {
                $join->on('purchase_orders.id', '=', 'vouchers.reference_id')
                    ->where('vouchers.reference_type', '=', DB::raw('"App\\\\Models\\\\PurchaseOrder"'));
            })
            ->leftJoin('cast_accounts', 'cast_accounts.id', 'vouchers.account_source_id')
            ->where('vouchers.payment_status', 'BELUM BAYAR');
        // ->where('approvals.status', Approval::APPROVED);

        if ($request->has('columns')) {
            if (trim($request->columns[1]['search']['value'] ?? '') != '') {
                $this->crud->query = $this->crud->query
                    ->where('vouchers.no_voucher', 'like', '%' . $request->columns[1]['search']['value'] . '%');
            }

            if (trim($request->columns[2]['search']['value'] ?? '') != '') {
                $this->crud->query = $this->crud->query
                    ->whereHas('voucher.subkon', function ($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->columns[2]['search']['value'] . '%');
                    });
            }

            if (trim($request->columns[3]['search']['value'] ?? '') != '') {
                $this->crud->query = $this->crud->query
                    ->whereHas('voucher.subkon', function ($q) use ($request) {
                        $q->where('bank_name', 'like', '%' . $request->columns[3]['search']['value'] . '%');
                    });
            }

            if (trim($request->columns[4]['search']['value'] ?? '') != '') {
                $this->crud->query = $this->crud->query
                    ->whereHas('voucher.subkon', function ($q) use ($request) {
                        $q->where('bank_account', 'like', '%' . $request->columns[4]['search']['value'] . '%');
                    });
            }

            if (trim($request->columns[5]['search']['value'] ?? '') != '') {
                $this->crud->query = $this->crud->query
                    ->where('vouchers.bill_number', 'like', '%' . $request->columns[5]['search']['value'] . '%');
            }

            if (trim($request->columns[6]['search']['value'] ?? '') != '') {
                $this->crud->query = $this->crud->query
                    ->where('vouchers.payment_description', 'like', '%' . $request->columns[6]['search']['value'] . '%');
            }

            if (trim($request->columns[7]['search']['value'] ?? '') != '') {
                $search = $request->columns[7]['search']['value'];
                $this->crud->query = $this->crud->query
                    ->whereHas('voucher', function ($q) use ($search) {
                        $q->whereHasMorph('reference', '*', function ($query) use ($search) {
                            $query->where('po_number', 'like', '%' . $search . '%')
                                ->orWhere('no_spk', 'like', '%' . $search . '%');
                        });
                    });
            }

            if (trim($request->columns[8]['search']['value'] ?? '') != '') {
                $this->crud->query = $this->crud->query
                    ->where('vouchers.payment_transfer', 'like', '%' . $request->columns[8]['search']['value'] . '%');
            }

            if (trim($request->columns[9]['search']['value'] ?? '') != '') {
                $this->crud->query = $this->crud->query
                    ->where('vouchers.factur_status', 'like', $request->columns[9]['search']['value'] . '%');
            }

            if (trim($request->columns[10]['search']['value'] ?? '') != '') {
                $search = $request->columns[10]['search']['value'];
                $this->crud->query = $this->crud->query
                    ->where('vouchers.job_name', 'like', '%' . $request->columns[10]['search']['value'] . '%');
                // ->whereHas('voucher', function($q)use($search){
                //     $q->whereHasMorph('reference', '*', function ($query) use($search){
                //         $query->where('job_name', 'like', '%'.$search.'%');
                //     });
                // });
            }

            if (trim($request->columns[11]['search']['value'] ?? '') != '') {
                $this->crud->query = $this->crud->query
                    ->where('vouchers.due_date', 'like', '%' . $request->columns[11]['search']['value'] . '%');
            }

            if (trim($request->columns[12]['search']['value'] ?? '') != '') {
                $this->crud->query = $this->crud->query
                    ->where('vouchers.payment_type', 'like', '%' . $request->columns[12]['search']['value'] . '%');
            }
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
            'label'  => 'No. Voucher',
            'name' => 'no_voucher',
            'type'  => 'text',
            'orderLogic' => function ($query, $column, $order) {
                return $query->orderBy('vouchers.no_voucher', $order);
            }
        ]);

        CRUD::column(
            [
                'label' => trans('backpack::crud.voucher.column.voucher.bussines_entity_name.label'),
                'name' => 'subkon_id',
                'type'  => 'closure',
                'function' => function ($entry) {
                    return $entry?->voucher?->subkon?->name;
                },
                'orderLogic' => function ($query, $column, $order) {
                    return $query->leftJoin('subkons', 'subkons.id', '=', 'vouchers.subkon_id')
                        ->orderBy('subkons.name', $order);
                }
            ],
        );

        CRUD::column(
            [
                'label' => trans('backpack::crud.voucher.column.voucher.bank.label'),
                'name' => 'bank_name',
                'type'  => 'closure',
                'function' => function ($entry) {
                    return $entry?->voucher?->subkon?->bank_name;
                },
                'orderLogic' => function ($query, $column, $order) {
                    return $query->leftJoin('subkons', 'subkons.id', '=', 'vouchers.subkon_id')
                        ->orderBy('subkons.bank_name', $order);
                }
            ],
        );

        CRUD::column(
            [
                'label' => trans('backpack::crud.voucher.column.voucher.number_account.label'),
                'name' => 'bank_account',
                'type'  => 'closure',
                'function' => function ($entry) {
                    return $entry?->voucher?->subkon?->bank_account;
                },
                'orderLogic' => function ($query, $column, $order) {
                    return $query->leftJoin('subkons', 'subkons.id', '=', 'vouchers.subkon_id')
                        ->orderBy('subkons.bank_account', $order);
                }
            ],
        );

        CRUD::column(
            [
                'label' => trans('backpack::crud.voucher.column.voucher.bill_number.label'),
                'name' => 'bill_number',
                'type'  => 'text',
            ],
        );

        CRUD::column(
            [
                'label' => trans('backpack::crud.voucher.column.voucher.payment_description.label'),
                'name' => 'payment_description',
                'type'  => 'wrap_text',
                ...$wrap_length,
            ],
        );

        CRUD::column(
            [
                'label' => trans('backpack::crud.voucher.column.voucher.no_po_spk.label'),
                'name' => 'reference_id',
                'type'  => 'closure',
                'function' => function ($entry) {
                    if ($entry?->voucher?->reference_type == 'App\Models\Spk') {
                        return $entry?->voucher?->reference?->no_spk;
                    }
                    return $entry?->voucher?->reference?->po_number;
                },
            ], // BELUM FILTER
        );

        CRUD::column([
            'label' => trans('backpack::crud.voucher.column.voucher.payment_transfer.label'),
            'name' => 'payment_transfer',
            'type'  => 'number',
            'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
            'orderLogic' => function ($query, $column, $order) {
                return $query->orderBy('vouchers.payment_transfer', $order);
            }
        ]);

        CRUD::column(
            [
                'label' => trans('backpack::crud.voucher.column.voucher.factur_status.label'),
                'name' => 'factur_status',
                'type'  => 'text',
                'orderLogic' => function ($query, $column, $order) {
                    return $query->orderBy('vouchers.factur_status', $order);
                }
            ],
        );

        CRUD::column(
            [
                'label' => trans('backpack::crud.voucher.column.voucher.job_name.label'),
                'name' => 'job_name',
                'type'  => 'wrap_text',
                ...$wrap_length,
            ], // BELUM FILTER
        );

        CRUD::column([
            'label' => trans('backpack::crud.voucher.column.voucher.due_date.label_2'),
            'name' => 'due_date',
            'type'  => 'date',
            'format' => 'D MMM Y',
            'orderLogic' => function ($query, $column, $order) {
                return $query->orderBy('vouchers.due_date', $order);
            }
        ]);

        CRUD::column(
            [
                'label' => trans('backpack::crud.voucher.column.voucher.payment_type.label'),
                'name' => 'payment_type',
                'type'  => 'text',
                'orderLogic' => function ($query, $column, $order) {
                    return $query->orderBy('vouchers.payment_type', $order);
                }
            ],
        );

        CRUD::column(
            [
                'label'  => '',
                'name' => 'status',
                'type'  => 'approval-voucher',
            ],
        );

        CRUD::addColumn([
            'name'     => 'user_approval',
            'label'    => trans('backpack::crud.voucher.column.voucher.user_approval.label'),
            'type'     => 'custom_html',
            'value' => function ($entry) {
                $approvals = Approval::where('model_type', PaymentVoucherPlan::class)
                    ->where('model_id', $entry->voucer_edit_id)
                    ->orderBy('no_apprv', 'ASC')
                    ->get();
                return "<ul>" . $approvals->map(function ($item, $key) {
                    if ($item->status == Approval::APPROVED) {
                        return "<li class='text-success'>" . $item->user->name . "</li>";
                    }
                    return "<li>" . $item->user->name . "</li>";
                })->implode('') . "</ul>";
            },
        ]);
    }

    private function setupListExport()
    {
        $settings = Setting::first();
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

        if ($user_approval->count() > 0) {
            $this->crud->query = $this->crud->query
                ->leftJoin('approvals', function ($join) use ($user_id) {
                    $join->on('approvals.model_id', '=', 'payment_voucher_plan.id')
                        ->where('approvals.model_type', 'App\\Models\\PaymentVoucherPlan')
                        ->where('approvals.user_id', $user_id);
                });
        } else {
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
            ->leftJoin('spk', function ($join) {
                $join->on('spk.id', '=', 'vouchers.reference_id')
                    ->where('vouchers.reference_type', '=', DB::raw('"App\\\\Models\\\\Spk"'));
            })
            ->leftJoin('purchase_orders', function ($join) {
                $join->on('purchase_orders.id', '=', 'vouchers.reference_id')
                    ->where('vouchers.reference_type', '=', DB::raw('"App\\\\Models\\\\PurchaseOrder"'));
            });

        CRUD::addClause('select', [
            DB::raw("
                    vouchers.*,
                    spk.no_spk as spk_no,
                    purchase_orders.po_number as po_no,
                    approvals.approved_at as approval_approved_at,
                    approvals.status as approval_status,
                    approvals.user_id as approval_user_id,
                    approvals.no_apprv as approval_no_apprv,
                    payment_voucher_plan.id as voucer_edit_id,
                    payment_vouchers.voucher_id,
                    payment_vouchers.id as payment_voucher_id
                ")
        ]);

        $request = request();

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
            'orderLogic' => function ($query, $column, $order) {
                return $query->orderBy('vouchers.no_voucher', $order);
            }
        ]);

        CRUD::column(
            [
                'label' => trans('backpack::crud.voucher.column.voucher.bussines_entity_name.label'),
                'name' => 'subkon_id',
                'type'  => 'closure',
                'function' => function ($entry) {
                    return $entry?->voucher?->subkon?->name;
                },
                'orderLogic' => function ($query, $column, $order) {
                    return $query->leftJoin('subkons', 'subkons.id', '=', 'vouchers.subkon_id')
                        ->orderBy('subkons.name', $order);
                }
            ],
        );

        CRUD::column(
            [
                'label' => trans('backpack::crud.voucher.column.voucher.bank.label'),
                'name' => 'bank_name',
                'type'  => 'closure',
                'function' => function ($entry) {
                    return $entry?->voucher?->subkon?->bank_name;
                },
                'orderLogic' => function ($query, $column, $order) {
                    return $query->leftJoin('subkons', 'subkons.id', '=', 'vouchers.subkon_id')
                        ->orderBy('subkons.bank_name', $order);
                }
            ],
        );

        CRUD::column(
            [
                'label' => trans('backpack::crud.voucher.column.voucher.number_account.label'),
                'name' => 'bank_account',
                'type'  => 'closure',
                'function' => function ($entry) {
                    return $entry?->voucher?->subkon?->bank_account;
                },
                'orderLogic' => function ($query, $column, $order) {
                    return $query->leftJoin('subkons', 'subkons.id', '=', 'vouchers.subkon_id')
                        ->orderBy('subkons.bank_account', $order);
                }
            ],
        );

        CRUD::column(
            [
                'label' => trans('backpack::crud.voucher.column.voucher.bill_number.label'),
                'name' => 'bill_number',
                'type'  => 'text',
            ],
        );

        CRUD::column(
            [
                'label' => trans('backpack::crud.voucher.column.voucher.payment_description.label'),
                'name' => 'payment_description',
                'type'  => 'text',
            ],
        );

        CRUD::column(
            [
                'label' => trans('backpack::crud.voucher.column.voucher.no_po_spk.label'),
                'name' => 'reference_id',
                'type'  => 'closure',
                'function' => function ($entry) {
                    return $entry?->voucher?->reference?->po_number;
                },
            ], // BELUM FILTER
        );

        CRUD::column([
            'label' => trans('backpack::crud.voucher.column.voucher.payment_transfer.label_2'),
            'name' => 'payment_transfer',
            'type'  => 'number',
            'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
            'orderLogic' => function ($query, $column, $order) {
                return $query->orderBy('vouchers.payment_transfer', $order);
            }
        ]);

        CRUD::column(
            [
                'label' => trans('backpack::crud.voucher.column.voucher.factur_status.label'),
                'name' => 'factur_status',
                'type'  => 'text',
                'orderLogic' => function ($query, $column, $order) {
                    return $query->orderBy('vouchers.factur_status', $order);
                }
            ],
        );

        CRUD::column(
            [
                'label' => trans('backpack::crud.voucher.column.voucher.job_name.label'),
                'name' => 'job_name',
                'type'  => 'closure',
                'function' => function ($entry) {
                    return $entry?->voucher?->reference?->job_name;
                },
            ], // BELUM FILTER
        );

        CRUD::column([
            'label' => trans('backpack::crud.voucher.column.voucher.due_date.label_2'),
            'name' => 'due_date',
            'type'  => 'date',
            'format' => 'D MMM Y',
            'orderLogic' => function ($query, $column, $order) {
                return $query->orderBy('vouchers.due_date', $order);
            }
        ]);

        CRUD::column(
            [
                'label' => trans('backpack::crud.voucher.column.voucher.payment_type.label'),
                'name' => 'payment_type',
                'type'  => 'text',
                'orderLogic' => function ($query, $column, $order) {
                    return $query->orderBy('vouchers.payment_type', $order);
                }
            ],
        );
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
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.add') . ' ' . $this->crud->entity_name;

        return response()->json([
            'html' => view('crud::create', $this->data)->render()
        ]);
    }

    private function ruleValidation()
    {
        return [
            'voucher' => [
                'required',
                'array',
                'min:1',
                function ($attr, $value, $fail) {
                    foreach ($value as $id_voucher) {
                        $payment_voucher = PaymentVoucher::find(request()->id);
                        if ($payment_voucher != null) {
                            $fail(trans('backpack::crud.voucher_payment.voucher_payment_exists'));
                        }
                    }
                }
            ],
        ];
    }

    protected function setupCreateOperation()
    {
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

        foreach ($voucherList as $list) {
            $list->date_voucher_str = Carbon::parse($list->date_voucher)->format('d M Y');
            $list->bill_date_str = Carbon::parse($list->bill_date)->format('d M Y');
            $list->due_date_str = Carbon::parse($list->due_date)->format('d M Y');
            $list->payment_transfer_str = ($settings?->currency_symbol) ? $settings->currency_symbol . ' ' . CustomHelper::formatRupiah($list->payment_transfer) : "Rp." . CustomHelper::formatRupiah($list->payment_transfer);
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
        try {

            $event = [];
            $event['crudTable-filter_voucher_payment_plugin_load'] = true;
            $event['crudTable-voucher_payment_plan_non_rutin_create_success'] = true;

            $voucher = $request->voucher;
            foreach ($voucher as $id_v) {
                $voucher = Voucher::find($id_v);
                CustomVoid::voucherPaymentPlan($voucher);
            }

            \Alert::success(trans('backpack::crud.insert_success'))->flash();

            $this->crud->setSaveAction();

            DB::commit();
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'events' => $event,
                ]);
            }
            // return $this->crud->performSaveAction($payment_voucher->getKey());
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }


    public function addTransaction($voucher_id)
    {
        $voucher = Voucher::find($voucher_id);
        $po = $voucher->reference;
        $po_type = $voucher->reference_type;
        $invoice = InvoiceClient::where('client_po_id', $voucher->client_po_id)->first();
        $client_po = $voucher->client_po;

        if ($client_po->status == 'TANPA PO') {
            // ada po
            $account = Account::where('code', "50222")->first();
            $price_general_loan = $po->load_general_value;
            CustomHelper::updateOrCreateJournalEntry([
                'account_id' => $account->id,
                'reference_id' => $po->id,
                'reference_type' => $po_type,
                'description' => "Transaksi tanpa PO " . $po->work_code,
                'date' => Carbon::now(),
                'debit' => $price_general_loan,
                // 'credit' => ($status == CastAccount::OUT) ? $nominal_transaction : 0,
            ], [
                'account_id' => $account->id,
                'reference_id' => $po->id,
                'reference_type' => $po_type,
            ]);
        }

        // periksa jenis voucher
        if ($voucher->reference_type == "App\Models\PurchaseOrder") {
            if ($invoice == null) {
                $account = Account::where('code', "504")->first();
                $payment_transfer = $voucher->payment_transfer;
                CustomHelper::updateOrCreateJournalEntry([
                    'account_id' => $account->id,
                    'reference_id' => $voucher_id,
                    'reference_type' => Voucher::class,
                    'description' => "Beban dalam proses pekerjaan voucher " . $voucher->no_voucher,
                    'date' => Carbon::now(),
                    'debit' => $payment_transfer,
                    'credit' => 0,
                    // 'credit' => ($status == CastAccount::OUT) ? $nominal_transaction : 0,
                ], [
                    'account_id' => $account->id,
                    'reference_id' => $voucher_id,
                    'reference_type' => Voucher::class,
                ]);
            } else {
                $account = Account::where('code', "501")->first();
                $payment_transfer = $voucher->payment_transfer;

                $invoice->status = 'Paid';
                $invoice->save();

                $transaksi = new AccountTransaction;
                $transaksi->cast_account_id = $voucher->account_source_id;
                $transaksi->reference_type = Voucher::class;
                $transaksi->reference_id = $voucher_id;
                $transaksi->date_transaction = Carbon::now()->format('Y-m-d');
                $transaksi->nominal_transaction = $payment_transfer;
                $transaksi->total_saldo_before = 0;
                $transaksi->total_saldo_after = 0;
                $transaksi->status = CastAccount::ENTER;
                $transaksi->kdp = $client_po?->work_code;
                $transaksi->job_name = $voucher?->job_name;
                $transaksi->save();

                CustomHelper::updateOrCreateJournalEntry([
                    'account_id' => $account->id,
                    'reference_id' => $transaksi->id,
                    'reference_type' => AccountTransaction::class,
                    'description' => $transaksi->kdp,
                    'date' => Carbon::now(),
                    'debit' => $payment_transfer,
                    'credit' => 0,
                    // 'credit' => ($status == CastAccount::OUT) ? $nominal_transaction : 0,
                ], [
                    'account_id' => $account->id,
                    'reference_id' => $voucher_id,
                    'reference_type' => AccountTransaction::class,
                ]);
            }
        } else if ($voucher->reference_type == "App\Models\ClientPo") {
            if ($invoice == null) {
                // jika tidak ada invoice di PO
                $account = Account::where('code', "504")->first();
                $payment_transfer = $voucher->payment_transfer;
                CustomHelper::updateOrCreateJournalEntry([
                    'account_id' => $account->id,
                    'reference_id' => $voucher_id,
                    'reference_type' => Voucher::class,
                    'description' => "Beban dalam proses pekerjaan voucher " . $voucher->no_voucher,
                    'date' => Carbon::now(),
                    'debit' => $payment_transfer,
                    'credit' => 0,
                    // 'credit' => ($status == CastAccount::OUT) ? $nominal_transaction : 0,
                ], [
                    'account_id' => $account->id,
                    'reference_id' => $voucher_id,
                    'reference_type' => Voucher::class,
                ]);
            } else {
                $account = Account::where('code', "501")->first();
                $payment_transfer = $voucher->payment_transfer;

                $invoice->status = 'Paid';
                $invoice->save();

                $transaksi = new AccountTransaction;
                $transaksi->cast_account_id = $voucher->account_source_id;
                $transaksi->reference_type = Voucher::class;
                $transaksi->reference_id = $voucher_id;
                $transaksi->date_transaction = Carbon::now()->format('Y-m-d');
                $transaksi->nominal_transaction = $payment_transfer;
                $transaksi->total_saldo_before = 0;
                $transaksi->total_saldo_after = 0;
                $transaksi->status = CastAccount::ENTER;
                $transaksi->kdp = $po?->work_code;
                $transaksi->job_name = $po?->job_name;
                $transaksi->save();

                CustomHelper::updateOrCreateJournalEntry([
                    'account_id' => $account->id,
                    'reference_id' => $transaksi->id,
                    'reference_type' => AccountTransaction::class,
                    'description' => $transaksi->kdp,
                    'date' => Carbon::now(),
                    'debit' => $payment_transfer,
                    'credit' => 0,
                    // 'credit' => ($status == CastAccount::OUT) ? $nominal_transaction : 0,
                ], [
                    'account_id' => $account->id,
                    'reference_id' => $voucher_id,
                    'reference_type' => AccountTransaction::class,
                ]);
            }
        }
    }

    public function approvedStore($id)
    {
        $this->crud->hasAccessOrFail('create');

        $request = $this->crud->validateRequest();

        $this->crud->registerFieldEvents();

        DB::beginTransaction();
        try {

            $event = [];

            $event['crudTable-filter_voucher_payment_plugin_load'] = true;

            $user_id = backpack_user()->id;
            $voucher_payment_plan = PaymentVoucherPlan::find($id);
            $voucher_payment = PaymentVoucher::where('id', $voucher_payment_plan->payment_voucher_id)->first();

            // if($voucher_payment->payment_type == 'NON RUTIN'){
            // }else if($voucher_payment->payment_type == 'RUTIN'){
            //     $event['crudTable-voucher_payment_rutin_create_success'] = true;
            //     $event['crudTable-voucher_payment_plan_rutin_create_success'] = true;
            // }

            $event['crudTable-voucher_payment_plan_non_rutin_create_success'] = true;

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

            if ($request->action == Approval::APPROVED) {
                if ($final_approval->no_apprv == $request->no_apprv) {
                    // $this->addTransaction($voucher_payment->voucher_id);
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
        DB::beginTransaction();
        try {
            $this->crud->hasAccessOrFail('delete');

            $payment_voucher = PaymentVoucher::find($id);
            $voucher = Voucher::find($payment_voucher->voucher_id);
            $payment_voucher_plan = PaymentVoucherPlan::where('payment_voucher_id', $payment_voucher->id)->first();

            Approval::where('model_type', 'App\\Models\\PaymentVoucherPlan')
                ->where('model_id', $payment_voucher_plan->id)
                ->delete();

            if ($payment_voucher) {
                $payment_voucher->delete();
            }

            if ($payment_voucher_plan) {
                $payment_voucher_plan->delete();
            }

            CustomVoid::rollbackPayment(Voucher::class, $voucher->id, "CREATE_PLAN_PAYMENT_VOUCHER");
            CustomVoid::rollbackPayment(Voucher::class, $voucher->id, "CREATE_PAYMENT_VOUCHER");

            $messages['success'][] = trans('backpack::crud.delete_confirmation_message');
            $messages['events'] = [
                'crudTable-filter_voucher_payment_plugin_load' => true,
                'crudTable-voucher_payment_plan_non_rutin_create_success' => true,
                'crudTable-voucher_payment_plan_rutin_create_success' => true,
            ];

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

    public function exportPdf()
    {
        $type = request()->tab;

        $this->setupListOperation();

        CRUD::removeColumn('status');
        CRUD::removeColumn('user_approval');
        CRUD::removeColumn('document_path');

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

        $title = "VOUCHER RENCANA PEMBAYARAN";

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
        $type = request()->tab;

        $this->setupListOperation();
        CRUD::removeColumn('status');
        CRUD::removeColumn('user_approval');
        CRUD::removeColumn('document_path');

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

        $name = 'VOUCHER RENCANA PEMBAYARAN';

        return response()->streamDownload(function () use ($type, $columns, $items, $all_items) {
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
}
