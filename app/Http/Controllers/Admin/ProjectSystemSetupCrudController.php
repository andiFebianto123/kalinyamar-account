<?php
namespace App\Http\Controllers\Admin;

use App\Models\SetupPpn;
use App\Models\SetupClient;
use App\Models\SetupOffering;
use App\Models\CategoryProject;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Exports\ExportExcel;
use App\Models\SetupStatusProject;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\CrudController;
use PhpOffice\PhpSpreadsheet\Calculation\Category;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class ProjectSystemSetupCrudController extends CrudController{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        $this->crud->denyAllAccess(['create', 'update', 'delete', 'list', 'show']);
        $this->crud->setModel(CategoryProject::class);
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/monitoring/project-system-setup');
        $this->crud->setEntityNameStrings(trans('backpack::crud.menu.project_system_setup'), trans('backpack::crud.menu.project_system_setup'));
        $user = backpack_user();
        $permissions = $user->getAllPermissions();
        if($permissions->whereIn('name', [
            'AKSES SEMUA VIEW PROJECT',
            'AKSES SEMUA MENU PROJECT',
        ])->count() > 0)
        {
            $this->crud->allowAccess(['list', 'show']);
        }

        if($permissions->whereIn('name',[
            'AKSES SEMUA MENU PROJECT',
        ])->count() > 0){
            $this->crud->allowAccess(['create', 'update', 'delete']);
        }
    }

    function listCardComponents(){
        $this->card->addCard([
            'name' => 'setup_category_project',
            'line' => 'top',
            'view' => 'crud::components.card-project',
            'params' => [
                'title' => trans('backpack::crud.project_system_setup.card.setup_category_project'),
                'title_create' => trans('backpack::crud.project_system_setup.card.setup_category_project_title_create'),
                'crud' => $this->crud,
                'route' => url($this->crud->route.'/search?_type=category_project'),
                'route_create' => url($this->crud->route.'/create?_type=category_project'),
                'route_store' => url($this->crud->route.'?_type=category_project'),
            ]
        ]);

        $this->card->addCard([
            'name' => 'setup_status_project',
            'line' => 'top',
            'view' => 'crud::components.card-project',
            'params' => [
                'title' => trans('backpack::crud.project_system_setup.card.setup_status_project'),
                'title_create' => trans('backpack::crud.project_system_setup.card.setup_status_project_title_create'),
                'crud' => $this->crud,
                'route' => url($this->crud->route.'/search?_type=status_project'),
                'route_create' => url($this->crud->route.'/create?_type=status_project'),
                'route_store' => url($this->crud->route.'?_type=status_project'),
            ]
        ]);

        $this->card->addCard([
            'name' => 'setup_status_offering',
            'line' => 'top',
            'view' => 'crud::components.card-project',
            'params' => [
                'title' => trans('backpack::crud.project_system_setup.card.setup_status_offering'),
                'title_create' => trans('backpack::crud.project_system_setup.card.setup_status_offering_title_create'),
                'crud' => $this->crud,
                'route' => url($this->crud->route.'/search?_type=status_offering'),
                'route_create' => url($this->crud->route.'/create?_type=status_offering'),
                'route_store' => url($this->crud->route.'?_type=status_offering'),
            ]
        ]);

        $this->card->addCard([
            'name' => 'setup_client',
            'line' => 'top',
            'view' => 'crud::components.card-project',
            'params' => [
                'title' => trans('backpack::crud.project_system_setup.card.setup_client'),
                'title_create' => trans('backpack::crud.project_system_setup.card.setup_client_title_create'),
                'crud' => $this->crud,
                'route' => url($this->crud->route.'/search?_type=client'),
                'route_create' => url($this->crud->route.'/create?_type=client'),
                'route_store' => url($this->crud->route.'?_type=client'),
            ]
        ]);

        $this->card->addCard([
            'name' => 'setup_ppn',
            'line' => 'top',
            'view' => 'crud::components.card-project',
            'params' => [
                'title' => trans('backpack::crud.project_system_setup.card.setup_ppn'),
                'title_create' => trans('backpack::crud.project_system_setup.card.setup_ppn_title_create'),
                'crud' => $this->crud,
                'route' => url($this->crud->route.'/search?_type=ppn'),
                'route_create' => url($this->crud->route.'/create?_type=ppn'),
                'route_store' => url($this->crud->route.'?_type=ppn'),
            ]
        ]);

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

    function fieldCreatesetupCategory(){

        $id = request()->id;

        $rule = [
            'name' => [
                'required',
                function($attribute, $value, $fail) use($id){
                    $val = explode(',', $value);
                    foreach($val as $v){
                        if(strlen(trim($v)) == 0){
                            $fail(trans('backpack::crud.project_system_setup.field.setup_category_project.name.errors.empty'));
                            return;
                        }else{
                            $data = CategoryProject::where('name', $v)->first();
                            if($data){
                                $fail(trans('backpack::crud.project_system_setup.field.setup_category_project.name.errors.unique'));
                                return;
                            }
                        }
                    }
                }
            ],
        ];

        if(request()->has('edit')){
            $rule = [
                'name' => 'required|max:30|unique:cateogry_projects,name,'.$id
            ];
        }

        CRUD::setValidation($rule);

        CRUD::addField([
            'name' => 'title',
            'label' => trans('backpack::crud.project_system_setup.field.setup_category_project.title.label'),
            'type' => 'title-project',
            'wrapper'   => [
                'class' => 'form-group col-md-12',
            ],
            'attributes' => [
                'class' => 'd-none',
            ]
        ]);

        CRUD::addField([
            'name' => 'name',
            'label' => trans('backpack::crud.project_system_setup.field.setup_category_project.name.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-12',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.project_system_setup.field.setup_category_project.name.placeholder'),
            ]
        ]);

        if(!request()->has('edit')){
            CRUD::addField([
                'name' => 'comma',
                'label' => trans('backpack::crud.project_system_setup.field.setup_category_project.comma.label'),
                'type' => 'comma-statement-project',
                'wrapper'   => [
                    'class' => 'form-group col-md-12',
                ],
                'attributes' => [
                    'class' => 'd-none',
                ]
            ]);
        }


    }

    function fieldCreatestatusCategory(){

        $id = request()->id;

        $rule = [
            'name' => [
                'required',
                function($attribute, $value, $fail) use($id){
                    $val = explode(',', $value);
                    foreach($val as $v){
                        if(strlen(trim($v)) == 0){
                            $fail(trans('backpack::crud.project_system_setup.field.setup_category_project.name.errors.empty'));
                            return;
                        }else{
                            $data = SetupStatusProject::where('name', $v)->first();
                            if($data){
                                $fail(trans('backpack::crud.project_system_setup.field.setup_category_project.name.errors.unique'));
                                return;
                            }
                        }
                    }
                }
            ]
        ];

        if(request()->has('edit')){
            $rule = [
                'name' => 'required|max:30|unique:setup_status_projects,name,'.$id
            ];
        }

        CRUD::setValidation($rule);

        CRUD::addField([
            'name' => 'title',
            'label' => trans('backpack::crud.project_system_setup.field.setup_status_project.title.label'),
            'type' => 'title-project',
            'wrapper'   => [
                'class' => 'form-group col-md-12',
            ],
            'attributes' => [
                'class' => 'd-none',
            ]
        ]);

        CRUD::addField([
            'name' => 'name',
            'label' => trans('backpack::crud.project_system_setup.field.setup_status_project.name.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-12',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.project_system_setup.field.setup_status_project.name.placeholder'),
            ]
        ]);

        if(!request()->has('edit')){
            CRUD::addField([
                'name' => 'comma',
                'label' => trans('backpack::crud.project_system_setup.field.setup_status_project.comma.label'),
                'type' => 'comma-statement-project',
                'wrapper'   => [
                    'class' => 'form-group col-md-12',
                ],
                'attributes' => [
                    'class' => 'd-none',
                ]
            ]);
        }



    }

    function fieldCreatestatusOffering(){
        $id = request()->id;

        $rule = [
            'name' => [
                'required',
                function($attribute, $value, $fail) use($id){
                    $val = explode(',', $value);
                    foreach($val as $v){
                        if(strlen(trim($v)) == 0){
                            $fail(trans('backpack::crud.project_system_setup.field.setup_category_project.name.errors.empty'));
                            return;
                        }else{
                            $data = SetupOffering::where('name', $v)->first();
                            if($data){
                                $fail(trans('backpack::crud.project_system_setup.field.setup_category_project.name.errors.unique'));
                                return;
                            }
                        }
                    }
                }
            ]
        ];

        if(request()->has('edit')){
            $rule = [
                'name' => 'required|max:30|unique:setup_offering,name,'.$id
            ];
        }

        CRUD::setValidation($rule);

        CRUD::addField([
            'name' => 'title',
            'label' => trans('backpack::crud.project_system_setup.field.setup_status_offering.title.label'),
            'type' => 'title-project',
            'wrapper'   => [
                'class' => 'form-group col-md-12',
            ],
            'attributes' => [
                'class' => 'd-none',
            ]
        ]);

        CRUD::addField([
            'name' => 'name',
            'label' => trans('backpack::crud.project_system_setup.field.setup_status_offering.name.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-12',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.project_system_setup.field.setup_status_offering.name.placeholder'),
            ]
        ]);

        if(!request()->has('edit')){
            CRUD::addField([
                'name' => 'comma',
                'label' => trans('backpack::crud.project_system_setup.field.setup_status_offering.comma.label'),
                'type' => 'comma-statement-project',
                'wrapper'   => [
                    'class' => 'form-group col-md-12',
                ],
                'attributes' => [
                    'class' => 'd-none',
                ]
            ]);
        }


    }

    function fieldCreateClient(){
        $id = request()->id;

        $rule = [
            'name' => [
                'required',
                function($attribute, $value, $fail) use($id){
                    $val = explode(',', $value);
                    foreach($val as $v){
                        if(strlen(trim($v)) == 0){
                            $fail(trans('backpack::crud.project_system_setup.field.setup_category_project.name.errors.empty'));
                            return;
                        }else{
                            $data = SetupClient::where('name', $v)->first();
                            if($data){
                                $fail(trans('backpack::crud.project_system_setup.field.setup_category_project.name.errors.unique'));
                                return;
                            }
                        }
                    }
                }
            ]
        ];

        if(request()->has('edit')){
            $rule = [
                'name' => 'required|max:30|unique:setup_clients,name,'.$id
            ];
        }

        CRUD::setValidation($rule);

        CRUD::addField([
            'name' => 'title',
            'label' => trans('backpack::crud.project_system_setup.field.setup_client.title.label'),
            'type' => 'title-project',
            'wrapper'   => [
                'class' => 'form-group col-md-12',
            ],
            'attributes' => [
                'class' => 'd-none',
            ]
        ]);

        CRUD::addField([
            'name' => 'name',
            'label' => trans('backpack::crud.project_system_setup.field.setup_client.name.label'),
            'type' => 'text',
            'wrapper'   => [
                'class' => 'form-group col-md-12',
            ],
            'attributes' => [
                'placeholder' => trans('backpack::crud.project_system_setup.field.setup_client.name.placeholder'),
            ]
        ]);

        if(!request()->has('edit')){
            CRUD::addField([
                'name' => 'comma',
                'label' => trans('backpack::crud.project_system_setup.field.setup_client.comma.label'),
                'type' => 'comma-statement-project',
                'wrapper'   => [
                    'class' => 'form-group col-md-12',
                ],
                'attributes' => [
                    'class' => 'd-none',
                ]
            ]);
        }
    }

    function fieldCreatePpn(){
        $id = request()->id;

        $rule = [
            'name' => [
                'required',
                function($attribute, $value, $fail) use($id){
                    $val = explode(',', $value);
                    foreach($val as $v){
                        if(strlen(trim($v)) == 0){
                            $fail(trans('backpack::crud.project_system_setup.field.setup_category_project.name.errors.empty'));
                            return;
                        }else{
                            $data = SetupPpn::where('name', $v)->first();
                            if($data){
                                $fail(trans('backpack::crud.project_system_setup.field.setup_category_project.name.errors.unique'));
                                return;
                            }
                        }
                    }
                }
            ],
        ];

        if(request()->has('edit')){
            $rule = [
                'name' => 'required|numeric|unique:setup_ppn,name,'.$id
            ];
        }

        CRUD::setValidation($rule);

        CRUD::addField([
            'name' => 'title',
            'label' => trans('backpack::crud.project_system_setup.field.setup_ppn.title.label'),
            'type' => 'title-project',
            'wrapper'   => [
                'class' => 'form-group col-md-12',
            ],
            'attributes' => [
                'class' => 'd-none',
            ]
        ]);

        CRUD::addField([
            'name' => 'name',
            'label' => trans('backpack::crud.project_system_setup.field.setup_ppn.name.label'),
            'type' => 'text',
            'attributes' => ["step" => "any"], // allow decimals
            'prefix'     => "%",
            'wrapper'   => [
                'class' => 'form-group col-md-12',
            ],
            'attributes' => [
                'placeholder' => '000.000',
            ]
        ]);

        if(!request()->has('edit')){
            CRUD::addField([
                'name' => 'comma',
                'label' => trans('backpack::crud.project_system_setup.field.setup_ppn.comma.label'),
                'type' => 'comma-statement-project',
                'wrapper'   => [
                    'class' => 'form-group col-md-12',
                ],
                'attributes' => [
                    'class' => 'd-none',
                ]
            ]);
        }

    }


    protected function setupCreateOperation(){
        $request = request();
        if($request->_type == 'category_project'){
            $this->fieldCreatesetupCategory();
        }else if($request->_type == 'status_project'){
            $this->fieldCreatestatusCategory();
        }else if($request->_type == 'status_offering'){
            $this->fieldCreatestatusOffering();
        }else if($request->_type == 'client'){
            $this->fieldCreateClient();
        }else if($request->_type == 'ppn'){
            $this->fieldCreatePpn();
        }
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    public function index()
    {
        $this->crud->hasAccessOrFail('list');

        CRUD::removeButtons(['create'], 'top');

        $this->listCardComponents();

        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? mb_ucfirst($this->crud->entity_name_plural);
        $this->data['title_modal_create'] = trans('backpack::crud.invoice_client.title_modal_create');
        $this->data['title_modal_edit'] = trans('backpack::crud.invoice_client.title_modal_edit');
        $this->data['title_modal_delete'] = trans('backpack::crud.invoice_client.title_modal_delete');

        $breadcrumbs = [
            trans('backpack::crud.menu.monitoring_project') => backpack_url('monitoring'),
            trans('backpack::crud.menu.project_system_setup') => backpack_url($this->crud->route)
        ];
        $this->data['breadcrumbs'] = $breadcrumbs;
        $this->data['cards'] = $this->card;
        $this->data['modals'] = $this->modal;
        $this->data['scripts'] = $this->script;

        $list = "crud::list-blank" ?? $this->crud->getListView();
        return view($list, $this->data);
    }

    public function edit($id)
    {
        $this->crud->hasAccessOrFail('update');

        $id = $this->crud->getCurrentEntryId() ?? $id;

        $this->crud->registerFieldEvents();

        $request = request();
        $type = $request->_type;
        if($type == 'category_project'){
            $item = CategoryProject::find($id);
        }else if($type == 'status_project'){
            CRUD::setModel(SetupStatusProject::class);
            $item = SetupStatusProject::find($id);
        }else if($type == 'status_offering'){
            CRUD::setModel(SetupOffering::class);
            $item = SetupOffering::find($id);
        }else if($type == 'client'){
            CRUD::setModel(SetupClient::class);
            $item = SetupClient::find($id);
        }else if($type == 'ppn'){
            CRUD::setModel(SetupPpn::class);
            $item = SetupPpn::find($id);
        }

        $this->data['entry'] = $item;
        $this->crud->setOperationSetting('fields', $this->crud->getUpdateFields());
        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.edit').' '.$this->crud->entity_name;
        $this->data['id'] = $id;

        return response()->json([
            'html' => view($this->crud->getEditView(), $this->data)->render()
        ]);
    }

    protected function setupListOperation()
    {
        CRUD::removeButtons(['show', 'delete', 'update'], 'line');
        $type = request()->_type;
        $this->crud->file_title_export_pdf = "Laporan_daftar_project_setup.pdf";
        $this->crud->file_title_export_excel = "Laporan_daftar_project_setup.xlsx";
        $this->crud->param_uri_export = "?export=1";

        CRUD::addButtonFromView('top', 'export-excel-table', 'export-excel-table', 'beginning');
        CRUD::addButtonFromView('top', 'export-pdf-table', 'export-pdf-table', 'beginning');

        if($type == 'category_project'){
            CRUD::setModel(CategoryProject::class);
            CRUD::addButtonFromView('line', 'update-project', 'update-project', 'end');
            CRUD::addButtonFromView('line', 'delete-project', 'delete-project', 'end');
            CRUD::column([
                'label'  => '',
                'name' => 'name',
                'type'  => 'custom_html',
                'value' => function($entry){
                    return "&bull; ".$entry->name;
                }
            ]);
            CRUD::addClause('select', DB::raw("
                cateogry_projects.*,
                'category_project' as type
            "));
        }else if($type == 'status_project'){
            CRUD::setModel(SetupStatusProject::class);
            CRUD::addButtonFromView('line', 'update-project', 'update-project', 'end');
            CRUD::addButtonFromView('line', 'delete-project', 'delete-project', 'end');
            CRUD::column([
                'label'  => '',
                'name' => 'name',
                'type'  => 'custom_html',
                'value' => function($entry){
                    return "&bull; ".$entry->name;
                }
            ]);
            CRUD::addClause('select', DB::raw("
                setup_status_projects.*,
                'status_project' as type
            "));
        }else if($type == 'status_offering'){
            CRUD::setModel(SetupOffering::class);
            CRUD::addButtonFromView('line', 'update-project', 'update-project', 'end');
            CRUD::addButtonFromView('line', 'delete-project', 'delete-project', 'end');
            CRUD::column([
                'label'  => '',
                'name' => 'name',
                'type'  => 'custom_html',
                'value' => function($entry){
                    return "&bull; ".$entry->name;
                }
            ]);
            CRUD::addClause('select', DB::raw("
                setup_offering.*,
                'status_offering' as type
            "));
        }else if($type == 'client'){
            CRUD::setModel(SetupClient::class);
            CRUD::addButtonFromView('line', 'update-project', 'update-project', 'end');
            CRUD::addButtonFromView('line', 'delete-project', 'delete-project', 'end');
            CRUD::column([
                'label'  => '',
                'name' => 'name',
                'type'  => 'custom_html',
                'value' => function($entry){
                    return "&bull; ".$entry->name;
                }
            ]);
            CRUD::addClause('select', DB::raw("
                setup_clients.*,
                'client' as type
            "));
        }else if($type == 'ppn'){
            CRUD::setModel(SetupPpn::class);
            CRUD::addButtonFromView('line', 'update-project', 'update-project', 'end');
            CRUD::addButtonFromView('line', 'delete-project', 'delete-project', 'end');
            CRUD::column([
                'label'  => '',
                'name' => 'name',
                'type'  => 'custom_html',
                'value' => function($entry){
                    return "&bull; ".number_format($entry->name, 0, '.', ',').' %';
                }
            ]);
            CRUD::addClause('select', DB::raw("
                setup_ppn.*,
                'ppn' as type
            "));
        }
    }

    protected function setupListExport()
    {
        // CRUD::removeButtons(['show', 'delete', 'update'], 'line');
        CRUD::setModel(CategoryProject::class);
        $this->crud->query = $this->crud->query
        ->unionAll(
            DB::table('setup_status_projects')
            ->select(DB::raw("
                setup_status_projects.name as name,
                'status_project' as type
            "))
        )
        ->unionAll(
            DB::table('setup_offering')
            ->select(DB::raw("
                setup_offering.name as name,
                'status_offering' as type
            "))
        )
        ->unionAll(
            DB::table('setup_clients')
            ->select(DB::raw("
                setup_clients.name as name,
                'client' as type
            "))
        )
        ->unionAll(
            DB::table('setup_ppn')
            ->select(DB::raw("
                setup_ppn.name as name,
                'ppn' as type
            "))
        );

        CRUD::addClause('select', DB::raw("
            cateogry_projects.name as name,
            'category_project' as type
        "));

        $this->crud->addColumn([
            'name'      => 'row_number',
            'type'      => 'export',
            'label'     => 'No',
            'orderable' => false,
            'wrapper' => [
                'element' => 'strong',
            ]
        ])->makeFirstColumn();

        CRUD::column([
            'label'  => 'Nama Setup',
            'name' => 'name',
            'type'  => 'closure',
            'function' => function($entry){
                if($entry->type == 'ppn'){
                    return number_format($entry->name, 0, '.', ',').' %';
                }
                return $entry->name;
            }
        ]);
        CRUD::column([
            'label'  => 'Kategori',
            'name' => 'type',
            'type'  => 'closure',
            'function' => function($entry){
                if($entry->type == 'category_project'){
                    return "Setup Ketegori Proyek";
                }else if($entry->type == 'status_project'){
                    return "Setup Status Proyek";
                }else if($entry->type == 'status_offering'){
                    return "Setup Status Penawaran";
                }else if($entry->type == 'client'){
                    return "Setup Data Client";
                }else if($entry->type == 'ppn'){
                    return "Setup Tarif PPn";
                }
            }
        ]);
    }

    public function exportPdf(){

        $this->setupListExport();

        $columns = $this->crud->columns();
        $items =  $this->crud->getEntries();

        $row_number = 0;

        $all_items = [];

        foreach($items as $item){
            $row_items = [];
            $row_number++;
            foreach($columns as $column){
                $item_value = ($column['name'] == 'row_number') ? $row_number : $this->crud->getCellView($column, $item, $row_number);
                $item_value = str_replace('<span>', '', $item_value);
                $item_value = str_replace('</span>', '', $item_value);
                $item_value = str_replace("\n", '', $item_value);
                $row_items[] = trim($item_value);
            }
            $all_items[] = $row_items;
        }

        $title = "DAFTAR PROJECT SETUP";

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
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    public function exportExcel(){

        $this->setupListExport();

        $columns = $this->crud->columns();
        $items =  $this->crud->getEntries();

        $row_number = 0;

        $all_items = [];

        foreach($items as $item){
            $row_items = [];
            $row_number++;
            foreach($columns as $column){
                $item_value = ($column['name'] == 'row_number') ? $row_number : $this->crud->getCellView($column, $item, $row_number);
                $item_value = str_replace('<span>', '', $item_value);
                $item_value = str_replace('</span>', '', $item_value);
                $item_value = str_replace("\n", '', $item_value);
                $row_items[] = trim($item_value);
            }
            $all_items[] = $row_items;
        }

        $name = 'DAFTAR SPK';

        return response()->streamDownload(function () use($columns, $items, $all_items){
            echo Excel::raw(new ExportExcel(
                $columns, $all_items), \Maatwebsite\Excel\Excel::XLSX);
        }, $name, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $name . '"',
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Download Failure',
        ], 400);
    }

    public function store()
    {
        $this->crud->hasAccessOrFail('create');

        $request = $this->crud->validateRequest();

        $this->crud->registerFieldEvents();

        DB::beginTransaction();
        try{

            $event = [];

            $type = $request->_type;
            if($type == 'category_project'){
                $event['setup_category_project_create_success'] = true;

                $val = explode(',', $request->name);
                foreach($val as $v){
                    $item = new CategoryProject;
                    $item->name = strtoupper(trim($v));
                    $item->save();
                }
            }else if($type == 'status_project'){
                $event['setup_status_project_create_success'] = true;
                $val = explode(',', $request->name);
                foreach($val as $v){
                    $item = new SetupStatusProject;
                    $item->name = strtoupper(trim($v));
                    $item->save();
                }
            }else if($type == 'status_offering'){
                $event['setup_status_offering_create_success'] = true;
                $val = explode(',', $request->name);
                foreach($val as $v){
                    $item = new SetupOffering;
                    $item->name = strtoupper(trim($v));
                    $item->save();
                }
            }else if($type == 'client'){
                $event['setup_client_create_success'] = true;
                $val = explode(',', $request->name);
                foreach($val as $v){
                    $item = new SetupClient;
                    $item->name = strtoupper(trim($v));
                    $item->save();
                }
            }else if($type == 'ppn'){
                $event['setup_ppn_create_success'] = true;
                $val = explode(',', $request->name);
                foreach($val as $v){
                    $item = new SetupPpn;
                    $item->name = strtoupper(trim($v));
                    $item->save();
                }
            }

            \Alert::success(trans('backpack::crud.insert_success'))->flash();

            $this->crud->setSaveAction();

            DB::commit();
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => true,
                    'events' => $event,
                ]);
            }
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

    public function update()
    {
        $this->crud->hasAccessOrFail('update');

        $request = $this->crud->validateRequest();

        $this->crud->registerFieldEvents();

        DB::beginTransaction();
        try{

            $event = [];

            $type = $request->_type;
            if($type == 'category_project'){
                $event['setup_category_project_create_success'] = true;
                $item = CategoryProject::find($request->id);
                $item->name = $request->name;
                $item->save();
            }else if($type == 'status_project'){
                $event['setup_status_project_create_success'] = true;
                $item = SetupStatusProject::find($request->id);
                $item->name = $request->name;
                $item->save();
            }else if($type == 'status_offering'){
                $event['setup_status_offering_create_success'] = true;
                $item = SetupOffering::find($request->id);
                $item->name = $request->name;
                $item->save();
            }else if($type == 'client'){
                $event['setup_client_create_success'] = true;
                $item = SetupClient::find($request->id);
                $item->name = $request->name;
                $item->save();
            }else if($type == 'ppn'){
                $event['setup_ppn_create_success'] = true;
                $item = SetupPpn::find($request->id);
                $item->name = $request->name;
                $item->save();
            }

            $this->data['entry'] = $item;

            \Alert::success(trans('backpack::crud.update_success'))->flash();

            $this->crud->setSaveAction();

            DB::commit();
            return response()->json([
                'success' => true,
                'data' => $item,
                'events' => $event
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

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $this->crud->hasAccessOrFail('delete');

            $event = [];
            $request = request();

            if($request->type == 'category_project'){
                $item = CategoryProject::find($id);
                $event['setup_category_project_create_success'] = true;
                $item->delete();
            }else if($request->type == 'status_project'){
                $item = SetupStatusProject::find($id);
                $event['setup_status_project_create_success'] = true;
                $item->delete();
            }else if($request->type == 'status_offering'){
                $item = SetupOffering::find($id);
                $event['setup_status_offering_create_success'] = true;
                $item->delete();
            }else if($request->type == 'client'){
                $item = SetupClient::find($id);
                $event['setup_client_create_success'] = true;
                $item->delete();
            }else if($request->type == 'ppn'){
                $item = SetupPpn::find($id);
                $event['setup_ppn_create_success'] = true;
                $item->delete();
            }

            $messages['success'][] = trans('backpack::crud.delete_confirmation_message');
            $messages['events'] = $event;

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
