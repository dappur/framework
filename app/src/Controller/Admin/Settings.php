<?php

namespace Dappur\Controller\Admin;

use Dappur\Dappurware\Settings as S;
use Dappur\Model\Config;
use Dappur\Model\ConfigGroups;
use Dappur\Model\ConfigTypes;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as V;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
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

    public function settingsDeveloper(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('settings.developer', 'dashboard') and $this->config['showInAdmin']) {
            return $check;
        }

        $settingsFile = S::getSettingsFile();

        $requestParams = $request->getParsedBody();
        
        return $this->view->render(
            $response,
            'settings-developer.twig',
            array(
                "settingsFile" => $settingsFile,
                "postVars" => $requestParams
            )
        );
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
                "allRoutes" => $allRoutes
            )
        );
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

            if (isset($allPostVars['page_name'])) {
                return $this->redirect(
                    $response,
                    'settings-page',
                    array(
                        'page_name' => $allPostVars['page_name']
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
                "allRoutes" => $allRoutes
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

        $checkName = ConfigGroups::where('page_name', '=', $allPostVars['page_name'])->get()->count();
        if ($checkName > 0) {
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
                "requestParams" => $allPostVars
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

        $pageSettings = ConfigGroups::where('page_name', '=', $pageName)->with('config')->skip(0)->take(1)->get();

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
                "requestParams" => $allPostVars
            )
        );
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
}
