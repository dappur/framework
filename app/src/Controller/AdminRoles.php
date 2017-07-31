<?php

namespace Dappur\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

class AdminRoles extends Controller{

    public function rolesAdd(Request $request, Response $response){
        
        if (!$this->auth->hasAccess('role.create')) {

            $loggedUser = $this->auth->check();
            
            $this->flash('danger', 'You do not have permission to create roles.');
            $this->logger->addError("Unauthorized Access", array("message" => "Unauthorized access was attempted on the create role page", "user_id" => $loggedUser['id']));
            return $this->redirect($response, 'admin-users');
            
        }

        if ($request->isPost()) {
            $roles = new \Dappur\Model\Roles;

            $role_name = $request->getParam('role_name');
            $role_slug = $request->getParam('role_slug');

            $this->validator->validate($request, [
                'role_name' => V::length(2, 25)->alpha('\''),
                'role_slug' => V::slug()
            ]);

            if ($this->validator->isValid()) {

                $role = $this->auth->getRoleRepository()->createModel()->create([
                    'name' => $role_name,
                    'slug' => $role_slug,
                    'permissions' => []
                ]);

                if ($role) {
                    $this->flash('success', 'Role has been successfully added.');
                    $this->logger->addInfo("Role added successfully", array("role_name" => $role_name, "role_slug" => $role_slug));
                    return $this->redirect($response, 'admin-users');
                }else{
                    $this->flash('danger', 'There was a problem adding the role.');
                    $this->logger->addError("Problem adding role.", array("role_name" => $role_name, "role_slug" => $role_slug));
                    return $this->redirect($response, 'admin-users');
                }
            }else{
                $this->flash('danger', 'There was a problem adding the role.');
                $this->logger->addError("Problem adding role.", array("role_name" => $role_name, "role_slug" => $role_slug));
                return $this->redirect($response, 'admin-users');
            }
        }

    }

    public function rolesDelete(Request $request, Response $response){

        if (!$this->auth->hasAccess('role.delete')) {

            $loggedUser = $this->auth->check();
            
            $this->flash('danger', 'You do not have permission to delete roles.');
            $this->logger->addError("Unauthorized Access", array("message" => "Unauthorized access was attempted on the delete role page", "user_id" => $loggedUser['id']));
            return $this->redirect($response, 'admin-users');
            
        }

        $requestParams = $request->getParams();

        if (is_numeric($requestParams['role_id']) && $role != 1) {

            $remove_user_roles = new \Dappur\Model\RoleUsers;

            $remove_user_roles->where('role_id', '=', $requestParams['role_id'])->delete();

            $remove_role = new \Dappur\Model\Roles;
            $remove_role = $remove_role->find($requestParams['role_id']);


            if ($remove_role->delete()) {
                $this->flash('success', 'Role has been removed.');
                $this->logger->addInfo("Role removed successfully", array("role_id" => $role));
            }else{
                $this->flash('danger', 'There was a problem removing the role.');
                $this->logger->addError("Problem removing role.", array("role_id" => $role));
            }
        }else{
            $this->flash('danger', 'There was a problem removing the role.');
            $this->logger->addError("Problem removing role.", array("role_id" => $role));
        }

        return $this->redirect($response, 'admin-users');

    }

    public function rolesEdit(Request $request, Response $response, $roleid){
        if (!$this->auth->hasAccess('role.update')) {

            $loggedUser = $this->auth->check();
            
            $this->flash('danger', 'You do not have permission to edit roles.');
            $this->logger->addError("Unauthorized Access", array("message" => "Unauthorized access was attempted on the edit role page", "user_id" => $loggedUser['id']));
            return $this->redirect($response, 'admin-users');
            
        }

        $roles = new \Dappur\Model\Roles;
        $role = $roles->find($roleid);

        if ($role) {
            if ($request->isPost()) {
                // Get Vars
                $role_name = $request->getParam('role_name');
                $role_slug = $request->getParam('role_slug');
                $role_id = $request->getParam('role_id');
                $perm_name = $request->getParam('perm_name');
                $perm_value = $request->getParam('perm_value');

                // Validate Data
                $validate_data = array(
                    'role_name' => array(
                        'rules' => V::length(2, 25)->alpha('\''), 
                        'messages' => array(
                            'length' => 'Must be between 2 and 25 characters.',
                            'alpha' => 'Letters only and can contain \''
                            )
                    ),
                    'role_slug' => array(
                        'rules' => V::slug(), 
                        'messages' => array(
                            'slug' => 'May only contain lowercase letters, numbers and hyphens.'
                            )
                    )
                );

                $this->validator->validate($request, $validate_data);

                //Validate Role Name
                $check_name = $role->where('id', '!=', $role_id)->where('name', '=', $role_name)->get()->count();
                if ($check_name > 0) {
                    $this->validator->addError('role_name', 'Role name is already in use.');
                }

                //Validate Role Name
                $check_slug = $role->where('id', '!=', $role_id)->where('slug', '=', $role_slug)->get()->count();
                if ($check_slug > 0) {
                    $this->validator->addError('role_slug', 'Role slug is already in use.');
                }

                // Create Permissions Array
                $permissions_array = array();
                foreach ($perm_name as $pkey => $pvalue) {
                    if ($perm_value[$pkey] == "true") {
                        $val = true;
                    }else{
                        $val = false;
                    }
                    $permissions_array[$pvalue] = $val;
                }



                if ($this->validator->isValid()) {

                    $update_role = $role;
                    $update_role->name = $role_name;
                    $update_role->slug = $role_slug;
                    $update_role->save();

                    $role_perms = $this->auth->findRoleById($role_id);
                    $role_perms->permissions = $permissions_array;
                    $role_perms->save();


                    $this->flash('success', 'Role has been updated successfully.');
                    $this->logger->addInfo("Role updated successfully", array("role_id" => $role_id));
                    return $this->redirect($response, 'admin-users');
                }
            }

            return $this->view->render($response, 'roles-edit.twig', ['role' => $role]);

        }else{
            $this->flash('danger', 'Sorry, that role was not found.');
            return $response->withRedirect($this->router->pathFor('admin-users'));
        }


    }

}