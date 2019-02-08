<?php

namespace Dappur\Controller\Admin;

use Dappur\Controller\Controller as Controller;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Menus extends Controller
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function view(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('pages.view', 'dashboard')) {
            return $check;
        }


        return $this->view->render(
            $response,
            'pages.twig',
            [
                "roles" => \Dappur\Model\Roles::get()
            ]
        );
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get(Request $request, Response $response)
    {
        $output['result'] = "error";
        $output['message'] = "An unknown error occured";

        if (!$this->auth->hasAccess('menus.view')) {
            $output['message'] = "Permission denied";
            return $response->withJson($output);
        }

        $menu = \Dappur\Model\Menus::find($request->getParam('menu_id'));

        if (!$menu) {
            $output['message'] = "Menu not found";
            return $response->withJson($output);
        }

        $output['result'] = "success";
        unset($output['message']);
        $output['menu']['id'] = $menu->id;
        $output['menu']['name'] = $menu->name;
        $output['menu']['json'] = json_decode($menu->json);
        return $response->withJson($output);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function update(Request $request, Response $response)
    {
        $output['result'] = "error";
        $output['message'] = "An unknown error occured";

        if (!$this->auth->hasAccess('menus.update')) {
            $output['message'] = "Permission denied";
            return $response->withJson($output);
        }

        $menu = \Dappur\Model\Menus::find($request->getParam('menu_id'));

        if (!$menu) {
            $output['message'] = "Menu not found";
            return $response->withJson($output);
        }

        $menu->json = $request->getParam('json');
        if ($menu->save()) {
            $output['result'] = "success";
            unset($output['message']);
            return $response->withJson($output);
        }

        return $response->withJson($output);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function menus(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('menus.create', 'dashboard')) {
            return $check;
        }

        $routes = $this->container->router->getRoutes();
        $routeNames = array();
        foreach ($routes as $route) {
            $routeNames[] = $route->getName();
        }
        asort($routeNames);

        return $this->view->render(
            $response,
            'menus.twig',
            [
                "roles" => \Dappur\Model\Roles::get(),
                "menus" => \Dappur\Model\Menus::get(),
                "routes" => $routeNames,
                "configOptions" => \Dappur\Model\Config::where('type_id', 6)->get()
            ]
        );
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function add(Request $request, Response $response)
    {
        $output['result'] = "error";
        $output['message'] = "An unknown error occured";

        if (!$this->auth->hasAccess('menus.create')) {
            $output['message'] = "Permission denied";
            return $response->withJson($output);
        }

        $checkName = \Dappur\Model\Menus::where('name', $request->getParam('name'))->first();

        if ($checkName) {
            $output['message'] = "There is already a menu with that name.";
            return $response->withJson($output);
        }

        $ins = new \Dappur\Model\Menus;
        $ins->name = $request->getParam('name');

        if ($ins->save()) {
            $output['result'] = "success";
            unset($output['message']);
            $output['menu']['id'] = $ins->id;
            $output['menu']['name'] = $ins->name;
            $output['menu']['json'] = json_decode($ins->json);
            return $response->withJson($output);
        }

        return $response->withJson($output);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function delete(Request $request, Response $response)
    {
        $output['result'] = "error";
        $output['message'] = "An unknown error occured";

        if (!$this->auth->hasAccess('menus.delete')) {
            $output['message'] = "Permission denied";
            return $response->withJson($output);
        }

        $menu = \Dappur\Model\Menus::find($request->getParam('menu_id'));

        if (!$menu) {
            $output['message'] = "Menu doesn't exist.";
            return $response->withJson($output);
        }

        if ($menu->id == 1 || $menu->id == 2) {
            $output['message'] = "You cannot delete the default menu.";
            return $response->withJson($output);
        }

        if ($menu->delete()) {
            $output['result'] = "success";
            unset($output['message']);
            return $response->withJson($output);
        }

        return $response->withJson($output);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function export(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('menus.view', 'admin-menus')) {
            return $check;
        }

        if ($request->getparam("all")) {
            $menu = \Dappur\Model\Menus::get();
        }

        if (is_numeric($request->getparam("menu_id"))) {
            $menu = \Dappur\Model\Menus::where('id', $request->getParam('menu_id'))->get();
        }

        if (!$menu) {
            $this->flash('danger', 'Export unsuccessful.  Menu Not Found.');
            return $this->redirect($response, 'admin-menus');
        }

        $final = array();
        $final['framework'] = $this->settings['framework'];
        $final['version'] = $this->settings['version'];
        $final['menus'] = $menu->toArray();

        $tempFile = tmpfile();
        fwrite($tempFile, json_encode($final, JSON_UNESCAPED_SLASHES));
        $metaDatas = stream_get_meta_data($tempFile);
        $filePath = $metaDatas['uri'];
        return \Dappur\Dappurware\FileResponse::getResponse(
            $response,
            $filePath,
            "menu-dappur" .
            "-" . date("Y-m-d-H-i-s") . ".json"
        );
        fclose($tempFile);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function import(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('menus.import', 'admin-menus')) {
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
            $this->flash('success', 'Manu imported successfully');
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

        foreach ($decoded->menus as $value) {
            $route = $this->importMenu($value, $overwrite);
        }

        $return->status = true;
        return $return;
    }

    private function importMenu($value, $overwrite = 0)
    {
        // Check if Exists
        $menu = \Dappur\Model\Menus::where('id', $value->id)->first();

        // Update Group if Overwrite
        if ($overwrite && $menu) {
            $menu->name = $value->name;
            $menu->json = $value->json;
            $menu->save();
        }

        if (!$menu) {
            // Create Group
            $menu = new \Dappur\Model\Menus;
            if (isset($value->id)) {
                $menu->id = $value->id;
            }
            $menu->name = $value->name;
            $menu->json = $value->json;
            $menu->save();
        }
        return $menu;
    }

    private function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}
