<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Project;
use App\Models\ClientPo;
use App\Models\Quotation;
use Illuminate\Http\Request;
use App\Models\InvoiceClient;
use App\Http\Helpers\CustomHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;


class AccountUserCrudController extends CrudController
{
        use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
        use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;

    public function setup()
    {
        CRUD::setModel(User::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/setting/account');
        CRUD::setEntityNameStrings(trans('backpack::crud.menu.account_setting'), trans('backpack::crud.menu.account_setting'));
    }

    function index(){
        $this->crud->hasAccessOrFail('list');

        $this->card->addCard([
            'name' => 'dashboard',
            'line' => 'top',
            'view' => 'crud::components.user_account',
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

        $user = backpack_user();
        $this->data['user_name'] = $user->name;
        $this->data['email'] = $user->email;

        $breadcrumbs = [
            trans('backpack::crud.menu.setting') => backpack_url('setting'),
            trans('backpack::crud.menu.account_setting') => backpack_url($this->crud->route)
        ];
        $this->data['breadcrumbs'] = $breadcrumbs;

        // $list = "crud::list-custom" ?? $this->crud->getListView();
        $list = "crud::list-blank" ?? $this->crud->getListView();
        return view($list, $this->data);
    }


    function update_personal(Request $request){

        $user = backpack_user();

        CRUD::setValidation([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ]);

        $request = $this->crud->validateRequest();

        $this->crud->registerFieldEvents();

        DB::beginTransaction();
        try{

            $user = User::find($user->id);
            $user->name = $request->name;
            $user->email = $request->email;

            if ($request->hasFile('profile_photo')) {
                if ($user->profile_photo && Storage::disk('public')->exists('logos/' . $user->profile_photo)) {
                    Storage::disk('public')->delete('logos/' . $user->profile_photo);
                }

                $fileName = time() . '_user.' . $request->file('profile_photo')->getClientOriginalExtension();
                $request->file('profile_photo')->storeAs('logos', $fileName, 'public'); // jangan pakai 'public/logos' di path

                $user->profile_photo = $fileName;
            }

            $user->save();

            $this->data['entry'] = $this->crud->entry = $user;

            \Alert::success(trans('backpack::crud.update_success'))->flash();

            $events = [];

            $this->crud->setSaveAction();

            DB::commit();
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $user,
                    'events' => $events,
                ]);
            }

            return $this->crud->performSaveAction($user->getKey());

        }catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    function update_password(Request $request){
         $user = backpack_user();

        CRUD::setValidation([
            'old_password' => ['required'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $request = $this->crud->validateRequest();

        $this->crud->registerFieldEvents();

        DB::beginTransaction();
        try{

            if (!Hash::check($request->old_password, $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'errors' => ['old_password' => ['The provided password was incorrect.']],
                ], 422);
            }

           $user->password = Hash::make($request->new_password);
           $user->save();

            $this->data['entry'] = $this->crud->entry = $user;

            \Alert::success(trans('backpack::crud.update_success'))->flash();

            $events = [];

            $this->crud->setSaveAction();

            DB::commit();
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $user,
                    'events' => $events,
                ]);
            }

            return $this->crud->performSaveAction($user->getKey());

        }catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

}
