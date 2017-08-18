<?php

namespace Dappur\Dappurware;

class Sentinel extends Dappurware
{
	public function userAccess(){
        $sentinel = $this->auth;
        $user = $sentinel->check();

        $permissions = [];
        $rolesSlugs = [];

        if ($user) {
            foreach ($user->getRoles() as $key => $value) {
                $rolesSlugs[] = $value->slug;
            }

            $roles = $sentinel->getRoleRepository()->createModel()->whereIn('slug', $rolesSlugs)->get();

            foreach ($roles as $role) {
                foreach ($role->permissions as $key => $value) {
                    if ($value == 1) {
                        $permissions[] = $key;
                    }
                }
            }

            foreach ($user->permissions as $key => $value) {
                if (!in_array($key, $permissions) && $value == 1) {
                    $permissions[] = $key;
                }

                if (in_array($key, $permissions) && $value == 0) {
                    $key = array_search($key, $permissions);
                    unset($permissions[$key]);
                }
            }

        }
        return array('roles' => $rolesSlugs, 'permissions' => $permissions);
    }

    public function hasPerm($permission, $redirect = "home", $flash = false){
        
        if (!$this->container->auth->hasAccess($permission)) {

            $user = $this->container->auth->check();

            if ($flash) {
                $this->flash('danger', 'You do not have permission to access that page.');
            }
            
            $this->logger->addError("Unauthorized Access", array("message" => "Unauthorized access was attempted on the dashboard", "user_id" => $user->id));
            return $this->redirect($response, $redirect);
        }
    }
}