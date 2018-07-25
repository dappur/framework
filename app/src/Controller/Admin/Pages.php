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
class Pages extends Controller
{
    public function datatables(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('pages.view')) {
            return $check;
        }
  
        $totalData = \Dappur\Model\Routes::count();
            
        $totalFiltered = $totalData;

        $limit = $request->getParam('length');
        $start = $request->getParam('start');
        $order = $request->getParam('columns')[$request->getParam('order')[0]['column']]['data'];
        $dir = $request->getParam('order')[0]['dir'];

        $users = \Dappur\Model\Routes::select('id', 'name', 'pattern', 'permission', 'status')
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
    public function add(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('pages.create')) {
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
            $insPage->css = null;
            if ($request->getParam('css') && !empty($request->getParam('css'))) {
                $insPage->css = $request->getParam('css');
            }
            $insPage->js = null;
            if ($request->getParam('js') && !empty($request->getParam('js'))) {
                $insPage->js = $request->getParam('js');
            }
            $insPage->permission = null;
            if ($request->getParam('permission') && !empty($request->getParam('permission'))) {
                $insPage->permission = $request->getParam('permission');
            }
            $insPage->status = 0;
            if ($request->getParam('status')) {
                $insPage->status = 1;
            }

            $insPage->save();
            
            
            foreach ($request->getParam('roles') as $value) {
                $insRole = new \Dappur\Model\RoleRoutes;
                $insRole->role_id = $value;
                $insRole->route_id = $insPage->id;
                $insRole->save();
            }

            $this->flash('success', $insPage->name . "has been successfully created.");
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
        if ($check = $this->sentinel->hasPerm('pages.update')) {
            return $check;
        }

        //Check Route
        $routeCheck = \Dappur\Model\Routes::with('roles')->find($routeId);
        if (!$routeCheck) {
            $this->flash('danger', "Route doesnt exist.");
            return $this->redirect($response, 'admin-pages');
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

            $routeCheck->save();
            
            \Dappur\Model\RoleRoutes::where('route_id', $routeCheck->id)->delete();
            foreach ($request->getParam('roles') as $value) {
                $insRole = new \Dappur\Model\RoleRoutes;
                $insRole->role_id = $value;
                $insRole->route_id = $routeCheck->id;
                $insRole->save();
            }

            $this->flash('success', $routeCheck->name . "has been successfully updated.");
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
    public function delete(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('pages.delete')) {
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
}
