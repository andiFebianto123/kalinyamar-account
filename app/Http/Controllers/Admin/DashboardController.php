<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Project;
use App\Models\InvoiceClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\CrudController;
use App\Http\Helpers\CustomHelper;
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

    public function totalInvoice(){
        $invoice_exclude_ppn = InvoiceClient::select(DB::raw('SUM(price_total_exclude_ppn + price_dpp) as total'))->get();
        $invoice_laba = InvoiceClient::leftJoin('client_po', 'client_po.id', 'invoice_clients.client_po_id')
        ->select(DB::raw('SUM((invoice_clients.price_total_exclude_ppn + invoice_clients.price_dpp) - client_po.price_total) as total'))->get();
        $invoice_first = InvoiceClient::orderBy('id', 'ASC')->first();
        return [
            'total_omzet_exclude_ppn' => CustomHelper::formatRupiah($invoice_exclude_ppn[0]->total),
            'total_laba' => CustomHelper::formatRupiah($invoice_laba[0]->total),
            'invoice_first_date' => Carbon::parse($invoice_first->invoice_date)
            ->locale(App::getLocale())
            ->translatedFormat('d F Y'), //$invoice_first->invoice_date,
        ];
    }

    public function totalProjects(){
        $projects = Project::groupBy('status_po')->select(DB::raw('
            SUM(price_total_include_ppn) as total,
            status_po as status
        '))->get();
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
            'list_projects' => $projects,
            'total_unpaid_rutin' => CustomHelper::formatRupiah($projects_unpaid_rutin[0]->total ?? 0),
            'total_unpaid_non_rutin' => CustomHelper::formatRupiah($projects_unpaid_non_rutin[0]->total ?? 0),
            'total_tertunda_rutin' => CustomHelper::formatRupiah($projects_Tertunda_rutin[0]->total ?? 0),
            'total_tertunda_non_rutin' => CustomHelper::formatRupiah($projects_Tertunda_non_rutin[0]->total ?? 0),
        ];
    }

    public function totalQuotations(){
        $quotations = Quotation::groupBy('status')
        ->select(DB::raw("SUM(rab) as total, status"))->get();
        foreach($quotations as $quotation){
            $quotation->total = CustomHelper::formatRupiah($quotation->total);
        }
        return [
            'list_quotations' => $quotations,
        ];
    }

    function index(){
        $this->crud->hasAccessOrFail('list');

        // dd($this->totalInvoice());
        // dd($this->totalProjects());
        // dd($this->totalQuotations());

        $this->card->addCard([
            'name' => 'dashboard',
            'line' => 'top',
            'view' => 'crud::components.dashboard',
            'parent_view' => 'crud::components.filter-parent',
            'params' => [
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
