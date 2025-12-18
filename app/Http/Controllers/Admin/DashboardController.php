<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Project;
use App\Models\Voucher;
use App\Models\ClientPo;
use App\Models\Quotation;
use App\Models\InvoiceClient;
use App\Models\ProjectProfitLost;
use App\Http\Helpers\CustomHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;


class DashboardController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;

    public function setup()
    {
        $this->crud->denyAllAccess(['create', 'update', 'delete', 'list', 'show']);
        $user = backpack_user();
        $permissions = $user->getAllPermissions();
        if ($permissions->whereIn('name', [
            'MENU INDEX DASHBOARD'
        ])->count() > 0) {
            $this->crud->allowAccess(['list', 'show']);
        }
        CRUD::setModel(Project::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/dashboard');
        CRUD::setEntityNameStrings(trans('backpack::crud.menu.dashboard'), trans('backpack::crud.menu.dashboard'));
    }

    public function firstInvoice()
    {

        // if($invoice_first == null){
        //     return [
        //         'invoice_first_date' => '',
        //     ];
        // }

        return [
            'invoice_first_date' => Carbon::now()
                ->locale(App::getLocale())
                ->translatedFormat('d F Y'),
        ];
    }

    public function totalProjects()
    {
        $project_status = [
            'CLOSE' => 0,
            'RETENSI' => 0,
            'BELUM_SELESAI' => 0,
            'TERTUNDA' => 0,
            'UNPAID' => 0,
            'BELUM_ADA_PO' => 0,
        ];
        $projects = Project::groupBy('status_po')->select(DB::raw('
            SUM(price_total_include_ppn) as total,
            status_po as status
        '))->get();

        foreach ($projects as $prj) {
            $project_status[str_replace(' ', '_', $prj->status)] = CustomHelper::formatRupiah($prj->total);
        }

        $projects_unpaid_rutin = Project::select(DB::raw('
            SUM(price_total_include_ppn) as total
        '))
            ->where('status_po', 'Unpaid')
            ->where('category', 'RUTIN')
            ->get();
        $projects_unpaid_non_rutin = Project::select(DB::raw('
            SUM(price_total_include_ppn) as total
        '))
            ->where('status_po', 'Unpaid')
            ->where('category', 'NON RUTIN')
            ->get();
        $projects_Tertunda_rutin = Project::select(DB::raw('
            SUM(price_total_include_ppn) as total
        '))
            ->where('status_po', 'Tertunda')
            ->where('category', 'RUTIN')
            ->get();
        $projects_Tertunda_non_rutin = Project::select(DB::raw('
            SUM(price_total_include_ppn) as total
        '))
            ->where('status_po', 'Tertunda')
            ->where('category', 'NON RUTIN')
            ->get();
        return [
            'list_projects' => $project_status,
            'total_unpaid_rutin' => CustomHelper::formatRupiah($projects_unpaid_rutin[0]->total ?? 0),
            'total_unpaid_non_rutin' => CustomHelper::formatRupiah($projects_unpaid_non_rutin[0]->total ?? 0),
            'total_tertunda_rutin' => CustomHelper::formatRupiah($projects_Tertunda_rutin[0]->total ?? 0),
            'total_tertunda_non_rutin' => CustomHelper::formatRupiah($projects_Tertunda_non_rutin[0]->total ?? 0),
        ];
    }

    public function totalQuotations()
    {
        $quotations = Quotation::groupBy('status')
            ->select(DB::raw("SUM(rab) as total, status"))->get();

        $quotation_type = Quotation::select(DB::raw("status"))
            ->groupBy('status')->get();

        $quotation_total = [
            strtoupper('HPS') => 0,
            strtoupper('QUOTATION') => 0,
            strtoupper('CLOSE') => 0,
        ];

        foreach ($quotation_type as $quotation_status) {
            $quotation_total[strtoupper($quotation_status->status)] = 0;
        }

        foreach ($quotations as $quotation) {
            $quotation_total[strtoupper(str_replace(' ', '_', $quotation->status))] = CustomHelper::formatRupiah($quotation->total);
        }

        return [
            'list_quotations' => $quotation_total,
        ];
    }

    public function totalOmzetAll()
    {
        $invoice = ClientPo::select(DB::raw("SUM(job_value) as total"))->get();

        if ($invoice->count() == 0) {
            $total_invoice = 0;
        } else {
            $total_invoice = $invoice[0]->total;
        }
        return [
            'total_omzet' => CustomHelper::formatRupiah($total_invoice),
        ];
    }

    public function totalLabaAll()
    {
        // $voucher = Voucher::select(DB::raw("SUM(bill_value) as total"))->get();
        // $invoice = ClientPo::select(DB::raw("SUM(job_value) as total"))->get();
        $invoice = ClientPo::whereExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('invoice_clients')
                ->whereRaw('invoice_clients.client_po_id = client_po.id');
        })->select(DB::raw("SUM(profit_and_lost_final) as total"))->get();
        if ($invoice->count() == 0) {
            $total_invoice = 0;
        } else {
            $total_invoice = $invoice[0]->total;
        }
        return [
            'total_laba' => CustomHelper::formatRupiah($total_invoice),
        ];
    }

    public function totalJobRealisasion()
    {
        $omset_rutin = InvoiceClient::selectRaw('
            COUNT(id) as total_invoice,
            SUM(price_total_exclude_ppn) as total_omzet
        ')
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('client_po')
                    ->whereColumn('client_po.id', 'invoice_clients.client_po_id')
                    ->where('client_po.category', 'RUTIN')
                    ->whereExists(function ($q2) {
                        $q2->select(DB::raw(1))
                            ->from('project_profit_lost')
                            ->whereColumn('project_profit_lost.client_po_id', 'client_po.id');
                    });
            })
            ->first();

        // $biaya_rutin = Voucher::select(DB::raw('SUM(vouchers.total) as nilai_biaya'))
        //     ->join('client_po', 'client_po.id', '=', 'vouchers.client_po_id')
        //     ->whereExists(function ($query) {
        //         $query->select(DB::raw(1))
        //             ->from('invoice_clients')
        //             ->whereColumn('invoice_clients.client_po_id', 'client_po.id');
        //     })
        //     ->whereExists(function ($query) {
        //         $query->select(DB::raw(1))
        //             ->from('project_profit_lost')
        //             ->whereColumn('project_profit_lost.client_po_id', 'client_po.id');
        //     })
        //     ->where('client_po.category', 'RUTIN')
        //     ->groupBy('client_po.category')
        //     ->get();
        $biaya_rutin = CustomHelper::profitLostRepository()
            ->where('client_po.category', 'RUTIN')
            ->select(DB::raw('SUM((IFNULL(project_profit_lost.price_after_year, 0) + IFNULL(vouchers.biaya, 0) + IFNULL(project_profit_lost.price_small_cash, 0))) as nilai_biaya'))
            ->get();

        $omset_non_rutin = InvoiceClient::selectRaw('
            COUNT(id) as total_invoice,
            SUM(price_total_exclude_ppn) as total_omzet
        ')
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('client_po')
                    ->whereColumn('client_po.id', 'invoice_clients.client_po_id')
                    ->where('client_po.category', 'NON RUTIN')
                    ->whereExists(function ($q2) {
                        $q2->select(DB::raw(1))
                            ->from('project_profit_lost')
                            ->whereColumn('project_profit_lost.client_po_id', 'client_po.id');
                    });
            })
            ->first();

        // $biaya_non_rutin = Voucher::select(DB::raw('SUM(vouchers.total) as nilai_biaya'))
        //     ->join('client_po', 'client_po.id', '=', 'vouchers.client_po_id')
        //     ->whereExists(function ($query) {
        //         $query->select(DB::raw(1))
        //             ->from('invoice_clients')
        //             ->whereColumn('invoice_clients.client_po_id', 'client_po.id');
        //     })
        //     ->whereExists(function ($query) {
        //         $query->select(DB::raw(1))
        //             ->from('project_profit_lost')
        //             ->whereColumn('project_profit_lost.client_po_id', 'client_po.id');
        //     })
        //     ->where('client_po.category', 'NON RUTIN')
        //     ->groupBy('client_po.category')
        //     ->get();

        $biaya_non_rutin = CustomHelper::profitLostRepository()
            ->where('client_po.category', 'NON RUTIN')
            ->select(DB::raw('SUM((IFNULL(project_profit_lost.price_after_year, 0) + IFNULL(vouchers.biaya, 0) + IFNULL(project_profit_lost.price_small_cash, 0))) as nilai_biaya'))
            ->get();

        $total_omzet_rutin = $omset_rutin->total_omzet ?? 0;
        $total_biaya_rutin = $biaya_rutin[0]?->nilai_biaya ?? 0;
        $total_omzet_non_rutin = $omset_non_rutin->total_omzet ?? 0;
        $total_biaya_non_rutin = $biaya_non_rutin[0]?->nilai_biaya ?? 0;

        $total_laba_rutin = $total_omzet_rutin - $total_biaya_rutin;
        $total_laba_non_rutin = $total_omzet_non_rutin - $total_biaya_non_rutin;

        $total_all_laba = $total_laba_rutin + $total_laba_non_rutin;
        $total_all_omzet = $total_omzet_rutin + $total_omzet_non_rutin;

        return [
            'total_omzet_rutin' => CustomHelper::formatRupiah($total_omzet_rutin),
            'total_biaya_rutin' => CustomHelper::formatRupiah($total_biaya_rutin),
            'total_omzet_non_rutin' => CustomHelper::formatRupiah($total_omzet_non_rutin),
            'total_biaya_non_rutin' => CustomHelper::formatRupiah($total_biaya_non_rutin),
            'total_laba_rutin' => CustomHelper::formatRupiah($total_laba_rutin),
            'total_laba_non_rutin' => CustomHelper::formatRupiah($total_laba_non_rutin),
            'total_all_laba' => CustomHelper::formatRupiah($total_all_laba),
            'total_all_omzet' => CustomHelper::formatRupiah($total_all_omzet),
        ];
    }

    public function totalLabaCategory()
    {
        $invoice_rutin = ClientPo::whereExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('invoice_clients')
                ->whereRaw('invoice_clients.client_po_id = client_po.id');
        })
            ->where('client_po.category', 'RUTIN')
            ->select(DB::raw("SUM(profit_and_lost_final) as total"))->get();

        $invoice_non_rutin = ClientPo::whereExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('invoice_clients')
                ->whereRaw('invoice_clients.client_po_id = client_po.id');
        })
            ->where('client_po.category', 'NON RUTIN')
            ->select(DB::raw("SUM(profit_and_lost_final) as total"))->get();

        return [
            'total_laba_rutin' => CustomHelper::formatRupiah($invoice_rutin[0]->total),
            'total_laba_non_rutin' => CustomHelper::formatRupiah($invoice_non_rutin[0]->total),
        ];
    }

    public function dataLabaCategory()
    {

        $voucher_query = DB::table('vouchers')
            ->select("client_po_id", DB::raw("SUM(total) as total"))
            ->groupBy("client_po_id");

        // $invoice_rutin = InvoiceClient::leftJoin('client_po', 'client_po.id', 'invoice_clients.client_po_id')
        //     ->leftJoinSub($voucher_query, 'voucher', function ($join) {
        //         $join->on('voucher.client_po_id', '=', 'client_po.id');
        //     })
        //     ->where('client_po.category', 'RUTIN')
        //     ->select(
        //         DB::raw("client_po.*, invoice_clients.kdp, invoice_clients.price_total_exclude_ppn as price_invoice, voucher.total as total_voucher"),
        //         DB::raw("(IFNULL(invoice_clients.price_total_exclude_ppn,0) - IFNULL(voucher.total,0)) as total_laba")
        //     )
        //     ->get();

        $profit_lost_rutin = CustomHelper::profitLostRepository()
            ->where('client_po.category', 'RUTIN')
            ->get();

        foreach ($profit_lost_rutin as $profit_rutin) {
            $profit_rutin->total_laba = ($profit_rutin->invoice_price_job_exlude_ppn ?? 0) - ($profit_rutin->price_total_str ?? 0);
        }

        // $invoice_non_rutin = InvoiceClient::leftJoin('client_po', 'client_po.id', 'invoice_clients.client_po_id')
        //     ->leftJoinSub($voucher_query, 'voucher', function ($join) {
        //         $join->on('voucher.client_po_id', '=', 'client_po.id');
        //     })
        //     ->where('client_po.category', 'NON RUTIN')
        //     ->select(
        //         DB::raw("client_po.*, invoice_clients.kdp, invoice_clients.price_total_exclude_ppn as price_invoice, voucher.total as total_voucher"),
        //         DB::raw("(IFNULL(invoice_clients.price_total_exclude_ppn,0) - IFNULL(voucher.total,0)) as total_laba")
        //     )->get();

        $profit_lost_non_rutin = CustomHelper::profitLostRepository()
            ->where('client_po.category', 'NON RUTIN')
            ->get();

        foreach ($profit_lost_non_rutin as $profit_non_rutin) {
            $profit_non_rutin->total_laba = ($profit_non_rutin->invoice_price_job_exlude_ppn ?? 0) - ($profit_non_rutin->price_total_str ?? 0);
        }

        return [
            'data_laba_rutin' => $profit_lost_rutin,
            'data_laba_non_rutin' => $profit_lost_non_rutin
        ];
    }

    public function dataNonRutinMonitoring()
    {
        $monitoring_result_1 = ClientPo::selectRaw('
            SUM(job_value) as job_value, 
            COUNT(id) as total_job,
            SUM(profit_and_lost_final) as profit_lost
        ')
            ->where('category', 'NON RUTIN')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('invoice_clients')
                    ->whereColumn('invoice_clients.client_po_id', 'client_po.id');
            })
            ->first();

        $monitoring_result_2 = DB::table('vouchers')
            ->leftJoin('client_po', 'client_po.id', '=', 'vouchers.client_po_id')
            ->selectRaw('SUM(vouchers.payment_transfer) as total_transfer')
            ->where('client_po.category', 'NON RUTIN')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('invoice_clients')
                    ->whereColumn('invoice_clients.client_po_id', 'client_po.id');
            })
            ->first();

        return [
            'total_job_value' => CustomHelper::formatRupiah($monitoring_result_1->job_value ?? 0),
            'total_transfer' => CustomHelper::formatRupiah($monitoring_result_2->total_transfer ?? 0),
            'total_profit_lost' => CustomHelper::formatRupiah(($monitoring_result_1->job_value - $monitoring_result_2->total_transfer) ?? 0),
            'total_job' => $monitoring_result_1->total_job
        ];
    }

    public function totalAlldashboard()
    {
        return [
            'first_invoice' => $this->firstInvoice(),
            'total_projects' => $this->totalProjects(),
            'total_quotations' => $this->totalQuotations(),
            'total_omzet_all' => $this->totalOmzetAll(),
            'total_laba_all' => $this->totalLabaAll(),
            'total_job_realisasion' => $this->totalJobRealisasion(),
            'total_laba_category' => $this->totalLabaCategory(),
        ];
    }

    function index()
    {
        $this->crud->hasAccessOrFail('list');

        // dd($this->totalInvoice());
        // dd($this->totalProjects());
        // dd($this->totalQuotations());
        // dd($this->totalOmzetAll());
        // dd($this->totalLabaAll());
        // dd($this->totalJobRealisasion());
        // dd($this->totalLabaCategory());

        $this->card->addCard([
            'name' => 'dashboard',
            'line' => 'top',
            'view' => 'crud::components.dashboard',
            'parent_view' => 'crud::components.filter-parent',
            'params' => [
                'data_laba' => $this->dataLabaCategory(),
                'data_monitoring' => $this->dataNonRutinMonitoring(),
            ]
        ]);

        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? mb_ucfirst($this->crud->entity_name_plural);
        $this->data['title_modal_create'] = trans('backpack::crud.project_status.title_modal_create');
        $this->data['title_modal_edit'] = trans('backpack::crud.project_status.title_modal_edit');
        $this->data['title_modal_delete'] = trans('backpack::crud.project_status.title_modal_delete');
        $this->data['cards'] = $this->card;

        $breadcrumbs = [
            trans('backpack::crud.menu.monitoring_project') => backpack_url('monitoring'),
            trans('backpack::crud.project_status.title_header') => backpack_url($this->crud->route)
        ];
        // $this->data['breadcrumbs'] = $breadcrumbs;

        // $list = "crud::list-custom" ?? $this->crud->getListView();
        $list = "crud::list-blank" ?? $this->crud->getListView();
        return view($list, $this->data);
    }
}
