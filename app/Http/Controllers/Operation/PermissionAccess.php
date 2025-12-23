<?php
    namespace App\Http\Controllers\Operation;

    trait PermissionAccess {

        private function disabledAllPermission(){
            $this->crud->denyAllAccess(['create', 'update', 'delete', 'list', 'show']);
        }

        public function settingPermission($rule){
            $this->disabledAllPermission();
            $user = backpack_user();
            $permissions = $user->getAllPermissions();
            foreach($rule as $access => $permission){
                if(is_array($permission)){
                    if($permissions->whereIn('name', $permission)->count() > 0){
                        $this->crud->allowAccess($access);
                    }
                }else if($permission){
                    $this->crud->allowAccess($access);
                }
            }
        }
    }
?>