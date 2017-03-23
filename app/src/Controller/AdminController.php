<?php

namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

class AdminController extends Controller{

    public function dashboard(Request $request, Response $response){

        return $this->view->render($response, 'Admin/dashboard.twig');

    }

    public function users(Request $request, Response $response){


        $users = new \App\Model\Users;

        return $this->view->render($response, 'Admin/users.twig', ["users" => $users->get()]);

    }

    public function usersEdit(Request $request, Response $response, $username){
        
        $users = new \App\Model\Users;
        $user = $users->where('username', '=', $username)->first();

        $roles = new \App\Model\Roles;

        if ($user) {
            return $this->view->render($response, 'Admin/users-edit.twig', ['user' => $user, 'roles' => $roles]);
        }else{
            $this->flash('danger', 'Sorry, that user was not found.');
            return $response->withRedirect($this->router->pathFor('admin-users'));
        }
        
    }

    public function settings(Request $request, Response $response){

        return $this->view->render($response, 'Admin/settings.twig');

    }

    public function settingsGlobal(Request $request, Response $response){

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