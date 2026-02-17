<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Account;
use App\Models\Setting;
use App\Models\Voucher;
use App\Models\Approval;
use App\Models\ClientPo;
use App\Models\LogPayment;
use App\Models\JournalEntry;
use App\Models\InvoiceClient;
use App\Models\PaymentVoucher;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Helpers\CustomVoid;
use App\Http\Exports\ExportExcel;
use App\Http\Helpers\CustomHelper;
use App\Models\PaymentVoucherPlan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\CrudController;
use App\Http\Controllers\Operation\PermissionAccess;
use App\Models\AccountTransaction;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class VoucherPaymentCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use PermissionAccess;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(PaymentVoucher::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/fa/voucher-payment');
        CRUD::setEntityNameStrings(trans('backpack::crud.voucher_payment.title_header'), trans('backpack::crud.voucher_payment.title_header'));


        $allAccess = [
            'AKSES SEMUA MENU ACCOUNTING',
            'AKSES MENU FA',
            'APPROVE RENCANA BAYAR'
        ];

        $viewMenu = [
            "MENU INDEX FA PEMBAYARAN"
        ];

        $this->settingPermission([
            'create' => [
                'CREATE INDEX FA PEMBAYARAN',
                ...$allAccess
            ],
            'update' => [
                'UPDATE INDEX FA PEMBAYARAN',
                ...$allAccess
            ],
            'delete' => [
                'DELETE INDEX FA PEMBAYARAN',
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
        $non_rutin_filter = $request->non_rutin;
        $rutin_filter = $request->rutin;
        $total_voucher_data_non_rutin = PaymentVoucher::select(DB::raw('SUM(vouchers.payment_transfer) as jumlah_nilai_transfer'));

        $user_id = backpack_user()->id;
        $user_approval = \App\Models\User::permission(['APPROVE RENCANA BAYAR'])
            ->where('id', $user_id)
            ->get();

        $p_v_p = DB::table('payment_voucher_plan')
            ->select(DB::raw('MAX(id) as id'), 'payment_voucher_id')
            ->groupBy('payment_voucher_id');

        $total_voucher_data_non_rutin = $total_voucher_data_non_rutin
            ->leftJoin('vouchers', 'vouchers.id', '=', 'payment_vouchers.voucher_id')
            ->leftJoinSub($p_v_p, 'p_v_p', function ($join) {
                $join->on('p_v_p.payment_voucher_id', '=', 'payment_vouchers.id');
            })
            ->leftJoin('payment_voucher_plan', 'payment_voucher_plan.id', '=', 'p_v_p.id');
        // if($user_approval->count() > 0){
        //     $total_voucher_data_non_rutin = $total_voucher_data_non_rutin
        //     ->leftJoin('approvals', function ($join) use($user_id){
        //         $join->on('approvals.model_id', '=', 'payment_voucher_plan.id')
        //             ->where('approvals.model_type', 'App\\Models\\PaymentVoucherPlan')
        //             ->where('approvals.user_id', $user_id);
        //     });
        // }

        $a_p = DB::table('approvals')
            ->select(DB::raw('MAX(id) as id'), 'model_type', 'model_id')
            ->groupBy('model_type', 'model_id');

        $total_voucher_data_non_rutin = $total_voucher_data_non_rutin
            ->leftJoinSub($a_p, 'a_p', function ($join) {
                $join->on('a_p.model_id', '=', 'payment_voucher_plan.id')
                    ->where('a_p.model_type', '=', DB::raw('"App\\\\Models\\\\PaymentVoucherPlan"'));
            })
            ->leftJoin('approvals', 'approvals.id', '=', 'a_p.id');

        $total_voucher_data_non_rutin = $total_voucher_data_non_rutin
            ->leftJoin('spk', function ($join) {
                $join->on('spk.id', '=', 'vouchers.reference_id')
                    ->where('vouchers.reference_type', '=', DB::raw('"App\\\\Models\\\\Spk"'));
            })
            ->leftJoin('purchase_orders', function ($join) {
                $join->on('purchase_orders.id', '=', 'vouchers.reference_id')
                    ->where('vouchers.reference_type', '=', DB::raw('"App\\\\Models\\\\PurchaseOrder"'));
            })
            ->where('payment_vouchers.payment_type', 'NON RUTIN')
            ->where('vouchers.payment_status', 'BAYAR')
            ->groupBy('payment_vouchers.payment_type');


        if ($request->has('non_rutin')) {
            if (isset($non_rutin_filter['columns'][1]['search']['value'])) {
                $search = trim($non_rutin_filter['columns'][1]['search']['value']);
                if ($search !== '') {
                    $total_voucher_data_non_rutin = $total_voucher_data_non_rutin
                        ->where('no_voucher', 'like', "%{$search}%");
                }
            }

            // Kolom 2: date_voucher
            if (isset($non_rutin_filter['columns'][2]['search']['value'])) {
                $search = trim($non_rutin_filter['columns'][2]['search']['value']);
                if ($search !== '') {
                    $total_voucher_data_non_rutin = $total_voucher_data_non_rutin
                        ->where('date_voucher', 'like', "%{$search}%");
                }
            }

            // Kolom 3: voucher.subkon.name
            if (isset($non_rutin_filter['columns'][3]['search']['value'])) {
                $search = trim($non_rutin_filter['columns'][3]['search']['value']);
                if ($search !== '') {
                    $total_voucher_data_non_rutin = $total_voucher_data_non_rutin
                        ->whereHas('voucher.subkon', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                }
            }

            // Kolom 4: bill_date
            if (isset($non_rutin_filter['columns'][4]['search']['value'])) {
                $search = trim($non_rutin_filter['columns'][4]['search']['value']);
                if ($search !== '') {
                    $total_voucher_data_non_rutin = $total_voucher_data_non_rutin
                        ->where('bill_date', 'like', "%{$search}%");
                }
            }

            // Kolom 5: voucher.reference.po_number
            if (isset($non_rutin_filter['columns'][5]['search']['value'])) {
                $search = trim($non_rutin_filter['columns'][5]['search']['value']);
                if ($search !== '') {
                    $total_voucher_data_non_rutin = $total_voucher_data_non_rutin
                        ->whereHas('voucher', function ($q) use ($search) {
                            $q->whereHasMorph('reference', '*', function ($query) use ($search) {
                                $query->where('po_number', 'like', "%{$search}%")
                                    ->orWhere('no_spk', 'like', '%' . $search . '%');
                            });
                        });
                }
            }

            // Kolom 6: payment_transfer
            if (isset($non_rutin_filter['columns'][6]['search']['value'])) {
                $search = trim($non_rutin_filter['columns'][6]['search']['value']);
                if ($search !== '') {
                    $total_voucher_data_non_rutin = $total_voucher_data_non_rutin
                        ->where('payment_transfer', 'like', "%{$search}%");
                }
            }

            // Kolom 7: due_date
            if (isset($non_rutin_filter['columns'][7]['search']['value'])) {
                $search = trim($non_rutin_filter['columns'][7]['search']['value']);
                if ($search !== '') {
                    $total_voucher_data_non_rutin = $total_voucher_data_non_rutin
                        ->where('due_date', 'like', "%{$search}%");
                }
            }

            // Kolom 8: factur_status
            if (isset($non_rutin_filter['columns'][8]['search']['value'])) {
                $search = trim($non_rutin_filter['columns'][8]['search']['value']);
                if ($search !== '') {
                    $total_voucher_data_non_rutin = $total_voucher_data_non_rutin
                        ->where('factur_status', 'like', "{$search}%");
                }
            }

            // Kolom 9: due_date (lagi)
            if (isset($non_rutin_filter['columns'][9]['search']['value'])) {
                $search = trim($non_rutin_filter['columns'][9]['search']['value']);
                if ($search !== '') {
                    $total_voucher_data_non_rutin = $total_voucher_data_non_rutin
                        ->where('due_date', 'like', "%{$search}%");
                }
            }

            // Kolom 10: payment_status
            if (isset($non_rutin_filter['columns'][10]['search']['value'])) {
                $search = trim($non_rutin_filter['columns'][10]['search']['value']);
                if ($search !== '') {
                    $total_voucher_data_non_rutin = $total_voucher_data_non_rutin
                        ->where('payment_status', 'like', "{$search}%");
                }
            }

            // Kolom 11: approvals.approved_at
            if (isset($non_rutin_filter['columns'][11]['search']['value'])) {
                $search = trim($non_rutin_filter['columns'][11]['search']['value']);
                if ($search !== '') {
                    $total_voucher_data_non_rutin = $total_voucher_data_non_rutin
                        ->where('approvals.approved_at', 'like', "%{$search}%");
                }
            }

            // Kolom 12: approvals.status
            if (isset($non_rutin_filter['columns'][12]['search']['value'])) {
                $search = trim($non_rutin_filter['columns'][12]['search']['value']);
                if ($search !== '') {
                    $total_voucher_data_non_rutin = $total_voucher_data_non_rutin
                        ->where('approvals.status', 'like', "%{$search}%");
                }
            }
        }
        if ($request->has('filter_year') && $request->filter_year != 'all') {
            $total_voucher_data_non_rutin = $total_voucher_data_non_rutin->whereYear('vouchers.date_voucher', $request->filter_year);
        }

        $total_voucher_data_non_rutin = $total_voucher_data_non_rutin->first();




        $total_voucher_data_rutin = PaymentVoucher::select(DB::raw('SUM(vouchers.payment_transfer) as jumlah_nilai_transfer'));
        $p_v_p = DB::table('payment_voucher_plan')
            ->select(DB::raw('MAX(id) as id'), 'payment_voucher_id')
            ->groupBy('payment_voucher_id');
        $total_voucher_data_rutin = $total_voucher_data_rutin
            ->leftJoin('vouchers', 'vouchers.id', '=', 'payment_vouchers.voucher_id')
            ->leftJoinSub($p_v_p, 'p_v_p', function ($join) {
                $join->on('p_v_p.payment_voucher_id', '=', 'payment_vouchers.id');
            })
            ->leftJoin('payment_voucher_plan', 'payment_voucher_plan.id', '=', 'p_v_p.id');
        // if($user_approval->count() > 0){
        //     $total_voucher_data_rutin = $total_voucher_data_rutin
        //     ->leftJoin('approvals', function ($join) use($user_id){
        //         $join->on('approvals.model_id', '=', 'payment_voucher_plan.id')
        //             ->where('approvals.model_type', 'App\\Models\\PaymentVoucherPlan')
        //             ->where('approvals.user_id', $user_id);
        //     });
        // }
        $a_p = DB::table('approvals')
            ->select(DB::raw('MAX(id) as id'), 'model_type', 'model_id')
            ->groupBy('model_type', 'model_id');

        $total_voucher_data_rutin = $total_voucher_data_rutin
            ->leftJoinSub($a_p, 'a_p', function ($join) {
                $join->on('a_p.model_id', '=', 'payment_voucher_plan.id')
                    ->where('a_p.model_type', '=', DB::raw('"App\\\\Models\\\\PaymentVoucherPlan"'));
            })
            ->leftJoin('approvals', 'approvals.id', '=', 'a_p.id');
        $total_voucher_data_rutin = $total_voucher_data_rutin
            ->leftJoin('spk', function ($join) {
                $join->on('spk.id', '=', 'vouchers.reference_id')
                    ->where('vouchers.reference_type', '=', DB::raw('"App\\\\Models\\\\Spk"'));
            })
            ->leftJoin('purchase_orders', function ($join) {
                $join->on('purchase_orders.id', '=', 'vouchers.reference_id')
                    ->where('vouchers.reference_type', '=', DB::raw('"App\\\\Models\\\\PurchaseOrder"'));
            })
            ->where('payment_vouchers.payment_type', 'SUBKON')
            ->where('vouchers.payment_status', 'BAYAR')
            ->groupBy('payment_vouchers.payment_type');

        if ($request->has('rutin')) {
            if (isset($rutin_filter['columns'][1]['search']['value'])) {
                $search = trim($rutin_filter['columns'][1]['search']['value']);
                if ($search !== '') {
                    $total_voucher_data_rutin = $total_voucher_data_rutin
                        ->where('no_voucher', 'like', "%{$search}%");
                }
            }

            // Kolom 2: date_voucher
            if (isset($rutin_filter['columns'][2]['search']['value'])) {
                $search = trim($rutin_filter['columns'][2]['search']['value']);
                if ($search !== '') {
                    $total_voucher_data_rutin = $total_voucher_data_rutin
                        ->where('date_voucher', 'like', "%{$search}%");
                }
            }

            // Kolom 3: voucher.subkon.name
            if (isset($rutin_filter['columns'][3]['search']['value'])) {
                $search = trim($rutin_filter['columns'][3]['search']['value']);
                if ($search !== '') {
                    $total_voucher_data_rutin = $total_voucher_data_rutin
                        ->whereHas('voucher.subkon', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                }
            }

            // Kolom 4: bill_date
            if (isset($rutin_filter['columns'][4]['search']['value'])) {
                $search = trim($rutin_filter['columns'][4]['search']['value']);
                if ($search !== '') {
                    $total_voucher_data_rutin = $total_voucher_data_rutin
                        ->where('bill_date', 'like', "%{$search}%");
                }
            }

            // Kolom 5: voucher.reference.po_number
            if (isset($rutin_filter['columns'][5]['search']['value'])) {
                $search = trim($rutin_filter['columns'][5]['search']['value']);
                if ($search !== '') {
                    $total_voucher_data_rutin = $total_voucher_data_rutin
                        ->whereHas('voucher', function ($q) use ($search) {
                            $q->whereHasMorph('reference', '*', function ($query) use ($search) {
                                $query->where('po_number', 'like', "%{$search}%")
                                    ->orWhere('no_spk', 'like', '%' . $search . '%');
                            });
                        });
                }
            }

            // Kolom 6: payment_transfer
            if (isset($rutin_filter['columns'][6]['search']['value'])) {
                $search = trim($rutin_filter['columns'][6]['search']['value']);
                if ($search !== '') {
                    $total_voucher_data_rutin = $total_voucher_data_rutin
                        ->where('payment_transfer', 'like', "%{$search}%");
                }
            }

            // Kolom 7: due_date
            if (isset($rutin_filter['columns'][7]['search']['value'])) {
                $search = trim($rutin_filter['columns'][7]['search']['value']);
                if ($search !== '') {
                    $total_voucher_data_rutin = $total_voucher_data_rutin
                        ->where('due_date', 'like', "%{$search}%");
                }
            }

            // Kolom 8: factur_status
            if (isset($rutin_filter['columns'][8]['search']['value'])) {
                $search = trim($rutin_filter['columns'][8]['search']['value']);
                if ($search !== '') {
                    $total_voucher_data_rutin = $total_voucher_data_rutin
                        ->where('factur_status', 'like', "{$search}%");
                }
            }

            // Kolom 9: due_date (lagi)
            if (isset($rutin_filter['columns'][9]['search']['value'])) {
                $search = trim($rutin_filter['columns'][9]['search']['value']);
                if ($search !== '') {
                    $total_voucher_data_rutin = $total_voucher_data_rutin
                        ->where('due_date', 'like', "%{$search}%");
                }
            }

            // Kolom 10: payment_status
            if (isset($rutin_filter['columns'][10]['search']['value'])) {
                $search = trim($rutin_filter['columns'][10]['search']['value']);
                if ($search !== '') {
                    $total_voucher_data_rutin = $total_voucher_data_rutin
                        ->where('payment_status', 'like', "{$search}%");
                }
            }

            // Kolom 11: approvals.approved_at
            if (isset($rutin_filter['columns'][11]['search']['value'])) {
                $search = trim($rutin_filter['columns'][11]['search']['value']);
                if ($search !== '') {
                    $total_voucher_data_rutin = $total_voucher_data_rutin
                        ->where('approvals.approved_at', 'like', "%{$search}%");
                }
            }

            // Kolom 12: approvals.status
            if (isset($rutin_filter['columns'][12]['search']['value'])) {
                $search = trim($rutin_filter['columns'][12]['search']['value']);
                if ($search !== '') {
                    $total_voucher_data_rutin = $total_voucher_data_rutin
                        ->where('approvals.status', 'like', "%{$search}%");
                }
            }
        }
        if ($request->has('filter_year') && $request->filter_year != 'all') {
            $total_voucher_data_rutin = $total_voucher_data_rutin->whereYear('vouchers.date_voucher', $request->filter_year);
        }

        $total_voucher_data_rutin = $total_voucher_data_rutin->first();


        return response()->json([
            'voucher_payment_non_rutin_total' => CustomHelper::formatRupiahWithCurrency(($total_voucher_data_non_rutin != null) ? $total_voucher_data_non_rutin->jumlah_nilai_transfer : 0),
            'voucher_payment_rutin_total' => CustomHelper::formatRupiahWithCurrency(($total_voucher_data_rutin != null) ? $total_voucher_data_rutin->jumlah_nilai_transfer : 0),
        ]);
    }

    function index()
    {
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
                            'route' => backpack_url('/fa/voucher-payment/search?tab=voucher_payment&type=NON RUTIN'),
                            'route_export_pdf' => url($this->crud->route . '/export-pdf?tab=voucher_payment&type=NON+RUTIN'),
                            'title_export_pdf' => 'Laporan_voucher_payment_non_rutin.pdf',
                            'route_export_excel' => url($this->crud->route . '/export-excel?tab=voucher_payment&type=NON+RUTIN'),
                            'title_export_excel' => 'Laporan_voucher_payment_non_rutin.xlsx',
                        ]
                    ],
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
                                    'name'      => 'subkon_id',
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
                                    'name'      => 'reference_id',
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
                            'route' => backpack_url('/fa/voucher-payment/search?tab=voucher_payment&type=SUBKON'),
                            'route_export_pdf' => url($this->crud->route . '/export-pdf?tab=voucher_payment&type=SUBKON'),
                            'title_export_pdf' => 'Laporan_voucher_payment_subkon.pdf',
                            'route_export_excel' => url($this->crud->route . '/export-excel?tab=voucher_payment&type=SUBKON'),
                            'title_export_excel' => 'Laporan_voucher_payment_subkon.xlsx',
                        ]
                    ],
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
        $this->data['title_modal_create'] = trans('backpack::crud.voucher_payment.title_modal_create_payment');
        $this->data['title_modal_edit'] = trans('backpack::crud.voucher_payment.title_modal_edit');
        $this->data['title_modal_delete'] = trans('backpack::crud.voucher_payment.title_modal_delete');
        $this->data['cards'] = $this->card;

        $breadcrumbs = [
            'FA' => backpack_url('fa'),
            trans('backpack::crud.voucher_payment.title_header') => backpack_url($this->crud->route)
        ];

        $this->data['breadcrumbs'] = $breadcrumbs;
        $this->data['year_options'] = CustomHelper::getYearOptions('vouchers', 'date_voucher');

        $list = "crud::list-blank" ?? $this->crud->getListView();
        return view($list, $this->data);
    }

    protected function setupListOperation()
    {
        $request = request();
        $tab = $request->tab;
        $type = $request->type;

        $settings = Setting::first();

        $this->crud->set('file_title_export_pdf', "Laporan_voucher_pembayaran.pdf");
        $this->crud->set('file_title_export_excel', "Laporan_voucher_pembayaran.xlsx");
        $this->crud->set('param_uri_export', "?export=1&tab=voucher_payment&type=NON+RUTIN");
        CRUD::addButtonFromView('top', 'export-excel', 'export-excel', 'beginning');
        CRUD::addButtonFromView('top', 'export-pdf', 'export-pdf', 'beginning');
        CRUD::addButtonFromView('top', 'filter_year', 'filter-year', 'beginning');
        // CRUD::addButtonFromView('line', 'void', 'void', 'beginning');
        CRUD::removeButton('delete');
        CRUD::addButtonFromView('line', 'approve_button', 'approve_button', 'end');


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

            // if($user_approval->count() > 0){
            //     $this->crud->query = $this->crud->query
            //     ->leftJoin('approvals', function ($join) use($user_id){
            //         $join->on('approvals.model_id', '=', 'payment_voucher_plan.id')
            //             ->where('approvals.model_type', 'App\\Models\\PaymentVoucherPlan')
            //             ->where('approvals.user_id', $user_id);
            //     });
            // }else{
            //     $a_p = DB::table('approvals')
            //     ->select(DB::raw('MAX(id) as id'), 'model_type', 'model_id')
            //     ->groupBy('model_type', 'model_id');

            //     $this->crud->query = $this->crud->query
            //     ->leftJoinSub($a_p, 'a_p', function ($join) {
            //         $join->on('a_p.model_id', '=', 'payment_voucher_plan.id')
            //         ->where('a_p.model_type', '=', DB::raw('"App\\\\Models\\\\PaymentVoucherPlan"'));
            //     })
            //     ->leftJoin('approvals', 'approvals.id', '=', 'a_p.id');
            // }

            $a_p = DB::table('approvals')
                ->select(DB::raw('MAX(id) as id'), 'model_type', 'model_id')
                ->groupBy('model_type', 'model_id');

            $this->crud->query = $this->crud->query
                ->leftJoinSub($a_p, 'a_p', function ($join) {
                    $join->on('a_p.model_id', '=', 'payment_voucher_plan.id')
                        ->where('a_p.model_type', '=', DB::raw('"App\\\\Models\\\\PaymentVoucherPlan"'));
                })
                ->leftJoin('approvals', 'approvals.id', '=', 'a_p.id');

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

            $this->crud->query = $this->crud->query
                ->leftJoin('spk', function ($join) {
                    $join->on('spk.id', '=', 'vouchers.reference_id')
                        ->where('vouchers.reference_type', '=', DB::raw('"App\\\\Models\\\\Spk"'));
                })
                ->leftJoin('purchase_orders', function ($join) {
                    $join->on('purchase_orders.id', '=', 'vouchers.reference_id')
                        ->where('vouchers.reference_type', '=', DB::raw('"App\\\\Models\\\\PurchaseOrder"'));
                })
                ->where('payment_vouchers.payment_type', 'NON RUTIN')
                ->where('vouchers.payment_status', 'BAYAR');

            if ($request->has('filter_year') && $request->filter_year != 'all') {
                $this->crud->query = $this->crud->query->whereYear('vouchers.date_voucher', $request->filter_year);
            }

            if (isset($request->columns[1]['search']['value'])) {
                $this->crud->query = $this->crud->query
                    ->where('no_voucher', 'like', '%' . $request->columns[1]['search']['value'] . '%');
            }

            if (isset($request->columns[2]['search']['value'])) {
                $this->crud->query = $this->crud->query
                    ->where('date_voucher', 'like', '%' . $request->columns[2]['search']['value'] . '%');
            }

            if (isset($request->columns[3]['search']['value'])) {
                $search = $request->columns[3]['search']['value'];
                $this->crud->query = $this->crud->query
                    ->whereHas('voucher.subkon', function ($q) use ($search) {
                        $q->where('name', 'like', "%$search%");
                    });
            }

            if (isset($request->columns[4]['search']['value'])) {
                $this->crud->query = $this->crud->query
                    ->where('bill_date', 'like', '%' . $request->columns[4]['search']['value'] . '%');
            }

            if (isset($request->columns[5]['search']['value'])) {
                $search = $request->columns[5]['search']['value'];
                $this->crud->query = $this->crud->query
                    ->whereHas('voucher', function ($q) use ($search) {
                        $q->whereHasMorph('reference', '*', function ($query) use ($search) {
                            $query->where('po_number', 'like', '%' . $search . '%')
                                ->orWhere('no_spk', 'like', '%' . $search . '%');
                        });
                    });
            }

            if (isset($request->columns[6]['search']['value'])) {
                $this->crud->query = $this->crud->query
                    ->where('payment_transfer', 'like', '%' . $request->columns[6]['search']['value'] . '%');
            }

            if (isset($request->columns[7]['search']['value'])) {
                $this->crud->query = $this->crud->query
                    ->where('due_date', 'like', '%' . $request->columns[7]['search']['value'] . '%');
            }

            if (isset($request->columns[8]['search']['value'])) {
                $this->crud->query = $this->crud->query
                    ->where('factur_status', 'like', $request->columns[8]['search']['value'] . '%');
            }

            if (isset($request->columns[9]['search']['value'])) {
                $this->crud->query = $this->crud->query
                    ->where('due_date', 'like', '%' . $request->columns[9]['search']['value'] . '%');
            }

            if (isset($request->columns[10]['search']['value'])) {
                $this->crud->query = $this->crud->query
                    ->where('payment_status', 'like', $request->columns[10]['search']['value'] . '%');
            }

            if (isset($request->columns[11]['search']['value'])) {
                $this->crud->query = $this->crud->query
                    ->where('approvals.approved_at', 'like', '%' . $request->columns[11]['search']['value'] . '%');
            }

            if (isset($request->columns[12]['search']['value'])) {
                $this->crud->query = $this->crud->query
                    ->where('approvals.status', 'like', '%' . $request->columns[12]['search']['value'] . '%');
            }

            if (isset($request->columns[13]['search']['value'])) {
                $status_search = trim($request->columns[13]['search']['value']);
                $this->crud->query = $this->crud->query
                    ->whereExists(function ($query) use ($status_search) {
                        $query->select(DB::raw(1))
                            ->from('approvals')
                            ->whereColumn('approvals.model_id', 'payment_voucher_plan.id')
                            ->where('approvals.model_type', 'App\\Models\\PaymentVoucherPlan')
                            ->whereExists(function ($q) use ($status_search) {
                                $q->select(DB::raw(1))
                                    ->from('users')
                                    ->whereColumn('users.id', 'approvals.user_id')
                                    ->where('users.name', 'like', '%' . $status_search . '%');
                            });
                    });
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
                        if ($entry?->voucher?->reference_type == 'App\Models\Spk') {
                            return $entry?->voucher?->reference?->no_spk;
                        }
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

            // CRUD::column([
            //     'label'  => '',
            //     'name' => 'action',
            //     'type'  => 'closure',
            //     'function' => function($entry){
            //         return '';
            //     }
            // ]);

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
                ->where('payment_vouchers.payment_type', 'SUBKON')
                ->where('vouchers.payment_status', 'BAYAR');

            if ($request->has('filter_year') && $request->filter_year != 'all') {
                $this->crud->query = $this->crud->query->whereYear('vouchers.date_voucher', $request->filter_year);
            }

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
                            $query->where('po_number', 'like', '%' . $search . '%')
                                ->orWhere('no_spk', 'like', '%' . $search . '%');
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
                    ->where('factur_status', 'like', $request->columns[8]['search']['value'] . '%');
            }

            if (trim($request->columns[9]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('due_date', 'like', '%' . $request->columns[9]['search']['value'] . '%');
            }

            if (trim($request->columns[10]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('payment_status', 'like', $request->columns[10]['search']['value'] . '%');
            }

            if (trim($request->columns[11]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('approvals.approved_at', 'like', '%' . $request->columns[11]['search']['value'] . '%');
            }

            if (trim($request->columns[12]['search']['value']) != '') {
                $this->crud->query = $this->crud->query
                    ->where('approvals.status', 'like', '%' . $request->columns[12]['search']['value'] . '%');
            }

            if (isset($request->columns[13]['search']['value'])) {
                $status_search = trim($request->columns[13]['search']['value']);
                $this->crud->query = $this->crud->query
                    ->whereExists(function ($query) use ($status_search) {
                        $query->select(DB::raw(1))
                            ->from('approvals')
                            ->whereColumn('approvals.model_id', 'payment_voucher_plan.id')
                            ->where('approvals.model_type', 'App\\Models\\PaymentVoucherPlan')
                            ->whereExists(function ($q) use ($status_search) {
                                $q->select(DB::raw(1))
                                    ->from('users')
                                    ->whereColumn('users.id', 'approvals.user_id')
                                    ->where('users.name', 'like', '%' . $status_search . '%');
                            });
                    });
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
                        if ($entry?->voucher?->reference_type == 'App\Models\Spk') {
                            return $entry?->voucher?->reference?->no_spk;
                        }
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

            // CRUD::column([
            //     'label'  => '',
            //     'name' => 'action',
            //     'type'  => 'closure',
            //     'function' => function($entry){
            //         return '';
            //     }
            // ]);
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


        $p_v_p = DB::table('payment_voucher_plan')
            ->select(DB::raw('MAX(id) as id'), 'payment_voucher_id')
            ->groupBy('payment_voucher_id');

        $a_p = DB::table('approvals')
            ->select(DB::raw('MAX(id) as id'), 'model_type', 'model_id')
            ->groupBy('model_type', 'model_id');


        $voucherList = Voucher::leftJoin('payment_vouchers', 'payment_vouchers.voucher_id', 'vouchers.id')
            ->leftJoin('accounts', 'accounts.id', '=', 'vouchers.account_id')
            ->leftJoinSub($p_v_p, 'p_v_p', function ($join) {
                $join->on('p_v_p.payment_voucher_id', '=', 'payment_vouchers.id');
            })
            ->leftJoin('payment_voucher_plan', 'payment_voucher_plan.id', '=', 'p_v_p.id')
            ->leftJoinSub($a_p, 'a_p', function ($join) {
                $join->on('a_p.model_id', '=', 'payment_voucher_plan.id')
                    ->where('a_p.model_type', '=', DB::raw('"App\\\\Models\\\\PaymentVoucherPlan"'));
            })
            ->leftJoin('approvals', 'approvals.id', '=', 'a_p.id')
            ->where('approvals.status', Approval::APPROVED)
            ->where('vouchers.payment_status', 'BELUM BAYAR')
            ->select(DB::raw("
            vouchers.*,
            accounts.name as account_name,
            accounts.code as account_code,
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
            'name' => 'voucher_payment',
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

            $voucher = $request->voucher;
            foreach ($voucher as $id_v) {
                $voucherItem = Voucher::find($id_v);
                $castAccount = $voucherItem->account_source;
                if ($voucherItem->payment_type == 'NON RUTIN') {
                    $event['crudTable-voucher_payment_non_rutin_create_success'] = true;
                    $event['crudTable-voucher_payment_plan_non_rutin_create_success'] = true;
                } else {
                    $event['crudTable-voucher_payment_rutin_create_success'] = true;
                    $event['crudTable-voucher_payment_plan_rutin_create_success'] = true;
                }

                CustomVoid::voucherPayment($voucherItem);
                $balance_out = CustomHelper::balanceAccount($castAccount->account->code);
                if ($balance_out < 0) {
                    throw new \Exception(trans('backpack::crud.cash_account.field_transfer.errors.balance_not_enough', ['castname' => $castAccount->name]));
                }
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



    public function exportPdf()
    {
        $type = request()->tab;

        $this->setupListOperation();

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

        $title = "VOUCHER PEMBAYARAN";

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

        $name = 'VOUCHER PEMBAYARAN';

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

    public function destroy($id)
    {
        $this->crud->hasAccessOrFail('delete');

        DB::beginTransaction();
        try {

            $event = [];
            $event['crudTable-filter_voucher_payment_plugin_load'] = true;

            $voucherItem = Voucher::find($id);
            if ($voucherItem->payment_type == 'NON RUTIN') {
                $event['crudTable-voucher_payment_non_rutin_create_success'] = true;
                $event['crudTable-voucher_payment_plan_non_rutin_create_success'] = true;
            } else {
                $event['crudTable-voucher_payment_rutin_create_success'] = true;
                $event['crudTable-voucher_payment_plan_rutin_create_success'] = true;
            }

            $messages['success'][] = trans('backpack::crud.delete_confirmation_message');
            $messages['events'] = $event;

            CustomVoid::rollbackPayment(Voucher::class, $id, "CREATE_PAYMENT_VOUCHER");

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
}
