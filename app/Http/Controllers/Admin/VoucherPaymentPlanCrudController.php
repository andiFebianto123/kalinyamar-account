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
use App\Models\Spk;
use App\Models\PurchaseOrder;
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
        CRUD::setRoute(config('backpack.base.route_prefix') . '/voucher-payment-plan');
        CRUD::setEntityNameStrings(trans('backpack::crud.menu.voucher_payment_plan'), trans('backpack::crud.menu.voucher_payment_plan'));


        $viewMenu = [
            'MENU INDEX RENCANA PEMBAYARAN',
        ];

        $this->settingPermission([
            'approve' => ["APPROVE RENCANA BAYAR"],
            'create' => [
                'CREATE INDEX RENCANA PEMBAYARAN',
            ],
            'update' => [
                'UPDATE INDEX RENCANA PEMBAYARAN',
            ],
            'delete' => [
                'DELETE INDEX RENCANA PEMBAYARAN',
            ],
            'list' => $viewMenu,
            'show' => $viewMenu,
            'print' => true,
        ]);
    }

    function total_voucher()
    {
        $request = request();
        $searchAll = $request->get('searchAll', []);
        $searchNonRutin = $request->get('searchNonRutin', []);
        $searchSubkon = $request->get('searchSubkon', []);

        $applyFilters = function ($query, $search) {
            if (empty($search)) return $query;

            // Kolom indices shifted to match new order (row_number at 0)
            // Kolom 2 - bank
            if (isset($search[2]) && trim($search[2]) !== '') {
                $value = trim($search[2]);
                $query = $query->whereHas('voucher.subkon', function ($q) use ($value) {
                    $q->where('bank_name', 'like', "%{$value}%");
                });
            }

            // Kolom 3 - number_account
            if (isset($search[3]) && trim($search[3]) !== '') {
                $value = trim($search[3]);
                $query = $query->whereHas('voucher.subkon', function ($q) use ($value) {
                    $q->where('bank_account', 'like', "%{$value}%");
                });
            }

            // Kolom 4 - A/N Rekening (account_holder_name)
            if (isset($search[4]) && trim($search[4]) !== '') {
                $value = trim($search[4]);
                $query = $query->where(function ($subQ) use ($value) {
                    $subQ->where('vouchers.account_holder_name', 'like', "%{$value}%")
                        ->orWhereHas('voucher', function ($q) use ($value) {
                            $q->whereHasMorph('reference', [Spk::class, PurchaseOrder::class], function ($query, $type) use ($value) {
                                $query->whereHas('subkon', function ($sub) use ($value) {
                                    $sub->where('account_holder_name', 'like', "%{$value}%");
                                });
                            });
                        });
                });
            }

            // Kolom 5 - payment_transfer
            if (isset($search[5]) && trim($search[5]) !== '') {
                $value = trim($search[5]);
                $query = $query->where('vouchers.payment_transfer', 'like', "%{$value}%");
            }

            // Kolom 6 - no_voucher
            if (isset($search[6]) && trim($search[6]) !== '') {
                $value = trim($search[6]);
                $query = $query->where('vouchers.no_voucher', 'like', "%{$value}%");
            }

            // Kolom 7 - bill_number
            if (isset($search[7]) && trim($search[7]) !== '') {
                $value = trim($search[7]);
                $query = $query->where('vouchers.bill_number', 'like', "%{$value}%");
            }

            // Kolom 8 - payment_description
            if (isset($search[8]) && trim($search[8]) !== '') {
                $value = trim($search[8]);
                $query = $query->where('vouchers.payment_description', 'like', "%{$value}%");
            }

            // Kolom 9 - reference.po_number / reference.no_spk
            if (isset($search[9]) && trim($search[9]) !== '') {
                $value = trim($search[9]);
                $query = $query->whereHas('voucher', function ($q) use ($value) {
                    $q->whereHasMorph('reference', [Spk::class, PurchaseOrder::class], function ($query, $type) use ($value) {
                        if ($type === Spk::class) {
                            $query->where('no_spk', 'like', "%{$value}%");
                        } else if ($type === PurchaseOrder::class) {
                            $query->where('po_number', 'like', "%{$value}%");
                        }
                    });
                });
            }

            // Kolom 10 - factur_status
            if (isset($search[10]) && trim($search[10]) !== '') {
                $value = trim($search[10]);
                $query = $query->where('vouchers.factur_status', 'like', "{$value}%");
            }

            // Kolom 11 - job_name
            if (isset($search[11]) && trim($search[11]) !== '') {
                $value = trim($search[11]);
                $query = $query->where('vouchers.job_name', 'like', "%{$value}%");
            }

            // Kolom 12 - due_date
            if (isset($search[12]) && trim($search[12]) !== '') {
                $value = trim($search[12]);
                $query = $query->where('vouchers.due_date', 'like', "%{$value}%");
            }

            // Kolom 13 - payment_type
            if (isset($search[13]) && trim($search[13]) !== '') {
                $value = trim($search[13]);
                $query = $query->where('vouchers.payment_type', 'like', "%{$value}%");
            }

            // Kolom 14 - status
            if (isset($search[14]) && trim($search[14]) !== '') {
                $value = trim($search[14]);
                $query = $query->where('approvals.status', 'like', "{$value}%");
            }

            return $query;
        };

        $applyFiltersSubkon = function ($query, $search) {
            if (empty($search)) return $query;

            // Subkon Tab Columns: row_number(0), no_voucher(1), subkon_id(2), bill_number(3), payment_description(4), reference_id(5), payment_transfer(6), job_name(7), due_date(8)
            if (isset($search[1]) && trim($search[1]) !== '') {
                $value = trim($search[1]);
                $query = $query->where('no_voucher', 'like', "%{$value}%");
            }

            if (isset($search[2]) && trim($search[2]) !== '') {
                $value = trim($search[2]);
                $query = $query->whereHas('subkon', function ($q) use ($value) {
                    $q->where('name', 'like', "%{$value}%");
                });
            }

            if (isset($search[3]) && trim($search[3]) !== '') {
                $value = trim($search[3]);
                $query = $query->where('bill_number', 'like', "%{$value}%");
            }

            if (isset($search[4]) && trim($search[4]) !== '') {
                $value = trim($search[4]);
                $query = $query->where('payment_description', 'like', "%{$value}%");
            }

            if (isset($search[5]) && trim($search[5]) !== '') {
                $value = trim($search[5]);
                $query = $query->whereHasMorph('reference', [Spk::class, PurchaseOrder::class], function ($query, $type) use ($value) {
                    if ($type === Spk::class) {
                        $query->where('no_spk', 'like', "%{$value}%");
                    } else if ($type === PurchaseOrder::class) {
                        $query->where('po_number', 'like', "%{$value}%");
                    }
                });
            }

            if (isset($search[6]) && trim($search[6]) !== '') {
                $value = trim($search[6]);
                $query = $query->where('total', 'like', "%{$value}%");
            }

            if (isset($search[7]) && trim($search[7]) !== '') {
                $value = trim($search[7]);
                $query = $query->where('job_name', 'like', "%{$value}%");
            }

            if (isset($search[8]) && trim($search[8]) !== '') {
                $value = trim($search[8]);
                $query = $query->where('due_date', 'like', "%{$value}%");
            }

            return $query;
        };

        // Base Plan Data (joins etc)
        $p_v_p = DB::table('payment_voucher_plan')
            ->select(DB::raw('MAX(id) as id'), 'payment_voucher_id')
            ->groupBy('payment_voucher_id');

        $a_p = DB::table('approvals')
            ->select(DB::raw('MAX(id) as id'), 'model_type', 'model_id')
            ->groupBy('model_type', 'model_id');

        $basePlanQuery = function ($type = null) use ($p_v_p, $a_p) {
            $query = PaymentVoucher::select(DB::raw('SUM(vouchers.payment_transfer) as jumlah_nilai_transfer'))
                ->leftJoin('vouchers', 'vouchers.id', '=', 'payment_vouchers.voucher_id')
                ->leftJoinSub($p_v_p, 'p_v_p', function ($join) {
                    $join->on('p_v_p.payment_voucher_id', '=', 'payment_vouchers.id');
                })
                ->leftJoin('payment_voucher_plan', 'payment_voucher_plan.id', '=', 'p_v_p.id')
                ->leftJoinSub($a_p, 'a_p', function ($join) {
                    $join->on('a_p.model_id', '=', 'payment_voucher_plan.id')
                        ->where('a_p.model_type', '=', DB::raw('"App\\\\Models\\\\PaymentVoucherPlan"'));
                })
                ->leftJoin('approvals', 'approvals.id', '=', 'a_p.id')
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

            if ($type) {
                $query->where('payment_vouchers.payment_type', $type);
            }

            return $query;
        };

        // 0. ALL PLAN
        $queryAll = $basePlanQuery();
        if ($request->has('filter_year') && $request->filter_year != 'all') {
            $queryAll = $queryAll->whereYear('vouchers.date_voucher', $request->filter_year);
        }
        $queryAll = $applyFilters($queryAll, $searchAll);
        $total_voucher_plan_data_all = $queryAll->first();

        // 1. NON RUTIN VOUCHER (Source: Voucher model as configured in setupListOperation)
        $total_voucher_data_non_rutin_query = Voucher::where('payment_type', 'NON RUTIN')
            ->where('payment_status', 'BELUM BAYAR')
            ->select(DB::raw('SUM(payment_transfer) as jumlah_nilai_transfer'));

        if ($request->has('filter_year') && $request->filter_year != 'all') {
            $total_voucher_data_non_rutin_query = $total_voucher_data_non_rutin_query->whereYear('date_voucher', $request->filter_year);
        }
        $total_voucher_data_non_rutin_query = $applyFiltersSubkon($total_voucher_data_non_rutin_query, $searchNonRutin);
        $total_voucher_data_non_rutin = $total_voucher_data_non_rutin_query->first();

        // 2. SUBKON VOUCHER (Source: Voucher model as configured in setupListOperation)
        $total_voucher_data_subkon_query = Voucher::where('payment_type', 'SUBKON')
            ->where('payment_status', 'BELUM BAYAR')
            ->select(DB::raw('SUM(payment_transfer) as jumlah_nilai_transfer'));

        if ($request->has('filter_year') && $request->filter_year != 'all') {
            $total_voucher_data_subkon_query = $total_voucher_data_subkon_query->whereYear('date_voucher', $request->filter_year);
        }
        $total_voucher_data_subkon_query = $applyFiltersSubkon($total_voucher_data_subkon_query, $searchSubkon);
        $total_voucher_data_subkon = $total_voucher_data_subkon_query->first();

        // 3. SUBKON PLAN (Approved) - kept for any remaining use cases but not displayed in the return below if not needed
        $querySubkonPlan = $basePlanQuery('SUBKON');
        if ($request->has('filter_year') && $request->filter_year != 'all') {
            $querySubkonPlan = $querySubkonPlan->whereYear('vouchers.date_voucher', $request->filter_year);
        }
        $querySubkonPlan = $applyFilters($querySubkonPlan, $searchSubkon);
        $total_voucher_plan_data_subkon = $querySubkonPlan->first();

        return response()->json([
            'voucher_payment_plan_all_total' => CustomHelper::formatRupiahWithCurrency(($total_voucher_plan_data_all != null) ? $total_voucher_plan_data_all->jumlah_nilai_transfer : 0),
            'voucher_payment_plan_non_rutin_total' => CustomHelper::formatRupiahWithCurrency(($total_voucher_data_non_rutin != null) ? $total_voucher_data_non_rutin->jumlah_nilai_transfer : 0),
            'voucher_payment_subkon_total' => CustomHelper::formatRupiahWithCurrency(($total_voucher_data_subkon != null) ? $total_voucher_data_subkon->jumlah_nilai_transfer : 0),
            'voucher_payment_plan_subkon_total' => CustomHelper::formatRupiahWithCurrency(($total_voucher_data_subkon != null) ? $total_voucher_data_subkon->jumlah_nilai_transfer : 0),
        ]);
    }

    function index()
    {
        $this->crud->hasAccessOrFail('list');

        $columns = [
            [
                'name'      => 'row_number',
                'type'      => 'row_number',
                'label'     => 'No',
                'orderable' => false,
                'searchable' => false,
            ],
            [
                'name'      => 'bulk_action_all',
                'type'      => 'text',
                'label'     => '<input type="checkbox" class="form-check-input bulk_all_checkbox">',
                'orderable' => false,
                'searchable' => false,
                'visibleInExport' => false,
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
                'label' => 'A/N Rekening',
                'type'      => 'text',
                'name'      => 'account_holder_name',
                'orderable' => true,
            ],
            [
                'label' => trans('backpack::crud.voucher.column.voucher.payment_transfer.label'),
                'type'      => 'text',
                'name'      => 'payment_transfer',
                'orderable' => true,
            ],
            [
                'label' => trans('backpack::crud.voucher.column.voucher.no_voucher.label'),
                'type'      => 'text',
                'name'      => 'no_voucher',
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
        ];

        $columns_subkon = [
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
                'name' => 'subkon_id',
                'type'  => 'text',
                'orderable' => true,
            ],
            [
                'label' => trans('backpack::crud.voucher.column.voucher.bill_number.label'),
                'name' => 'bill_number',
                'type' => 'text',
                'orderable' => true,
            ],
            [
                'label' => trans('backpack::crud.voucher.column.voucher.payment_description.label'),
                'name' => 'payment_description',
                'type' => 'text',
                'orderable' => true,
            ],
            [
                'label' => trans('backpack::crud.voucher.column.voucher.no_po_spk.label'),
                'name' => 'reference_id',
                'type'  => 'text',
                'orderable' => false,
            ],
            [
                'label' => trans('backpack::crud.voucher.column.voucher.total.label'),
                'name' => 'total',
                'type' => 'text',
                'orderable' => true,
            ],
            [
                'label' => trans('backpack::crud.voucher.column.voucher.job_name.label'),
                'name' => 'job_name',
                'type' => 'text',
                'orderable' => true,
            ],
            [
                'label' => trans('backpack::crud.voucher.column.voucher.due_date.label_2'),
                'name' => 'due_date',
                'type' => 'text',
                'orderable' => true,
            ],
        ];

        $this->card->addCard([
            'name' => 'payment_plan',
            'line' => 'top',
            'view' => 'crud::components.card-tab',
            // 'title' => 'Rencana Pembayaran',
            'params' => [
                'tabs' => [
                    [
                        'name' => 'voucher_payment_plan_all',
                        'label' => 'Data',
                        'view' => 'crud::components.datatable',
                        'active' => true,
                        'params' => [
                            'filter' => true,
                            'crud_custom' => $this->crud,
                            'columns' => $columns,
                            'route' => backpack_url('voucher-payment-plan/search?tab=voucher_payment_plan_all&type=all'),
                            'route_export_pdf' => url($this->crud->route . '/export-pdf?tab=voucher_payment_plan_all'),
                            'title_export_pdf' => 'Voucher-payment-plan-all.pdf',
                            'route_export_excel' => url($this->crud->route . '/export-excel?tab=voucher_payment_plan_all'),
                            'title_export_excel' => 'Voucher-payment-plan-all.xlsx',
                        ]
                    ],
                    [
                        'name' => 'voucher_payment_plan_non_rutin',
                        'label' => 'Non Rutin',
                        'view' => 'crud::components.datatable',
                        'active' => false,
                        'params' => [
                            'filter' => true,
                            'crud_custom' => $this->crud,
                            'columns' => $columns_subkon,
                            'route' => backpack_url('voucher-payment-plan/search?tab=voucher_payment_plan_non_rutin&type=NON RUTIN'),
                            'route_export_pdf' => url($this->crud->route . '/export-pdf?tab=voucher_payment_plan_non_rutin'),
                            'title_export_pdf' => 'Voucher-payment-plan-non-rutin.pdf',
                            'route_export_excel' => url($this->crud->route . '/export-excel?tab=voucher_payment_plan_non_rutin'),
                            'title_export_excel' => 'Voucher-payment-plan-non-rutin.xlsx',
                        ]
                    ],
                    [
                        'name' => 'voucher_payment_plan_subkon',
                        'label' => 'Subkon',
                        'view' => 'crud::components.datatable',
                        'active' => false,
                        'params' => [
                            'filter' => true,
                            'crud_custom' => $this->crud,
                            'columns' => $columns_subkon,
                            'route' => backpack_url('voucher-payment-plan/search?tab=voucher_payment_plan_subkon&type=SUBKON'),
                            'route_export_pdf' => url($this->crud->route . '/export-pdf?tab=voucher_payment_plan_subkon'),
                            'title_export_pdf' => 'Voucher-payment-plan-subkon.pdf',
                            'route_export_excel' => url($this->crud->route . '/export-excel?tab=voucher_payment_plan_subkon'),
                            'title_export_excel' => 'Voucher-payment-plan-subkon.xlsx',
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

        $this->data['year_options'] = CustomHelper::getYearOptions('vouchers', 'date_voucher');
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
        $request = request();
        $tab = $request->tab;
        $type = $request->type;
        $settings = Setting::first();
        $new_format_date = 'DD/MM/YYYY';

        $status_file = '';
        if (strpos(url()->current(), 'export-excel')) {
            $status_file = 'excel';
        } else {
            $status_file = 'pdf';
        }

        $wrap_length = [];

        if ($status_file == 'excel' || $status_file == 'pdf') {
            $wrap_length = [
                'width_box' => '100%',
            ];
        }

        CRUD::removeButton('delete');
        CRUD::addButtonFromView('top', 'filter_year', 'filter-year', 'beginning');
        CRUD::addButtonFromView('top', 'bulk-actions-payment-plan', 'bulk-actions-payment-plan', 'beginning');
        CRUD::addButtonFromView('top', 'export-pdf', 'export-pdf', 'end');
        CRUD::addButtonFromView('top', 'export-excel', 'export-excel', 'end');
        CRUD::addButtonFromView('line', 'delete-payment-plan', 'delete-payment-plan', 'beginning');

        if ($tab == 'voucher_payment_plan_subkon' || $tab == 'voucher_payment_plan_non_rutin') {
            CRUD::setModel(Voucher::class);
            CRUD::disableResponsiveTable();

            $payment_type_filter = ($tab == 'voucher_payment_plan_subkon') ? 'SUBKON' : 'NON RUTIN';

            $this->crud->query = $this->crud->query
                ->where('vouchers.payment_status', 'BELUM BAYAR')
                ->where('vouchers.payment_type', $payment_type_filter);

            if ($request->has('filter_year') && $request->filter_year != 'all') {
                $this->crud->query = $this->crud->query->whereYear('vouchers.date_voucher', $request->filter_year);
            }

            if (isset($request->columns[1]['search']['value'])) {
                $this->crud->query = $this->crud->query
                    ->where('no_voucher', 'like', '%' . $request->columns[1]['search']['value'] . '%');
            }

            if (isset($request->columns[2]['search']['value'])) {
                $search = trim($request->columns[2]['search']['value']);
                $this->crud->query = $this->crud->query
                    ->whereHas('subkon', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    });
            }

            if (isset($request->columns[3]['search']['value'])) {
                $this->crud->query = $this->crud->query
                    ->where('bill_number', 'like', '%' . $request->columns[3]['search']['value'] . '%');
            }

            if (isset($request->columns[4]['search']['value'])) {
                $this->crud->query = $this->crud->query
                    ->where('payment_description', 'like', '%' . $request->columns[4]['search']['value'] . '%');
            }

            if (isset($request->columns[5]['search']['value'])) {
                $search = trim($request->columns[5]['search']['value']);
                $this->crud->query = $this->crud->query
                    ->whereHasMorph('reference', [Spk::class, PurchaseOrder::class], function ($query, $type) use ($search) {
                        if ($type === Spk::class) {
                            $query->where('no_spk', 'like', "%{$search}%");
                        } else if ($type === PurchaseOrder::class) {
                            $query->where('po_number', 'like', "%{$search}%");
                        }
                    });
            }

            if (isset($request->columns[6]['search']['value'])) {
                $this->crud->query = $this->crud->query
                    ->where('total', 'like', '%' . $request->columns[6]['search']['value'] . '%');
            }

            if (isset($request->columns[7]['search']['value'])) {
                $this->crud->query = $this->crud->query
                    ->where('job_name', 'like', '%' . $request->columns[7]['search']['value'] . '%');
            }

            if (isset($request->columns[8]['search']['value'])) {
                $this->crud->query = $this->crud->query
                    ->where('due_date', 'like', '%' . $request->columns[8]['search']['value'] . '%');
            }


            CRUD::addColumn([
                'name'      => 'row_number',
                'type'      => 'row_number',
                'label'     => 'No',
                'orderable' => false,
            ])->makeFirstColumn();

            CRUD::addColumn([
                'label' => trans('backpack::crud.voucher.column.voucher.no_voucher.label'),
                'type'      => 'text',
                'name'      => 'no_voucher',
            ]);

            CRUD::addColumn([
                'label' => trans('backpack::crud.voucher.column.voucher.bussines_entity_name.label'),
                'name' => 'subkon_id',
                'type'  => 'closure',
                'function' => function ($entry) {
                    return $entry?->subkon?->name;
                }
            ]);

            CRUD::addColumn([
                'label' => trans('backpack::crud.voucher.column.voucher.bill_number.label'),
                'name' => 'bill_number',
                'type' => 'text',
            ]);

            CRUD::addColumn([
                'label' => trans('backpack::crud.voucher.column.voucher.payment_description.label'),
                'name' => 'payment_description',
                'type' => 'wrap_text',
                ...$wrap_length,
            ]);

            CRUD::column(
                [
                    'label' => trans('backpack::crud.voucher.column.voucher.no_po_spk.label'),
                    'name' => 'reference_id',
                    'type'  => 'closure',
                    'function' => function ($entry) {
                        if ($entry->reference_type == Spk::class) {
                            return $entry?->reference?->no_spk;
                        }
                        return $entry?->reference?->po_number;
                    }
                ],
            );

            CRUD::addColumn([
                'label' => trans('backpack::crud.voucher.column.voucher.total.label'),
                'name' => 'total',
                'type' => 'closure',
                'function' => function ($entry) use ($status_file) {
                    return $this->priceFormatExport($status_file, $entry->total);
                },
                // 'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
                'orderable' => false,
            ]);

            CRUD::addColumn([
                'label' => trans('backpack::crud.voucher.column.voucher.job_name.label'),
                'name' => 'job_name',
                'type' => 'wrap_text',
                ...$wrap_length,
            ]);

            CRUD::addColumn([
                'label' => trans('backpack::crud.voucher.column.voucher.due_date.label_2'),
                'name' => 'due_date',
                'type' => 'date',
                'format' => $new_format_date,
            ]);

            return;
        }

        CRUD::setModel(PaymentVoucher::class);
        // CRUD::removeButton('create');

        // Store flags for the bulk-actions button blade
        $this->crud->set('is_approver', backpack_user()->hasPermissionTo('APPROVE RENCANA BAYAR'));
        $this->crud->set('has_bulk_delete', $this->crud->hasAccess('delete'));


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

        if ($tab == 'voucher_payment_plan_subkon') {
            $this->crud->query = $this->crud->query->where('vouchers.payment_type', 'SUBKON');
        } else if ($tab == 'voucher_payment_plan_non_rutin') {
            $this->crud->query = $this->crud->query->where('vouchers.payment_type', 'NON RUTIN');
        }

        if ($request->has('filter_year') && $request->filter_year != 'all') {
            $this->crud->query = $this->crud->query->whereYear('vouchers.date_voucher', $request->filter_year);
        }
        // ->where('approvals.status', Approval::APPROVED);

        // Column indices shifted +1 to account for bulk_checkbox at index 0
        // Column indices shifted to match new order (row_number at 0, bulk_checkbox at 1)
        if ($request->has('columns')) {
            // bank (2)
            if (trim($request->columns[2]['search']['value'] ?? '') != '') {
                $this->crud->query = $this->crud->query
                    ->whereHas('voucher.subkon', function ($q) use ($request) {
                        $q->where('bank_name', 'like', '%' . $request->columns[2]['search']['value'] . '%');
                    });
            }

            // number_account (3)
            if (trim($request->columns[3]['search']['value'] ?? '') != '') {
                $this->crud->query = $this->crud->query
                    ->whereHas('voucher.subkon', function ($q) use ($request) {
                        $q->where('bank_account', 'like', '%' . $request->columns[3]['search']['value'] . '%');
                    });
            }

            // account_holder_name (4)
            if (trim($request->columns[4]['search']['value'] ?? '') != '') {
                $search = $request->columns[4]['search']['value'];
                $this->crud->query = $this->crud->query
                    ->where(function ($query) use ($search) {
                        $query->where('vouchers.account_holder_name', 'like', '%' . $search . '%')
                            ->orWhereHas('voucher', function ($q) use ($search) {
                                $q->whereHasMorph('reference', [Spk::class, PurchaseOrder::class], function ($query, $type) use ($search) {
                                    $query->whereHas('subkon', function ($sub) use ($search) {
                                        $sub->where('account_holder_name', 'like', "%{$search}%");
                                    });
                                });
                            });
                    });
            }

            // payment_transfer (5)
            if (trim($request->columns[5]['search']['value'] ?? '') != '') {
                $this->crud->query = $this->crud->query
                    ->where('vouchers.payment_transfer', 'like', '%' . $request->columns[5]['search']['value'] . '%');
            }

            // no_voucher (6)
            if (trim($request->columns[6]['search']['value'] ?? '') != '') {
                $this->crud->query = $this->crud->query
                    ->where('vouchers.no_voucher', 'like', '%' . $request->columns[6]['search']['value'] . '%');
            }

            // bill_number (7)
            if (trim($request->columns[7]['search']['value'] ?? '') != '') {
                $this->crud->query = $this->crud->query
                    ->where('vouchers.bill_number', 'like', '%' . $request->columns[7]['search']['value'] . '%');
            }

            // payment_description (8)
            if (trim($request->columns[8]['search']['value'] ?? '') != '') {
                $this->crud->query = $this->crud->query
                    ->where('vouchers.payment_description', 'like', '%' . $request->columns[8]['search']['value'] . '%');
            }

            // no_po_spk (9)
            if (trim($request->columns[9]['search']['value'] ?? '') != '') {
                $search = $request->columns[9]['search']['value'];
                $this->crud->query = $this->crud->query
                    ->whereHas('voucher', function ($q) use ($search) {
                        $q->whereHasMorph('reference', [Spk::class, PurchaseOrder::class], function ($query, $type) use ($search) {
                            if ($type === Spk::class) {
                                $query->where('no_spk', 'like', '%' . $search . '%');
                            } else if ($type === PurchaseOrder::class) {
                                $query->where('po_number', 'like', '%' . $search . '%');
                            }
                        });
                    });
            }

            // factur_status (10)
            if (trim($request->columns[10]['search']['value'] ?? '') != '') {
                $this->crud->query = $this->crud->query
                    ->where('vouchers.factur_status', 'like', $request->columns[10]['search']['value'] . '%');
            }

            // job_name (11)
            if (trim($request->columns[11]['search']['value'] ?? '') != '') {
                $this->crud->query = $this->crud->query
                    ->where('vouchers.job_name', 'like', '%' . $request->columns[11]['search']['value'] . '%');
            }

            // due_date (12)
            if (trim($request->columns[12]['search']['value'] ?? '') != '') {
                $this->crud->query = $this->crud->query
                    ->where('vouchers.due_date', 'like', '%' . $request->columns[12]['search']['value'] . '%');
            }

            // payment_type (13)
            if (trim($request->columns[13]['search']['value'] ?? '') != '') {
                $this->crud->query = $this->crud->query
                    ->where('vouchers.payment_type', 'like', '%' . $request->columns[13]['search']['value'] . '%');
            }

            // status (14)
            if (trim($request->columns[14]['search']['value'] ?? '') != '') {
                $this->crud->query = $this->crud->query
                    ->where('approvals.status', 'like', '%' . $request->columns[14]['search']['value'] . '%');
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
        ]);

        CRUD::addColumn([
            'name'     => 'bulk_checkbox',
            'label'    => '<input type="checkbox" id="bulk-select-all" class="bulk-checkbox-header" />',
            'type'     => 'custom_html',
            'value'    => function ($entry) {
                $noApprv = $entry->user_live_no_apprv ?? '';
                $userId = $entry->user_live_user_id ?? '';
                return '<input type="checkbox" class="form-check-input bulk-checkbox" data-id="' . $entry->voucer_edit_id . '" data-no-apprv="' . $noApprv . '" data-user-id="' . $userId . '" />';
            },
            'orderable' => false,
            'searchable' => false,
            'visibleInExport' => false,
            'escaped' => false,
        ]);

        CRUD::column(
            [
                'label' => trans('backpack::crud.voucher.column.voucher.bank.label'),
                'name' => 'bank_name',
                'type'  => 'closure',
                'width_box' => '70px',
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
                'width_box' => '120px',
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
                'label' => 'A/N Rekening',
                'name' => 'account_holder_name',
                'type'  => 'closure',
                'function' => function ($entry) {
                    return $entry->account_holder_name ?? $entry?->voucher?->reference?->subkon?->account_holder_name;
                },
            ],
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
            ], // BELUM FILTER
        );

        CRUD::column([
            'label' => trans('backpack::crud.voucher.column.voucher.due_date.label_2'),
            'name' => 'due_date',
            'type'  => 'date',
            'format' => $new_format_date,
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
                'label'  => trans('backpack::crud.voucher.column.voucher.status.label'),
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

        if (request()->has('filter_year') && request()->filter_year != 'all') {
            $this->crud->query = $this->crud->query->whereYear('vouchers.date_voucher', request()->filter_year);
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

    public function searchDummy()
    {
        // === DUMMY DATA (hapus block ini untuk production) ===
        $draw = (int) request()->input('draw', 1);
        $dummyRows = [];
        $dummyData = [
            ['VCR-2026-001', 'PT Subkon Abadi', 'Bank Mandiri', '1234567890', 'INV-001', 'Pembayaran Termin 1', 'PO-2026-001', 50000000, 'Faktur Pajak', 'Gedung A Lt.3', '2026-03-15', 'Transfer'],
            ['VCR-2026-002', 'CV Maju Jaya', 'Bank BCA', '9876543210', 'INV-002', 'Pembayaran Material', 'SPK-2026-005', 32500000, 'Non Faktur', 'Jembatan Kali Besar', '2026-03-20', 'Transfer'],
            ['VCR-2026-003', 'PT Bangun Karya', 'Bank BRI', '5551234567', 'INV-003', 'Termin 2 Pekerjaan Sipil', 'PO-2026-008', 78000000, 'Faktur Pajak', 'Rumah Sakit Baru', '2026-04-01', 'Giro'],
            ['VCR-2026-004', 'PT Sumber Rejeki', 'Bank Mandiri', '7778889990', 'INV-004', 'Pembayaran Sewa Alat', 'SPK-2026-012', 15000000, 'Non Faktur', 'Proyek Tol Selatan', '2026-03-25', 'Transfer'],
            ['VCR-2026-005', 'CV Teknik Andal', 'Bank BNI', '3334445556', 'INV-005', 'Termin 3 Finishing', 'PO-2026-015', 42000000, 'Faktur Pajak', 'Apartemen Citra', '2026-04-10', 'Transfer'],
            ['VCR-2026-001', 'PT Subkon Abadi', 'Bank Mandiri', '1234567890', 'INV-001', 'Pembayaran Termin 1', 'PO-2026-001', 50000000, 'Faktur Pajak', 'Gedung A Lt.3', '2026-03-15', 'Transfer'],
            ['VCR-2026-002', 'CV Maju Jaya', 'Bank BCA', '9876543210', 'INV-002', 'Pembayaran Material', 'SPK-2026-005', 32500000, 'Non Faktur', 'Jembatan Kali Besar', '2026-03-20', 'Transfer'],
            ['VCR-2026-003', 'PT Bangun Karya', 'Bank BRI', '5551234567', 'INV-003', 'Termin 2 Pekerjaan Sipil', 'PO-2026-008', 78000000, 'Faktur Pajak', 'Rumah Sakit Baru', '2026-04-01', 'Giro'],
            ['VCR-2026-004', 'PT Sumber Rejeki', 'Bank Mandiri', '7778889990', 'INV-004', 'Pembayaran Sewa Alat', 'SPK-2026-012', 15000000, 'Non Faktur', 'Proyek Tol Selatan', '2026-03-25', 'Transfer'],
            ['VCR-2026-005', 'CV Teknik Andal', 'Bank BNI', '3334445556', 'INV-005', 'Termin 3 Finishing', 'PO-2026-015', 42000000, 'Faktur Pajak', 'Apartemen Citra', '2026-04-10', 'Transfer'],
            ['VCR-2026-001', 'PT Subkon Abadi', 'Bank Mandiri', '1234567890', 'INV-001', 'Pembayaran Termin 1', 'PO-2026-001', 50000000, 'Faktur Pajak', 'Gedung A Lt.3', '2026-03-15', 'Transfer'],
            ['VCR-2026-002', 'CV Maju Jaya', 'Bank BCA', '9876543210', 'INV-002', 'Pembayaran Material', 'SPK-2026-005', 32500000, 'Non Faktur', 'Jembatan Kali Besar', '2026-03-20', 'Transfer'],
            ['VCR-2026-003', 'PT Bangun Karya', 'Bank BRI', '5551234567', 'INV-003', 'Termin 2 Pekerjaan Sipil', 'PO-2026-008', 78000000, 'Faktur Pajak', 'Rumah Sakit Baru', '2026-04-01', 'Giro'],
            ['VCR-2026-004', 'PT Sumber Rejeki', 'Bank Mandiri', '7778889990', 'INV-004', 'Pembayaran Sewa Alat', 'SPK-2026-012', 15000000, 'Non Faktur', 'Proyek Tol Selatan', '2026-03-25', 'Transfer'],
            ['VCR-2026-005', 'CV Teknik Andal', 'Bank BNI', '3334445556', 'INV-005', 'Termin 3 Finishing', 'PO-2026-015', 42000000, 'Faktur Pajak', 'Apartemen Citra', '2026-04-10', 'Transfer'],
            ['VCR-2026-001', 'PT Subkon Abadi', 'Bank Mandiri', '1234567890', 'INV-001', 'Pembayaran Termin 1', 'PO-2026-001', 50000000, 'Faktur Pajak', 'Gedung A Lt.3', '2026-03-15', 'Transfer'],
            ['VCR-2026-002', 'CV Maju Jaya', 'Bank BCA', '9876543210', 'INV-002', 'Pembayaran Material', 'SPK-2026-005', 32500000, 'Non Faktur', 'Jembatan Kali Besar', '2026-03-20', 'Transfer'],
            ['VCR-2026-003', 'PT Bangun Karya', 'Bank BRI', '5551234567', 'INV-003', 'Termin 2 Pekerjaan Sipil', 'PO-2026-008', 78000000, 'Faktur Pajak', 'Rumah Sakit Baru', '2026-04-01', 'Giro'],
            ['VCR-2026-004', 'PT Sumber Rejeki', 'Bank Mandiri', '7778889990', 'INV-004', 'Pembayaran Sewa Alat', 'SPK-2026-012', 15000000, 'Non Faktur', 'Proyek Tol Selatan', '2026-03-25', 'Transfer'],
            ['VCR-2026-005', 'CV Teknik Andal', 'Bank BNI', '3334445556', 'INV-005', 'Termin 3 Finishing', 'PO-2026-015', 42000000, 'Faktur Pajak', 'Apartemen Citra', '2026-04-10', 'Transfer'],
            ['VCR-2026-004', 'PT Sumber Rejeki', 'Bank Mandiri', '7778889990', 'INV-004', 'Pembayaran Sewa Alat', 'SPK-2026-012', 15000000, 'Non Faktur', 'Proyek Tol Selatan', '2026-03-25', 'Transfer'],
            ['VCR-2026-005', 'CV Teknik Andal', 'Bank BNI', '3334445556', 'INV-005', 'Termin 3 Finishing', 'PO-2026-015', 42000000, 'Faktur Pajak', 'Apartemen Citra', '2026-04-10', 'Transfer'],
        ];

        $start = (int) request()->input('start');
        $length = (int) request()->input('length');

        $dataset = collect($dummyData);

        $total = $dataset->count();

        if ($start) {
            $dataset = $dataset->skip($start);
        }

        if ($length) {
            $dataset = $dataset->take($length);
        }

        foreach ($dataset->all() as $i => $d) {
            $fakeId = $i + 101; // fake voucer_edit_id
            $checkbox = '<input type="checkbox" class="form-check-input bulk-checkbox" data-id="' . $fakeId . '" data-no-apprv="1" />';
            $statusBadge = ($i % 2 == 0)
                ? '<span class="badge bg-warning text-dark">Pending</span>'
                : '<span class="badge bg-success">Approved</span>';
            $approvalList = '<ul><li class="text-success">Admin 1</li><li>Admin 2</li></ul>';
            $actionButtons = '<a href="javascript:void(0)" class="btn btn-sm btn-link"><i class="la la-trash"></i></a>';

            $dummyRows[] = [
                $checkbox,                                          // bulk_checkbox
                '<strong>' . ($i + 1) . '</strong>',               // row_number
                $d[0],                                              // no_voucher
                $d[1],                                              // bussines_entity_name
                $d[2],                                              // bank
                $d[3],                                              // number_account
                $d[4],                                              // bill_number
                $d[5],                                              // payment_description
                $d[6],                                              // no_po_spk
                number_format($d[7], 0, ',', '.'),                  // payment_transfer
                $d[8],                                              // factur_status
                $d[9],                                              // job_name
                $d[10],                                             // due_date
                $d[11],                                             // payment_type
                $statusBadge,                                       // status
                $approvalList,                                      // user_approval
                $actionButtons,                                     // action
            ];
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $dummyRows,
        ]);
        // === END DUMMY DATA ===
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
                        $payment_voucher = PaymentVoucher::where('voucher_id', $id_voucher)->first();
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

        CRUD::addField([
            'name' => 'voucher',
            'label' => '',
            'type' => 'voucher-list-ajax',
        ]);
    }

    public function datatableVoucher()
    {
        $settings = Setting::first();
        $v_e = DB::table('voucher_edit')
            ->select(DB::raw('MAX(id) as id'), 'voucher_id')
            ->groupBy('voucher_id');

        $a_p = DB::table('approvals')
            ->select(DB::raw('MAX(id) as id'), 'model_type', 'model_id')
            ->groupBy('model_type', 'model_id');

        $query = Voucher::leftJoin('accounts', 'accounts.id', '=', 'vouchers.account_id')
            ->leftJoin('subkons', 'subkons.id', '=', 'vouchers.subkon_id')
            ->leftJoinSub($v_e, 'v_e', function ($join) {
                $join->on('v_e.voucher_id', '=', 'vouchers.id');
            })
            ->leftJoin('voucher_edit', 'voucher_edit.id', '=', 'v_e.id')
            ->leftJoinSub($a_p, 'a_p', function ($join) {
                $join->on('a_p.model_id', '=', 'voucher_edit.id')
                    ->where('a_p.model_type', '=', DB::raw('"App\\\\Models\\\\VoucherEdit"'));
            })
            ->leftJoin('approvals', 'approvals.id', '=', 'a_p.id')
            ->leftJoin('spk', function ($join) {
                $join->on('spk.id', '=', 'vouchers.reference_id')
                    ->where('vouchers.reference_type', '=', DB::raw('"App\\\\Models\\\\Spk"'));
            })
            ->leftJoin('purchase_orders', function ($join) {
                $join->on('purchase_orders.id', '=', 'vouchers.reference_id')
                    ->where('vouchers.reference_type', '=', DB::raw('"App\\\\Models\\\\PurchaseOrder"'));
            })
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('payment_vouchers')
                    ->whereColumn('payment_vouchers.voucher_id', 'vouchers.id');
            })
            ->where('approvals.status', Approval::APPROVED)
            ->select(DB::raw("
                vouchers.*,
                subkons.name as subkon_name,
                spk.no_spk as spk_no,
                purchase_orders.po_number as po_no
            "));

        $totalData = (clone $query)->count('vouchers.id');

        // Searching
        if ($search = request('search')['value']) {
            $query->where(function ($q) use ($search) {
                $q->where('vouchers.no_voucher', 'like', "%{$search}%")
                    ->orWhere('vouchers.account_holder_name', 'like', "%{$search}%")
                    ->orWhere('subkons.name', 'like', "%{$search}%")
                    ->orWhere('spk.no_spk', 'like', "%{$search}%")
                    ->orWhere('vouchers.payment_description', 'like', "%{$search}%")
                    ->orWhere('purchase_orders.po_number', 'like', "%{$search}%");
            });
        }

        $totalFiltered = (clone $query)->count('vouchers.id');

        // Ordering for restricted columns
        $order = request('order');
        if (isset($order[0]['column'])) {
            $columnIndex = $order[0]['column'];
            $columnDir = $order[0]['dir'];
            $columns = request('columns');
            $columnData = $columns[$columnIndex]['data'] ?? '';
            $columnName = $columns[$columnIndex]['name'] ?? '';

            if ($columnName == 'date_voucher' || $columnData == 'date_voucher') {
                $query->orderBy('vouchers.date_voucher', $columnDir);
            } elseif ($columnName == 'payment_type' || $columnData == 'payment_type') {
                $query->orderBy('vouchers.payment_type', $columnDir);
            } else {
                $query->orderBy('vouchers.date_voucher', 'desc');
            }
        } else {
            $query->orderBy('vouchers.date_voucher', 'desc');
        }

        $vouchers = $query->offset(request('start'))
            ->limit(request('length'))
            ->get();

        $data = [];
        foreach ($vouchers as $v) {
            $data[] = [
                'id' => $v->id,
                'no_voucher' => $v->no_voucher,
                'date_voucher' => Carbon::parse($v->date_voucher)->format('d/m/Y'),
                'subkon_name' => $v->account_holder_name ?? $v->reference->subkon->account_holder_name,
                'bill_date' => Carbon::parse($v->bill_date)->format('d/m/Y'),
                'reference_no' => ($v->reference_type == 'App\Models\Spk') ? $v->spk_no : $v->po_no,
                'payment_transfer' => ($settings?->currency_symbol) ? $settings->currency_symbol . ' ' . CustomHelper::formatRupiah($v->payment_transfer) : "Rp." . CustomHelper::formatRupiah($v->payment_transfer),
                'due_date' => Carbon::parse($v->due_date)->format('d/m/Y'),
                'factur_status' => $v->factur_status,
                'payment_type' => $v->payment_type,
                'payment_description' => $v->payment_description,
            ];
        }

        return response()->json([
            "draw" => intval(request('draw')),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data" => $data
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

            $voucher = $request->voucher;
            foreach ($voucher as $id_v) {
                $voucher = Voucher::find($id_v);
                if ($voucher->payment_type == 'SUBKON') {
                    $event['crudTable-voucher_payment_plan_subkon_create_success'] = true;
                } else {
                    $event['crudTable-voucher_payment_plan_non_rutin_create_success'] = true;
                }
                $event['crudTable-voucher_payment_plan_all_create_success'] = true;
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

    public function storeSingle()
    {
        $this->crud->hasAccessOrFail('create');
        $request = request();
        $request->validate([
            'id' => 'required|exists:vouchers,id',
        ]);

        $this->crud->registerFieldEvents();

        DB::beginTransaction();
        try {

            $event = [];
            $event['crudTable-filter_voucher_payment_plugin_load'] = true;

            $voucher = Voucher::find($request->id);
            $voucher->payment_status = 'BAYAR';
            $voucher->payment_date = $request->date;
            $voucher->save();
            $type = '';
            if ($voucher->payment_type == 'NON RUTIN') {
                $type = 'NON RUTIN';
                $event['crudTable-voucher_payment_non_rutin_create_success'] = true;
                $event['crudTable-voucher_payment_plan_non_rutin_create_success'] = true;
            } else {
                $type = 'SUBKON';
                $event['crudTable-voucher_payment_rutin_create_success'] = true;
                $event['crudTable-voucher_payment_plan_rutin_create_success'] = true;
            }
            CustomHelper::voucherPayment($voucher);

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
            ];

            if ($voucher->payment_type == 'SUBKON') {
                $messages['events']['crudTable-voucher_payment_plan_subkon_create_success'] = true;
            } else {
                $messages['events']['crudTable-voucher_payment_plan_non_rutin_create_success'] = true;
            }

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

    public function bulkApprove()
    {
        // === PRODUCTION MODE ===
        $this->crud->hasAccessOrFail('create');

        $user = backpack_user();
        if (!$user->hasPermissionTo('APPROVE RENCANA BAYAR')) {
            return response()->json([
                'success' => false,
                'error' => 'Anda tidak memiliki izin untuk melakukan approve.',
            ], 403);
        }

        $entries = json_decode(request()->entries, true);

        if (empty($entries)) {
            return response()->json([
                'success' => false,
                'error' => 'Tidak ada item yang dipilih.',
            ]);
        }

        DB::beginTransaction();
        try {
            $approved_count = 0;
            $user_id = $user->id;

            $event = [];

            foreach ($entries as $entry) {
                $payment_plan_id = $entry['id'];
                $no_apprv = $entry['no_apprv'] ?? null;

                $voucher_payment_plan = PaymentVoucherPlan::find($payment_plan_id);
                if (!$voucher_payment_plan) {
                    continue;
                }

                $payment_voucher = PaymentVoucher::where('id', $voucher_payment_plan->payment_voucher_id)->first();

                if ($payment_voucher->voucher->payment_type == 'SUBKON') {
                    $event['crudTable-voucher_payment_plan_subkon_create_success'] = true;
                } else {
                    $event['crudTable-voucher_payment_plan_non_rutin_create_success'] = true;
                }
                $event['crudTable-voucher_payment_plan_all_create_success'] = true;

                $approval = Approval::where('model_type', PaymentVoucherPlan::class)
                    ->where('model_id', $voucher_payment_plan->id)
                    ->where('user_id', $user_id)
                    ->where('no_apprv', $no_apprv)
                    ->first();

                if (!$approval || $approval->status !== 'Pending') {
                    continue;
                }

                // Check Sequential Approval (must approve previous stage first)
                if ($no_apprv > 1) {
                    $prev_approvals_count = Approval::where('model_type', PaymentVoucherPlan::class)
                        ->where('model_id', $voucher_payment_plan->id)
                        ->where('no_apprv', '<', $no_apprv)
                        ->where('status', '!=', Approval::APPROVED)
                        ->count();

                    if ($prev_approvals_count > 0) {
                        continue; // Skip because previous stages are not approved yet
                    }
                }

                $final_approval = Approval::where('model_type', PaymentVoucherPlan::class)
                    ->where('model_id', $voucher_payment_plan->id)
                    ->orderBy('no_apprv', 'DESC')->first();

                $approval->status = Approval::APPROVED;
                $approval->approved_at = Carbon::now();
                $approval->save();

                $approved_count++;
            }

            $event['crudTable-filter_voucher_payment_plugin_load'] = true;


            DB::commit();
            return response()->json([
                'success' => true,
                'approved_count' => $approved_count,
                'events' => $event,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function bulkDelete()
    {
        // === PRODUCTION MODE ===
        $this->crud->hasAccessOrFail('delete');

        $entries = json_decode(request()->entries, true);

        if (empty($entries)) {
            return response()->json([
                'success' => false,
                'error' => 'Tidak ada item yang dipilih.',
            ]);
        }

        DB::beginTransaction();
        try {
            $deleted_count = 0;
            $event = [
                'crudTable-filter_voucher_payment_plugin_load' => true,
            ];

            foreach ($entries as $payment_plan_id) {
                $payment_voucher_plan = PaymentVoucherPlan::find($payment_plan_id);
                if (!$payment_voucher_plan) {
                    continue;
                }

                $payment_voucher = PaymentVoucher::find($payment_voucher_plan->payment_voucher_id);
                if (!$payment_voucher) {
                    continue;
                }

                $voucher = Voucher::find($payment_voucher->voucher_id);
                if ($voucher->payment_type == 'SUBKON') {
                    $event['crudTable-voucher_payment_plan_subkon_create_success'] = true;
                } else {
                    $event['crudTable-voucher_payment_plan_non_rutin_create_success'] = true;
                }
                $event['crudTable-voucher_payment_plan_all_create_success'] = true;

                Approval::where('model_type', 'App\Models\PaymentVoucherPlan')
                    ->where('model_id', $payment_voucher_plan->id)
                    ->delete();

                $payment_voucher->delete();
                $payment_voucher_plan->delete();

                if ($voucher) {
                    CustomVoid::rollbackPayment(Voucher::class, $voucher->id, "CREATE_PLAN_PAYMENT_VOUCHER");
                    CustomVoid::rollbackPayment(Voucher::class, $voucher->id, "CREATE_PAYMENT_VOUCHER");
                }

                $deleted_count++;
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'deleted_count' => $deleted_count,
                'events' => $event,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function exportPdf()
    {
        $type = request()->tab;

        $this->setupListOperation();

        CRUD::removeColumn('bulk_checkbox');
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
                if ($column['name'] == 'row_number') {
                    $item_value = $row_number;
                } elseif ($column['name'] == 'due_date') {
                    // Format date to dd/mm/yyyy
                    $item_value = $item->due_date ? \Carbon\Carbon::parse($item->due_date)->format('d/m/Y') : '-';
                } else {
                    $item_value = $this->crud->getCellView($column, $item, $row_number);
                }

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
        CRUD::removeColumn('bulk_checkbox');
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
                if ($column['name'] == 'row_number') {
                    $item_value = $row_number;
                } elseif ($column['name'] == 'payment_transfer') {
                    // Raw number for price
                    $item_value = $item->payment_transfer;
                } elseif ($column['name'] == 'due_date') {
                    // Format date to dd/mm/yyyy
                    $item_value = $item->due_date ? \Carbon\Carbon::parse($item->due_date)->format('d/m/Y') : '-';
                } else {
                    $item_value = $this->crud->getCellView($column, $item, $row_number);
                }

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
