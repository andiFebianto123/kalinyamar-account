<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Setting;
use App\Models\ClientPo;
use App\Models\InvoiceClient;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Helpers\CustomVoid;
use App\Http\Exports\ExportExcel;
use App\Http\Helpers\CustomHelper;
use Illuminate\Support\Facades\DB;
use App\Models\InvoiceClientDetail;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\CrudController;
use App\Http\Requests\InvoiceClientRequest;
use App\Http\Controllers\Operation\FormaterExport;
use App\Http\Controllers\Operation\PermissionAccess;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class InvoiceClientCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class InvoiceClientCrudController extends CrudController
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
        CRUD::setModel(\App\Models\InvoiceClient::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/invoice-client');
        CRUD::setEntityNameStrings(trans('backpack::crud.menu.invoice_client'), trans('backpack::crud.menu.invoice_client'));

        $base = 'INDEX INVOICE';
        $allAccess = ['AKSES SEMUA MENU ACCOUNTING'];
        $viewMenu  = ["MENU $base"];

        $this->settingPermission([
            'create' => ["CREATE $base", ...$allAccess],
            'update' => ["UPDATE $base", ...$allAccess],
            'delete' => ["DELETE $base", ...$allAccess],
            'list'   => $viewMenu,
            'show'   => $viewMenu,
            'print'  => true,
        ]);
    }

    public function select2ClientPo()
    {
        $this->crud->hasAccessOrFail('create');

        $search = request()->input('q');
        $dataset = \App\Models\ClientPo::select(['id', 'po_number'])
            ->where('po_number', 'LIKE', "%$search%")
            ->orWhere('work_code', 'like', "$search")
            ->orWhere('job_name', 'LIKE', "%$search%")
            ->paginate(10);

        $results = [];
        foreach ($dataset as $item) {
            $results[] = [
                'id' => $item->id,
                'text' => $item->po_number,
            ];
        }
        return response()->json(['results' => $results]);
    }

    public function selectedClientPo()
    {
        $this->crud->hasAccessOrFail('create');
        $id = request()->id;
        $entry = ClientPo::where('id', $id)->first();

        $entry->date_invoice = ($entry->date_invoice) ? Carbon::createFromFormat('Y-m-d', $entry->date_invoice)->format('d/m/Y') : Carbon::now()->format('d/m/Y');
        $entry->date_po_str = ($entry->date_po) ? Carbon::createFromFormat('Y-m-d', $entry->date_po)->format('d/m/Y') : Carbon::now()->format('d/m/Y');
        // $entry->job_value = CustomHelper::formatRupiah($entry->job_value);
        // $entry->total_value_with_tax = CustomHelper::formatRupiah($entry->job_value_include_ppn);
        $entry->client_name = $entry->client->name;
        return response()->json([
            'result' => $entry,
        ]);
    }

    private function getComponent()
    {
        $this->crud->filter('invoice_date11crudTable-invoice')
            ->label(trans('backpack::crud.invoice_client.column.invoice_date'))
            ->type('date');

        $this->crud->filter('po_date11crudTable-invoice')
            ->label(trans('backpack::crud.invoice_client.column.po_date'))
            ->type('date');

        $this->crud->filter('send_invoice_normal11crudTable-invoice')
            ->label(trans('backpack::crud.invoice_client.column.send_invoice_normal'))
            ->type('date');

        $this->crud->filter('send_invoice_revision11crudTable-invoice')
            ->label(trans('backpack::crud.invoice_client.column.send_invoice_revision'))
            ->type('date');

        $this->card->addCard([
            'name' => 'invoice',
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
                        'label'  => trans('backpack::crud.invoice_client.column.invoice_number'),
                        'name' => 'invoice_number',
                        'type'  => 'text',
                        'orderlable' => true,
                    ],
                    [
                        'label' => trans('backpack::crud.invoice_client.field.kdp.label'),
                        'name' => 'kdp',
                        'type'  => 'text',
                        'orderable' => true,
                    ],
                    [
                        'label'  => trans('backpack::crud.invoice_client.column.name'),
                        'name' => 'name',
                        'type'  => 'text',
                        'orderable' => true,
                    ],
                    [
                        'label'  => trans('backpack::crud.invoice_client.column.invoice_date'),
                        'name' => 'invoice_date',
                        'type'  => 'text',
                        'orderable' => true,
                    ],
                    [
                        'label' => trans('backpack::crud.invoice_client.column.description'),
                        'name' => 'description',
                        'type' => 'text',
                        'orderable' => true,
                    ],
                    [
                        'label'  => trans('backpack::crud.invoice_client.column.client_po_id'),
                        'name' => 'client_po_id',
                        'type'  => 'text',
                        'orderable' => true,
                        'limit' => 40,
                    ],
                    [
                        'label' => trans('backpack::crud.invoice_client.column.po_date'),
                        'name' => 'po_date',
                        'type' => 'text',
                        'orderable' => true,
                    ],
                    [
                        'label' => trans('backpack::crud.invoice_client.column.client_id'),
                        'type' => 'text',
                        'name' => 'client_name',
                        'orderable' => true,
                    ],
                    [
                        'label'  => trans('backpack::crud.invoice_client.column.price_total_exclude_ppn'),
                        'name' => 'price_total_exclude_ppn',
                        'type'  => 'text',
                        'orderable' => true,
                    ],
                    [
                        'label'  => trans('backpack::crud.invoice_client.column.price_total_include_ppn'),
                        'name' => 'price_total_include_ppn',
                        'type'  => 'text',
                        'orderable' => true,
                    ],
                    [
                        'label' => trans('backpack::crud.invoice_client.column.send_invoice_normal'),
                        'name' => 'send_invoice_normal',
                        'type'  => 'text',
                        'orderable' => true,
                    ],
                    [
                        'label' => trans('backpack::crud.invoice_client.column.send_invoice_revision'),
                        'name' => 'send_invoice_revision',
                        'type'  => 'text',
                        'orderable' => true,
                    ],
                    [
                        'label' => trans('backpack::crud.invoice_client.column.status'),
                        'name' => 'status',
                        'type' => 'text',
                    ],
                    [
                        'label' => trans('backpack::crud.invoice_client.column.document_invoice'),
                        'name' => 'invoice_document',
                        'type' => 'text',
                        'orderable' => false,
                    ],
                    [
                        'name' => 'action',
                        'type' => 'action',
                        'label' =>  trans('backpack::crud.actions'),
                    ]
                ],
                'filter_table' => collect($this->crud->filters())->slice(0, 4),
                'route' => backpack_url('invoice-client/search'),
            ]
        ]);

        $this->card->addCard([
            'name' => 'invoice-plugin',
            'line' => 'top',
            'view' => 'crud::components.invoice-plugin',
            'parent_view' => 'crud::components.filter-parent',
            'params' => [],
        ]);
    }

    public function total_price()
    {
        $total_price = InvoiceClient::select(
            DB::raw("SUM(price_total_exclude_ppn) as total_price_exclude_ppn"),
            DB::raw("SUM(price_total_include_ppn) as total_price_include_ppn")
        );

        $request = request();

        if ($request->search) {
            if (isset($request->search[1])) {
                $search = trim($request->search[1]);
                $total_price = $total_price
                    ->where('invoice_clients.invoice_number', 'like', '%' . $search . '%');
            }

            if (isset($request->search[2])) {
                $search = trim($request->search[2]);
                $total_price = $total_price
                    ->where('invoice_clients.kdp', 'like', '%' . $search . '%');
            }

            if (isset($request->search[3])) {
                $search = trim($request->search[3]);
                $total_price = $total_price->orWhereHas('client_po', function ($query) use ($search) {
                    $query->where('job_name', 'like', '%' . $search . '%');
                });
            }

            // if (isset($request->search[4])) {
            //     $search = trim($request->search[4]);
            //     $total_price = $total_price
            //         ->where('invoice_clients.invoice_date', 'like', '%' . $search . '%');
            // }

            if (isset($request->search[5])) {
                $search = trim($request->search[5]);
                $total_price = $total_price
                    ->where('invoice_clients.description', 'like', '%' . $search . '%');
            }

            if (isset($request->search[6])) {
                $search = trim($request->search[6]);
                $total_price = $total_price->orWhereHas('client_po', function ($query) use ($search) {
                    $query->where('po_number', 'like', '%' . $search . '%');
                });
            }

            // if (isset($request->search[7])) {
            //     $search = trim($request->search[7]);
            //     $total_price = $total_price
            //         ->where('invoice_clients.po_date', 'like', '%' . $search . '%');
            // }

            if (isset($request->search[8])) {
                $search = trim($request->search[8]);
                $total_price = $total_price->orWhereHas('client_po.client', function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%');
                });
            }

            if (isset($request->search[9])) {
                $search = trim($request->search[9]);
                $total_price = $total_price
                    ->where('invoice_clients.price_total_exclude_ppn', 'like', '%' . $search . '%');
            }

            if (isset($request->search[10])) {
                $search = trim($request->search[10]);
                $total_price = $total_price
                    ->where('invoice_clients.price_total_include_ppn', 'like', '%' . $search . '%');
            }

            // if (isset($request->search[11])) {
            //     $search = trim($request->search[11]);
            //     $total_price = $total_price
            //         ->where('invoice_clients.send_invoice_normal_date', 'like', '%' . $search . '%');
            // }

            // if (isset($request->search[12])) {
            //     $search = trim($request->search[12]);
            //     $total_price = $total_price
            //         ->where('invoice_clients.send_invoice_revision_date', 'like', '%' . $search . '%');
            // }

            // if (isset($request->search[13])) {
            //     $search = trim($request->search[13]);
            //     $total_price = $total_price
            //         ->where('invoice_clients.status', 'like', '%' . $search . '%');
            // }
        }

        if ($request->has('invoice_date')) {
            $total_price = $total_price
                ->where('invoice_clients.invoice_date', $request->invoice_date);
        }

        if ($request->has('po_date')) {
            $total_price = $total_price
                ->where('invoice_clients.po_date', $request->po_date);
        }

        if ($request->has('send_invoice_normal')) {
            $total_price = $total_price
                ->where('invoice_clients.send_invoice_normal_date', $request->send_invoice_normal);
        }

        if ($request->has('send_invoice_revision')) {
            $total_price = $total_price
                ->where('invoice_clients.send_invoice_revision_date', $request->send_invoice_revision);
        }

        if ($request->has('filter_paid_status')) {
            if ($request->filter_paid_status != 'all') {
                $total_price = $total_price
                    ->where('invoice_clients.status', $request->filter_paid_status);
            }
        }

        $total_price = $total_price->first();

        return response()->json([
            'total_price_exclude_ppn' => CustomHelper::formatRupiahWithCurrency($total_price->total_price_exclude_ppn),
            'total_price_include_ppn' => CustomHelper::formatRupiahWithCurrency($total_price->total_price_include_ppn),
        ]);
    }

    public function index()
    {
        $this->crud->hasAccessOrFail('list');

        $this->crud->param_uri_export = "?export=1";

        $this->getComponent();

        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? mb_ucfirst($this->crud->entity_name_plural);
        $this->data['title_modal_create'] = trans('backpack::crud.invoice_client.title_modal_create');
        $this->data['title_modal_edit'] = trans('backpack::crud.invoice_client.title_modal_edit');
        $this->data['title_modal_delete'] = trans('backpack::crud.invoice_client.title_modal_delete');
        $this->data['cards'] = $this->card;

        $breadcrumbs = [
            'Invoice (client)' => backpack_url('invoice-client'),
            // trans('backpack::crud.menu.list_client') => backpack_url($this->crud->route)
        ];
        $this->data['breadcrumbs'] = $breadcrumbs;

        $list = "crud::list-blank" ?? $this->crud->getListView();
        return view($list, $this->data);
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

    public function edit($id)
    {
        $this->crud->hasAccessOrFail('update');

        $id = $this->crud->getCurrentEntryId() ?? $id;

        $this->crud->registerFieldEvents();

        $entry = $this->crud->getEntryWithLocale($id);
        $entry->po_date = Carbon::createFromFormat('Y-m-d', $entry->po_date)->format('d/m/Y');
        // $entry->client_name = $entry->client->name;
        $entry->price_total_exclude_ppn = $entry->price_total_exclude_ppn;
        $entry->price_total_include_ppn = $entry->price_total_include_ppn;

        $entry->invoice_client_details_edit = $entry->invoice_client_details;
        $entry->client_name = $entry->client_po->client->name;
        $entry->nominal_exclude_ppn = $entry->price_total_exclude_ppn;
        $entry->nominal_include_ppn = $entry->price_total_include_ppn;
        $entry->send_invoice_normal = $entry->send_invoice_normal_date;
        $entry->send_invoice_revision = $entry->send_invoice_revision_date;

        $this->data['entry'] = $entry;

        $this->crud->setOperationSetting('fields', $this->crud->getUpdateFields());

        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.edit') . ' ' . $this->crud->entity_name;
        $this->data['id'] = $id;

        return response()->json([
            'html' => view($this->crud->getEditView(), $this->data)->render()
        ]);
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::disableResponsiveTable();
        CRUD::removeButtons(['delete', 'show', 'update'], 'line');

        $this->crud->file_title_export_pdf = "Laporan_invoice.pdf";
        $this->crud->file_title_export_excel = "Laporan_invoice.xlsx";
        $this->crud->param_uri_export = "?export=1";

        CRUD::addButtonFromView('top', 'export-excel-table', 'export-excel-table', 'beginning');
        CRUD::addButtonFromView('top', 'export-pdf-table', 'export-pdf-table', 'beginning');
        CRUD::addButtonFromView('top', 'filter_paid_unpaid', 'filter-paid_unpaid', 'beginning');
        CRUD::addButtonFromView('line', 'show', 'show', 'end');
        CRUD::addButtonFromView('line', 'update', 'update', 'end');
        CRUD::addButtonFromView('line', 'print', 'print', 'end');
        CRUD::addButtonFromView('line', 'delete', 'delete', 'end');

        $status_file = '';
        if (strpos(url()->current(), 'excel')) {
            $status_file = 'excel';
        } else {
            $status_file = 'pdf';
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
                'label'  => trans('backpack::crud.invoice_client.column.invoice_number'),
                'name' => 'invoice_number',
                'type'  => 'text'
            ],
        );

        CRUD::column(
            [
                'label' => trans('backpack::crud.invoice_client.field.kdp.label'),
                'name' => 'kdp',
                'type'  => 'text'
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.invoice_client.column.name'),
                'name' => 'name',
                'type'  => 'closure',
                'function' => function ($entry) {
                    return $entry->client_po->job_name;
                },
                'orderable' => true,
                'orderLogic' => function ($query, $column, $columnDir) {
                    return $query->leftJoin('client_po', 'client_po.id', '=', 'invoice_clients.client_po_id')
                        ->orderBy('client_po.job_name', $columnDir)
                        ->select('invoice_clients.*');
                },
                // 'searchable' => true,
                // 'searchLogic' => function ($query, $column, $searchTerm) {
                //     return $query->orWhereHas('client_po', function ($query) use ($searchTerm) {
                //         $query->where('job_name', 'like', '%' . $searchTerm . '%');
                //     });
                // }
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.invoice_client.column.invoice_date'),
                'name' => 'invoice_date',
                'type'  => 'date'
            ],
        );

        CRUD::column(
            [
                'label' => trans('backpack::crud.invoice_client.column.description'),
                'name' => 'description',
                'type' => 'wrap_text'
            ]
        );

        CRUD::column([
            // 1-n relationship
            'label' => trans('backpack::crud.invoice_client.column.client_po_id'),
            'type'      => 'select',
            'name'      => 'client_po_id', // the column that contains the ID of that connected entity;
            'entity'    => 'client_po', // the method that defines the relationship in your Model
            'attribute' => 'po_number', // foreign key attribute that is shown to user
            'model'     => "App\Models\ClientPo", // foreign key model
            // OPTIONAL
            'limit' => 40, // Limit the number of characters shown
        ]);

        CRUD::column(
            [
                'label' => trans('backpack::crud.invoice_client.column.po_date'),
                'name' => 'po_date',
                'type' => 'date',
            ]
        );

        CRUD::column([
            // 1-n relationship
            'label' => trans('backpack::crud.invoice_client.column.client_id'),
            'type'      => 'closure',
            'name'      => 'client_name',
            'function' => function ($entry) {
                return $entry->client_po->client->name;
            } // the column that contains the ID of that connected entity;
            // OPTIONAL
            // 'limit' => 32, // Limit the number of characters shown
        ]);

        CRUD::column(
            [
                'label'  => trans('backpack::crud.invoice_client.column.price_total_exclude_ppn'),
                'name' => 'price_total_exclude_ppn',
                'type'  => 'closure',
                'function' => function ($entry) use ($status_file) {
                    return $this->priceFormatExport($status_file, $entry->price_total_exclude_ppn);
                },
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.invoice_client.column.price_total_include_ppn'),
                'name' => 'price_total_include_ppn',
                'type'  => 'closure',
                'function' => function ($entry) use ($status_file) {
                    return $this->priceFormatExport($status_file, $entry->price_total_include_ppn);
                },
            ],
        );

        CRUD::column(
            [
                'label' => trans('backpack::crud.invoice_client.column.send_invoice_normal'),
                'name' => 'send_invoice_normal_date',
                'type'  => 'date'
            ],
        );

        CRUD::column(
            [
                'label' => trans('backpack::crud.invoice_client.column.send_invoice_revision'),
                'name' => 'send_invoice_revision_date',
                'type'  => 'date'
            ],
        );

        CRUD::column(
            [
                'label' => trans('backpack::crud.invoice_client.column.status'),
                'name' => 'status',
                'type' => 'text',
            ]
        );

        // CRUD::column([
        //     'label' => 'Dokumen Invoice',
        //     'name' => 'invoice_document',
        //     'type' => 'closure',
        //     'function' => function ($entry) {
        //         if ($entry->invoice_document) {
        //             $url = asset('storage/' . $entry->invoice_document);
        //             $filename = basename($entry->invoice_document);
        //             return '<a href="' . $url . '" target="_blank" class="btn btn-sm btn-success" title="' . $filename . '">
        //                       <i class="la la-file-pdf"></i> PDF
        //                     </a>';
        //         }
        //         return '';
        //     },
        //     'escaped' => false,
        //     'searchLogic' => false,
        //     'orderable' => false,
        // ]);

        CRUD::column([
            'name'   => 'invoice_document',
            'type'   => 'upload',
            'label'  => trans('backpack::crud.client_po.column.document_path'),
            'disk'   => 'public',
        ]);

        $request = request();

        if ($request->columns) {
            if (isset($request->columns[1]['search']['value'])) {
                $search = $request->columns[1]['search']['value'];
                $this->crud->query = $this->crud->query
                    ->where('invoice_clients.invoice_number', 'like', '%' . $search . '%');
            }

            if (isset($request->columns[2]['search']['value'])) {
                $search = trim($request->columns[2]['search']['value']);
                $this->crud->query = $this->crud->query
                    ->where('invoice_clients.kdp', 'like', '%' . $search . '%');
            }

            if (isset($request->columns[3]['search']['value'])) {
                $search = trim($request->columns[3]['search']['value']);
                $this->crud->query = $this->crud->query->orWhereHas('client_po', function ($query) use ($search) {
                    $query->where('job_name', 'like', '%' . $search . '%');
                });
            }

            // if (isset($request->columns[4]['search']['value'])) {
            //     $search = trim($request->columns[4]['search']['value']);
            //     $this->crud->query = $this->crud->query
            //         ->where('invoice_clients.invoice_date', 'like', '%' . $search . '%');
            // }

            // Search for Description column (index 5)
            if (isset($request->columns[5]['search']['value'])) {
                $search = trim($request->columns[5]['search']['value']);
                $this->crud->query = $this->crud->query
                    ->where('invoice_clients.description', 'like', '%' . $search . '%');
            }

            // Client PO Number (shifted from index 5 to 6)
            if (isset($request->columns[6]['search']['value'])) {
                $search = trim($request->columns[6]['search']['value']);
                $this->crud->query = $this->crud->query->orWhereHas('client_po', function ($query) use ($search) {
                    $query->where('po_number', 'like', '%' . $search . '%');
                });
            }

            // if (isset($request->columns[7]['search']['value'])) {
            //     $search = trim($request->columns[7]['search']['value']);
            //     $this->crud->query = $this->crud->query
            //         ->where('invoice_clients.po_date', 'like', '%' . $search . '%');
            // }

            // Client Name (shifted from index 7 to 8)
            if (isset($request->columns[8]['search']['value'])) {
                $search = trim($request->columns[8]['search']['value']);
                $this->crud->query = $this->crud->query->orWhereHas('client_po.client', function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%');
                });
            }

            // Price Exclude PPN (shifted from index 8 to 9)
            if (isset($request->columns[9]['search']['value'])) {
                $search = trim($request->columns[9]['search']['value']);
                $this->crud->query = $this->crud->query
                    ->where('invoice_clients.price_total_exclude_ppn', 'like', '%' . $search . '%');
            }

            // Price Include PPN (shifted from index 9 to 10)
            if (isset($request->columns[10]['search']['value'])) {
                $search = trim($request->columns[10]['search']['value']);
                $this->crud->query = $this->crud->query
                    ->where('invoice_clients.price_total_include_ppn', 'like', '%' . $search . '%');
            }

            // if (isset($request->columns[10]['search']['value'])) {
            //     $search = trim($request->columns[10]['search']['value']);
            //     $this->crud->query = $this->crud->query
            //         ->where('invoice_clients.send_invoice_normal_date', 'like', '%' . $search . '%');
            // }

            // if (isset($request->columns[11]['search']['value'])) {
            //     $search = trim($request->columns[11]['search']['value']);
            //     $this->crud->query = $this->crud->query
            //         ->where('invoice_clients.send_invoice_revision_date', 'like', '%' . $search . '%');
            // }

            // if (isset($request->columns[12]['search']['value'])) {
            //     $search = trim($request->columns[12]['search']['value']);
            //     $this->crud->query = $this->crud->query
            //         ->where('invoice_clients.status', 'like', '%' . $search . '%');
            // }
        }

        if ($request->has('invoice_date')) {
            $this->crud->query = $this->crud->query
                ->where('invoice_clients.invoice_date', $request->invoice_date);
        }

        if ($request->has('po_date')) {
            $this->crud->query = $this->crud->query
                ->where('invoice_clients.po_date', $request->po_date);
        }

        if ($request->has('send_invoice_normal')) {
            $this->crud->query = $this->crud->query
                ->where('invoice_clients.send_invoice_normal_date', $request->send_invoice_normal);
        }

        if ($request->has('send_invoice_revision')) {
            $this->crud->query = $this->crud->query
                ->where('invoice_clients.send_invoice_revision_date', $request->send_invoice_revision);
        }

        if ($request->has('filter_paid_status')) {
            if ($request->filter_paid_status != 'all') {
                $this->crud->query = $this->crud->query
                    ->where('status', $request->filter_paid_status);
            }
        }
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

        $title = "DAFTAR INVOICE";

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

        $name = 'DAFTAR INVOICE';

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
        CRUD::setValidation(InvoiceClientRequest::class);
        $settings = Setting::first();
        $inv_prefix_value = [];
        if (!$this->crud->getCurrentEntryId()) {
            $inv_prefix_value = [
                'value' => $settings?->invoice_prefix,
            ];
        }
        // CRUD::setFromDb(); // set fields from db columns.
        CRUD::addField([
            'name' => 'invoice_number',
            'label' => trans('backpack::crud.invoice_client.field.invoice_number.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-12',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.invoice_client.field.invoice_number.placeholder'),
            ],
            ...$inv_prefix_value,
        ]);

        CRUD::addField([   // date_picker
            'name'  => 'invoice_date',
            'type'  => 'date_picker',
            'label' => trans('backpack::crud.invoice_client.field.invoice_date.label'),

            // optional:
            'date_picker_options' => [
                'language' => App::getLocale(),
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.invoice_client.field.invoice_date.placeholder')
            ]
        ]);

        CRUD::addField([
            'name' => 'client_name',
            'label' => trans('backpack::crud.invoice_client.field.client_id.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.invoice_client.field.client_id.placeholder'),
                'disabled' => true,
            ]
        ]);

        CRUD::addField([
            'name' => 'address_po',
            'label' => trans('backpack::crud.invoice_client.field.address.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-12',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.invoice_client.field.address.placeholder'),
            ]
        ]);

        CRUD::addField([   // 1-n relationship
            'label'       => trans('backpack::crud.invoice_client.field.client_po_id.label'), // Table column heading
            'type'        => "select2_ajax_custom",
            'name'        => 'client_po_id', // the column that contains the ID of that connected entity
            'entity'      => 'client_po', // the method that defines the relationship in your Model
            'attribute'   => 'po_number', // foreign key attribute that is shown to user
            'data_source' => backpack_url('invoice-client/select2-client-po'), // url to controller search function (with /{id} should return a single entry)
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'placeholder' => trans('backpack::crud.invoice_client.field.client_po_id.placeholder'),
        ]);

        CRUD::addField([   // date_picker
            'name'  => 'po_date',
            'type'  => 'text',
            'label' => trans('backpack::crud.invoice_client.field.po_date.label'),

            'suffix' => '<i class="la la-calendar"></i>',
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.invoice_client.field.po_date.placeholder'),
                'disabled' => true
            ]
        ]);

        CRUD::addField([
            'name' => 'description',
            'label' => trans('backpack::crud.invoice_client.field.description.label'),
            'type' => 'textarea',
            'wrapper'   => [
                'class' => 'form-group col-md-12',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.invoice_client.field.description.placeholder'),
            ]
        ]);

        CRUD::addField([
            'name' => 'nominal_exclude_ppn',
            'label' => trans('backpack::crud.invoice_client.field.nominal_exclude_ppn.label'),
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
                'placeholder' => trans('backpack::crud.invoice_client.field.nominal_exclude_ppn.placeholder'),
                // 'disabled' => true,
            ]
        ]);

        CRUD::addField([
            'name' => 'dpp_other',
            'label' => trans('backpack::crud.invoice_client.field.dpp_other.label'),
            'type' => 'mask',
            'mask' => '000.000.000.000.000.000',
            'mask_options' => [
                'reverse' => true
            ],
            'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : 'Rp.',
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.invoice_client.field.dpp_other.placeholder'),
            ]
        ]);

        CRUD::addField([
            'name' => 'tax_ppn',
            'label' => trans('backpack::crud.invoice_client.field.tax_ppn.label'),
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
            ]
        ]);

        CRUD::addField([
            'name' => 'nominal_include_ppn',
            'label' => trans('backpack::crud.invoice_client.field.nominal_include_ppn.label'),
            'type' => 'text',
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
                'disabled' => true,
            ]
        ]);

        CRUD::addField([
            'name' => 'kdp',
            'label' => trans('backpack::crud.invoice_client.field.kdp.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.invoice_client.field.kdp.placeholder'),
            ]
        ]);

        CRUD::addField([
            'name' => 'space_1',
            'label' => '',
            'type' => 'hidden',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
        ]);


        CRUD::addField([   // date_picker
            'name'  => 'send_invoice_normal',
            'type'  => 'date_picker',
            'label' => trans('backpack::crud.invoice_client.field.send_invoice_normal.label'),

            // optional:
            'date_picker_options' => [
                'language' => App::getLocale(),
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::addField([   // date_picker
            'name'  => 'send_invoice_revision',
            'type'  => 'date_picker',
            'label' => trans('backpack::crud.invoice_client.field.send_invoice_revision.label'),

            // optional:
            'date_picker_options' => [
                'language' => App::getLocale(),
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);


        CRUD::addField([
            'name'        => 'status',
            'label'       => trans('backpack::crud.invoice_client.field.status.label'),
            'type'        => 'select_from_array',
            'options'     => ['' => trans('backpack::crud.invoice_client.field.status.placeholder'), 'Paid' => 'Paid', 'Unpaid' => 'Unpaid'],
            'allows_null' => false,
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'disabled' => true,
            ]
            // 'allows_multiple' => true, // OPTIONAL; needs you to cast this to array in your model;
        ]);

        CRUD::addField([
            'name' => 'space_2',
            'label' => '',
            'type' => 'hidden',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        CRUD::addField([
            'name' => 'nominal_information',
            'label' => trans('backpack::crud.invoice_client.field.nominal_information.label'),
            'type' => 'text',
            'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : 'Rp.',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => '000.000',
                'readonly' => true,
            ]
        ]);

        CRUD::addField([
            'name' => 'space_3',
            'label' => '',
            'type' => 'hidden',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        CRUD::addField([
            'name' => 'invoice_document',
            'label' => trans('backpack::crud.invoice_client.field.invoice_document.label'),
            'type' => 'upload',
            'upload' => true,
            'disk' => 'public',
            'prefix' => 'document_invoice/',
            'wrapper'   => [
                'class' => 'form-group col-md-12',
            ],
            'attributes' => [
                'accept' => '.pdf',
            ],
            'hint' => trans('backpack::crud.invoice_client.field.invoice_document.hint'),
        ]);

        $id = request()->segment(3);

        if ($id != 'create') {
            CRUD::addField([
                'name' => 'invoice_client_details_edit',
                'label' => trans('backpack::crud.invoice_client.field.item.label'),
                'type' => 'repeatable',
                'new_item_label'  => trans('backpack::crud.invoice_client.field.item.new_item_label'),
                'fields' => [
                    [
                        'name' => 'name',
                        'type' => 'text',
                        'label' => trans('backpack::crud.invoice_client.field.item.items.name.label'),
                        'wrapper' => [
                            'class' => 'form-group col-md-6',
                        ]
                    ],
                    [
                        'name' => 'price',
                        'type' => 'mask_repeat',
                        'label' => trans('backpack::crud.invoice_client.field.item.items.price.label'),
                        'wrapper' => [
                            'class' => 'form-group col-md-6',
                        ],
                        'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : 'Rp.',
                        'mask' => '000.000.000.000.000.000',
                        'mask_options' => [
                            'reverse' => true
                        ],
                    ],
                ]
            ]);
        } else {
            CRUD::addField([
                'name' => 'invoice_client_details',
                'label' => trans('backpack::crud.invoice_client.field.item.label'),
                'type' => 'repeatable',
                'new_item_label'  => trans('backpack::crud.invoice_client.field.item.new_item_label'),
                'fields' => [
                    [
                        'name' => 'name',
                        'type' => 'text',
                        'label' => trans('backpack::crud.invoice_client.field.item.items.name.label'),
                        'wrapper' => [
                            'class' => 'form-group col-md-6',
                        ]
                    ],
                    [
                        'name' => 'price',
                        'label' => trans('backpack::crud.invoice_client.field.item.items.price.label'),
                        'type' => 'mask',
                        'mask' => '000.000.000.000.000.000',
                        'mask_options' => [
                            'reverse' => true
                        ],
                        'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : 'Rp.',
                        'wrapper'   => [
                            'class' => 'form-group col-md-6'
                        ],
                    ]
                ]
            ]);
        }


        CRUD::addField([
            'name' => 'logic_invoice',
            'label' => '',
            'type' => 'logic_invoice',
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

    public function store()
    {
        $this->crud->hasAccessOrFail('create');

        $po = ClientPo::find(request()->client_po_id);

        $request = request();

        if ($po != null) {
            request()->merge([
                // 'nominal_exclude_ppn' => $po->job_value,
                'nominal_include_ppn' => (int) $request->nominal_exclude_ppn + ($request->nominal_exclude_ppn * $request->tax_ppn / 100),
            ]);
        }

        $request = $this->crud->validateRequest();

        $this->crud->registerFieldEvents();

        DB::beginTransaction();
        try {

            $total_price = 0;
            // if ($request->dpp_other) {
            //     $total_price += $request->dpp_other;
            // }
            if ($request->nominal_include_ppn) {
                $total_price += $request->nominal_include_ppn;
            }

            $items = $request->invoice_client_details;
            $total_item_price = 0;
            foreach ($items as $item) {
                $total_price += (int) ($item['price'] != '' && $item['price'] != null) ? $item['price'] : 0;
                $total_item_price += (int) ($item['price'] != '' && $item['price'] != null) ? $item['price'] : 0;
            }

            // $item = $this->crud->create($this->crud->getStrippedSaveRequest($request));
            $invoice = new InvoiceClient();
            $invoice->invoice_number = $request->invoice_number;
            $invoice->name = 'invoice';
            $invoice->address_po = ($request->address_po != '' && $request->address_po != null) ? $request->address_po : '';
            $invoice->description = $request->description;
            $invoice->invoice_date = $request->invoice_date;
            $invoice->client_po_id = $request->client_po_id;
            $invoice->po_date = $po->date_po;
            $invoice->tax_ppn = $request->tax_ppn;
            $invoice->price_dpp = $request->dpp_other;
            $invoice->kdp = $request->kdp;
            $invoice->send_invoice_normal_date = $request->send_invoice_normal;
            $invoice->send_invoice_revision_date = $request->send_invoice_revision;
            $invoice->price_total_exclude_ppn = $request->nominal_exclude_ppn;
            $invoice->price_total_include_ppn = $request->nominal_include_ppn;
            $invoice->status = 'Unpaid';
            $invoice->price_total = $total_price;

            // Handle file upload for invoice_document
            if ($request->hasFile('invoice_document')) {
                $file = $request->file('invoice_document');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('document_invoice', $filename, 'public');
                $invoice->invoice_document = $path;
            }

            $invoice->save();

            if ($total_item_price > 0) {
                foreach ($items as $item) {
                    $invoice_item = new InvoiceClientDetail();
                    $invoice_item->invoice_client_id = $invoice->id;
                    $invoice_item->name = $item['name'];
                    $invoice_item->price = $item['price'];
                    $invoice_item->save();
                }
            }

            $this->data['entry'] = $this->crud->entry = $invoice;

            CustomVoid::invoiceMakeVoucherMoveAccount($invoice);
            CustomVoid::invoiceCreate($invoice);

            \Alert::success(trans('backpack::crud.insert_success'))->flash();

            $this->crud->setSaveAction();

            DB::commit();
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $invoice,
                    'events' => [
                        'crudTable-filter_invoice_plugin_load' => true,
                        'crudTable-invoice_create_success' => true
                    ],
                ]);
            }
            return $this->crud->performSaveAction($invoice->getKey());
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function update($id)
    {
        $this->crud->hasAccessOrFail('update');

        $po = ClientPo::find(request()->client_po_id);

        $request = request();

        if ($po != null) {
            request()->merge([
                'nominal_include_ppn' => (int) $request->nominal_exclude_ppn + ($request->nominal_exclude_ppn * $request->tax_ppn / 100),
            ]);
        }

        // execute the FormRequest authorization and validation, if one is required
        $request = $this->crud->validateRequest();

        // register any Model Events defined on fields
        $this->crud->registerFieldEvents();

        // update the row in the db
        DB::beginTransaction();
        try {

            $total_price = 0;

            if ($request->nominal_include_ppn) {
                $total_price += $request->nominal_include_ppn;
            }

            $items = $request->invoice_client_details_edit;
            $total_item_price = 0;
            foreach ($items as $item) {
                $total_price += (int) ($item['price'] != '' && $item['price'] != null) ? $item['price'] : 0;
                $total_item_price += (int) ($item['price'] != '' && $item['price'] != null) ? $item['price'] : 0;
            }

            $invoice = InvoiceClient::where('id', $id)->first();
            $old_client_po_id = $invoice->client_po_id;

            $invoice->invoice_number = $request->invoice_number;
            $invoice->name = 'invoice';
            $invoice->address_po = ($request->address_po != '' && $request->address_po != null) ? $request->address_po : '';
            $invoice->description = $request->description;
            $invoice->invoice_date = $request->invoice_date;
            $invoice->client_po_id = $request->client_po_id;
            $invoice->po_date = Carbon::now();
            $invoice->tax_ppn = $request->tax_ppn;
            $invoice->price_dpp = $request->dpp_other;
            $invoice->kdp = $request->kdp;
            $invoice->send_invoice_normal_date = $request->send_invoice_normal;
            $invoice->send_invoice_revision_date = $request->send_invoice_revision;
            $invoice->price_total_exclude_ppn = $request->nominal_exclude_ppn;
            $invoice->price_total_include_ppn = $request->nominal_include_ppn;
            $invoice->price_total = $total_price;

            // Handle file upload for invoice_document
            if ($request->hasFile('invoice_document')) {
                // Delete old file if exists
                if ($invoice->invoice_document && Storage::disk('public')->exists($invoice->invoice_document)) {
                    Storage::disk('public')->delete($invoice->invoice_document);
                }

                // Upload new file
                $file = $request->file('invoice_document');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('document_invoice', $filename, 'public');
                $invoice->invoice_document = $path;
            }

            $invoice->save();

            InvoiceClientDetail::where('invoice_client_id', $id)->delete();

            if ($total_item_price > 0) {
                foreach ($items as $item) {
                    $invoice_item = new InvoiceClientDetail();
                    $invoice_item->invoice_client_id = $invoice->id;
                    $invoice_item->name = $item['name'];
                    $invoice_item->price = $item['price'];
                    $invoice_item->save();
                }
            }

            $this->data['entry'] = $this->crud->entry = $invoice;

            CustomVoid::invoiceUpdate($invoice, $old_client_po_id);

            DB::commit();
            // show a success message
            \Alert::success(trans('backpack::crud.update_success'))->flash();
            // save the redirect choice for next time
            $this->crud->setSaveAction();
            return response()->json([
                'success' => true,
                'data' => $invoice,
                'events' => [
                    'crudTable-filter_invoice_plugin_load' => true,
                    'crudTable-invoice_updated_success' => true
                ]
            ]);
            // return $this->crud->performSaveAction($invoice);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function applyInvoicePaymentToAccount($invoice)
    {
        // $approval_voucher = Approval::where('model_type', 'App\\Models\\PaymentVoucherPlan')
        // ->whereExists(function ($query) use ($invoice) {
        //     $query->select(DB::raw(1))
        //     ->from('payment_voucher_plan')
        //     ->whereColumn('payment_voucher_plan.id', 'approvals.model_id')
        //     ->whereExists(function ($query) use ($invoice) {
        //         $query->select(DB::raw(1))
        //         ->from('payment_vouchers')
        //         ->whereColumn('payment_vouchers.id', 'payment_voucher_plan.payment_voucher_id')
        //         ->whereExists(function ($query) use ($invoice) {
        //             $query->select(DB::raw(1))
        //             ->from('vouchers')
        //             ->whereColumn('vouchers.id', 'payment_vouchers.voucher_id')
        //             // ->where('vouchers.reference_type', '=', 'App\\Models\\ClientPo')
        //             ->where('vouchers.client_po_id', '=', $invoice->client_po_id);
        //         });
        //     });
        // })->orderBy('id', 'desc')->first();
    }

    protected function setupShowOperation()
    {
        CRUD::addField([
            'name' => 'invoice_number',
            'label' => trans('backpack::crud.invoice_client.field.invoice_number.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.invoice_client.field.invoice_number.placeholder'),
            ],
        ]);

        CRUD::addField([
            'name' => 'name',
            'label' => trans('backpack::crud.invoice_client.column.name'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.invoice_client.field.invoice_number.placeholder'),
            ],
        ]);

        CRUD::addField([   // date_picker
            'name'  => 'invoice_date',
            'type'  => 'date_picker',
            'label' => trans('backpack::crud.invoice_client.field.invoice_date.label'),

            // optional:
            'date_picker_options' => [
                'language' => App::getLocale(),
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.invoice_client.field.invoice_date.placeholder')
            ]
        ]);

        CRUD::addField([
            'name' => 'client_name',
            'label' => trans('backpack::crud.invoice_client.field.client_id.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.invoice_client.field.client_id.placeholder'),
                'disabled' => true,
            ]
        ]);

        CRUD::addField([
            'name' => 'address_po',
            'label' => trans('backpack::crud.invoice_client.field.address.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-12',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.invoice_client.field.address.placeholder'),
            ]
        ]);

        CRUD::addField([   // 1-n relationship
            'label'       => trans('backpack::crud.invoice_client.field.client_po_id.label'), // Table column heading
            'type'        => "select2_ajax_custom",
            'name'        => 'client_po_id', // the column that contains the ID of that connected entity
            'entity'      => 'client_po', // the method that defines the relationship in your Model
            'attribute'   => 'po_number', // foreign key attribute that is shown to user
            'data_source' => backpack_url('invoice-client/select2-client-po'), // url to controller search function (with /{id} should return a single entry)
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'placeholder' => trans('backpack::crud.invoice_client.field.client_po_id.placeholder'),
        ]);

        CRUD::addField([   // date_picker
            'name'  => 'po_date',
            'type'  => 'text',
            'label' => trans('backpack::crud.invoice_client.field.po_date.label'),

            'suffix' => '<i class="la la-calendar"></i>',
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.invoice_client.field.po_date.placeholder'),
                'disabled' => true,
            ]
        ]);

        CRUD::addField([
            'name' => 'description',
            'label' => trans('backpack::crud.invoice_client.field.description.label'),
            'type' => 'textarea',
            'wrapper'   => [
                'class' => 'form-group col-md-12',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.invoice_client.field.description.placeholder'),
            ]
        ]);

        CRUD::addField([
            'name' => 'nominal_exclude_ppn',
            'label' => trans('backpack::crud.invoice_client.field.nominal_exclude_ppn.label'),
            'type' => 'mask',
            'mask' => '000.000.000.000.000.000',
            'mask_options' => [
                'reverse' => true
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.invoice_client.field.nominal_exclude_ppn.placeholder'),
                'disabled' => true,
            ]
        ]);

        CRUD::addField([
            'name' => 'dpp_other',
            'label' => trans('backpack::crud.invoice_client.field.dpp_other.label'),
            'type' => 'mask',
            'mask' => '000.000.000.000.000.000',
            'mask_options' => [
                'reverse' => true
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.invoice_client.field.dpp_other.placeholder'),
            ]
        ]);

        CRUD::addField([
            'name' => 'tax_ppn',
            'label' => trans('backpack::crud.invoice_client.field.tax_ppn.label'),
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
            ]
        ]);

        CRUD::addField([
            'name' => 'nominal_include_ppn',
            'label' => trans('backpack::crud.invoice_client.field.nominal_include_ppn.label'),
            'type' => 'mask',
            'mask' => '000.000.000.000.000.000',
            'mask_options' => [
                'reverse' => true
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => '000.000',
                'disabled' => true,
            ]
        ]);

        CRUD::addField([
            'name' => 'kdp',
            'label' => trans('backpack::crud.invoice_client.field.kdp.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-12',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.invoice_client.field.kdp.placeholder'),
            ]
        ]);

        CRUD::addField([   // date_picker
            'name'  => 'send_invoice_normal',
            'type'  => 'date_picker',
            'label' => trans('backpack::crud.invoice_client.field.send_invoice_normal.label'),

            // optional:
            'date_picker_options' => [
                'language' => App::getLocale(),
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::addField([   // date_picker
            'name'  => 'send_invoice_revision',
            'type'  => 'date_picker',
            'label' => trans('backpack::crud.invoice_client.field.send_invoice_revision.label'),

            // optional:
            'date_picker_options' => [
                'language' => App::getLocale(),
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::addField([
            'name'        => 'status',
            'label'       => trans('backpack::crud.invoice_client.field.status.label'),
            'type'        => 'select_from_array',
            'options'     => ['' => trans('backpack::crud.invoice_client.field.status.placeholder'), 'Paid' => 'Paid', 'Unpaid' => 'Unpaid'],
            'allows_null' => false,
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        CRUD::addField([
            'name' => 'invoice_client_details_edit',
            'label' => trans('backpack::crud.invoice_client.field.item.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-12',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.invoice_client.field.kdp.placeholder'),
            ]
        ]);
        // COLUMN

        CRUD::column(
            [
                'label'  => trans('backpack::crud.invoice_client.column.invoice_number'),
                'name' => 'invoice_number',
                'type'  => 'text'
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.invoice_client.column.name'),
                'name' => 'name',
                'type'  => 'closure',
                'function' => function ($entry) {
                    return $entry->client_po->job_name;
                }
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.invoice_client.column.invoice_date'),
                'name' => 'invoice_date',
                'type'  => 'date'
            ],
        );

        CRUD::column([
            // 1-n relationship
            'label' => trans('backpack::crud.invoice_client.column.client_id'),
            'type'      => 'closure',
            'name'      => 'client_name',
            'function' => function ($entry) {
                return $entry->client_po->client->name;
            } // the column that contains the ID of that connected entity;
            // OPTIONAL
            // 'limit' => 32, // Limit the number of characters shown
        ]);

        CRUD::column(
            [
                'label'  => trans('backpack::crud.invoice_client.column.name'),
                'name' => 'address_po',
                'type'  => 'text'
            ],
        );


        CRUD::column([
            // 1-n relationship
            'label' => trans('backpack::crud.invoice_client.column.client_po_id'),
            'type'      => 'select',
            'name'      => 'client_po_id', // the column that contains the ID of that connected entity;
            'entity'    => 'client_po', // the method that defines the relationship in your Model
            'attribute' => 'po_number', // foreign key attribute that is shown to user
            'model'     => "App\Models\ClientPo", // foreign key model
            // OPTIONAL
            // 'limit' => 32, // Limit the number of characters shown
        ]);

        CRUD::column(
            [
                'label' => trans('backpack::crud.invoice_client.column.po_date'),
                'name' => 'po_date',
                'type' => 'date',
            ]
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.invoice_client.column.name'),
                'name' => 'description',
                'type'  => 'closure',
                'function' => function ($entry) {
                    return $entry->description;
                },
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.invoice_client.column.price_total_exclude_ppn'),
                'name' => 'price_total_exclude_ppn',
                'type'  => 'number',
                'prefix' => "Rp.",
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.invoice_client.column.price_total_exclude_ppn'),
                'name' => 'price_dpp',
                'type'  => 'number',
                'prefix' => "Rp.",
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.invoice_client.column.price_total_exclude_ppn'),
                'name' => 'tax_ppn',
                'type'  => 'number',
            ],
        );

        CRUD::column(
            [
                'label'  => trans('backpack::crud.invoice_client.column.price_total_include_ppn'),
                'name' => 'price_total_include_ppn',
                'type'  => 'number',
                'prefix' => "Rp.",
                'decimals'      => 2,
                'dec_point'     => ',',
                'thousands_sep' => '.',
            ],
        );

        CRUD::column(
            [
                'label' => trans('backpack::crud.invoice_client.column.status'),
                'name' => 'kdp',
                'type' => 'text',
            ]
        );

        CRUD::column(
            [
                'label' => trans('backpack::crud.invoice_client.column.po_date'),
                'name' => 'send_invoice_normal_date',
                'type' => 'date',
            ]
        );

        CRUD::column(
            [
                'label' => trans('backpack::crud.invoice_client.column.po_date'),
                'name' => 'send_invoice_revision_date',
                'type' => 'date',
            ]
        );

        CRUD::column(
            [
                'label' => trans('backpack::crud.invoice_client.column.status'),
                'name' => 'status',
                'type' => 'text',
            ]
        );

        CRUD::column([
            'label' => 'Dokumen Invoice',
            'name' => 'invoice_document',
            'type' => 'closure',
            'function' => function ($entry) {
                if ($entry->invoice_document) {
                    $url = asset('storage/' . $entry->invoice_document);
                    $filename = basename($entry->invoice_document);
                    return '<a href="' . $url . '" target="_blank" class="btn btn-sm btn-success"><i class="la la-file-pdf"></i> ' . $filename . '</a>';
                }
                return '';
            },
            'escaped' => false,
        ]);

        CRUD::column(
            [
                'label' => trans('backpack::crud.invoice_client.column.status'),
                'name' => 'list_invoice',
                'type' => 'list-invoice',
            ]
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
        $id = $this->crud->getCurrentEntryId() ?? $id; // id invoice

        $invoice = InvoiceClient::find($id);

        CustomVoid::invoiceDelete($invoice);
        $invoice->delete();
        $messages['success'][] = trans('backpack::crud.delete_confirmation_message');
        $messages['events'] = [
            'crudTable-filter_invoice_plugin_load' => true,
            'crudTable-invoice_create_success' => true,
        ];
        return response()->json($messages);
    }


    public function printInvoice($id)
    {

        $data = [];
        $data['header'] = InvoiceClient::where('id', $id)->first();
        $data['details'] = InvoiceClientDetail::where('invoice_client_id', $id)->get();

        $pdf = Pdf::loadView('exports.invoice-client-pdf-new', $data);
        return $pdf->stream('invoice.pdf');
        return view('exports.invoice-client-pdf-new');
    }
}
