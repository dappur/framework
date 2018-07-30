<?php

namespace Dappur\Controller\Admin;

use Carbon\Carbon;
use Dappur\Dappurware\Utils;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class Menus extends Controller
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function view(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('pages.view')) {
            return $check;
        }

        return $this->view->render(
            $response,
            'pages.twig',
            ["roles" => \Dappur\Model\Roles::get()]
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
        if ($check = $this->sentinel->hasPerm('menus.create')) {
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
                "routes" => $routeNames
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

        $menu = \Dappur\Model\Menus::find($request->getParam('menu_id'));

        if (!$menu) {
            $this->flash('danger', 'Export unsuccessful.  Menu Not Found.');
            return $this->redirect($response, 'admin-menus');
        }

        $final = array();
        $final['framework'] = $this->settings['framework'];
        $final['version'] = $this->settings['version'];
        $final['menu']['id'] = $menu->id;
        $final['menu']['name'] = $menu->name;
        $final['menu']['json'] = json_decode($menu->json);
        $final['menu']['updated_at'] = $menu->updated_at;
        $final['menu']['created_at'] = $menu->created_at;

        $tempFile = tmpfile();
        fwrite($tempFile, json_encode($final, JSON_PRETTY_PRINT));
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
}
