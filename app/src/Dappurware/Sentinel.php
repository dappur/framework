<?php

namespace Dappur\Dappurware;

class Sentinel extends Dappurware
{
    public function userAccess()
    {
        $user = $this->auth->check();

        $permissions = [];
        $rolesSlugs = [];

        if ($user) {
            foreach ($user->getRoles() as $value) {
                $rolesSlugs[] = $value->slug;
            }

            $permissions = $this->getUserPermissions($user, $rolesSlugs);
        }
        return array('permissions' => $permissions);
    }

    public function hasPerm($permission, $redirectTo = 'home', $enabled = 1)
    {
        if (!$this->auth->hasAccess($permission) || !$enabled) {
            $user = $this->auth->check();

            $error = new \Slim\Flash\Messages();
            $error->addMessage('danger', 'You do not have permission to access that page.');
            
            $this->logger->addWarning("Unauthorized Access", array("user_id" => $user->id));

            return $this->response->withRedirect($this->router->pathFor($redirectTo));
        }

        return false;
    }

    private function getUserPermissions($user, $rolesSlugs)
    {
        $permissions = [];
        $roles = $this->auth->getRoleRepository()->createModel()->whereIn('slug', $rolesSlugs)->get();

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

        return $permissions;
    }
}
