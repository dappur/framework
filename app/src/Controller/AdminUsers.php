<?php

namespace Dappur\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;
use Dappur\Model\Users;
use Dappur\Model\Roles;
use Dappur\Dappurware\Sentinel as S;

class AdminUsers extends Controller{

	public function users(Request $request, Response $response){

        $sentinel = new S($this->container);
        $sentinel->hasPerm('user.view');

        return $this->view->render($response, 'users.twig', ["users" => Users::get(), "roles" => Roles::get()]);

    }
    
    public function usersAdd(Request $request, Response $response){

        $sentinel = new S($this->container);
        $sentinel->hasPerm('user.create');

        $requestParams = $request->getParams();

        if ($request->isPost()) {
            $user_id = $request->getParam('user_id');
            $first_name = $request->getParam('first_name');
            $last_name = $request->getParam('last_name');
            $email = $request->getParam('email');
            $username = $request->getParam('username');
            $password = $request->getParam('password');
            $user_roles = $request->getParam('roles');
            $perm_name = $request->getParam('perm_name');
            $perm_value = $request->getParam('perm_value');

            $permissions_array = array();

            if (is_array($perm_name)) {
                foreach ($perm_name as $pkey => $pvalue) {
                    if ($perm_value[$pkey] == "true") {
                        $val = true;
                    }else{
                        $val = false;
                    }
                    $permissions_array[$pvalue] = $val;
                }
            }

            // Check if roles exist
            $roles_array = array();
            if (is_array($user_roles)) {
                foreach ($user_roles as $rkey => $rvalue) {
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
                ),
                'password' => array(
                    'rules' => V::noWhitespace()->length(6, 25),
                    'messages' => array(
                        'noWhitespace' => 'Must not contain spaces.',
                        'length' => 'Must be between 6 and 25 characters.'
                    )
                ),
                'password_confirm' => array(
                    'rules' => V::equals($password),
                    'messages' => array(
                        'equals' => 'Passwords do not match.'
                    )
                )
            );
            $this->validator->validate($request, $validate_data);

            // Validate Username
            if ($this->auth->findByCredentials(['login' => $username])) {
                $this->validator->addError('username', 'User already exists with this username.');
            }

            // Validate Email
            if ($this->auth->findByCredentials(['login' => $email])) {
                $this->validator->addError('email', 'User already exists with this email.');
            }

            if ($this->validator->isValid()) {

                $user = $this->auth->registerAndActivate([
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'username' => $username,
                    'password' => $password,
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
                        if ($rolevalue['slug'] == 'admin' && $user_id == 1) {
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
                $this->logger->addInfo("User added successfully", array("first_name" => $first_name, "last_name" => $last_name, "email" => $email, "username" => $username));
                return $this->redirect($response, 'admin-users');
            }
        }

        return $this->view->render($response, 'users-add.twig', ['roles' => Roles::get(), 'requestParams' => $requestParams]);

        
    }

    public function usersEdit(Request $request, Response $response, $userid){

        $sentinel = new S($this->container);
        $sentinel->hasPerm('user.update');

        $requestParams = $request->getParams();

        $user = Users::find($userid);

        if ($user) {
            if ($request->isPost()) {
                $user_id = $request->getParam('user_id');
                $first_name = $request->getParam('first_name');
                $last_name = $request->getParam('last_name');
                $email = $request->getParam('email');
                $username = $request->getParam('username');
                $user_roles = $request->getParam('roles');
                $perm_name = $request->getParam('perm_name');
                $perm_value = $request->getParam('perm_value');

                // Create Permissions Array
                $permissions_array = array();
                if(null !== $perm_name){
                    foreach ($perm_name as $pkey => $pvalue) {
                        if ($perm_value[$pkey] == "true") {
                            $val = true;
                        }else{
                            $val = false;
                        }
                        $permissions_array[$pvalue] = $val;
                    }
                }

                // Check if roles exist
                $roles_array = array();
                if(null !== $user_roles){
                    foreach ($user_roles as $rkey => $rvalue) {
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
                if ($this->auth->findByCredentials(['login' => $username]) && $user->username != $username) {
                    $this->validator->addError('username', 'User already exists with this username.');
                }

                // Validate Email
                if ($this->auth->findByCredentials(['login' => $email]) && $user->email != $email) {
                    $this->validator->addError('email', 'User already exists with this email.');
                }

                if ($this->validator->isValid()) {

                    // Get User Info
                    $user = $this->auth->findById($user_id);


                    // Update User Info
                    $update_user = $user;
                    $update_user->first_name = $first_name;
                    $update_user->last_name = $last_name;
                    $update_user->email = $email;
                    $update_user->username = $username;
                    $update_user->save();

                    $user_perms = $user;
                    $user_perms->permissions = $permissions_array;
                    $user_perms->save();

                    foreach (Roles::get() as $rolekey => $rolevalue) {
                        echo $rolevalue['slug'] . "<br>";
                        
                        if (!in_array($rolevalue['slug'], $roles_array)) {
                            if ($rolevalue['slug'] == 'admin' && $user_id == 1) {
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

                    $this->flash('success', $username.' has been updated successfully.');
                    $this->logger->addInfo("User updated successfully", array("first_name" => $first_name, "last_name" => $last_name, "email" => $email, "username" => $username));
                    return $this->redirect($response, 'admin-users');
                }
            }

            return $this->view->render($response, 'users-edit.twig', ['user' => $user, 'roles' => Roles::get(), 'requestParams' => $requestParams]);
        }else{
            $this->flash('danger', 'Sorry, that user was not found.');
            return $response->withRedirect($this->router->pathFor('admin-users'));
        }
        
    }

    public function usersDelete(Request $request, Response $response){

        $sentinel = new S($this->container);
        $sentinel->hasPerm('user.delete');

        $user = $this->auth->findById($request->getParam('user_id'));

        if($user->delete()){
            $this->flash('success', 'User has been deleted successfully.');
            $this->logger->addInfo("User deleted successfully", array("user" => $user));
            return $this->redirect($response, 'admin-users');
        }else{
            $this->flash('danger'.'There was an error deleting the user.');
            $this->logger->addInfo("User ", array("user" => $user));
            return $this->redirect($response, 'admin-users');
        }
        
    }
}