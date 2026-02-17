<?php

namespace App\Http\Controllers\Admin;

use App\Models\Setting;
use App\Models\Voucher;
use App\Models\ClientPo;
use App\Models\InvoiceClient;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Exports\ExportExcel;
use App\Http\Helpers\CustomHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\ClientPoRequest;
use App\Http\Controllers\CrudController;
use App\Http\Controllers\Operation\FormaterExport;
use App\Http\Controllers\Operation\PermissionAccess;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class ClientPoCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ClientPoCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use PermissionAccess;
    use FormaterExport;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(ClientPo::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/client/po');
        CRUD::setEntityNameStrings(trans('backpack::crud.client_po.title_header'), trans('backpack::crud.client_po.title_header'));

        $allAccess = [
            'AKSES SEMUA MENU ACCOUNTING',
            'AKSES MENU CLIENT',
        ];

        $viewMenu = [
            'MENU INDEX CLIENT PO',
        ];

        $this->settingPermission([
            'create' => [
                'CREATE INDEX CLIENT PO',
                ...$allAccess
            ],
            'update' => [
                'UPDATE INDEX CLIENT PO',
                ...$allAccess
            ],
            'delete' => [
                'DELETE INDEX CLIENT PO',
                ...$allAccess
            ],
            'list' => $viewMenu,
            'show' => $viewMenu,
            'print' => true,
        ]);
    }

    public function index()
    {
        $this->crud->hasAccessOrFail('list');

        $this->crud->filter('status_invoice11crudTable-client_po')
            ->label(trans('backpack::crud.client_po.column.list_invoice'))
            ->type('select2')
            ->values([
                '1' => 'ADA',
                '0' => 'TIDAK ADA',
            ]);

        $this->crud->filter('date_po11crudTable-client_po')
            ->label(trans('backpack::crud.client_po.column.date_po'))
            ->type('date');

        $this->card->addCard([
            'name' => 'client_po',
            'line' => 'top',
            'view' => 'crud::components.datatable-origin',
            'params' => [
                'filter' => true,
                'crud_custom' => $this->crud,
                'hide_title' => true,
                'columns' => [
                    [
                        'name'      => 'row_number',
                        'type'      => 'row_number',
                        'label'     => 'No',
                        'orderable' => false,
                    ],
                    [
                        'name'      => 'work_code',
                        'type'      => 'text',
                        'label'     => trans('backpack::crud.client_po.column.work_code'),
                        'orderable' => true,
                    ],
                    [
                        'label' => trans('backpack::crud.client_po.column.client_id'),
                        'type'      => 'text',
                        'name'      => 'client_id',
                        'orderable' => true,
                    ],
                    [
                        'name'      => 'reimburse_type',
                        'type'      => 'text',
                        'label'     => trans('backpack::crud.client_po.column.reimburse_type'),
                        'orderable' => true,
                    ],
                    [
                        'name'      => 'po_number',
                        'type'      => 'text',
                        'label'     => trans('backpack::crud.client_po.column.po_number'),
                        'orderable' => true,
                        'limit'     => 40,
                    ],
                    [
                        'name'      => 'job_name',
                        'type'      => 'text',
                        'label'     => trans('backpack::crud.client_po.column.job_name'),
                        'orderable' => true,
                    ],
                    [
                        'name'      => 'rap_value',
                        'type'      => 'text',
                        'label'     => trans('backpack::crud.client_po.column.rap_value'),
                        'orderable' => true,
                    ],
                    [
                        'name'      => 'job_value_exclude_ppn',
                        'type'      => 'text',
                        'label'     => trans('backpack::crud.client_po.column.job_value_exclude_ppn'),
                        'orderable' => true,
                    ],
                    [
                        'name'      => 'job_value_include_ppn',
                        'type'      => 'text',
                        'label'     => trans('backpack::crud.client_po.column.job_value_include_ppn'),
                        'orderable' => true,
                    ],
                    [
                        'name'      => 'start_date,end_date',
                        'type'      => 'text',
                        'label'     => trans('backpack::crud.client_po.column.start_date_end_date'),
                        'orderable' => false,
                    ],
                    [
                        'label'  => trans('backpack::crud.client_po.column.date_po'),
                        'name' => 'date_po',
                        'type'  => 'date',
                        'orderable' => true,
                    ],
                    [
                        'name'      => 'document_path',
                        'type'      => 'text',
                        'label'     => trans('backpack::crud.client_po.column.document_path'),
                        'orderable' => false,
                    ],
                    [
                        'name'      => 'category',
                        'type'      => 'text',
                        'label'     => trans('backpack::crud.client_po.column.category'),
                        'orderable' => true,
                    ],
                    [
                        'name' => 'list_invoice',
                        'type' => 'text',
                        'label' => trans('backpack::crud.client_po.column.list_invoice'),
                    ],
                    [
                        'name' => 'action',
                        'type' => 'action',
                        'label' =>  trans('backpack::crud.actions'),
                    ]
                ],
                'filter_table' => collect($this->crud->filters())->slice(0, 2),
                'route' => backpack_url('/client/po/search'),
            ]
        ]);

        $this->card->addCard([
            'name' => 'client_po-plugin',
            'line' => 'top',
            'view' => 'crud::components.client_po-plugin',
            'parent_view' => 'crud::components.filter-parent',
            'params' => [],
        ]);

        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? mb_ucfirst($this->crud->entity_name_plural);
        $this->data['title_modal_create'] = "PO Client";
        $this->data['title_modal_edit'] = "PO Client";
        $this->data['title_modal_delete'] = "PO Client";
        $this->data['cards'] = $this->card;
        $breadcrumbs = [
            'Client' => backpack_url('client'),
            'PO' => backpack_url($this->crud->route)
        ];
        $this->data['breadcrumbs'] = $breadcrumbs;
        $this->data['year_options'] = CustomHelper::getYearOptions('client_po', 'date_po');

        $list = "crud::list-blank" ?? $this->crud->getListView();

        return view($list, $this->data);
    }

    public function countAllPPn()
    {
        $request = request();
        $client_po = ClientPo::select(DB::raw("SUM(job_value) as total_job_value, SUM(job_value_include_ppn) as total_job_value_ppn"));

        // filter
        if ($request->has('status_invoice')) {
            $invoice = InvoiceClient::select(DB::raw('client_po_id, count(invoice_number) as total_invoice'))
                ->groupBy('client_po_id');
            $client_po = $client_po->leftJoinSub($invoice, 'invoices', function ($join) {
                $join->on('client_po.id', 'invoices.client_po_id');
            });
            if ($request->status_invoice == 1) {
                $client_po = $client_po
                    ->where('invoices.total_invoice', '>=', $request->status_invoice);
            } else {
                $client_po = $client_po
                    ->whereNull('invoices.total_invoice');
            }
        }

        if ($request->has('date_po')) {
            $client_po = $client_po
                ->where('date_po', 'like', '%' . $request->date_po . '%');
        }

        if ($request->has('filter_year') && $request->filter_year != 'all') {
            $client_po = $client_po
                ->where(DB::raw("YEAR(date_po)"), $request->filter_year);
        }

        if ($request->has('search')) {
            if (isset($request->search[1])) {
                $search = trim($request->search[1]);
                $client_po = $client_po
                    ->where('work_code', 'like', '%' . $search . '%');
            }

            if (isset($request->search[2])) {
                $search = $request->search[2];
                $client_po = $client_po
                    ->WhereExists(function ($q) use ($search) {
                        $q->from('clients')
                            ->whereColumn('clients.id', 'client_po.client_id')
                            ->where('clients.name', 'like', '%' . $search . '%');
                    });
            }

            if (isset($request->search[3])) {
                $search = $request->search[3];
                $client_po = $client_po->where('reimburse_type', 'like', $search . '%');
            }

            // filter po_number (kolom 4)
            if (isset($request->search[4])) {
                $search = $request->search[4];
                $client_po = $client_po->where('po_number', 'like', '%' . $search . '%');
            }

            // filter job_name (kolom 5)
            if (isset($request->search[5])) {
                $search = $request->search[5];
                $client_po = $client_po->where('job_name', 'like', '%' . $search . '%');
            }

            // filter rap_value (kolom 6)
            if (isset($request->search[6])) {
                $search = $request->search[6];
                $client_po = $client_po->where('rap_value', 'like', '%' . $search . '%');
            }

            // filter job_value (kolom 7)
            if (isset($request?->search[7])) {
                $search = $request->search[7];
                $client_po = $client_po->where('job_value', 'like', '%' . $search . '%');
            }

            // filter job_value_include_ppn (kolom 8)
            if (isset($request?->search[8])) {
                $search = $request->search[8];
                $client_po = $client_po->where('job_value_include_ppn', 'like', '%' . $search . '%');
            }

            // filter start_date / end_date (kolom 9)
            if (isset($request?->search[9])) {
                $search = $request->search[9];
                $client_po = $client_po->where(function ($query) use ($search) {
                    $query->where('start_date', 'like', '%' . $search . '%')
                        ->orWhere('end_date', 'like', '%' . $search . '%');
                });
            }

            if (isset($request->search[11])) {
                $search = $request->search[11];
                $client_po = $client_po->where('document_path', 'like', '%' . $search . '%');
            }

            if (isset($request->search[12])) {
                $search = $request->search[12];
                $client_po = $client_po->where('category', 'like', '%' . $search . '%');
            }
        }


        $client_po = $client_po->get();
        if ($client_po->count() > 0) {
            return response()->json([
                'total_job_value' => CustomHelper::formatRupiahWithCurrency($client_po[0]->total_job_value),
                'total_job_value_ppn' => CustomHelper::formatRupiahWithCurrency($client_po[0]->total_job_value_ppn)
            ]);
        }
        return response()->json([
            'total_job_value' => CustomHelper::formatRupiahWithCurrency(0),
            'total_job_value_ppn' => CustomHelper::formatRupiahWithCurrency(0)
        ]);
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

    public function select2Client()
    {
        $this->crud->hasAccessOrFail('create');

        $search = request()->input('q');
        $dataset = \App\Models\Client::select(['id', 'name'])
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

    public function select_count_without_po()
    {
        $po = ClientPo::select(DB::raw("COUNT(id) as count"))
            ->where('status', 'TANPA PO')->first();
        return response()->json([
            'count' => $po->count + 1,
        ]);
    }

    public function calculateClientPo($request)
    {
        $nilaiPekerjaan = floatval(str_replace(',', '', $request->input('job_value')));
        $ppn = floatval($request->input('tax_ppn'));

        $nilaiPpn = ($ppn == 0) ? 0 : ($nilaiPekerjaan * ($ppn / 100));
        $total = $nilaiPekerjaan + $nilaiPpn;

        $totalBiaya = floatval(str_replace(',', '', $request->input('price_total')));
        $labaRugiPo = $nilaiPekerjaan - $totalBiaya;

        $bebanUmum = floatval(str_replace(',', '', $request->input('load_general_value')));
        $labaRugiAkhir = $labaRugiPo - $bebanUmum;

        // Simpan ke database atau kirim balik ke view
        return [
            'price_after_year' => 0,
            'price_total' => 0,
            'load_general_value' => 0,
            'job_value_include_ppn' => $total,
            'profit_and_loss' => 0,
            'profit_and_loss_final' => 0,
        ];
    }

    public function store()
    {
        $this->crud->hasAccessOrFail('create');

        $setting = Setting::first();

        $request = $this->crud->validateRequest();

        $this->crud->registerFieldEvents();

        $calculate = $this->calculateClientPo($request);
        $request->merge($calculate);

        if ($request->status == 'TANPA PO') {
            $request->merge([
                'po_number' => $setting->work_code_prefix,
                // 'category' => 'RUTIN',
            ]);
        }

        DB::beginTransaction();
        try {

            $item = $this->crud->create($this->crud->getStrippedSaveRequest($request));
            $this->data['entry'] = $this->crud->entry = $item;

            \Alert::success(trans('backpack::crud.insert_success'))->flash();

            $this->crud->setSaveAction();

            DB::commit();
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $item,
                    'events' => [
                        'crudTable-filter_client_po_plugin_load' => true,
                        'crudTable-client_po_create_success' => true
                    ],
                ]);
            }

            return $this->crud->performSaveAction($item->getKey());
        } catch (\Exception $e) {
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
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.edit') . ' ' . $this->crud->entity_name;
        $this->data['id'] = $id;

        return response()->json([
            'html' => view($this->crud->getEditView(), $this->data)->render()
        ]);
    }

    public function update()
    {
        $this->crud->hasAccessOrFail('update');

        $request = $this->crud->validateRequest();

        $calculate = $this->calculateClientPo($request);
        $request->merge($calculate);

        $this->crud->registerFieldEvents();

        DB::beginTransaction();
        try {

            $item = $this->crud->update(
                $request->get($this->crud->model->getKeyName()),
                $this->crud->getStrippedSaveRequest($request)
            );
            $this->data['entry'] = $this->crud->entry = $item;

            \Alert::success(trans('backpack::crud.update_success'))->flash();

            $this->crud->setSaveAction();

            DB::commit();
            return response()->json([
                'success' => true,
                'data' => $item,
                'events' => [
                    'crudTable-filter_client_po_plugin_load' => true,
                    'crudTable-client_po_updated_success' => true
                ]
            ]);
            return $this->crud->performSaveAction($item->getKey());
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        // CRUD::setFromDb(); // set columns from db columns.

        $this->crud->file_title_export_pdf = "Laporan_daftar_client_po.pdf";
        $this->crud->file_title_export_excel = "Laporan_daftar_client_po.xlsx";
        $this->crud->param_uri_export = "?export=1";

        CRUD::addButtonFromView('top', 'export-excel-table', 'export-excel-table', 'beginning');
        CRUD::addButtonFromView('top', 'export-pdf-table', 'export-pdf-table', 'beginning');
        CRUD::addButtonFromView('top', 'filter_year', 'filter-year', 'beginning');

        $request = request();
        CRUD::disableResponsiveTable();

        $status_file = '';
        if (strpos(url()->current(), 'excel')) {
            $status_file = 'excel';
        } else {
            $status_file = 'pdf';
        }


        if ($request->columns) {

            if (isset($request->columns[1]['search']['value'])) {
                $search = $request->columns[1]['search']['value'];
                $this->crud->query = $this->crud->query
                    ->where('work_code', 'like', '%' . $search . '%');
            }

            if (isset($request->columns[2]['search']['value'])) {
                $search = $request->columns[2]['search']['value'];
                $this->crud->query = $this->crud->query
                    ->WhereExists(function ($q) use ($search) {
                        $q->from('clients')
                            ->whereColumn('clients.id', 'client_po.client_id')
                            ->where('clients.name', 'like', '%' . $search . '%');
                    });
            }

            if (isset($request->columns[3]['search']['value'])) {
                $search = $request->columns[3]['search']['value'];
                $this->crud->query = $this->crud->query
                    ->where('reimburse_type', 'like', $search . '%');
            }

            if (isset($request->columns[4]['search']['value'])) {
                $search = $request->columns[4]['search']['value'];
                $this->crud->query = $this->crud->query
                    ->where('po_number', 'like', '%' . $search . '%');
            }

            if (isset($request->columns[5]['search']['value'])) {
                $search = $request->columns[5]['search']['value'];
                $this->crud->query = $this->crud->query
                    ->where('job_name', 'like', '%' . $search . '%');
            }

            if (isset($request->columns[6]['search']['value'])) {
                $search = $request->columns[6]['search']['value'];
                $this->crud->query = $this->crud->query
                    ->where('rap_value', 'like', '%' . $search . '%');
            }

            if (isset($request->columns[7]['search']['value'])) {
                $search = $request->columns[7]['search']['value'];
                $this->crud->query = $this->crud->query
                    ->where('job_value', 'like', '%' . $search . '%');
            }

            if (isset($request->columns[8]['search']['value'])) {
                $search = $request->columns[8]['search']['value'];
                $this->crud->query = $this->crud->query
                    ->where('job_value_include_ppn', 'like', '%' . $search . '%');
            }

            if (isset($request->columns[9]['search']['value'])) {
                $search = $request->columns[9]['search']['value'];
                $this->crud->query = $this->crud->query
                    ->where(function ($query) use ($search) {
                        $query->where('start_date', 'like', '%' . $search . '%')
                            ->orWhere('end_date', 'like', '%' . $search . '%');
                    });
            }

            if (isset($request->columns[11]['search']['value'])) {
                $search = $request->columns[11]['search']['value'];
                $this->crud->query = $this->crud->query
                    ->where('document_path', 'like', $search . '%');
            }

            if (isset($request->columns[12]['search']['value'])) {
                $search = $request->columns[12]['search']['value'];
                $this->crud->query = $this->crud->query
                    ->where('category', 'like', $search . '%');
            }
        }

        // filter
        if ($request->has('status_invoice')) {
            $invoice = InvoiceClient::select(DB::raw('client_po_id, count(invoice_number) as total_invoice'))
                ->groupBy('client_po_id');
            $this->crud->query = $this->crud->query->leftJoinSub($invoice, 'invoices', function ($join) {
                $join->on('client_po.id', 'invoices.client_po_id');
            });
            if ($request->status_invoice == 1) {
                $this->crud->query = $this->crud->query
                    ->where('invoices.total_invoice', '>=', $request->status_invoice);
            } else {
                $this->crud->query = $this->crud->query
                    ->whereNull('invoices.total_invoice');
            }
        }

        if ($request->has('date_po')) {
            $this->crud->query = $this->crud->query
                ->where('date_po', 'like', '%' . $request->date_po . '%');
        }

        if ($request->has('filter_year') && $request->filter_year != 'all') {
            $this->crud->query = $this->crud->query
                ->where(DB::raw("YEAR(date_po)"), $request->filter_year);
        }

        $this->crud->addColumn([
            'name'      => 'row_number',
            'type'      => 'row_number',
            'label'     => 'No',
            'orderable' => false,
            'wrapper' => [
                'element' => 'strong',
            ]
        ])->makeFirstColumn();

        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.work_code'),
                'name' => 'work_code',
                'type'  => 'text'
            ],
        );

        CRUD::column([
            // 1-n relationship
            'label' => trans('backpack::crud.client_po.column.client_id'),
            'type'      => 'select',
            'name'      => 'client_id', // the column that contains the ID of that connected entity;
            'entity'    => 'client', // the method that defines the relationship in your Model
            'attribute' => 'name', // foreign key attribute that is shown to user
            'model'     => "App\Models\Client", // foreign key model
            // OPTIONAL
            // 'limit' => 32, // Limit the number of characters shown
        ]);

        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.reimburse_type'),
                'name' => 'reimburse_type',
                'type'  => 'text'
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.po_number'),
                'name' => 'po_number',
                'type'  => 'text',
                'limit'  => 40,
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.job_name'),
                'name' => 'job_name',
                'type'  => 'wrap_text'
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.rap_value'),
                'name' => 'rap_value',
                'type'  => 'closure',
                'function' => function ($entry) use ($status_file) {
                    return $this->priceFormatExport($status_file, $entry->rap_value);
                },
                // 'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.job_value_exclude_ppn'),
                'name' => 'job_value',
                'type'  => 'closure',
                'function' => function ($entry) use ($status_file) {
                    return $this->priceFormatExport($status_file, $entry->job_value);
                },
                // 'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.job_value_include_ppn'),
                'name' => 'job_value_include_ppn',
                'type'  => 'closure',
                'function' => function ($entry) use ($status_file) {
                    return $this->priceFormatExport($status_file, $entry->job_value_include_ppn);
                },
                // 'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.startdate_and_enddate'),
                'name' => 'start_date,end_date',
                'type'  => 'date_range_custom'
            ],
        );

        CRUD::column([
            'label'  => trans('backpack::crud.client_po.column.date_po'),
            'name' => 'date_po',
            'type'  => 'date',
            'format' => 'D MMM Y'
        ]);

        CRUD::column([
            'name'   => 'document_path',
            'type'   => 'upload',
            'label'  => trans('backpack::crud.client_po.column.document_path'),
            'disk'   => 'public',
        ]);

        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.category'),
                'name' => 'category',
                'type'  => 'text'
            ],
        );

        CRUD::addColumn([
            'name' => 'list_invoice',
            'label' => trans('backpack::crud.client_po.column.list_invoice'),
            'type' => 'custom_html',
            'value' => function ($entry) {
                $count_data = $entry->invoices->count();
                if ($count_data > 0) {
                    return "ADA";
                }
                return 'TIDAK ADA';
            },
            'orderable'  => true,
            'orderLogic' => function ($query, $column, $columnDirection) {
                $invoice = InvoiceClient::select(DB::raw('client_po_id, count(invoice_number) as total_invoice'))
                    ->groupBy('client_po_id');
                return $query->leftJoinSub($invoice, 'invoices', function ($join) {
                    $join->on('client_po.id', 'invoices.client_po_id');
                })->select('client_po.*')->orderBy('invoices.total_invoice', $columnDirection);
            }
        ]);
    }

    private function setupListExport()
    {
        $this->crud->addColumn([
            'name'      => 'row_number',
            'type'      => 'export',
            'label'     => 'No',
            'orderable' => false,
            'wrapper' => [
                'element' => 'strong',
            ]
        ])->makeFirstColumn();

        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.work_code'),
                'name' => 'work_code',
                'type'  => 'export'
            ],
        );

        CRUD::column([
            // 1-n relationship
            'label' => trans('backpack::crud.client_po.column.client_id'),
            'type'      => 'closure',
            'name'      => 'client_id', // the column that contains the ID of that connected entity;
            'entity'    => 'client', // the method that defines the relationship in your Model
            'attribute' => 'name', // foreign key attribute that is shown to user
            'model'     => "App\Models\Client", // foreign key model
            'function' => function ($client_id) {
                return $client_id?->client?->name;
            }
        ]);

        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.reimburse_type'),
                'name' => 'reimburse_type',
                'type'  => 'export'
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.po_number'),
                'name' => 'po_number',
                'type'  => 'export'
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.job_name'),
                'name' => 'job_name',
                'type'  => 'export'
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.rap_value'),
                'name' => 'rap_value',
                'type'  => 'export',
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.job_value_exclude_ppn'),
                'name' => 'job_value',
                'type'  => 'export',
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.job_value_include_ppn'),
                'name' => 'job_value_include_ppn',
                'type'  => 'export',
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
            ],
        );

        CRUD::column(
            [
                'label'  => 'Start Date',
                'name' => 'start_date',
                'type'  => 'text'
            ],
        );

        CRUD::column(
            [
                'label'  => "End Date",
                'name' => 'end_date',
                'type'  => 'text'
            ],
        );
        // CRUD::column([
        //     'name'   => 'document_path',
        //     'type'   => 'upload',
        //     'label'  => trans('backpack::crud.client_po.column.document_path'),
        //     'disk'   => 'public',
        // ]);

        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.category'),
                'name' => 'category',
                'type'  => 'export'
            ],
        );
    }

    public function exportPdf()
    {

        // $this->setupListExport();
        $this->setupListOperation();

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

        $title = "DAFTAR CLIENT PO";

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

        // $this->setupListExport();
        $this->setupListOperation();

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

        $name = 'DAFTAR CLIENT PO';

        return response()->streamDownload(function () use ($columns, $items, $all_items) {
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

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(ClientPoRequest::class);
        $settings = Setting::first();

        $po_prefix = [];
        $work_code_prefix = [];
        $work_code_disabled = [
            // 'disabled' => true,
        ];
        $po_number_disabled = [
            'disabled' => true,
        ];
        if (!$this->crud->getCurrentEntryId()) {
            if ($settings?->po_prefix) {
                $po_prefix = [
                    'value' => $settings->po_prefix,
                ];
            }
            if ($settings?->work_code_prefix) {
                $work_code_prefix = [
                    'value' => $settings->work_code_prefix,
                ];
            }
            $work_code_disabled = [];
            $po_number_disabled = [];
        } else {
            $id = $this->crud->getCurrentEntryId();
            $voucher_exists = Voucher::where('client_po_id', $id)
                ->first();
            if ($voucher_exists) {
                $work_code_disabled = [
                    'disabled' => true,
                ];
            }
        }


        // CRUD::setFromDb(); // set fields from db columns.
        CRUD::field([   // 1-n relationship
            'label'       => trans('backpack::crud.client_po.field.client_id.label'), // Table column heading
            'type'        => "select2_ajax_custom",
            'name'        => 'client_id', // the column that contains the ID of that connected entity
            'entity'      => 'client', // the method that defines the relationship in your Model
            'attribute'   => "name", // foreign key attribute that is shown to user
            'data_source' => backpack_url('client/select2-client'), // url to controller search function (with /{id} should return a single entry)
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.client_po.field.client_id.placeholder'),
            ]
        ]);

        CRUD::addField([
            'name' => 'work_code',
            'label' => trans('backpack::crud.client_po.field.work_code.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                ...$work_code_disabled,
                'placeholder' => trans('backpack::crud.client_po.field.work_code.placeholder'),
            ],
            ...$work_code_prefix,
        ]);

        CRUD::addField([  // Select2
            'label'     => trans('backpack::crud.client_po.field.status.label'),
            'type'      => 'select2_array',
            'name'      => 'status',
            'options'   => [
                'ADA PO' => 'ADA PO',
                'TANPA PO' => 'TANPA PO',
            ], // force the related options to be a custom query, instead of all(); you can use this to filter the results show in the select
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ]
        ]);

        CRUD::addField([
            'name' => 'po_number',
            'label' => trans('backpack::crud.client_po.field.po_number.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
                'placeholder' => trans('backpack::crud.client_po.field.po_number.placeholder')
            ],
            'attributes' => [
                ...$po_number_disabled,
                'placeholder' => trans('backpack::crud.client_po.field.po_number.placeholder')
            ],
            ...$po_prefix,
        ]);

        // CRUD::addField([   // Hidden
        //     'name'  => 'space',
        //     'type'  => 'hidden',
        //     'value' => 'active',
        //     'wrapper'   => [
        //         'class' => 'form-group col-md-6'
        //     ],
        //     'attributes' => [
        //         'disabled'  => 'disabled',
        //         // 'placeholder' => trans('backpack::crud.spk.field.')
        //     ]
        // ]);

        CRUD::addField([
            'name' => 'job_name',
            'label' => trans('backpack::crud.client_po.field.job_name.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-12',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.client_po.field.job_name.placeholder'),
            ]
        ]);

        CRUD::addField([
            'name' => 'rap_value',
            'label' => trans('backpack::crud.client_po.column.rap_value'),
            'type' => 'mask',
            'mask' => '000.000.000.000.000.000',
            'mask_options' => [
                'reverse' => true
            ],
            'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp",
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => '000.000',
            ]
        ]);

        CRUD::addField([
            'name' => 'job_value',
            'label' => trans('backpack::crud.client_po.field.job_value.label'),
            'type' => 'mask',
            'mask' => '000.000.000.000.000.000',
            'mask_options' => [
                'reverse' => true
            ],
            'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp",
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => '000.000',
            ]
        ]);

        CRUD::addField([
            'name' => 'tax_ppn',
            'label' => trans('backpack::crud.client_po.field.tax_ppn.label'),
            'type' => 'number',
            // optionals
            'attributes' => ["step" => "any"], // allow decimals
            'prefix'     => "%",
            // 'suffix'     => ".00",
            'wrapper'   => [
                'class' => 'form-group col-md-2',
            ],
            'attributes' => [
                'placeholder' => '0',
            ]
        ]);

        CRUD::addField([   // Hidden
            'name'  => 'space_1',
            'type'  => 'hidden',
            'value' => 'active',
            'wrapper'   => [
                'class' => 'form-group col-md-2'
            ],
            'attributes' => [
                'disabled'  => 'disabled',
                // 'placeholder' => trans('backpack::crud.spk.field.')
            ]
        ]);

        CRUD::addField([   // Hidden
            'name'  => 'space_2',
            'type'  => 'hidden',
            'value' => 'active',
            'wrapper'   => [
                'class' => 'form-group col-md-2'
            ],
            'attributes' => [
                'disabled'  => 'disabled',
                // 'placeholder' => trans('backpack::crud.spk.field.')
            ]
        ]);

        CRUD::addField([
            'name' => 'job_value_include_ppn',
            'label' => trans('backpack::crud.client_po.column.job_value_include_ppn_2'),
            'type' => 'text',
            'mask' => '000.000.000.000.000.000',
            'mask_options' => [
                'reverse' => true
            ],
            // optionals
            'attributes' => [
                'disabled' => true,
            ], // allow decimals
            'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp",
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::addField([
            'name'  => 'date_po',
            'type'  => 'date_picker',
            'label' => trans('backpack::crud.client_po.field.date_po.label'),
            'date_picker_options' => [
                'language' => App::getLocale(),
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::field([   // date_range
            'name'  => 'start_date,end_date', // db columns for start_date & end_date
            'label' => trans('backpack::crud.client_po.field.startdate_and_enddate.label'),
            'type'  => 'date_range',

            'date_range_options' => [
                'drops' => 'down', // can be one of [down/up/auto]
                // 'locale' => ['format' => 'DD/MM/YYYY']
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.client_po.field.startdate_and_enddate.placeholder'),
            ]
        ]);

        CRUD::field([
            'name'        => 'reimburse_type',
            'label'       => trans('backpack::crud.client_po.field.reimburse_type.label'),
            'type'        => 'select_from_array',
            'options'     => ['' => trans('backpack::crud.client_po.field.reimburse_type.placeholder'), 'REIMBURSE' => 'REIMBURSE', 'NON REIMBURSE' => 'NON REIMBURSE'],
            'allows_null' => false,
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            // 'allows_multiple' => true, // OPTIONAL; needs you to cast this to array in your model;
        ]);

        // CRUD::addField([
        //     'name' => 'price_after_year',
        //     'label' => trans('backpack::crud.client_po.column.price_after_year'),
        //     'type' => 'mask',
        //     'mask' => '000.000.000.000.000.000',
        //     'mask_options' => [
        //         'reverse' => true
        //     ],
        //     'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp",
        //     'wrapper'   => [
        //         'class' => 'form-group col-md-6',
        //     ],
        //     'attributes' => [
        //         'placeholder' => '000.000',
        //     ]
        // ]);

        // CRUD::addField([
        //     'name' => 'price_total',
        //     'label' => trans('backpack::crud.client_po.field.price_total.label'),
        //     'type' => 'mask',
        //     'mask' => '000.000.000.000.000.000',
        //     'mask_options' => [
        //         'reverse' => true
        //     ],
        //     'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp",
        //     'wrapper'   => [
        //         'class' => 'form-group col-md-6',
        //     ],
        //     'attributes' => [
        //         'placeholder' => '000.000',
        //     ]
        // ]);

        // CRUD::addField([
        //     'name' => 'profit_and_loss',
        //     'label' => trans('backpack::crud.client_po.column.profit_and_loss'),
        //     'type' => 'mask',
        //     'mask' => '000.000.000.000.000.000',
        //     'mask_options' => [
        //         'reverse' => true
        //     ],
        //     'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp",
        //     'wrapper'   => [
        //         'class' => 'form-group col-md-6',
        //     ],
        //     'attributes' => [
        //         'placeholder' => '000.000',
        //         'disabled' => true,
        //     ]
        // ]);

        // CRUD::addField([
        //     'name' => 'load_general_value',
        //     'label' => trans('backpack::crud.client_po.column.load_general_value'),
        //     'type' => 'mask',
        //     'mask' => '000.000.000.000.000.000',
        //     'mask_options' => [
        //         'reverse' => true
        //     ],
        //     'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp",
        //     'wrapper'   => [
        //         'class' => 'form-group col-md-6 .load_general_value_wrapper',
        //     ],
        //     'attributes' => [
        //         'placeholder' => '000.000',
        //     ]
        // ]);

        // CRUD::addField([
        //     'name' => 'profit_and_lost_final',
        //     'label' => trans('backpack::crud.client_po.column.profit_and_lost_final'),
        //     'type' => 'mask',
        //     'mask' => '000.000.000.000.000.000',
        //     'mask_options' => [
        //         'reverse' => true
        //     ],
        //     'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp",
        //     'wrapper'   => [
        //         'class' => 'form-group col-md-6',
        //     ],
        //     'attributes' => [
        //         'placeholder' => '000.000',
        //         'disabled' => true,
        //     ]
        // ]);

        CRUD::addField([
            'name' => 'document_path',
            'label' => trans('backpack::crud.client_po.field.document_path.label'),
            'type' => 'upload',
            'hint' => trans('backpack::crud.client_po.field.document_path.hint'),
            'attributes' => [
                'accept' => '.pdf'
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
            'withFiles' => [
                'disk' => 'public',
                'path' => 'document_client_po',
                'deleteWhenEntryIsDeleted' => true,
            ],
        ]);

        // CRUD::field([   // date_picker
        //     'name'  => 'date_invoice',
        //     'type'  => 'date_picker',
        //     'label' => trans('backpack::crud.client_po.field.date_invoice.label'),

        //     // optional:
        //     'date_picker_options' => [
        //         'language' => App::getLocale(),
        //     ],
        //     'wrapper'   => [
        //         'class' => 'form-group col-md-6'
        //     ],
        // ]);

        CRUD::addField([  // Select2
            'label'     => trans('backpack::crud.client_po.column.category'),
            'type'      => 'select2_array',
            'name'      => 'category',
            'options'   => [
                '' => trans('backpack::crud.voucher.field.payment_type.placeholder'),
                'RUTIN' => 'RUTIN',
                'NON RUTIN' => 'NON RUTIN',
            ], // force the related options to be a custom query, instead of all(); you can use this to filter the results show in the select
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ]
        ]);

        CRUD::addField([
            'name' => 'logic_client_po',
            'type' => 'logic_client_po',
        ]);

        /**
         * Fields can be defined using the fluent syntax:
         * - CRUD::field('price')->type('number');
         */
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

    protected function setupShowOperation()
    {
        $settings = Setting::first();

        CRUD::field([   // 1-n relationship
            'label'       => trans('backpack::crud.client_po.field.client_id.label'), // Table column heading
            'type'        => "select2_ajax_custom",
            'name'        => 'client_id', // the column that contains the ID of that connected entity
            'entity'      => 'client', // the method that defines the relationship in your Model
            'attribute'   => "name", // foreign key attribute that is shown to user
            'data_source' => backpack_url('client/select2-client'), // url to controller search function (with /{id} should return a single entry)
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.client_po.field.client_id.placeholder'),
            ]
        ]);

        CRUD::addField([
            'name' => 'work_code',
            'label' => trans('backpack::crud.client_po.field.work_code.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.client_po.field.work_code.placeholder'),
            ]
        ]);

        CRUD::addField([
            'name' => 'po_number',
            'label' => trans('backpack::crud.client_po.field.po_number.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-12',
                'placeholder' => trans('backpack::crud.client_po.field.po_number.placeholder')
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.client_po.field.po_number.placeholder')
            ]
        ]);

        CRUD::addField([
            'name' => 'job_name',
            'label' => trans('backpack::crud.client_po.field.job_name.label'),
            'type' => 'wrap_text',
            'wrapper'   => [
                'class' => 'form-group col-md-12',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.client_po.field.job_name.placeholder'),
            ]
        ]);

        CRUD::addField([
            'name' => 'rap_value',
            'label' => trans('backpack::crud.client_po.column.rap_value'),
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
            'name' => 'job_value',
            'label' => trans('backpack::crud.client_po.field.job_value.label'),
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
            'name' => 'job_value_include_ppn',
            'label' => trans('backpack::crud.client_po.column.job_value_include_ppn_2'),
            'type' => 'mask',
            'mask' => '000.000.000.000.000.000',
            'mask_options' => [
                'reverse' => true
            ],
            // optionals
            'attributes' => [
                'disabled' => true,
            ], // allow decimals
            'prefix'     => "Rp.",
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::field([   // date_range
            'name'  => 'start_date,end_date', // db columns for start_date & end_date
            'label' => trans('backpack::crud.client_po.field.startdate_and_enddate.label'),
            'type'  => 'date_range',

            'date_range_options' => [
                'drops' => 'down', // can be one of [down/up/auto]
                // 'locale' => ['format' => 'DD/MM/YYYY']
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.client_po.field.startdate_and_enddate.placeholder'),
            ]
        ]);

        CRUD::field([
            'name'        => 'reimburse_type',
            'label'       => trans('backpack::crud.client_po.field.reimburse_type.label'),
            'type'        => 'select_from_array',
            'options'     => ['' => trans('backpack::crud.client_po.field.reimburse_type.placeholder'), 'REIMBURSE' => 'REIMBURSE', 'NON REIMBURSE' => 'NON REIMBURSE'],
            'allows_null' => false,
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            // 'allows_multiple' => true, // OPTIONAL; needs you to cast this to array in your model;
        ]);

        CRUD::addField([
            'name' => 'price_after_year',
            'label' => trans('backpack::crud.client_po.column.price_after_year'),
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
            'name' => 'price_total',
            'label' => trans('backpack::crud.client_po.field.price_total.label'),
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
            'name' => 'profit_and_loss',
            'label' => trans('backpack::crud.client_po.column.profit_and_loss'),
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
                'disabled' => true,
            ]
        ]);

        CRUD::addField([
            'name' => 'profit_and_lost_final',
            'label' => trans('backpack::crud.client_po.column.profit_and_lost_final'),
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
                'disabled' => true,
            ]
        ]);

        CRUD::addField([
            'name' => 'document_path',
            'label' => trans('backpack::crud.client_po.field.document_path.label'),
            'type' => 'upload',
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
            'withFiles' => [
                'disk' => 'public',
                'path' => 'document_client_po',
                'deleteWhenEntryIsDeleted' => true,
            ],
        ]);

        CRUD::addField([  // Select2
            'label'     => trans('backpack::crud.client_po.column.category'),
            'type'      => 'select2_array',
            'name'      => 'category',
            'options'   => [
                '' => trans('backpack::crud.voucher.field.payment_type.placeholder'),
                'RUTIN' => 'RUTIN',
                'NON RUTIN' => 'NON RUTIN',
            ], // force the related options to be a custom query, instead of all(); you can use this to filter the results show in the select
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ]
        ]);
        //
        CRUD::column([
            // 1-n relationship
            'label' => trans('backpack::crud.client_po.column.client_id'),
            'type'      => 'select',
            'name'      => 'client_id', // the column that contains the ID of that connected entity;
            'entity'    => 'client', // the method that defines the relationship in your Model
            'attribute' => 'name', // foreign key attribute that is shown to user
            'model'     => "App\Models\Client", // foreign key model
            // OPTIONAL
            // 'limit' => 32, // Limit the number of characters shown
        ]);
        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.work_code'),
                'name' => 'work_code',
                'type'  => 'text'
            ],
        );
        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.po_number'),
                'name' => 'po_number',
                'type'  => 'text'
            ],
        );
        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.job_name'),
                'name' => 'job_name',
                'type'  => 'wrap_text'
            ],
        );
        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.rap_value'),
                'name' => 'rap_value',
                'type'  => 'number',
                'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
            ],
        );
        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.rap_value'),
                'name' => 'job_value',
                'type'  => 'number',
                'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
            ],
        );
        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.job_value_include_ppn'),
                'name' => 'job_value_include_ppn',
                'type'  => 'number',
                'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
            ],
        );
        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.startdate_and_enddate'),
                'name' => 'start_date,end_date',
                'type'  => 'date_range_custom'
            ],
        );
        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.reimburse_type'),
                'name' => 'reimburse_type',
                'type'  => 'text'
            ],
        );
        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.price_after_year'),
                'name' => 'price_after_year',
                'type'  => 'number',
                'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
            ],
        );
        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.price_total'),
                'name' => 'price_total',
                'type'  => 'number',
                'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
            ],
        );
        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.profit_and_loss'),
                'name' => 'profit_and_loss',
                'type'  => 'number',
                'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
            ],
        );
        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.profit_and_lost_final'),
                'name' => 'profit_and_lost_final',
                'type'  => 'number',
                'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
            ],
        );
        CRUD::column([
            'label'  => trans('backpack::crud.client_po.field.document_path.label'),
            'name' => 'document_path',
            'type'  => 'text',
            'wrapper'   => [
                'element' => 'a', // the element will default to "a" so you can skip it here
                'href' => function ($crud, $column, $entry, $related_key) {
                    if ($entry->document_path != '') {
                        return url('storage/document_client_po/' . $entry->document_path);
                    }
                    return "javascript:void(0)";
                },
                'target' => '_blank',
                // 'class' => 'some-class',
            ],
        ]);

        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.category'),
                'name' => 'category',
                'type'  => 'text'
            ],
        );
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

        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.preview') . ' ' . $this->crud->entity_name;

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        // return view($this->crud->getShowView(), $this->data);
        return response()->json([
            'html' => view($this->crud->getShowView(), $this->data)->render()
        ]);
    }

    public function destroy($id)
    {
        $this->crud->hasAccessOrFail('delete');

        // get entry ID from Request (makes sure its the last ID for nested resources)
        $id = $this->crud->getCurrentEntryId() ?? $id;

        $this->crud->delete($id);

        $messages['success'][] = trans('backpack::crud.delete_confirmation_message');
        $messages['events'] = [
            'crudTable-filter_client_po_plugin_load' => true,
            'crudTable-client_po_create_success' => true,
        ];
        return response()->json($messages);
    }
}
