<?php

namespace Dappur\Controller\Admin;

use Dappur\Controller\Controller as Controller;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Seo extends Controller
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function seo(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('seo.view', 'dashboard')) {
            return $check;
        }
        $seo = new \Dappur\Model\Seo;
        return $this->view->render($response, 'seo.twig', array("seo" => $seo->get()));
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity) At threshold
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function seoAdd(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('seo.create', 'dashboard')) {
            return $check;
        }

        $availableRoutes = $this->getAllRoutes();

        if ($request->isPost()) {
            // Validate Form Data
            $validateData = array(
                'title' => array(
                    'rules' => \Respect\Validation\Validator::notEmpty()->length(10, 60),
                    'messages' => array(
                        'notEmpty' => 'Title is required.',
                        'length' => 'SEO titles need to be between 10-60 characters.'
                        )
                ),
                'description' => array(
                    'rules' => \Respect\Validation\Validator::notEmpty()->length(50, 300),
                    'messages' => array(
                        'notEmpty' => 'Title is required.',
                        'length' => 'SEO descriptions need to be between 50-300 characters.'
                        )
                ),
                'featured_image' => array(
                    'rules' => \Respect\Validation\Validator::notEmpty(),
                    'messages' => array(
                        'notEmpty' => 'Featured image is required.'
                        )
                )
            );
            $this->validator->validate($request, $validateData);

            
            // Validate Page
            $pageAvailable = false;
            foreach ($availableRoutes as $arvalue) {
                if ($arvalue['name'] == $request->getParam('page')) {
                    $pageAvailable = true;
                }
            }

            if (!$pageAvailable) {
                $this->validator->addError('page', 'Page is not available for SEO optimization.');
            }

            if ($this->validator->isValid()) {
                $add = new \Dappur\Model\Seo;
                $add->page = $request->getParam('page');
                $add->title = $request->getParam('title');
                $add->description = $request->getParam('description');
                if ($request->getParam('featured_image') != "") {
                    $add->image = $request->getParam('featured_image');
                }
                if ($request->getParam('video') != "") {
                    $add->video = $request->getParam('video');
                }
                
                if ($add->save()) {
                    $this->flash('success', 'SEO settings have been saved successfully.');
                    return $this->redirect($response, 'admin-seo');
                }

                $this->flashNow('danger', 'There was an error saving your settings.  Please try again.');
                return $this->redirect($response, 'admin-seo');
            }
        }

        return $this->view->render($response, 'seo-add.twig', array("availableRoutes" => $availableRoutes));
    }

    public function seoDelete(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('seo.delete', 'dashboard')) {
            return $check;
        }
        $seo = new \Dappur\Model\Seo;
        $seo = $seo->find($request->getParam('seo_id'));

        if (!$seo) {
            $this->flash('danger', 'Could not find SEO record.');
            return $this->redirect($response, 'admin-seo');
        }

        if ($seo->default) {
            $this->flash('danger', 'You canot delete the default SEO configuration.');
            return $this->redirect($response, 'admin-seo');
        }

        if ($seo->delete()) {
            $this->flash('success', 'SEO configuration was deleted successfully.');
            return $this->redirect($response, 'admin-seo');
        }
        
        $this->flash('danger', 'There was an error deleting that SEO record.');
        return $this->redirect($response, 'admin-seo');
    }

    public function seoDefault(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('seo.default')) {
            return $check;
        }
        $seo = new \Dappur\Model\Seo;
        $seo = $seo->find($request->getParam('seo_id'));

        if (!$seo) {
            $this->flash('danger', 'Could not find SEO record.');
            return $this->redirect($response, 'admin-seo');
        }

        if ($seo->default) {
            $this->flash('info', 'This is already the default SEO configuration.');
            return $this->redirect($response, 'admin-seo');
        }


        \Dappur\Model\Seo::where('default', 1)->update(['default' => 0]);
        $seo->default = 1;
        if ($seo->save()) {
            $this->flash('success', 'New default SEO configruation was set.');
            return $this->redirect($response, 'admin-seo');
        }

        $this->flash('danger', 'There was an error making that SEO record default.');
        return $this->redirect($response, 'admin-seo');
    }

    /** @SuppressWarnings(PHPMD.StaticAccess) */
    public function seoEdit(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('seo.update', 'dashboard')) {
            return $check;
        }
        $seo = new \Dappur\Model\Seo;
        $seo = $seo->find($request->getAttribute('route')->getArgument('seo_id'));

        if (!$seo) {
            $this->flash('danger', 'Could not find SEO record.');
            return $this->redirect($response, 'admin-seo');
        }

        $availableRoutes = $this->getAllRoutes(false);
        $routeInfo = array();
        foreach ($availableRoutes as $arvalue) {
            if ($arvalue['name'] == $seo->page) {
                $routeInfo = array("name" => $arvalue['name'], "pattern" => $arvalue['pattern']);
            }
        }

        if ($request->isPost()) {
            // Validate Form Data
            $validateData = array(
                'title' => array(
                    'rules' => \Respect\Validation\Validator::notEmpty()->length(10, 60),
                    'messages' => array(
                        'notEmpty' => 'Title is required.',
                        'length' => 'SEO titles need to be between 10-60 characters.'
                        )
                ),
                'description' => array(
                    'rules' => \Respect\Validation\Validator::notEmpty()->length(50, 300),
                    'messages' => array(
                        'notEmpty' => 'Title is required.',
                        'length' => 'SEO descriptions need to be between 50-300 characters.'
                        )
                ),
                'featured_image' => array(
                    'rules' => \Respect\Validation\Validator::notEmpty(),
                    'messages' => array(
                        'notEmpty' => 'Featured image is required.'
                        )
                )
            );
            $this->validator->validate($request, $validateData);

            
            if ($this->validator->isValid()) {
                $seo->title = $request->getParam('title');
                $seo->description = $request->getParam('description');
                $seo->image = $request->getParam('featured_image');
                $seo->video = $request->getParam('video');

                if ($seo->save()) {
                    $this->flash('success', 'SEO settings have been saved successfully for ' . $routeInfo['pattern']);
                    return $this->redirect($response, 'admin-seo');
                }

                $this->flashNow(
                    'danger',
                    'There was an error saving the settings for ' . $routeInfo['pattern'] . '.  Please try again.'
                );
            }
        }

        return $this->view->render($response, 'seo-edit.twig', array("seo" => $seo, "route_info" => $routeInfo));
    }

    private function getAllRoutes($available = null)
    {
        $routes = $this->container->router->getRoutes();
        $allRoutes = array();

        $existing = [];
        if ($available) {
            $existing = \Dappur\Model\Seo::select('page')->get()->pluck('page')->toArray();
        }

        $excludePages = array("blog-post","deploy","asset","csrf","logout","oauth","profile","profile-incomplete");

        foreach ($routes as $route) {
            // Do not include pages that are pre optimized or unnecessary/non-GET
            if (strpos($route->getPattern(), '/dashboard') !== false ||
                in_array($route->getName(), $excludePages) ||
                in_array($route->getName(), $existing) ||
                !in_array("GET", $route->getMethods())) {
                continue;
            }
            
            $allRoutes[] = array("name" => $route->getName(), "pattern" => $route->getPattern());
        }
        usort($allRoutes, function ($first, $second) {
            return strcmp($first["name"], $second["name"]);
        });

        return $allRoutes;
    }
}
