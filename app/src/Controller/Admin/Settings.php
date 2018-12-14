<?php

namespace Dappur\Controller\Admin;

use Dappur\Controller\Controller as Controller;
use Dappur\Dappurware\FileResponse;
use Dappur\Dappurware\Settings as S;
use Dappur\Model\Config;
use Dappur\Model\ConfigGroups;
use Dappur\Model\ConfigTypes;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as V;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Settings extends Controller
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function developerLogs(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('settings.developer', 'dashboard')) {
            return $check;
        }

        $logFiles = scandir(__DIR__ . '/../../../storage/log/monolog');

        foreach ($logFiles as $key => $value) {
            if ($value == ".." || $value == "." || $value == ".gitkeep") {
                unset($logFiles[$key]);
            }
        }
        
        return $this->view->render($response, 'developer-logs.twig', array("logFiles" => $logFiles));
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function export(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('settings.export', 'dashboard')) {
            return $check;
        }

        $group =  $request->getParam('group_id');
        $page =  $request->getParam('page_name');
        $all =  $request->getParam('all');

        if ($group) {
            $export = ConfigGroups::with('config')->where('id', $group)->get();

            if (!$export) {
                $this->flash('danger', 'Export unsuccessful.  Group Not Found.');
                return $this->redirect($response, 'settings-global');
            }
            $fileDesc = strtolower($export[0]->name);
        }

        if ($page) {
            $export = ConfigGroups::with('config')->where("page_name", $page)->get();

            if (!$export) {
                $this->flash('danger', 'Export unsuccessful.  Page Not Found.');
                return $this->redirect($response, 'settings-global');
            }
            $fileDesc = $page;
        }

        if ($all) {
            $export = ConfigGroups::with('config')->get();

            if (!$export) {
                $this->flash('danger', 'Export unsuccessful.  Page Not Found.');
                return $this->redirect($response, 'settings-global');
            }
            $fileDesc = "all";
        }

        $final = array();
        $final['framework'] = $this->settings['framework'];
        $final['version'] = $this->settings['version'];
        $final['config'] = $export->toArray();

        $tempFile = tmpfile();
        fwrite($tempFile, json_encode($final, JSON_PRETTY_PRINT));
        $metaDatas = stream_get_meta_data($tempFile);
        $filePath = $metaDatas['uri'];
        return FileResponse::getResponse(
            $response,
            $filePath,
            $this->settings['framework'] .
            "-" .
            preg_replace('/[^a-zA-Z0-9]/', "-", $fileDesc) .
            "-" . date("Y-m-d-H-i-s") . ".json"
        );
        fclose($tempFile);
    }
    
    public function settingsGlobal(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('settings.view', 'dashboard')) {
            return $check;
        }

        $timezones = S::getTimezones();
        $themeList = S::getThemeList();
        $bootswatch = S::getBootswatch();
        $settingsGrouped = S::getSettingsByGroup();
        $types = ConfigTypes::orderBy('name')->get();
        $groups = ConfigGroups::orderBy('name')->get();

        $allRoutes = $this->getRouteNames();

        if ($request->isPost()) {
            if ($check = $this->sentinel->hasPerm('settings.update', 'dashboard')) {
                return $check;
            }

            $allPostVars = $request->getParsedBody();

            if ($this->validator->isValid()) {
                foreach ($allPostVars as $key => $value) {
                    if (isset($value) || is_null($value)) {
                        $value = "";
                    }
                    Config::where('name', $key)->update(['value' => $value]);
                }

                $this->flash('success', 'Global settings have been updated successfully.');
                return $this->redirect($response, 'settings-global');
            }
        }

        return $this->view->render(
            $response,
            'settings-global.twig',
            array(
                "settingsGrouped" => $settingsGrouped,
                "configTypes" => $types,
                "configGroups" => $groups,
                "themeList" => $themeList,
                "timezones" => $timezones,
                "bsThemes" => $bootswatch,
                "allRoutes" => $allRoutes,
                "menus" => \Dappur\Model\Menus::get()
            )
        );
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function save(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('settings.update', 'dashboard')) {
            return $check;
        }

        $output = array();
        $output['status'] = "error";
        $output['message'] = "An unknown error occured.";

        foreach ($request->getParams() as $key => $value) {
            $checkItem = \Dappur\Model\Config::where('name', $key)->first();
            if ($checkItem) {
                $checkItem->value = $value;
                if ($checkItem->save()) {
                    $output['status'] = "success";
                    $output['message'] = $checkItem->name . " has been successfully updated.";
                    return json_encode($output);
                }
            }
        }

        return json_encode($output);
    }

    public function settingsGlobalAdd(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('settings.create', 'dashboard')) {
            return $check;
        }

        $allPostVars = $request->getParsedBody();

        $this->validator->validate(
            $request,
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

        $checkConfig = Config::where('name', '=', $allPostVars['add_name'])->get()->count();
        if ($checkConfig > 0) {
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

            if ($configOption->group->page_name) {
                return $this->redirect(
                    $response,
                    'settings-page',
                    array(
                        'page_name' => $configOption->group->page_name
                    )
                );
            }

            return $this->redirect($response, 'settings-global');
        }

        $allRoutes = $this->getRouteNames();

        $timezones = S::getTimezones();
        $themeList = S::getThemeList();
        $bootswatch = S::getBootswatch();
        $settingsGrouped = S::getSettingsByGroup();

        $types = ConfigTypes::orderBy('name')->get();

        $groups = ConfigGroups::orderBy('name')->get();

        return $this->view->render(
            $response,
            'settings-global.twig',
            array(
                "settingsGrouped" => $settingsGrouped,
                "configTypes" => $types,
                "configGroups" => $groups,
                "themeList" => $themeList,
                "timezones" => $timezones,
                "bsThemes" => $bootswatch,
                "requestParams" => $allPostVars,
                "allRoutes" => $allRoutes,
                "menus" => \Dappur\Model\Menus::get()
            )
        );
    }

    public function settingsGlobalAddGroup(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('settings.create', 'dashboard')) {
            return $check;
        }

        $allPostVars = $request->getParsedBody();

        $allRoutes = $this->getRouteNames();

        $this->validator->validate(
            $request,
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

        $checkGroup = ConfigGroups::where('name', '=', $allPostVars['group_name'])->get()->count();
        if ($checkGroup > 0) {
            $this->validator->addError('group_name', 'Name is already in use.');
        }

        if ($allPostVars['page'] == 1) {
            $this->validator->validate(
                $request,
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

        if ($this->validator->isValid()) {
            $configOption = new ConfigGroups;
            $configOption->name = $allPostVars['group_name'];
            if ($allPostVars['page'] == 1) {
                $configOption->page_name = $allPostVars['page_name'];
                $configOption->description = $allPostVars['description'];
                $configOption->save();
                $this->flash('success', 'Config group successfully added.');
                return $this->redirect($response, 'settings-page', array('page_name' => $allPostVars['page_name']));
            }
            
            $configOption->save();
            $this->flash('success', 'Config group successfully added.');
            return $this->redirect($response, 'settings-global');
        }

        $timezones = S::getTimezones();
        $themeList = S::getThemeList();
        $bootswatch = S::getBootswatch();
        $settingsGrouped = S::getSettingsByGroup();

        return $this->view->render(
            $response,
            'settings-global.twig',
            array(
                "settingsGrouped" => $settingsGrouped,
                "themeList" => $themeList,
                "timezones" => $timezones,
                "bsThemes" => $bootswatch,
                "allRoutes" => $allRoutes,
                "requestParams" => $allPostVars,
                "menus" => \Dappur\Model\Menus::get()
            )
        );
    }

    public function settingsGlobalDeleteGroup(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('settings.delete', 'dashboard')) {
            return $check;
        }

        $allPostVars = $request->getParsedBody();

        $checkGroup = ConfigGroups::find($allPostVars['group_id']);

        if (!$checkGroup) {
            $this->flash('danger', 'Group does not exist.');
            return $this->redirect($response, 'settings-global');
        }

        $checkConfig = Config::where('group_id', '=', $allPostVars['group_id'])->get()->count();
        if ($checkConfig > 0) {
            $this->flash('danger', 'You cannot delete a group with config items in it.');
            return $this->redirect($response, 'settings-global');
        }

        if ($checkGroup->delete()) {
            $this->flash('success', 'Group was successfully deleted.');
            return $this->redirect($response, 'settings-global');
        }
        
        $this->flash('danger', 'There was an error deleting the group.');
        return $this->redirect($response, 'settings-global');
    }

    public function settingsPage(Request $request, Response $response, $pageName)
    {
        if ($check = $this->sentinel->hasPerm('settings.page', 'dashboard')) {
            return $check;
        }

        $pageSettings = ConfigGroups::where('page_name', '=', $pageName)->with('config')->get();

        $timezones = S::getTimezones();
        $themeList = S::getThemeList();

        $types = ConfigTypes::orderBy('name')->get();

        $groups = ConfigGroups::orderBy('name')->get();

        $allPostVars = $request->getParsedBody();

        if ($request->isPost()) {
            if ($check = $this->sentinel->hasPerm('settings.update', 'dashboard')) {
                return $check;
            }

            if ($this->validator->isValid()) {
                foreach ($allPostVars as $key => $value) {
                    Config::where('name', $key)->update(['value' => $value]);
                }

                $this->flash('success', 'Page settings have been updated successfully.');
                return $this->redirect($response, 'settings-page', array('page_name' => $pageName));
            }
        }

        return $this->view->render(
            $response,
            'settings-page.twig',
            array(
                "settingsGrouped" => $pageSettings,
                "configTypes" => $types,
                "configGroups" => $groups,
                "themeList" => $themeList,
                "timezones" => $timezones,
                "requestParams" => $allPostVars,
                "pageName" => $pageName,
                "menus" => \Dappur\Model\Menus::get()
            )
        );
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function import(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('settings.import', 'dashboard')) {
            return $check;
        }

        $return = new \stdClass();
        $return->status = "error";

        if (!$request->getUploadedFiles()['import_file']) {
            $return->message = "No file detected.";
            return $response->withJSON($return, 200, JSON_UNESCAPED_UNICODE);
        }

        $file = $request->getUploadedFiles()['import_file'];

        $json = $file->getStream();

        if (!$this->isJson($json)) {
            $return->message = "error - not a valid json file";
            return $response->withJSON($return, 200, JSON_UNESCAPED_UNICODE);
        }
        $overwrite = false;
        if ($request->getParam('overwrite')) {
            $overwrite = true;
        }

        $import = $this->processImport($json, $overwrite);

        if ($import->status) {
            $return->status = "success";
            $this->flash('success', 'Settings imported successfully');
        }

        if (!$import->status) {
            $return->message = $import->message;
        }
        
        return $response->withJSON($return, 200, JSON_UNESCAPED_UNICODE);
    }

    public function processImport($json, $overwrite = 0)
    {
        $decoded = json_decode($json);

        // Create Return Object
        $return = new \stdClass();
        $return->status = false;

        if (!$decoded->framework || $decoded->framework != $this->settings['framework']) {
            $return->message = "Framework mismatch.";
            return $return;
        }

        if (!$decoded->version || $decoded->version != $this->settings['version']) {
            $return->message = "Version mismatch.";
            return $return;
        }

        foreach ($decoded->config as $value) {
            $group = $this->importGroup($value, $overwrite);
            $this->importConfig($group, $value->config, $overwrite);
        }

        $return->status = true;
        return $return;
    }

    private function importGroup($value, $overwrite = 0)
    {
        // Check if Exists
        $group = \Dappur\Model\ConfigGroups::find($value->id);

        // Update Group if Overwrite
        if ($overwrite && $group) {
            $group->name = $value->name;
            $group->description = $value->description;
            $group->page_name = $value->page_name;
            $group->save();
        }

        if (!$group) {
            // Create Group
            $group = new \Dappur\Model\ConfigGroups;
            $group->name = $value->name;
            $group->description = $value->description;
            $group->page_name = $value->page_name;
            $group->save();
        }
        return $group;
    }

    private function importConfig($group, $config, $overwrite = 0)
    {
        // Process Config Items
        foreach ($config as $cfg) {
            // Check if Item Exists
            $config = \Dappur\Model\Config::where('name', $cfg->name)->where('group_id', $group->id)->first();

            // Update Config if Overwrite
            if ($overwrite && $config) {
                $config->group_id = $group->id;
                $config->type_id = $cfg->type_id;
                $config->name = $cfg->name;
                $config->description = $cfg->description;
                $config->value = $cfg->value;
                $config->save();
            }

            if (!$config) {
                // Create Config Item
                $config = new \Dappur\Model\Config;
                $config->group_id = $group->id;
                $config->type_id = $cfg->type_id;
                $config->name = $cfg->name;
                $config->description = $cfg->description;
                $config->value = $cfg->value;
                $config->save();
            }
        }
    }

    private function getRouteNames()
    {
        $routes = $this->container->router->getRoutes();
        $allRoutes = array();
        foreach ($routes as $route) {
            $allRoutes[] = $route->getName();
        }

        asort($allRoutes);

        return $allRoutes;
    }

    private function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}
