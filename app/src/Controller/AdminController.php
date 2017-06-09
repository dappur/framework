<?php

namespace Dappur\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

class AdminController extends Controller{

    public function dashboard(Request $request, Response $response){

        return $this->view->render($response, 'Admin/dashboard.twig');

    }

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
                    'slug' => $role_slug
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

            return $this->view->render($response, 'Admin/roles-edit.twig', ['role' => $role]);

        }else{
            $this->flash('danger', 'Sorry, that role was not found.');
            return $response->withRedirect($this->router->pathFor('admin-users'));
        }


    }

    public function users(Request $request, Response $response){

        if (!$this->auth->hasAccess('user.view')) {

            $loggedUser = $this->auth->check();
            
            $this->flash('danger', 'You do not have permission to view users.');
            $this->logger->addError("Unauthorized Access", array("message" => "Unauthorized access was attempted on the users page", "user_id" => $loggedUser['id']));
            return $this->redirect($response, 'dashboard');
            
        }

        $users = new \Dappur\Model\Users;
        $roles = new \Dappur\Model\Roles;

        return $this->view->render($response, 'Admin/users.twig', ["users" => $users->get(), "roles" => $roles->get()]);

    }
    
    public function usersAdd(Request $request, Response $response){

        $requestParams = $request->getParams();

        if (!$this->auth->hasAccess('user.create')) {

            $loggedUser = $this->auth->check();
            
            $this->flash('danger', 'You do not have permission to access the settings.');
            $this->logger->addError("Unauthorized Access", array("message" => "Unauthorized access was attempted on the add user page", "user_id" => $loggedUser['id']));
            return $this->redirect($response, 'dashboard');
            
        }

        $users = new \Dappur\Model\Users;

        $roles = new \Dappur\Model\Roles;

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

            // Validate Data
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

            //Check username
            $check_username = $users->where('id', '!=', $user_id)->where('username', '=', $username)->get()->count();
            if ($check_username > 0) {
                $this->validator->addError('username', 'Username is already in use.');
            }

            //Check Email
            $check_email = $users->where('id', '!=', $user_id)->where('email', '=', $email)->get()->count();
            if ($check_email > 0) {
                $this->validator->addError('email', 'Email address is already in use.');
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

                foreach ($roles->get() as $rolekey => $rolevalue) {
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

        return $this->view->render($response, 'Admin/users-add.twig', ['roles' => $roles->get(), 'requestParams' => $requestParams]);

        
    }

    public function usersEdit(Request $request, Response $response, $userid){

        if (!$this->auth->hasAccess('user.update')) {

            $loggedUser = $this->auth->check();
            
            $this->flash('danger', 'You do not have permission to edit users.');
            $this->logger->addError("Unauthorized Access", array("message" => "Unauthorized access was attempted on the edit user page", "user_id" => $loggedUser['id']));
            return $this->redirect($response, 'dashboard');
            
        }

        $requestParams = $request->getParams();

        $users = new \Dappur\Model\Users;
        $user = $users->where('id', '=', $userid)->first();

        $roles = new \Dappur\Model\Roles;

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

                // Validate Data
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

                //Check username
                $check_username = $users->where('id', '!=', $user_id)->where('username', '=', $username)->get()->count();
                if ($check_username > 0) {
                    $this->validator->addError('username', 'Username is already in use.');
                }

                //Check Email
                $check_email = $users->where('id', '!=', $user_id)->where('email', '=', $email)->get()->count();
                if ($check_email > 0) {
                    $this->validator->addError('email', 'Email address is already in use.');
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

                    foreach ($roles->get() as $rolekey => $rolevalue) {
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

            return $this->view->render($response, 'Admin/users-edit.twig', ['user' => $user, 'roles' => $roles->get(), 'requestParams' => $requestParams]);
        }else{
            $this->flash('danger', 'Sorry, that user was not found.');
            return $response->withRedirect($this->router->pathFor('admin-users'));
        }
        
    }

    public function usersDelete(Request $request, Response $response){

        if (!$this->auth->hasAccess('user.delete')) {

            $loggedUser = $this->auth->check();
            
            $this->flash('danger', 'You do not have permission to delete users.');
            $this->logger->addError("Unauthorized Access", array("message" => "Unauthorized access was attempted on the edit user page", "user_id" => $loggedUser['id']));
            return $this->redirect($response, 'dashboard');
            
        }


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

    public function settingsGlobal(Request $request, Response $response){

        if (!$this->auth->hasAccess('config.global')) {

            $loggedUser = $this->auth->check();
            
            $this->flash('danger', 'You do not have permission to access the settings.');
            $this->logger->addError("Unauthorized Access", array("message" => "Unauthorized access was attempted on the global settings page", "user_id" => $loggedUser['id']));
            return $this->redirect($response, 'dashboard');
            
        }

        $timezones = $this->getTimezones();
        $theme_list = $this->getThemeList();
        $config = new \Dappur\Model\Config;
        $global_config = $config->get();

        if ($request->isPost()) {

            $allPostVars = $request->getParsedBody();

            // Validate Domain
            if (array_key_exists('domain', $allPostVars)){
                $this->validator->validate($request, ['domain' => array('rules' => V::domain(), 'messages' => array('domain' => 'Please enter a valid domain.'))]);
            }

            // Validate Reply To Email
            if (array_key_exists('replyto-email', $allPostVars)){
                $this->validator->validate($request, ['replyto-email' => array('rules' => V::noWhitespace()->email(), 'messages' => array('noWhitespace' => 'Must not contain any spaces.', 'email' => 'Enter a valid email address.'))]);
            }

            // Validate Google Analytics
            if (isset($allPostVars['ga']) && !empty($allPostVars['ga'])){
                $this->validator->validate($request, ['ga' => array('rules' => V::regex('/(UA|YT|MO)-\d+-\d+/'), 'messages' => array('regex' => 'Enter a valid UA Tracking Code'))]);
            }

            // Additional Validation
            foreach ($allPostVars as $key => $value) {
                if (strip_tags($value) != $value) {
                    $this->validator->addError($key, 'Please do not use any HTML Tags');
                    $this->logger->addWarning("possible scripting attack", array("message" => "HTML tags were blocked from being put into the config."));
                }

                if ($key == "theme" && !in_array($value, $theme_list)) {
                    $this->validator->addError($key, 'Not a valid global setting.');
                }
            }


            if ($this->validator->isValid()) {

                foreach ($allPostVars as $key => $value) {
                    $updateRow = new \Dappur\Model\Config;
                    $updateRow->where('name', $key)->update(['value' => $value]);
                }

                $this->flash('success', 'Global settings have been updated successfully.');
                return $this->redirect($response, 'settings-global');
            }

            
        }

        return $this->view->render($response, 'Admin/global-settings.twig', array("globalConfig" => $global_config, "themeList" => $theme_list, "timezones" => $timezones));

    }

    public function settingsGlobalAdd(Request $request, Response $response){

        if (!$this->auth->hasAccess('config.global')) {

            $loggedUser = $this->auth->check();
            
            $this->flash('danger', 'You do not have permission to add settings.');
            $this->logger->addError("Unauthorized Access", array("message" => "Unauthorized access was attempted on the global settings add page", "user_id" => $loggedUser['id']));
            return $this->redirect($response, 'dashboard');
            
        }

        $timezones = $this->getTimezones();
        $theme_list = $this->getThemeList();
        $config = new \Dappur\Model\Config;
        $global_config = $config->get();

        if ($request->isPost()) {

            $allPostVars = $request->getParsedBody();

            $this->validator->validate($request, array('add_name' => array('rules' => V::slug()->length(4, 32), 'messages' => array('slug' => 'May only contain lowercase letters, numbers and hyphens.', 'length' => 'Must be between 4 and 32 characters.'))));
            $this->validator->validate($request, array('add_description' => array('rules' => V::alnum()->length(4, 32), 'messages' => array('alnum' => 'May only contain letters and numbers.', 'length' => 'Must be between 4 and 32 characters.'))));
            
            if ($allPostVars['add_type'] == "string") {
                // Check for HTML Tags
                if (strip_tags($allPostVars['add_value']) != $allPostVars['add_value']) {
                    $this->validator->addError('add_value', 'Please do not use any HTML Tags');
                    $this->logger->addWarning("possible scripting attack", array("message" => "HTML tags were blocked from being put into the config."));
                }
            } else {
                $this->validator->addError('add_value', 'Not a valid global setting.');
            }

            $check_config = $config->where('name', '=', $allPostVars['add_name'])->get()->count();
            if ($check_config > 0) {
                $this->validator->addError('add_name', 'Name is already in use.');
            }

            if ($this->validator->isValid()) {

                $configOption = new \Dappur\Model\Config;
                $configOption->name = $allPostVars['add_name'];
                $configOption->description = $allPostVars['add_description'];
                $configOption->type = $allPostVars['add_type'];
                $configOption->value = $allPostVars['add_value'];
                $configOption->save();


                $this->flash('success', 'Global settings successfully added.');
                return $this->redirect($response, 'settings-global');
            }

            
        }

        return $this->view->render($response, 'Admin/global-settings.twig', array("globalConfig" => $global_config, "themeList" => $theme_list, "timezones" => $timezones));

    }

    public function myAccount(Request $request, Response $response){

        $requestParams = $request->getParams();

        $loggedUser = $this->auth->check();

        $users = new \Dappur\Model\Users;

        if (!$loggedUser) {
            
            $this->flash('danger', 'You need to be logged in to access this page.');
            $this->logger->addError("Unauthorized Access", array("message" => "Unauthorized access was attempted on the my account page", "user_id" => $loggedUser['id']));
            return $this->redirect($response, 'dashboard');
            
        }

        if ($request->isPost()) {
            $first_name = $request->getParam('first_name');
            $last_name = $request->getParam('last_name');
            $email = $request->getParam('email');
            $username = $request->getParam('username');
            $password = $request->getParam('password');
            $password_confirm = $request->getParam('password_confirm');

            if (null !== $request->getParam('update_account')) {
                 // Validate Data
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
                //Check username
                if ($loggedUser['username'] != $username) {
                    $check_username = $users->where('id', '!=', $user_id)->where('username', '=', $username)->get()->count();
                    if ($check_username > 0) {
                        $this->validator->addError('username', 'Username is already in use.');
                    }
                }
                

                //Check Email
                if ($loggedUser['email'] != $email) {
                    $check_email = $users->where('id', '!=', $user_id)->where('email', '=', $email)->get()->count();
                    if ($check_email > 0) {
                        $this->validator->addError('email', 'Email address is already in use.');
                    }
                }

                $this->validator->validate($request, $validate_data);

                if ($this->validator->isValid()) {

                    $new_information = [
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'email' => $email,
                        'username' => $username
                    ];

                    $update_user = $this->auth->update($loggedUser, $new_information);

                    if ($update_user) {
                        $this->flash('success', 'Your account has been updated successfully.');
                        $this->logger->addInfo("My Account: User successfully updated.", array("first_name" => $first_name, "last_name" => $last_name, "email" => $email, "username" => $username, "user_id" => $loggedUser['id']));
                        return $this->redirect($response, 'my-account');
                    }else{
                        $this->flash('danger', 'There was an error updating your account information.');
                        $this->logger->addInfo("My Account: An unknown error occured updating user.", array("first_name" => $first_name, "last_name" => $last_name, "email" => $email, "username" => $username, "user_id" => $loggedUser['id']));
                    }
                }
            }

            if (null !== $request->getParam('change_password')) {
                // Validate Data
                $validate_data = array(
                    'password' => array(
                    'rules' => V::noWhitespace()->length(6, 25), 
                    'messages' => array(
                        'length' => 'Must be between 6 and 25 characters.',
                        'noWhitespace' => 'Must not contain any spaces.'
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

                if ($this->validator->isValid()) {

                    $new_information = [
                        'password' => $password,
                    ];

                    $update_user = $this->auth->update($loggedUser, $new_information);

                    if ($update_user) {
                        $this->flash('success', 'Your password has been updated successfully.');
                        $this->logger->addInfo("My Account: Password successfully changed", array("user_id" => $loggedUser['id']));
                        return $this->redirect($response, 'my-account');
                    }else{
                        $this->flash('danger', 'There was an error changing your password.');
                        $this->logger->addInfo("My Account: An unknown error occured changing a password.", array("user_id" => $loggedUser['id']));
                    }
                }
            }

        }

        return $this->view->render($response, 'Admin/my-account.twig', array("requestParams" => $requestParams));
    }

    public function getCloudinaryCMS($container){

        // Generate Timestamp
        $date = new \DateTime();
        $timestamp = $date->getTimestamp();
        
        // Prepare Cloudinary CMS Params
        $params = array("timestamp" => $timestamp, "mode" => "tinymce");

        // Prepare Cloudinary Options
        $options = array("cloud_name" => $container->settings['cloudinary']['cloud_name'],
            "api_key" => $container->settings['cloudinary']['api_key'],
            "api_secret" => $container->settings['cloudinary']['api_secret']);

        // Sign Request With Cloudinary
        $output = \Cloudinary::sign_request($params, $options);

        if ($output) {
            // Build the http query
            $api_params_cl = http_build_query($output);

            // Complete the Cloudinary URL
            $cloudinary_cms_url = "https://cloudinary.com/console/media_library/cms?$api_params_cl";

            return $cloudinary_cms_url;
        }else{
            return false;
        }
        
    }

    private function getThemeList(){
        $public_assets = array_filter(glob('../public/assets/*'), 'is_dir');
        $internal_assets = array_filter(glob('../app/views/*'), 'is_dir');

        $public_array = array();
        $internal_array = array();
        foreach ($public_assets as $key => $value) {
            $public_array[] = substr($value, strrpos($value, '/') + 1);
        }

        foreach ($internal_assets as $key => $value) {
            $internal_array[] = substr($value, strrpos($value, '/') + 1);
        }

        foreach ($internal_array as $key => $value) {
            if (!in_array($value, $public_array)) {
                unset($internal_array[$key]);
            }
        }

        return $internal_array;
    }

    private function getTimezones(){

        $zones_array = array();
        $timestamp = time();
        foreach(timezone_identifiers_list() as $key => $zone) {
            date_default_timezone_set($zone);
            $zones_array[$key]['zone'] = $zone;
            $zones_array[$key]['diff_from_GMT'] = 'UTC/GMT ' . date('P', $timestamp);
        }
        return $zones_array;
    }


}