<?php

namespace Dappur\Controller;

use Dappur\Dappurware\Sentinel as S;
use Dappur\Dappurware\Settings;
use Dappur\Model\Config;
use Dappur\Model\ConfigGroups;
use Dappur\Model\ConfigTypes;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as V;

class AdminSettings extends Controller {

    private function getRouteNames(){
        $routes = $this->container->router->getRoutes();
        $all_routes = array();
        foreach ($routes as $route) {
            $all_routes[] = $route->getName();
        }

        asort($all_routes);

        return $all_routes;
    }

    public function settingsDeveloper(Request $request, Response $response){

        $sentinel = new S($this->container);
        if(!$sentinel->hasPerm('settings.developer')){
            return $this->redirect($response, 'dashboard');
        }

        $settings_file = Settings::getSettingsFile();

        $requestParams = $request->getParsedBody();
        
        return $this->view->render($response, 'settings-developer.twig', array("settingsFile" => $settings_file, "postVars" => $requestParams));
    }
	
    public function settingsGlobal(Request $request, Response $response){

        $sentinel = new S($this->container);
        if(!$sentinel->hasPerm('settings.view')){
            return $this->redirect($response, 'dashboard');
        }

        $timezones = Settings::getTimezones();
        $theme_list = Settings::getThemeList();
        $bootswatch = Settings::getBootswatch();
        $settings_grouped = Settings::getSettingsByGroup();
        $types = ConfigTypes::orderBy('name')->get();
        $groups = ConfigGroups::orderBy('name')->get();

        $all_routes = $this->getRouteNames();

        if ($request->isPost()) {
            $sentinel = new S($this->container);
            $sentinel->hasPerm('settings.update');

            $allPostVars = $request->getParsedBody();

            // Validate Domain
            if (array_key_exists('domain', $allPostVars)){
                $this->validator->validate($request, ['domain' => array('rules' => V::domain(), 'messages' => array('domain' => 'Please enter a valid domain.'))]);
            }

            // Validate Reply To Email
            if (array_key_exists('from-email', $allPostVars)){
                $this->validator->validate($request, ['from-email' => array('rules' => V::noWhitespace()->email(), 'messages' => array('noWhitespace' => 'Must not contain any spaces.', 'email' => 'Enter a valid email address.'))]);
            }

            // Validate Google Analytics
            if (isset($allPostVars['ga']) && !empty($allPostVars['ga'])){
                $this->validator->validate($request, ['ga' => array('rules' => V::regex('/(UA|YT|MO)-\d+-\d+/'), 'messages' => array('regex' => 'Enter a valid UA Tracking Code'))]);
            }

            // Additional Validation
            foreach ($allPostVars as $key => $value) {
                if ($key == "theme" && !in_array($value, $theme_list)) {
                    $this->validator->addError($key, 'Not a valid global setting.');
                }
            }


            if ($this->validator->isValid()) {

                foreach ($allPostVars as $key => $value) {
                    Config::where('name', $key)->update(['value' => $value]);
                }

                $this->flash('success', 'Global settings have been updated successfully.');
                return $this->redirect($response, 'settings-global');
            }

            
        }

        return $this->view->render($response, 'settings-global.twig', array("settingsGrouped" => $settings_grouped, "configTypes" => $types, "configGroups" => $groups, "themeList" => $theme_list, "timezones" => $timezones, "bsThemes" => $bootswatch, "allRoutes" => $all_routes));

    }

    public function settingsGlobalAdd(Request $request, Response $response){

        $sentinel = new S($this->container);
        if(!$sentinel->hasPerm('settings.add')){
            return $this->redirect($response, 'dashboard');
        }

        $allPostVars = $request->getParsedBody();

        $this->validator->validate($request, 
            array(
                'add_name' => array(
                    'rules' => V::slug()->length(4, 32), 
                    'messages' => array(
                        'slug' => 'May only contain lowercase letters, numbers and hyphens.', 
                        'length' => 'Must be between 4 and 32 characters.'
                    )
                ),
                'add_description' => array(
                    'rules' => V::alnum()->length(4, 32), 
                    'messages' => array(
                        'alnum' => 'May only contain letters and numbers.', 
                        'length' => 'Must be between 4 and 32 characters.'
                    )
                )
            )
        );

        $check_config = Config::where('name', '=', $allPostVars['add_name'])->get()->count();
        if ($check_config > 0) {
            $this->validator->addError('add_name', 'Name is already in use.');
        }

        if ($this->validator->isValid()) {

            $configOption = new Config;
            $configOption->name = $allPostVars['add_name'];
            $configOption->description = $allPostVars['add_description'];
            $configOption->type_id = $allPostVars['add_type'];
            $configOption->group_id = $allPostVars['add_group'];
            $configOption->value = $allPostVars['add_value'];
            $configOption->save();

            $this->flash('success', 'Global settings successfully added.');

            if (isset($allPostVars['page_name'])) {
                return $this->redirect($response, 'settings-page', array('page_name' => $allPostVars['page_name']));
            }else{
                return $this->redirect($response, 'settings-global');
            }

            
        }

        $all_routes = $this->getRouteNames();

        $timezones = Settings::getTimezones();
        $theme_list = Settings::getThemeList();
        $bootswatch = Settings::getBootswatch();
        $settings_grouped = Settings::getSettingsByGroup();

        $types = ConfigTypes::orderBy('name')->get();

        $groups = ConfigGroups::orderBy('name')->get();

        return $this->view->render($response, 'settings-global.twig', array("settingsGrouped" => $settings_grouped, "configTypes" => $types, "configGroups" => $groups, "themeList" => $theme_list, "timezones" => $timezones, "bsThemes" => $bootswatch, "requestParams" => $allPostVars, "allRoutes" => $all_routes));

    }

    public function settingsGlobalAddGroup(Request $request, Response $response){

        $sentinel = new S($this->container);
        if(!$sentinel->hasPerm('settings.group.add')){
            return $this->redirect($response, 'dashboard');
        }

        $allPostVars = $request->getParsedBody();

        $all_routes = $this->getRouteNames();

        $this->validator->validate($request, 
            array(
                'group_name' => array(
                    'rules' => V::alnum()->length(4, 32), 
                    'messages' => array(
                        'alnum' => 'May only contain lowercase letters, numbers and hyphens.', 
                        'length' => 'Must be between 4 and 32 characters.'
                    )
                )
            )
        );

        $check_group = ConfigGroups::where('name', '=', $allPostVars['group_name'])->get()->count();
        if ($check_group > 0) {
            $this->validator->addError('group_name', 'Name is already in use.');
        }

        if ($allPostVars['page'] == 1) {
            $this->validator->validate($request, 
                array(
                    'page_name' => array(
                        'rules' => V::slug()->length(2, 32), 
                        'messages' => array(
                            'slug' => 'Alphanumeric and can contain hyphens.',
                            'length' => 'Must be between 2 and 32 characters.'
                        )
                    ),
                    'description' => array(
                        'rules' => V::alnum('\'".')->length(2, 255), 
                        'messages' => array(
                            'alnum' => 'May only contain letters, numbers and \'".', 
                            'length' => 'Must be between 2 and 255 characters.'
                        )
                    )
                )
            );
        }

        $check_name = ConfigGroups::where('page_name', '=', $allPostVars['page_name'])->get()->count();
        if ($check_name > 0) {
            $this->validator->addError('page_name', 'Name is already in use.');
        }

        if ($this->validator->isValid()) {

            $configOption = new ConfigGroups;
            $configOption->name = $allPostVars['group_name'];
            if ($allPostVars['page'] == 1) {
                $configOption->page_name = $allPostVars['page_name'];
                $configOption->description = $allPostVars['description'];
                $configOption->save();
                $this->flash('success', 'Config group successfully added.');
                return $this->redirect($response, 'settings-page', array('page_name' => $allPostVars['page_name']));
            }else{
                $configOption->save();
                $this->flash('success', 'Config group successfully added.');
                return $this->redirect($response, 'settings-global');
            }
        }

        $timezones = Settings::getTimezones();
        $theme_list = Settings::getThemeList();
        $bootswatch = Settings::getBootswatch();
        $settings_grouped = Settings::getSettingsByGroup();

        $global_config = Config::get();

        return $this->view->render($response, 'settings-global.twig', array("settingsGrouped" => $settings_grouped, "configTypes" => $types, "configGroups" => $groups, "themeList" => $theme_list, "timezones" => $timezones, "bsThemes" => $bootswatch, "allRoutes" => $all_routes, "requestParams" => $allPostVars));

    }

    public function settingsGlobalDeleteGroup(Request $request, Response $response){

        $sentinel = new S($this->container);
        if(!$sentinel->hasPerm('settings.group.delete')){
            return $this->redirect($response, 'dashboard');
        }

        $allPostVars = $request->getParsedBody();

        $check_group = ConfigGroups::find($allPostVars['group_id']);

        if (!$check_group) {
            $this->flash('danger', 'Group does not exist.');
            return $this->redirect($response, 'settings-global');
        }else{
            $check_config = Config::where('group_id', '=', $allPostVars['group_id'])->get()->count();

            if ($check_config > 0) {
                $this->flash('danger', 'You cannot delete a group with config items in it.');
                return $this->redirect($response, 'settings-global');
            }else{
                $check_group->delete();
                $this->flash('success', 'Group was successfully deleted.');
                return $this->redirect($response, 'settings-global');
            }
        }

    }

    public function settingsPage(Request $request, Response $response, $page_name){

        $page_settings = ConfigGroups::where('page_name', '=', $page_name)->with('config')->skip(0)->take(1)->get();

        $timezones = Settings::getTimezones();
        $theme_list = Settings::getThemeList();
        $bootswatch = Settings::getBootswatch();
        $settings_grouped = Settings::getSettingsByGroup();

        $types = ConfigTypes::orderBy('name')->get();

        $groups = ConfigGroups::orderBy('name')->get();

        $allPostVars = $request->getParsedBody();

        if ($request->isPost()) {
            $sentinel = new S($this->container);
            $sentinel->hasPerm('settings.update');

            // Validate Domain
            if (array_key_exists('domain', $allPostVars)){
                $this->validator->validate($request, ['domain' => array('rules' => V::domain(), 'messages' => array('domain' => 'Please enter a valid domain.'))]);
            }

            // Validate Reply To Email
            if (array_key_exists('from-email', $allPostVars)){
                $this->validator->validate($request, ['from-email' => array('rules' => V::noWhitespace()->email(), 'messages' => array('noWhitespace' => 'Must not contain any spaces.', 'email' => 'Enter a valid email address.'))]);
            }

            // Validate Google Analytics
            if (isset($allPostVars['ga']) && !empty($allPostVars['ga'])){
                $this->validator->validate($request, ['ga' => array('rules' => V::regex('/(UA|YT|MO)-\d+-\d+/'), 'messages' => array('regex' => 'Enter a valid UA Tracking Code'))]);
            }

            // Additional Validation
            foreach ($allPostVars as $key => $value) {
                if ($key == "theme" && !in_array($value, $theme_list)) {
                    $this->validator->addError($key, 'Not a valid global setting.');
                }
            }


            if ($this->validator->isValid()) {

                foreach ($allPostVars as $key => $value) {
                    Config::where('name', $key)->update(['value' => $value]);
                }

                $this->flash('success', 'Page settings have been updated successfully.');
                return $this->redirect($response, 'settings-page', array('page_name' => $page_name));
            }

            
        }

        return $this->view->render($response, 'settings-page.twig', array("settingsGrouped" => $page_settings, "configTypes" => $types, "configGroups" => $groups, "themeList" => $theme_list, "timezones" => $timezones, "requestParams" => $allPostVars));
        
    }

}
