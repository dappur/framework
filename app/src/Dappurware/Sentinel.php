<?php

namespace App\Dappurware;

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
}