<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CrudController;
use App\Http\Controllers\Operation\FormaterExport;
use App\Http\Controllers\Operation\PermissionAccess;
use App\Http\Exports\ExportExcel;
use App\Http\Helpers\CustomHelper;
use App\Models\CategoryProject;
use App\Models\Project;
use App\Models\Setting;
use App\Models\SetupClient;
use App\Models\SetupCompanyClassification;
use App\Models\SetupPph;
use App\Models\SetupPpn;
use App\Models\SetupStatusProject;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ProjectListReportCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use PermissionAccess;
    use FormaterExport;

    public function setup()
    {
        CRUD::setModel(Project::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/monitoring/project-report');
        CRUD::setEntityNameStrings(trans('backpack::crud.menu.project_report'), trans('backpack::crud.menu.project_report'));

        $base = 'INDEX MONITORING PROYEK PROYEK REPORT';
        $viewMenu  = ["MENU $base"];

        $this->settingPermission([
            'list'   => $viewMenu,
            'show'   => $viewMenu,
            'print'  => true,
        ]);
    }

    function index()
    {
        $this->crud->hasAccessOrFail('list');

        $this->card->addCard([
            'name' => 'hightlight',
            'line' => 'top',
            'label' => '',
            'parent_view' => 'crud::components.filter-parent',
            'view' => 'crud::components.hightligh-column',
        ]);

        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? mb_ucfirst($this->crud->entity_name_plural);
        $this->data['title_modal_create'] = trans('backpack::crud.project_report.title_modal_create');
        $this->data['title_modal_edit'] = trans('backpack::crud.project_report.title_modal_edit');
        $this->data['title_modal_delete'] = trans('backpack::crud.project_report.title_modal_delete');
        $yearOptions = Project::selectRaw('YEAR(start_date) as year')
            ->union(Project::selectRaw('YEAR(end_date) as year'))
            ->pluck('year')
            ->filter()
            ->toArray();
        $yearOptions = array_unique($yearOptions);
        rsort($yearOptions);

        $this->crud->year_options = $yearOptions;

        $this->data['cards'] = $this->card;

        $breadcrumbs = [
            trans('backpack::crud.menu.monitoring_project') => backpack_url('monitoring'),
            trans('backpack::crud.menu.project_report') => backpack_url($this->crud->route)
        ];
        $this->data['breadcrumbs'] = $breadcrumbs;
        $list = "crud::list-custom" ?? $this->crud->getListView();
        return view($list, $this->data);
    }

    function hitungDurasiHari($actualStartDate, $actualEndDate)
    {
        if ($actualStartDate == null || $actualEndDate == null) {
            return 0;
        }

        $startDate = Carbon::parse($actualStartDate);
        $endDate = Carbon::parse($actualEndDate);

        // Selisih termasuk hari ini
        return $startDate->diffInDays($endDate);
    }

    protected function setupListOperation()
    {
        CRUD::disableResponsiveTable();
        // $settings = Setting::first();

        $status_file = '';
        if (strpos(url()->current(), 'excel')) {
            $status_file = 'excel';
        } else {
            $status_file = 'pdf';
        }

        $wrap_length = [
            'width_box' => '500px',
        ];

        // $new_format_date = 'D MMM Y';
        $new_format_date = 'DD/MM/YYYY';

        CRUD::addButtonFromView('top', 'filter-year', 'filter-year', 'beginning');
        CRUD::addButtonFromView('top', 'filter-project', 'filter-project', 'beginning');
        CRUD::addButtonFromView('top', 'filter-status-project', 'filter-status-project', 'beginning');
        $this->crud->addButton('line_start', 'show', 'view', 'crud::buttons.show', 'end');

        $this->crud->file_title_export_pdf = "Laporan_project-report.pdf";
        $this->crud->file_title_export_excel = "Laporan_project-report.xlsx";
        $this->crud->param_uri_export = "?export=1";

        if (request()->has('filter_year') && request()->filter_year != 'all') {
            $year = request()->filter_year;
            $this->crud->query = $this->crud->query->where(function ($q) use ($year) {
                $q->whereYear('projects.start_date', $year)
                    ->orWhereYear('projects.end_date', $year);
            });
        }

        CRUD::addButtonFromView('top', 'export-excel-table', 'export-excel-table', 'beginning');
        CRUD::addButtonFromView('top', 'export-pdf-table', 'export-pdf-table', 'beginning');


        if (request()->has('filter_category')) {
            if (request()->filter_category != 'all') {
                $this->crud->addClause('where', 'category', request()->filter_category);
            }
        }

        if (request()->has('filter_client')) {
            if (request()->filter_client != 'all') {
                $this->crud->query = $this->crud->query
                    ->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('setup_clients')
                            ->whereRaw('setup_clients.id = projects.client_id')
                            ->where('setup_clients.id', request()->filter_client);
                    });
            }
        }

        if (request()->has('filter_status_project')) {
            if (request()->filter_status_project != 'all') {
                $this->crud->addClause('where', 'status_po', request()->filter_status_project);
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

        CRUD::addColumn([
            'name' => 'action',
            'type' => 'closure',
            'label' =>  trans('backpack::crud.actions'),
            'escaped' => false,
            'function' => function ($entry, $rowNumber) {
                $crud = $this->crud;
                return \View::make('crud::inc.button_stack', ['stack' => 'line_start'])
                    ->with('crud', $crud)
                    ->with('entry', $entry)
                    ->with('row_number', $rowNumber)
                    ->render();
            }
        ]);

        CRUD::column(
            [
                'label' => trans('backpack::crud.project.column.project.no_po_spk.label'),
                'name' => 'no_po_spk',
                'type'  => 'wrap_text',
                'searchLogic' => function ($query, $column, $searchTerm) {
                    $query->orWhere('no_po_spk', 'like', '%' . $searchTerm . '%');
                }
            ],
        );
        CRUD::column(
            [
                'label' => trans('backpack::crud.project.column.project.name.label'),
                'name' => 'name',
                'type'  => 'wrap_text',
                ...$wrap_length,
                'searchLogic' => function ($query, $column, $searchTerm) {
                    $query->orWhere('name', 'like', '%' . $searchTerm . '%');
                }
            ],
        );
        CRUD::column([
            'label' => trans('backpack::crud.project.column.project.price_total_include_ppn.label'),
            'name' => 'price_total_include_ppn',
            'type'  => 'closure',
            'function' => function ($entry) use ($status_file) {
                return $this->priceFormatExport($status_file, $entry->price_total_include_ppn);
            },
            // 'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
        ]);
        CRUD::column([
            'label' => trans('backpack::crud.project.column.project.transfer_value.label'),
            'name' => 'transfer_value',
            'type'  => 'closure',
            'function' => function ($entry) use ($status_file) {
                $price_pph = $entry->price_pph ?? 0;
                $price_fine = $entry->fine_price ?? 0;
                if ($entry->company_classification == 'WAPU') {
                    $transfer_value = $entry->price_total_exclude_ppn - $price_pph - $price_fine;
                } else if ($entry->company_classification == 'NON WAPU') {
                    $transfer_value = $entry->price_total_include_ppn - $price_pph - $price_fine;
                } else {
                    return '-';
                }
                return $this->priceFormatExport($status_file, $transfer_value);
            },
            // 'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
        ]);
        CRUD::column([
            // 1-n relationship
            'label' => trans('backpack::crud.client_po.column.client_id'),
            'type'      => 'select',
            'name'      => 'client_id', // the column that contains the ID of that connected entity;
            'entity'    => 'setup_client', // the method that defines the relationship in your Model
            'attribute' => 'name', // foreign key attribute that is shown to user
            'model'     => "App\Models\SetupClient", // foreign key model
            // OPTIONAL
            'limit' => 50, // Limit the number of characters shown
        ]);
        CRUD::column([
            'label' => trans('backpack::crud.project.column.project.price_total_exclude_ppn.label'),
            'name' => 'price_total_exclude_ppn',
            'type'  => 'closure',
            'function' => function ($entry) use ($status_file) {
                return $this->priceFormatExport($status_file, $entry->price_total_exclude_ppn);
            },
            // 'prefix' => ($settings?->currency_symbol) ? $settings->currency_symbol : "Rp.",
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
        ]);
        CRUD::column([
            'label' => trans('backpack::crud.project.column.project.price_ppn.label'),
            'name' => 'price_ppn',
            'type'  => 'closure',
            'function' => function ($entry) use ($status_file) {
                return $this->priceFormatExport($status_file, $entry->price_ppn);
            },
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
        ]);

        CRUD::column([
            'label' => trans('backpack::crud.project.column.project.tax_ppn.label'),
            'name' => 'tax_ppn',
            'type'  => 'closure',
            'function' => function ($entry) {
                $val = number_format($entry->tax_ppn, 2, ',', '.');
                return str_replace(',00', '', $val) . '%';
            }
        ]);
        CRUD::column([
            'label' => trans('backpack::crud.project.column.project.price_pph.label'),
            'name' => 'price_pph',
            'type'  => 'closure',
            'function' => function ($entry) use ($status_file) {
                return $this->priceFormatExport($status_file, $entry->price_pph);
            },
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
        ]);
        CRUD::column([
            'label' => trans('backpack::crud.project.column.project.tax_pph.label'),
            'name' => 'tax_pph',
            'type'  => 'closure',
            'function' => function ($entry) {
                $val = number_format($entry->tax_pph, 2, ',', '.');
                return str_replace(',00', '', $val) . '%';
            }
        ]);
        CRUD::column([
            'label' => trans('backpack::crud.project.column.project.fine_price.label'),
            'name' => 'fine_price',
            'type'  => 'closure',
            'function' => function ($entry) use ($status_file) {
                return $this->priceFormatExport($status_file, $entry->fine_price);
            },
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
        ]);
        CRUD::column([
            'label' => trans('backpack::crud.project.column.project.company_classification.label'),
            'name' => 'company_classification',
            'type'  => 'text',
        ]);
        CRUD::column([
            'label' => trans('backpack::crud.project.column.project.received_po_date.label'),
            'name' => 'received_po_date',
            'type'  => 'date',
            'format' => $new_format_date
        ]);
        $date_format = ($status_file == 'excel') ? 'DD/MM/YYYY' : 'D MMM Y';
        CRUD::column([
            'label' => trans('backpack::crud.project.column.project.po_date.label'),
            'name' => 'po_date',
            'type'  => 'date',
            'format' => $new_format_date
        ]);
        CRUD::column(
            [
                'label'  => trans('backpack::crud.client_po.column.startdate_and_enddate'),
                'name' => 'start_date,end_date',
                'type'  => 'date_range_custom',
                'format' => $new_format_date
            ],
        );
        CRUD::column(
            [
                'label' => trans('backpack::crud.project.column.project.duration.label'),
                'name' => 'duration',
                'type'  => 'closure',
                'value' => function ($row) {
                    $total_day = $this->hitungDurasiHari($row->actual_start_date, $row->actual_end_date);
                    $day = ($row->actual_end_date) ? $total_day : '0';
                    return $day . ' Hari';
                }
            ],
        );
        CRUD::column([
            'label' => trans('backpack::crud.project.column.project.actual_start_date.label'),
            'name' => 'actual_start_date',
            'type'  => 'date',
            'format' => $new_format_date
        ]);
        CRUD::column([
            'label' => trans('backpack::crud.project.column.project.actual_end_date.label'),
            'name' => 'actual_end_date',
            'type'  => 'date',
            'format' => $new_format_date
        ]);
        CRUD::column(
            [
                'label' => trans('backpack::crud.project.column.project.status_po.label'),
                'name' => 'status_po',
                'type'  => 'text'
            ],
        );
        CRUD::column(
            [
                'label' => trans('backpack::crud.project.column.project.progress.label'),
                'name' => 'progress',
                'type'  => 'closure',
                'function' => function ($entry) {
                    $val = number_format($entry->progress, 2, ',', '.');
                    return str_replace(',00', '', $val);
                }
            ],
        );
        CRUD::column(
            [
                'label' => trans('backpack::crud.project.column.project.pic.label'),
                'name' => 'pic',
                'type'  => 'text'
            ],
        );
        CRUD::column(
            [
                'label' => trans('backpack::crud.project.column.project.user.label'),
                'name' => 'user',
                'type'  => 'text'
            ],
        );
        CRUD::column(
            [
                'label' => trans('backpack::crud.project.column.project.category.label'),
                'name' => 'category',
                'type'  => 'text'
            ],
        );
        CRUD::column(
            [
                'label' => trans('backpack::crud.project.column.project.information.label'),
                'name' => 'information',
                'type'  => 'wrap_text'
            ],
        );

        CRUD::column([
            'label'  => 'Dokumen Proyek',
            'name' => 'document_path',
            'type'  => 'text',
            'wrapper'   => [
                'element' => 'a', // the element will default to "a" so you can skip it here
                'href' => function ($crud, $column, $entry, $related_key) {
                    if ($entry->document_path != '') {
                        return url('storage/' . $entry->document_path);
                    }
                    return "javascript:void(0)";
                },
                'target' => '_blank',
                // 'class' => 'some-class',
            ],
        ]);
    }

    protected function setupCreateOperation()
    {

        $user = backpack_user();
        $permissions = $user->getAllPermissions();
        $total_permission = $permissions->whereIn('name', [
            'EDIT KOLOM PROGRES DAN KETERANGAN DAFTAR PROJECT'
        ])->count();
        if ($total_permission) {
            CRUD::setValidation([
                'progress' => 'nullable|numeric',
                'information' => 'nullable',
            ]);
            $edit_column_progress_and_information = true;
        } else {
            CRUD::setValidation([]);
            $edit_column_progress_and_information = false;
        }

        $attributes_added = [];
        if ($edit_column_progress_and_information) {
            $attributes_added = [
                'disabled' => true,
            ];
        }


        $settings = Setting::first();
        $po_prefix_value = [];
        if (!$this->crud->getCurrentEntryId()) {
            $po_prefix_value = [
                'value' => $settings?->po_prefix,
            ];
        }

        CRUD::addField([
            'name' => 'name',
            'label' => trans('backpack::crud.project.field.name.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-12',
            ],
            'attributes' => [
                ...$attributes_added,
                'placeholder' => trans('backpack::crud.project.field.name.placeholder'),
            ]
        ]);

        CRUD::field([
            'name'  => 'po_status',
            'label' => trans('backpack::crud.project.field.po_status.label'),
            'type'  => 'checkbox',
            'attributes' => [
                ...$attributes_added,
                'name' => 'po_status_check',
            ]
        ]);

        CRUD::addField([
            'name' => 'no_po_spk',
            'label' => trans('backpack::crud.project.field.no_po_spk.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6 no_po_spk',
            ],
            'attributes' => [
                ...$attributes_added,
                'placeholder' => trans('backpack::crud.project.field.no_po_spk.placeholder'),
            ],
            ...$po_prefix_value,
        ]);

        CRUD::addField([   // date_picker
            'name'  => 'po_date',
            'type'  => 'date_picker',
            'label' => trans('backpack::crud.project.field.po_date.label'),

            // optional:
            'date_picker_options' => [
                'language' => App::getLocale(),
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6 po_date'
            ],
            'attributes' =>  [
                ...$attributes_added,
            ]
        ]);

        CRUD::addField([   // date_picker
            'name'  => 'received_po_date',
            'type'  => 'date_picker',
            'label' => trans('backpack::crud.project.field.received_po_date.label'),

            // optional:
            'date_picker_options' => [
                'language' => App::getLocale(),
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6 received_po_date'
            ],
            'attributes' =>  [
                ...$attributes_added,
            ]
        ]);

        CRUD::addField([
            'label' => '',
            'name' => 'space',
            'type' => 'hidden',
            'wrapper'   => [
                'class' => 'form-group col-md-6 space'
            ],
        ]);

        CRUD::addField([
            'name' => 'price_total_exclude_ppn',
            'label' =>  trans('backpack::crud.project.field.price_total_exclude_ppn.label'),
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
                ...$attributes_added,
            ]
        ]);

        $tarif_ppn = SetupPpn::pluck('name', 'name');

        $tax_ppn_option = [
            '' => trans('backpack::crud.project.field.tax_ppn.placeholder'),
            ...$tarif_ppn->map(function ($value, $key) {
                $name = number_format($value, 0, '.', ',') . ' %';
                return $name;
            }),
        ];


        CRUD::addField([  // Select2
            'label'     => trans('backpack::crud.project.field.tax_ppn.label'),
            'type'      => 'select2_array',
            'name'      => 'tax_ppn',
            'options'   => $tax_ppn_option, // force the related options to be a custom query, instead of all(); you can use this to filter the results show in the select
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ],
            'attributes' => [
                ...$attributes_added,
            ]
        ]);

        CRUD::addField([
            'name' => 'price_ppn',
            'label' =>  trans('backpack::crud.project.field.price_ppn.label'),
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
                'disabled' => true,
                'placeholder' => '000.000',
            ]
        ]);

        CRUD::addField([
            'name' => 'price_total_include_ppn',
            'label' =>  trans('backpack::crud.project.field.price_total_include_ppn.label'),
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
                'disabled' => true,
                'placeholder' => '000.000',
            ]
        ]);

        $tarif_pph = SetupPph::pluck('name', 'name');
        $tax_pph_option = [
            '' => trans('backpack::crud.project.field.tax_pph.placeholder'),
            ...$tarif_pph->map(function ($value, $key) {
                return number_format($value, 0, '.', ',') . ' %';
            }),
        ];

        CRUD::addField([
            'label'     => trans('backpack::crud.project.field.tax_pph.label'),
            'type'      => 'select2_array',
            'name'      => 'tax_pph',
            'options'   => $tax_pph_option,
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ],
            'attributes' => [
                ...$attributes_added,
            ]
        ]);

        CRUD::addField([
            'name' => 'price_pph',
            'label' =>  trans('backpack::crud.project.field.price_pph.label'),
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
                'disabled' => true,
                'placeholder' => '000.000',
            ]
        ]);

        CRUD::addField([
            'name' => 'fine_price',
            'label' =>  trans('backpack::crud.project.field.fine_price.label'),
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
                ...$attributes_added,
            ]
        ]);

        CRUD::field([   // date_range
            'name'  => 'start_date,end_date', // db columns for start_date & end_date
            'label' => trans('backpack::crud.project.field.start_date.label'),
            'type'  => 'date_range',

            'date_range_options' => [
                'drops' => 'down', // can be one of [down/up/auto]
                // 'locale' => ['format' => 'DD/MM/YYYY']
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                ...$attributes_added,
                'placeholder' => trans('backpack::crud.client_po.field.startdate_and_enddate.placeholder'),
            ]
        ]);

        CRUD::addField([
            'name' => 'duration',
            'label' => trans('backpack::crud.project.field.duration.label'),
            'type' => 'number',
            // optionals
            'attributes' => [
                "step" => "any",
                'disabled' => true,
            ], // allow decimals
            // 'suffix'     => ".00",
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::addField([   // date_picker
            'name'  => 'actual_start_date',
            'type'  => 'date_picker',
            'label' => trans('backpack::crud.project.field.actual_start_date.label'),

            // optional:
            'date_picker_options' => [
                'language' => App::getLocale(),
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
            'attributes' => [
                ...$attributes_added,
            ]
        ]);

        CRUD::addField([   // date_picker
            'name'  => 'actual_end_date',
            'type'  => 'date_picker',
            'label' => trans('backpack::crud.project.field.actual_end_date.label'),

            // optional:
            'date_picker_options' => [
                'language' => App::getLocale(),
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
            'attributes' => [
                ...$attributes_added,
            ]
        ]);

        $status_po = SetupStatusProject::pluck('name', 'name');

        $status_po_option = [
            '' => trans('backpack::crud.project.field.status_po.placeholder'),
            ...$status_po->map(function ($value, $key) {
                $name = $value;
                return $name;
            }),
        ];

        CRUD::addField([  // Select2
            'label'     => trans('backpack::crud.project.field.status_po.label'),
            'type'      => 'select2_array',
            'name'      => 'status_po',
            'options'   => $status_po_option, // force the related options to be a custom query, instead of all(); you can use this to filter the results show in the select
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ],
            'attributes' => [
                ...$attributes_added,
            ]
        ]);

        $client = SetupClient::all();
        $client_option = [
            '' => trans('backpack::crud.project.field.client_id.placeholder'),
        ];

        foreach ($client as $c) {
            $client_option[$c->id] = $c->name;
        }


        CRUD::addField([  // Select2
            'label'     => trans('backpack::crud.project.column.project.client_id.label'),
            'type'      => 'select2_array',
            'name'      => 'client_id',
            'options'   => $client_option, // force the related options to be a custom query, instead of all(); you can use this to filter the results show in the select
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ],
            'attributes' => [
                ...$attributes_added,
            ]
        ]);

        $category = CategoryProject::pluck('name', 'name');
        $category_option = [
            '' => trans('backpack::crud.project.field.category.placeholder'),
            ...$category->map(function ($value, $key) {
                $name = $value;
                return $name;
            }),
        ];

        CRUD::addField([  // Select2
            'label'     => trans('backpack::crud.project.field.category.label'),
            'type'      => 'select2_array',
            'name'      => 'category',
            'options'   => $category_option, // force the related options to be a custom query, instead of all(); you can use this to filter the results show in the select
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ],
            'attributes' => [
                ...$attributes_added,
            ]
        ]);

        $company_classification = SetupCompanyClassification::pluck('name', 'name');
        $company_classification_option = [
            '' => trans('backpack::crud.project.field.company_classification.placeholder'),
            ...$company_classification->map(function ($value, $key) {
                return $value;
            }),
        ];

        CRUD::addField([  // Select2
            'label'     => trans('backpack::crud.project.field.company_classification.label'),
            'type'      => 'select2_array',
            'name'      => 'company_classification',
            'options'   => $company_classification_option,
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ],
            'attributes' => [
                ...$attributes_added,
            ]
        ]);

        CRUD::addField([
            'name' => 'progress',
            'label' => trans('backpack::crud.project.field.progress.label'),
            'type' => 'number',
            // optionals
            'attributes' => ["step" => "any"], // allow decimals
            'prefix'     => "%",
            // 'suffix'     => ".00",
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::addField([
            'name' => 'pic',
            'label' => trans('backpack::crud.project.field.pic.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                ...$attributes_added,
                'placeholder' => trans('backpack::crud.project.field.pic.placeholder'),
            ]
        ]);

        CRUD::addField([
            'name' => 'user',
            'label' => trans('backpack::crud.project.field.user.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                ...$attributes_added,
                'placeholder' => trans('backpack::crud.project.field.user.placeholder'),
            ]
        ]);

        CRUD::addField([
            'name' => 'information',
            'label' => trans('backpack::crud.project.field.information.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-6',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.project.field.information.placeholder'),
            ]
        ]);

        CRUD::addField([
            'name' => 'document_path',
            'label' => 'Dokumen Proyek',
            'type' => 'upload',
            'wrapper'   => [
                'class' => 'form-group col-md-6'
            ],
            'attributes' => [
                ...$attributes_added,
            ],
            'withFiles' => [
                'disk' => 'public',
                'path' => 'document_proyek',
                'deleteWhenEntryIsDeleted' => true,
            ],
        ]);

        CRUD::addField([
            'name' => 'logic_project',
            'type' => 'logic_project',
        ]);
    }

    protected function setupShowOperation()
    {
        $this->setupCreateOperation();

        // Add Transfer Value field for display
        CRUD::field([
            'name' => 'transfer_value',
            'label' => trans('backpack::crud.project.column.project.transfer_value.label'),
            'type' => 'text'
        ])->after('price_total_include_ppn');

        CRUD::field('received_po_date')->remove();
        CRUD::field([   // date_picker
            'name'  => 'received_po_date',
            'type'  => 'date_picker',
            'label' => trans('backpack::crud.project.field.received_po_date.label'),

            // optional:
            'date_picker_options' => [
                'language' => App::getLocale(),
            ],
            'wrapper'   => [
                'class' => 'form-group col-md-12 received_po_date'
            ],
        ])->after('po_date');
        CRUD::field('space')->remove();
        CRUD::field('logic_project')->remove();
        CRUD::field('po_status')->remove();

        // Columns must follow the exact order of fields to align with custom show.blade.php
        $status_file = 'pdf';

        // 1. name
        CRUD::column(['label' => '', 'name' => 'name', 'type' => 'wrap_text']);
        // 2. no_po_spk
        CRUD::column(['label' => '', 'name' => 'no_po_spk', 'type' => 'wrap_text']);
        // 3. po_date
        CRUD::column(['label' => '', 'name' => 'po_date', 'type' => 'date', 'format' => 'D MMM Y']);
        // 4. received_po_date
        CRUD::column(['label' => '', 'name' => 'received_po_date', 'type' => 'date', 'format' => 'D MMM Y']);
        // 5. price_total_exclude_ppn
        CRUD::column([
            'label'  => '',
            'name' => 'price_total_exclude_ppn',
            'type'  => 'number',
            'prefix' => "Rp.",
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
        ]);
        // 6. tax_ppn
        CRUD::column([
            'label'  => trans('backpack::crud.client_po.column.tax_ppn'),
            'name' => 'tax_ppn',
            'type'  => 'closure',
            'function' => function ($entry) {
                $val = number_format($entry->tax_ppn, 2, ',', '.');
                return str_replace(',00', '', $val) . '%';
            }
        ]);
        // 7. price_ppn
        CRUD::column([
            'label'  => '',
            'name' => 'price_ppn',
            'type'  => 'number',
            'prefix' => "Rp.",
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
        ]);
        // 8. price_total_include_ppn
        CRUD::column([
            'label'  => '',
            'name' => 'price_total_include_ppn',
            'type'  => 'number',
            'prefix' => "Rp.",
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
        ]);
        // 9. transfer_value (Calculated)
        CRUD::column([
            'label' => '',
            'name' => 'transfer_value',
            'type'  => 'closure',
            'function' => function ($entry) use ($status_file) {
                $price_pph = $entry->price_pph ?? 0;
                $price_fine = $entry->fine_price ?? 0;
                if ($entry->company_classification == 'WAPU') {
                    $transfer_value = $entry->price_total_exclude_ppn - $price_pph - $price_fine;
                } else if ($entry->company_classification == 'NON WAPU') {
                    $transfer_value = $entry->price_total_include_ppn - $price_pph - $price_fine;
                } else {
                    return '-';
                }
                return $this->priceFormatExport($status_file, $transfer_value);
            },
        ]);
        // 10. tax_pph
        CRUD::column([
            'label'  => '',
            'name' => 'tax_pph',
            'type'  => 'closure',
            'function' => function ($entry) {
                $val = number_format($entry->tax_pph, 2, ',', '.');
                return str_replace(',00', '', $val) . '%';
            }
        ]);
        // 11. price_pph
        CRUD::column([
            'label'  => '',
            'name' => 'price_pph',
            'type'  => 'number',
            'prefix' => "Rp.",
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
        ]);
        // 12. fine_price
        CRUD::column([
            'label'  => '',
            'name' => 'fine_price',
            'type'  => 'number',
            'prefix' => "Rp.",
            'decimals'      => 2,
            'dec_point'     => ',',
            'thousands_sep' => '.',
        ]);
        // 13. start_date,end_date
        CRUD::column([
            'label'  => trans('backpack::crud.client_po.column.startdate_and_enddate'),
            'name' => 'start_date,end_date',
            'type'  => 'date_range_custom'
        ]);
        // 14. duration
        CRUD::column(['label'  => '', 'name' => 'duration', 'type'  => 'text']);
        // 15. actual_start_date
        CRUD::column(['label'  => '', 'name' => 'actual_start_date', 'type'  => 'date', 'format' => 'D MMM Y']);
        // 16. actual_end_date
        CRUD::column(['label'  => '', 'name' => 'actual_end_date', 'type'  => 'date', 'format' => 'D MMM Y']);
        // 17. status_po
        CRUD::column(['label'  => '', 'name' => 'status_po', 'type'  => 'text']);
        // 18. client_id
        CRUD::column([
            'label' => trans('backpack::crud.client_po.column.client_id'),
            'type'      => 'select',
            'name'      => 'client_id',
            'entity'    => 'setup_client',
            'attribute' => 'name',
            'model'     => "App\Models\SetupClient",
        ]);
        // 19. category
        CRUD::column(['label'  => '', 'name' => 'category', 'type'  => 'text']);
        // 20. company_classification
        CRUD::column(['label'  => '', 'name' => 'company_classification', 'type'  => 'text']);
        // 21. progress
        CRUD::column([
            'label'  => '',
            'name' => 'progress',
            'type'  => 'closure',
            'function' => function ($entry) {
                $val = number_format($entry->progress, 2, ',', '.');
                return str_replace(',00', '', $val);
            }
        ]);
        // 22. pic
        CRUD::column(['label'  => '', 'name' => 'pic', 'type'  => 'text']);
        // 23. user
        CRUD::column(['label'  => '', 'name' => 'user', 'type'  => 'text']);
        // 24. information
        CRUD::column(['label'  => '', 'name' => 'information', 'type'  => 'wrap_text']);
        // 25. document_path
        CRUD::column([
            'label'  => 'Dokumen Proyek',
            'name' => 'document_path',
            'type'  => 'text',
            'wrapper'   => [
                'element' => 'a',
                'href' => function ($crud, $column, $entry, $related_key) {
                    if ($entry->document_path != '') {
                        return url('storage/document_proyek/' . $entry->document_path);
                    }
                    return "javascript:void(0)";
                },
                'target' => '_blank',
            ],
        ]);
    }

    public function show($id)
    {
        $this->crud->hasAccessOrFail('show');

        $id = $this->crud->getCurrentEntryId() ?? $id;

        if ($this->crud->get('show.softDeletes') && in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($this->crud->model))) {
            $this->data['entry'] = $this->crud->getModel()->withTrashed()->findOrFail($id);
        } else {
            $this->data['entry'] = $this->crud->getEntryWithLocale($id);
        }

        $this->data['entry_value'] = $this->crud->getRowViews($this->data['entry']);
        $this->data['crud'] = $this->crud;

        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.preview') . ' ' . $this->crud->entity_name;

        return response()->json([
            'html' => view($this->crud->getShowView(), $this->data)->render()
        ]);
    }

    public function exportPdf()
    {
        $type = request()->type;

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

        $title = 'Project Report';

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
        $type = request()->type;

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

        $name = 'Status Project - ' . $type;

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
