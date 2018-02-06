<?php

namespace Dappur\Controller;

use Dappur\Dappurware\Sentinel as S;
use Dappur\Model\RoleUsers;
use Dappur\Model\Roles;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

class AdminRoles extends Controller{

    public function rolesAdd(Request $request, Response $response){
        
        $sentinel = new S($this->container);
        if(!$sentinel->hasPerm('role.create')){
            return $this->redirect($response, 'dashboard');
        }

        if ($request->isPost()) {

            $this->validator->validate($request, [
                'role_name' => V::length(2, 25)->alpha('\''),
                'role_slug' => V::slug()
            ]);

            if ($this->validator->isValid()) {

                $role = $this->auth->getRoleRepository()->createModel()->create([
                    'name' => $request->getParam('role_name'),
                    'slug' => $request->getParam('role_slug'),
                    'permissions' => []
                ]);

                if ($role) {
                    $this->flash('success', 'Role has been successfully added.');
                }else{
                    $this->flash('danger', 'There was a problem adding the role.');
                }
            }else{
                $this->flash('danger', 'There was a problem adding the role.');
            }
            return $this->redirect($response, 'admin-users');
        }

    }

    public function rolesDelete(Request $request, Response $response){

        $sentinel = new S($this->container);
        if(!$sentinel->hasPerm('role.delete')){
            return $this->redirect($response, 'dashboard');
        }

        if (is_numeric($request->getParam('role_id')) && $role != 1) {

            RoleUsers::where('role_id', '=', $request->getParam('role_id'))->delete();

            $remove_role = Roles::find($request->getParam('role_id'));

            if ($remove_role->delete()) {
                $this->flash('success', 'Role has been removed.');
            }else{
                $this->flash('danger', 'There was a problem removing the role.');
            }
        }else{
            $this->flash('danger', 'There was a problem removing the role.');
        }

        return $this->redirect($response, 'admin-users');

    }

    public function rolesEdit(Request $request, Response $response, $roleid){
        
        $sentinel = new S($this->container);
        if(!$sentinel->hasPerm('role.update')){
            return $this->redirect($response, 'dashboard');
        }

        $role = Roles::find($roleid);

        if ($role) {
            if ($request->isPost()) {

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
                $check_name = $role->where('id', '!=', $request->getParam('role_id'))->where('name', '=', $request->getParam('role_name'))->get()->count();
                if ($check_name > 0) {
                    $this->validator->addError('role_name', 'Role name is already in use.');
                }

                //Validate Role Name
                $check_slug = $role->where('id', '!=', $request->getParam('role_id'))->where('slug', '=', $request->getParam('role_slug'))->get()->count();
                if ($check_slug > 0) {
                    $this->validator->addError('role_slug', 'Role slug is already in use.');
                }

                // Create Permissions Array
                $permissions_array = array();
                foreach ($request->getParam('perm_name') as $pkey => $pvalue) {
                    if ($request->getParam('perm_value')[$pkey] == "true") {
                        $val = true;
                    }else{
                        $val = false;
                    }
                    $permissions_array[$pvalue] = $val;
                }



                if ($this->validator->isValid()) {

                    $update_role = $role;
                    $update_role->name = $request->getParam('role_name');
                    $update_role->slug = $request->getParam('role_slug');
                    $update_role->save();

                    $role_perms = $this->auth->findRoleById($request->getParam('role_id'));
                    $role_perms->permissions = $permissions_array;
                    $role_perms->save();

                    $this->flash('success', 'Role has been updated successfully.');
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