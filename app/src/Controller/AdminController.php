<?php

namespace App\Controller;

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
            return $this->redirect($response, 'dashboard');
            
        }
        if ($request->isPost()) {
            $roles = new \App\Model\Roles;

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

    public function rolesDelete(Request $request, Response $response, $role){
        if (!$this->auth->hasAccess('role.delete')) {

            $loggedUser = $this->auth->check();
            
            $this->flash('danger', 'You do not have permission to delete roles.');
            $this->logger->addError("Unauthorized Access", array("message" => "Unauthorized access was attempted on the delete role page", "user_id" => $loggedUser['id']));
            return $this->redirect($response, 'dashboard');
            
        }

        if (is_numeric($role) && $role != 1) {
            $remove_role = new \App\Model\Roles;
            $remove_role = $remove_role->find($role);
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
            return $this->redirect($response, 'dashboard');
            
        }

        $roles = new \App\Model\Roles;
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


        $users = new \App\Model\Users;
        $roles = new \App\Model\Roles;

        return $this->view->render($response, 'Admin/users.twig', ["users" => $users->get(), "roles" => $roles->get()]);

    }

    public function usersEdit(Request $request, Response $response, $userid){

        if (!$this->auth->hasAccess('user.edit')) {

            $loggedUser = $this->auth->check();
            
            $this->flash('danger', 'You do not have permission to access the settings.');
            $this->logger->addError("Unauthorized Access", array("message" => "Unauthorized access was attempted on the edit user page", "user_id" => $loggedUser['id']));
            return $this->redirect($response, 'dashboard');
            
        }

        $users = new \App\Model\Users;
        $user = $users->where('id', '=', $userid)->first();

        $roles = new \App\Model\Roles;

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

                $permissions_array = array();


                foreach ($perm_name as $pkey => $pvalue) {
                    if ($perm_value[$pkey] == "true") {
                        $val = true;
                    }else{
                        $val = false;
                    }
                    $permissions_array[$pvalue] = $val;
                }

                // Check if roles exist
                $roles_array = array();
                foreach ($user_roles as $rkey => $rvalue) {
                    if (!$this->auth->findRoleBySlug($rvalue)) {
                        $this->validator->addError('roles', 'Role does not exist.');
                    }else{
                        $roles_array[] = $rvalue;
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

            return $this->view->render($response, 'Admin/users-edit.twig', ['user' => $user, 'roles' => $roles->get()]);
        }else{
            $this->flash('danger', 'Sorry, that user was not found.');
            return $response->withRedirect($this->router->pathFor('admin-users'));
        }
        
    }

    public function settings(Request $request, Response $response){

        return $this->view->render($response, 'Admin/settings.twig');

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
        $global_config = new \App\Model\Config;
        $global_config = $global_config->get();

        if ($request->isPost()) {

            $allPostVars = $request->getParsedBody();

            if (array_key_exists('domain', $allPostVars)){
                $this->validator->validate($request, ['domain' => V::domain()]);
            }

            if (array_key_exists('replyto-email', $allPostVars)){
                $this->validator->validate($request, ['replyto-email' => V::noWhitespace()->email()]);
            }

            if (isset($allPostVars['ga']) && !empty($allPostVars['ga'])){
                $this->validator->validate($request, ['ga' => V::regex('/(UA|YT|MO)-\d+-\d+/')]);
            }

            foreach ($allPostVars as $key => $value) {
                if (strip_tags($value) != $value) {
                    $this->validator->addError($key, 'Please do not use any HTML Tags');
                    $this->logger->addWarning("possible scripting attack", array("message" => "HTML tags were blocked from being put into the config."));
                }

                if ($key == "theme" && !in_array($value, $theme_list)) {
                    $this->validator->addError($key, 'Not a valid global setting.');
                }

                $tz_list = array();
                foreach ($timezones as $tkey => $tvalue) {
                    $tz_list[] = $tvalue['zone'];
                }

                if ($key == "timezone" && !in_array($value, $tz_list)) {
                    $this->validator->addError($key, 'Not a valid global setting.');
                }
            }

            if ($this->validator->isValid()) {

                foreach ($allPostVars as $key => $value) {
                    $updateRow = new \App\Model\Config;
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
        $global_config = new \App\Model\Config;
        $global_config = $global_config->get();

        if ($request->isPost()) {

            $allPostVars = $request->getParsedBody();

            $this->validator->validate($request, ['add_name' => V::slug()]);
            $this->validator->validate($request, ['add_description' => V::alnum()->length(1, 32)]);
            
            if ($allPostVars['add_type'] == "string") {
                // Check for HTML Tags
                if (strip_tags($allPostVars['add_value']) != $allPostVars['add_value']) {
                    $this->validator->addError('add_value', 'Please do not use any HTML Tags');
                    $this->logger->addWarning("possible scripting attack", array("message" => "HTML tags were blocked from being put into the config."));
                }
            } else if ($allPostVars['add_type'] == "timezone") {
                //Get Timezone List
                $tz_list = array();
                foreach ($timezones as $tkey => $tvalue) {
                    $tz_list[] = $tvalue['zone'];
                }

                if (in_array($allPostVars['add_value'], $tz_list)) {
                    $this->validator->addError('add_value', 'Not a valid global setting.');
                }
            } else {
                $this->validator->addError('add_value', 'Not a valid global setting.');
            }

            if ($this->validator->isValid()) {

                $configOption = new \App\Model\Config;
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