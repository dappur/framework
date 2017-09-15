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

    public function hasPerm($permission, $flash = true){
        
        if (!$this->container->auth->hasAccess($permission)) {

            $user = $this->container->auth->check();

            if ($flash) {
                $error = new \Slim\Flash\Messages();
                $error->addMessage('danger', 'You do not have permission to access that page.');
            }
            
            $this->container->logger->addWarning("Unauthorized Access", array("user_id" => $user->id));
            return false;
        }else{
            return true;
        }
    }
}