<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Project;
use App\Models\InvoiceClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\CrudController;
use App\Http\Helpers\CustomHelper;
use App\Models\ClientPo;
use App\Models\Quotation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;


class DashboardController extends CrudController
{
        use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;

    public function setup()
    {
        CRUD::setModel(Project::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/dashboard');
        CRUD::setEntityNameStrings(trans('backpack::crud.menu.dashboard'), trans('backpack::crud.menu.dashboard'));
    }

    public function firstInvoice(){
        $invoice_first = InvoiceClient::orderBy('id', 'ASC')->first();
        return [
            'invoice_first_date' => Carbon::parse($invoice_first->invoice_date)
            ->locale(App::getLocale())
            ->translatedFormat('d F Y'),
        ];
    }

    public function totalProjects(){
        $project_status = [
            'Close' => 0,
            'Retensi' => 0,
            'Belum_Selesai' => 0,
            'Tertunda' => 0,
            'Unpaid' => 0,
            'Belum_ada_PO' => 0,
        ];
        $projects = Project::groupBy('status_po')->select(DB::raw('
            SUM(price_total_include_ppn) as total,
            status_po as status
        '))->get();

        foreach($projects as $prj){
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

    public function totalQuotations(){
        $quotations = Quotation::groupBy('status')
        ->select(DB::raw("SUM(rab) as total, status"))->get();

        $quotation_total = [
            'HPS' => 0,
            'Quotation' => 0,
            'Close' => 0,
        ];

        foreach($quotations as $quotation){
            $quotation_total[str_replace(' ', '_', $quotation->status)] = CustomHelper::formatRupiah($quotation->total);
        }
        return [
            'list_quotations' => $quotation_total,
        ];
    }

    public function totalOmzetAll(){
        $invoice = ClientPo::whereExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('invoice_clients')
                ->whereRaw('invoice_clients.client_po_id = client_po.id');
        })->select(DB::raw("SUM(job_value) as total"))->get();

        if($invoice->count() == 0){
            $total_invoice = 0;
        }else{
            $total_invoice = $invoice[0]->total;
        }
        return [
            'total_omzet' => CustomHelper::formatRupiah($total_invoice),
        ];
    }

    public function totalLabaAll(){
        $invoice = ClientPo::whereExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('invoice_clients')
                ->whereRaw('invoice_clients.client_po_id = client_po.id');
        })->select(DB::raw("SUM(profit_and_lost_final) as total"))->get();
        if($invoice->count() == 0){
            $total_invoice = 0;
        }else{
            $total_invoice = $invoice[0]->total;
        }
        return [
            'total_laba' => CustomHelper::formatRupiah($total_invoice),
        ];
    }

    public function totalJobRealisasion(){
        $invoice_rutin = ClientPo::whereExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('invoice_clients')
                ->whereRaw('invoice_clients.client_po_id = client_po.id');
        })
        ->where('client_po.category', 'RUTIN')
        ->select(DB::raw("SUM(job_value) as total_omzet, SUM(price_total) as total_biaya"))->get();

        $invoice_non_rutin = ClientPo::whereExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('invoice_clients')
                ->whereRaw('invoice_clients.client_po_id = client_po.id');
        })
        ->where('client_po.category', 'NON RUTIN')
        ->select(DB::raw("SUM(job_value) as total_omzet, SUM(price_total) as total_biaya"))->get();

        return [
            'total_omzet_rutin' => CustomHelper::formatRupiah($invoice_rutin[0]->total_omzet),
            'total_biaya_rutin' => CustomHelper::formatRupiah($invoice_rutin[0]->total_biaya),
            'total_omzet_non_rutin' => CustomHelper::formatRupiah($invoice_non_rutin[0]->total_omzet),
            'total_biaya_non_rutin' => CustomHelper::formatRupiah($invoice_non_rutin[0]->total_biaya),
        ];
    }

    public function totalLabaCategory(){
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

    public function dataLabaCategory(){
        $invoice_rutin = InvoiceClient::leftJoin('client_po', 'client_po.id', 'invoice_clients.client_po_id')
        ->where('client_po.category', 'RUTIN')
        ->select(DB::raw("client_po.*, invoice_clients.kdp"))
        ->get();

        $invoice_non_rutin = InvoiceClient::leftJoin('client_po', 'client_po.id', 'invoice_clients.client_po_id')
        ->where('client_po.category', 'NON RUTIN')
        ->select(DB::raw("client_po.*, invoice_clients.kdp"))
        ->get();

        return [
            'data_laba_rutin' => $invoice_rutin,
            'data_laba_non_rutin' => $invoice_non_rutin
        ];
    }

    public function dataNonRutinMonitoring(){
        $monitoring = ClientPo::whereExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('invoice_clients')
                ->whereRaw('invoice_clients.client_po_id = client_po.id');
        })
        ->where('client_po.category', 'NON RUTIN')
        ->select(DB::raw("
            SUM(job_value) as total_job_value,
            SUM(price_total) as total_price_total,
            SUM(profit_and_lost_final) as total_profit_and_lost_final,
            COUNT(po_number) as total_po_number
        "))->get();
        return $monitoring;
    }

    public function totalAlldashboard(){
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

    function index(){
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
