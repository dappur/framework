<?php

namespace Dappur\Controller;

use Dappur\Model\Roles;
use Dappur\Model\Users;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

class AdminUsers extends Controller{

    public function users(Request $request, Response $response){

        if($check = $this->sentinel->hasPerm('user.view')){
            return $check;
        }

        return $this->view->render($response, 'users.twig', ["users" => Users::get(), "roles" => Roles::get()]);

    }
    
    public function usersAdd(Request $request, Response $response){

        if($check = $this->sentinel->hasPerm('user.create')){
            return $check;
        }

        $requestParams = $request->getParams();

        if ($request->isPost()) {

            $permissions_array = array();

            if (is_array($request->getParam('perm_name'))) {
                foreach ($request->getParam('perm_name') as $pkey => $pvalue) {
                    if ($request->getParam('perm_value')[$pkey] == "true") {
                        $val = true;
                    }else{
                        $val = false;
                    }
                    $permissions_array[$pvalue] = $val;
                }
            }

            // Check if roles exist
            $roles_array = array();
            if (is_array($request->getParam('roles'))) {
                foreach ($request->getParam('roles') as $rkey => $rvalue) {
                    if (!$this->auth->findRoleBySlug($rvalue)) {
                        $this->validator->addError('roles', 'Role does not exist.');
                    }else{
                        $roles_array[] = $rvalue;
                    }
                }
            }

            // Validate Form Data
            $validate_data = array(
                'first_name' => array(
                    'rules' => V::length(2, 25), 
                    'messages' => array(
                        'length' => 'Must be between 2 and 25 characters.'
                        )
                ),
                'last_name' => array(
                    'rules' => V::length(2, 25), 
                    'messages' => array(
                        'length' => 'Must be between 2 and 25 characters.'
                        )
                ),
                'email' => array(
                    'rules' => V::noWhitespace()->email(), 
                    'messages' => array(
                        'email' => 'Enter a valid email address.',
                        'noWhitespace' => 'Must not contain any spaces.'
                        )
                ),
                'username' => array(
                    'rules' => V::noWhitespace()->alnum(), 
                    'messages' => array(
                        'slug' => 'Must be alpha numeric with no spaces.',
                        'noWhitespace' => 'Must not contain any spaces.'
                        )
                ),
                'password' => array(
                    'rules' => V::noWhitespace()->length(6, 25),
                    'messages' => array(
                        'noWhitespace' => 'Must not contain spaces.',
                        'length' => 'Must be between 6 and 25 characters.'
                    )
                ),
                'password_confirm' => array(
                    'rules' => V::equals($request->getParam('password')),
                    'messages' => array(
                        'equals' => 'Passwords do not match.'
                    )
                )
            );
            $this->validator->validate($request, $validate_data);

            // Validate Username
            if ($this->auth->findByCredentials(['login' => $request->getParam('username')])) {
                $this->validator->addError('username', 'User already exists with this username.');
            }

            // Validate Email
            if ($this->auth->findByCredentials(['login' => $request->getParam('email')])) {
                $this->validator->addError('email', 'User already exists with this email.');
            }

            if ($this->validator->isValid()) {

                $user = $this->auth->registerAndActivate([
                    'first_name' => $request->getParam('first_name'),
                    'last_name' => $request->getParam('last_name'),
                    'email' => $request->getParam('email'),
                    'username' => $request->getParam('username'),
                    'password' => $request->getParam('password'),
                    'permissions' => [
                        'user.delete' => 0
                    ]
                ]);

                $user_perms = $user;
                $user_perms->permissions = $permissions_array;
                $user_perms->save();

                foreach (Roles::get() as $rolekey => $rolevalue) {
                    echo $rolevalue['slug'] . "<br>";
                    
                    if (!in_array($rolevalue['slug'], $roles_array)) {
                        if ($rolevalue['slug'] == 'admin' && $request->getParam('user_id') == 1) {
                            continue;
                        }else{
                            $role = $this->auth->findRoleBySlug($rolevalue['slug']);
                            $role->users()->detach($user);
                        }
                        
                    }
                    
                }

                foreach ($roles_array as $rakey => $ravalue) {
                    if ($user->inRole($ravalue)) {
                        continue;
                    }else{
                        $role = $this->auth->findRoleBySlug($ravalue);
                        $role->users()->attach($user);
                    }
                }

                $this->flash('success', $username.' has been added successfully.');
                return $this->redirect($response, 'admin-users');
            }
        }

        return $this->view->render($response, 'users-add.twig', ['roles' => Roles::get()]);

        
    }

    public function usersEdit(Request $request, Response $response, $userid){

        if($check = $this->sentinel->hasPerm('user.update')){
            return $check;
        }

        $requestParams = $request->getParams();

        $user = Users::find($userid);

        if ($user) {
            if ($request->isPost()) {

                // Create Permissions Array
                $permissions_array = array();
                if(null !== $request->getParam('perm_name')){
                    foreach ($request->getParam('perm_name') as $pkey => $pvalue) {
                        if ($request->getParam('perm_value')[$pkey] == "true") {
                            $val = true;
                        }else{
                            $val = false;
                        }
                        $permissions_array[$pvalue] = $val;
                    }
                }

                // Check if roles exist
                $roles_array = array();
                if(null !== $request->getParam('roles')){
                    foreach ($request->getParam('roles') as $rkey => $rvalue) {
                        if (!$this->auth->findRoleBySlug($rvalue)) {
                            $this->validator->addError('roles', 'Role does not exist.');
                        }else{
                            $roles_array[] = $rvalue;
                        }
                    }
                }

                // Validate Form Data
                $validate_data = array(
                    'first_name' => array(
                        'rules' => V::length(2, 25)->alpha('\''), 
                        'messages' => array(
                            'length' => 'Must be between 2 and 25 characters.',
                            'alpha' => 'Letters only and can contain \''
                            )
                    ),
                    'last_name' => array(
                        'rules' => V::length(2, 25)->alpha('\''), 
                        'messages' => array(
                            'length' => 'Must be between 2 and 25 characters.',
                            'alpha' => 'Letters only and can contain \''
                            )
                    ),
                    'email' => array(
                        'rules' => V::noWhitespace()->email(), 
                        'messages' => array(
                            'email' => 'Enter a valid email address.',
                            'noWhitespace' => 'Must not contain any spaces.'
                            )
                    ),
                    'username' => array(
                        'rules' => V::noWhitespace()->alnum(), 
                        'messages' => array(
                            'slug' => 'Must be alpha numeric with no spaces.',
                            'noWhitespace' => 'Must not contain any spaces.'
                            )
                    )
                );
                $this->validator->validate($request, $validate_data);

                // Validate Username
                if ($this->auth->findByCredentials(['login' => $request->getParam('username')]) && $user->username != $request->getParam('username')) {
                    $this->validator->addError('username', 'User already exists with this username.');
                }

                // Validate Email
                if ($this->auth->findByCredentials(['login' => $request->getParam('email')]) && $user->email != $request->getParam('email')) {
                    $this->validator->addError('email', 'User already exists with this email.');
                }

                if ($this->validator->isValid()) {

                    // Get User Info
                    $user = $this->auth->findById($request->getParam('user_id'));

                    $user->first_name = $request->getParam('first_name');
                    $user->last_name = $request->getParam('last_name');
                    $user->email = $request->getParam('email');
                    $user->username = $request->getParam('username');
                    $user->save();

                    $user->permissions = $permissions_array;
                    $user->save();

                    foreach (Roles::get() as $rolekey => $rolevalue) {
                        echo $rolevalue['slug'] . "<br>";
                        
                        if (!in_array($rolevalue['slug'], $roles_array)) {
                            if ($rolevalue['slug'] == 'admin' && $request->getParam('user_id') == 1) {
                                continue;
                            }else{
                                $role = $this->auth->findRoleBySlug($rolevalue['slug']);
                                $role->users()->detach($user);
                            }
                        }
                    }

                    foreach ($roles_array as $rakey => $ravalue) {
                        if ($user->inRole($ravalue)) {
                            continue;
                        }else{
                            $role = $this->auth->findRoleBySlug($ravalue);
                            $role->users()->attach($user);
                        }
                    }

                    $this->flash('success', $user->username.' has been updated successfully.');
                    return $this->redirect($response, 'admin-users');
                }
            }

            return $this->view->render($response, 'users-edit.twig', ['user' => $user, 'roles' => Roles::get()]);
        }else{
            $this->flash('danger', 'Sorry, that user was not found.');
            return $response->withRedirect($this->router->pathFor('admin-users'));
        }
        
    }

    public function usersDelete(Request $request, Response $response){

        if($check = $this->sentinel->hasPerm('user.delete')){
            return $check;
        }

        $user = $this->auth->findById($request->getParam('user_id'));

        if($user->delete()){
            $this->flash('success', 'User has been deleted successfully.');
        }else{
            $this->flash('danger'.'There was an error deleting the user.');
        }

        return $this->redirect($response, 'admin-users');
        
    }
}