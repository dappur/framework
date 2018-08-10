<?php

namespace Dappur\Controller\Admin;

use Carbon\Carbon;
use Dappur\Dappurware\FileResponse;
use Dappur\Dappurware\Utils;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class Pages extends Controller
{
    public function datatables(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('pages.view', 'dashboard')) {
            return $check;
        }
  
        $totalData = \Dappur\Model\Routes::count();
            
        $totalFiltered = $totalData;

        $limit = $request->getParam('length');
        $start = $request->getParam('start');
        $order = $request->getParam('columns')[$request->getParam('order')[0]['column']]['data'];
        $dir = $request->getParam('order')[0]['dir'];

        $users = \Dappur\Model\Routes::select('id', 'name', 'pattern', 'permission', 'sidebar', 'header', 'status')
            ->with('roles')
            ->skip($start)
            ->take($limit)
            ->orderBy($order, $dir);
            
        if (!empty($request->getParam('search')['value'])) {
            $search = $request->getParam('search')['value'];

            $users =  $users->where('id', 'LIKE', "%{$search}%")
                    ->orWhere('name', 'LIKE', "%{$search}%")
                    ->orWhere('pattern', 'LIKE', "%{$search}%")
                    ->orWhere('permission', 'LIKE', "%{$search}%")
                    ->orWhereHas(
                        'roles',
                        function ($query) use ($search) {
                            $query->where('name', 'LIKE', "%{$search}%");
                        }
                    );

            $totalFiltered = \Dappur\Model\Routes::where('id', 'LIKE', "%{$search}%")
                    ->orWhere('name', 'LIKE', "%{$search}%")
                    ->orWhere('pattern', 'LIKE', "%{$search}%")
                    ->orWhere('permission', 'LIKE', "%{$search}%")
                    ->orWhereHas(
                        'roles',
                        function ($query) use ($search) {
                            $query->where('name', 'LIKE', "%{$search}%");
                        }
                    )
                    ->count();
        }
          
        $jsonData = array(
            "draw"            => intval($request->getParam('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $users->get()->toArray()
            );

        return $response->withJSON(
            $jsonData,
            200
        );
    }

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
            ["roles" => \Dappur\Model\Roles::get()]
        );
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function add(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('pages.create', 'dashboard')) {
            return $check;
        }

        if ($request->isPost()) {
            $routes = $this->container->router->getRoutes();
            $routeNames = array();
            $routePatterns = array();
            foreach ($routes as $route) {
                $routeNames[] = $route->getName();
                $routePatterns[] = $route->getPattern();
            }
            if (in_array($request->getParam('name'), $routeNames)) {
                $this->validator->addError('name', 'Name already exists');
            }

            if (in_array("/" . $request->getParam('pattern'), $routePatterns)) {
                $this->validator->addError('pattern', 'Pattern already exists.');
            }
            
            // Add New Page
            $insPage = new \Dappur\Model\Routes;
            $insPage->name = $request->getParam('name');
            $insPage->pattern = $request->getParam('pattern');
            $insPage->content = $request->getParam('page_content');
            if ($request->getParam('css') && !empty($request->getParam('css'))) {
                $insPage->css = $request->getParam('css');
            }
            if ($request->getParam('js') && !empty($request->getParam('js'))) {
                $insPage->js = $request->getParam('js');
            }
            if ($request->getParam('permission') && !empty($request->getParam('permission'))) {
                $insPage->permission = $request->getParam('permission');
            }
            if ($request->getParam('status')) {
                $insPage->status = 1;
            }
            if ($request->getParam('sidebar')) {
                $insPage->sidebar = 1;
            }
            if ($request->getParam('header')) {
                $insPage->header = 1;
                $insPage->header_text = $request->getParam('header_text');
                $insPage->header_image = $request->getParam('header_image');
            }

            $insPage->save();
            
            
            foreach ($request->getParam('roles') as $value) {
                $insRole = new \Dappur\Model\RoleRoutes;
                $insRole->role_id = $value;
                $insRole->route_id = $insPage->id;
                $insRole->save();
            }

            $this->flash('success', $insPage->name . " has been successfully created.");
            return $this->redirect($response, 'admin-pages');
        }

        return $this->view->render(
            $response,
            'pages-add.twig',
            ["roles" => \Dappur\Model\Roles::get()]
        );
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function edit(Request $request, Response $response, $routeId)
    {
        if ($check = $this->sentinel->hasPerm('pages.update', 'dashboard')) {
            return $check;
        }

        //Check Route
        $routeCheck = \Dappur\Model\Routes::with('roles')->find($routeId);
        if (!$routeCheck) {
            $this->flash('danger', "Route doesnt exist.");
            return $this->redirect($response, 'admin-pages', 'dashboard');
        }

        if ($request->isPost()) {
            $routes = $this->container->router->getRoutes();
            $routeNames = array();
            $routePatterns = array();
            foreach ($routes as $route) {
                $routeNames[] = $route->getName();
                $routePatterns[] = $route->getPattern();
            }
            if (in_array($request->getParam('name'), $routeNames) && $routeCheck->name != $request->getParam('name')) {
                $this->validator->addError('name', 'Name already exists');
            }

            if (in_array("/" . $request->getParam('pattern'), $routePatterns) && $routeCheck->pattern != $request->getParam('pattern')) {
                $this->validator->addError('pattern', 'Pattern already exists.');
            }
            
            $routeCheck->name = $request->getParam('name');
            $routeCheck->pattern = $request->getParam('pattern');
            $routeCheck->content = $request->getParam('page_content');
            $routeCheck->css = null;
            if ($request->getParam('css') && !empty($request->getParam('css'))) {
                $routeCheck->css = $request->getParam('css');
            }
            $routeCheck->js = null;
            if ($request->getParam('js') && !empty($request->getParam('js'))) {
                $routeCheck->js = $request->getParam('js');
            }
            $routeCheck->permission = null;
            if ($request->getParam('permission') && !empty($request->getParam('permission'))) {
                $routeCheck->permission = $request->getParam('permission');
            }
            $routeCheck->status = 0;
            if ($request->getParam('status')) {
                $routeCheck->status = 1;
            }
            $routeCheck->sidebar = 0;
            if ($request->getParam('sidebar')) {
                $routeCheck->sidebar = 1;
            }
            $routeCheck->header = 0;
            $routeCheck->header_text = null;
            $routeCheck->header_image = null;
            if ($request->getParam('header')) {
                $routeCheck->header = 1;
                $routeCheck->header_text = $request->getParam('header_text');
                $routeCheck->header_image = $request->getParam('header_image');
            }

            $routeCheck->save();
            
            \Dappur\Model\RoleRoutes::where('route_id', $routeCheck->id)->delete();
            foreach ($request->getParam('roles') as $value) {
                $insRole = new \Dappur\Model\RoleRoutes;
                $insRole->role_id = $value;
                $insRole->route_id = $routeCheck->id;
                $insRole->save();
            }

            $this->flash('success', $routeCheck->name . " has been successfully updated.");
            return $this->redirect($response, 'admin-pages');
        }

        return $this->view->render(
            $response,
            'pages-edit.twig',
            ["roles" => \Dappur\Model\Roles::get(), "route" => $routeCheck]
        );
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function export(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('pages.export', 'admin-pages')) {
            return $check;
        }

        $page =  $request->getParam('page_name');
        $all =  $request->getParam('all');

        if ($page) {
            $export = \Dappur\Model\Routes::with('roles')->where("name", $page)->get();

            if (!$export) {
                $this->flash('danger', 'Export unsuccessful.  Page Not Found.');
                return $this->redirect($response, 'admin-pages');
            }
            $fileDesc = $page;
        }

        if ($all) {
            $export = \Dappur\Model\Routes::with('roles')->get();

            if (!$export) {
                $this->flash('danger', 'Export unsuccessful.  Page Not Found.');
                return $this->redirect($response, 'admin-pages');
            }
            $fileDesc = "all";
        }

        $final = array();
        $final['framework'] = $this->settings['framework'];
        $final['version'] = $this->settings['version'];
        $final['routes'] = $export->toArray();

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

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function import(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('pages.import', 'admin-pages')) {
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

        foreach ($decoded->routes as $value) {
            $route = $this->importRoute($value, $overwrite);
            $this->importRoles($route, $value->roles, $overwrite);
        }

        $return->status = true;
        return $return;
    }

    private function importRoute($value, $overwrite = 0)
    {
        // Check if Exists
        $route = \Dappur\Model\Routes::where('id', (string)$value->id)->first();

        $patternCheck = \Dappur\Model\Routes::where('pattern', $value->pattern)->first();

        // Update Group if Overwrite
        if ($overwrite && $route) {
            $route->name = $value->name;
            $route->pattern = $value->pattern;
            $route->content = $value->content;
            $route->css = $value->css;
            $route->js = $value->js;
            $route->sidebar = $value->sidebar;
            $route->header = $value->header;
            $route->header_text = $value->header_text;
            $route->header_image = $value->header_image;
            $route->permission = $value->permission;
            $route->status = $value->status;
            $route->save();
        }

        if (!$route &&  !$patternCheck) {
            // Create Group
            $route = new \Dappur\Model\Routes;
            if (isset($value->id)) {
                $route->id = $value->id;
            }
            $route->name = $value->name;
            $route->pattern = $value->pattern;
            $route->content = $value->content;
            $route->css = $value->css;
            $route->js = $value->js;
            $route->sidebar = $value->sidebar;
            $route->header = $value->header;
            $route->header_text = $value->header_text;
            $route->header_image = $value->header_image;
            $route->permission = $value->permission;
            $route->status = $value->status;
            $route->save();
        }
        return $route;
    }

    private function importRoles($route, $roles, $overwrite = 0)
    {
        // Process Config Items
        foreach ($roles as $role) {
            if ($overwrite) {
                $deleteRoles = \Dappur\Model\RoleRoutes::where('route_id', $route->id)
                    ->get()
                    ->delete();
            }

            // Check if Item Exists
            $updateRole = \Dappur\Model\RoleRoutes::where('role_id', $role->id)->where('route_id', $route->id)->first();

            if (!$updateRole) {
                // Create Config Item
                $updateRole = new \Dappur\Model\RoleRoutes;
                $updateRole->role_id = $role->id;
                $updateRole->route_id = $route->id;
                $updateRole->save();
            }
        }
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function delete(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('pages.delete', 'dashboard')) {
            return $check;
        }

        $page = \Dappur\Model\Routes::find($request->getParam('page_id'));

        if ($page->delete()) {
            $this->flash('success', 'Page has been deleted successfully.');
            return $this->redirect($response, 'admin-pages');
        }

        $this->flash('danger'.'There was an error deleting the page.');
        return $this->redirect($response, 'admin-pages');
    }

    private function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}
