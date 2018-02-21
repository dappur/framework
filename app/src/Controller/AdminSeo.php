<?php

namespace Dappur\Controller;

use Dappur\Model\ContactRequests;
use Dappur\Model\Users;
use Dappur\Model\UsersProfile;
use Dappur\Model\Seo;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

class AdminSeo extends Controller{

    public function seo(Request $request, Response $response){

        if($check = $this->sentinel->hasPerm('seo.view')){
            return $check;
        }

        return $this->view->render($response, 'seo.twig', array("seo" => Seo::get()));

    }

    public function seoAdd(Request $request, Response $response){

        if($check = $this->sentinel->hasPerm('seo.create')){
            return $check;
        }

        $available_routes = $this->getAllRoutes();

        if ($request->isPost()) {
            // Validate Form Data
            $validate_data = array(
                'title' => array(
                    'rules' => V::notEmpty()->length(10, 60), 
                    'messages' => array(
                        'notEmpty' => 'Title is required.',
                        'length' => 'SEO titles need to be between 10-60 characters.'
                        )
                ),
                'description' => array(
                    'rules' => V::notEmpty()->length(50, 300), 
                    'messages' => array(
                        'notEmpty' => 'Title is required.',
                        'length' => 'SEO descriptions need to be between 50-300 characters.'
                        )
                )
            );
            $this->validator->validate($request, $validate_data);

            
            // Validate Page
            $page_available = false;
            foreach ($available_routes as $arkey => $arvalue) {
                if ($arvalue['name'] == $request->getParam('page')) {
                    $page_available = true;
                }
            }

            if (!$page_available) {
                $this->validator->addError('page', 'Page is not available for SEO optimization.');
            }

            if ($this->validator->isValid()) {
                $add = new Seo;
                $add->page = $request->getParam('page');
                $add->title = $request->getParam('title');
                $add->description = $request->getParam('description');
                if ($request->getParam('image') != "") {
                    $add->image = $request->getParam('featured_image');
                }
                if ($request->getParam('video') != "") {
                    $add->video = $request->getParam('video');
                }
                
                if ($add->save()) {
                    $this->flash('success', 'SEO settings have been saved successfully.');
                    return $this->redirect($response, 'admin-seo');
                }else{
                    $this->flashNow('danger', 'There was an error saving your settings.  Please try again.');
                }
            }

        }

        return $this->view->render($response, 'seo-add.twig', array("availableRoutes" => $available_routes));

    }

    public function seoDelete(Request $request, Response $response){

        if($check = $this->sentinel->hasPerm('seo.delete')){
            return $check;
        }

        $seo = Seo::find($request->getParam('seo_id'));

        if (!$seo) {
            $this->flash('danger', 'Could not find SEO record.');
        }else if($seo->default){
            $this->flash('danger', 'You canot delete the default SEO configuration.');
        }else{
            if ($seo->delete()) {
                $this->flash('success', 'SEO configuration was deleted successfully.');
            }else{
                $this->flash('danger', 'There was an error deleting that SEO record.');
            }
        }

        return $this->redirect($response, 'admin-seo');

    }

    public function seoDefault(Request $request, Response $response){

        if($check = $this->sentinel->hasPerm('seo.default')){
            return $check;
        }

        $seo = Seo::find($request->getParam('seo_id'));

        if (!$seo) {
            $this->flash('danger', 'Could not find SEO record.');
        }else if($seo->default){
            $this->flash('info', 'This is already the default SEO configuration.');
        }else{
            Seo::where('default', 1)->update(['default' => 0]);
            $seo->default = 1;
            if ($seo->save()) {
                $this->flash('success', 'New default SEO configruation was set.');
            }else{
                $this->flash('danger', 'There was an error making that SEO record default.');
            }
        }

        return $this->redirect($response, 'admin-seo');

    }

    public function seoEdit(Request $request, Response $response){

        if($check = $this->sentinel->hasPerm('seo.update')){
            return $check;
        }

        $seo = Seo::find($request->getAttribute('route')->getArgument('seo_id'));

        if (!$seo) {
            $this->flash('danger', 'Could not find SEO record.');
            return $this->redirect($response, 'admin-seo');
        }

        $available_routes = $this->getAllRoutes(false);
        $route_info = array();
        foreach ($available_routes as $arkey => $arvalue) {
            if ($arvalue['name'] == $seo->page) {
                $route_info = array("name" => $arvalue['name'], "pattern" => $arvalue['pattern']);
            }
        }

        if ($request->isPost()) {
            // Validate Form Data
            $validate_data = array(
                'title' => array(
                    'rules' => V::notEmpty()->length(10, 60), 
                    'messages' => array(
                        'notEmpty' => 'Title is required.',
                        'length' => 'SEO titles need to be between 10-60 characters.'
                        )
                ),
                'description' => array(
                    'rules' => V::notEmpty()->length(50, 300), 
                    'messages' => array(
                        'notEmpty' => 'Title is required.',
                        'length' => 'SEO descriptions need to be between 50-300 characters.'
                        )
                )
            );
            $this->validator->validate($request, $validate_data);

            
            if ($this->validator->isValid()) {
                
                $seo->title = $request->getParam('title');
                $seo->description = $request->getParam('description');
                $seo->image = $request->getParam('featured_image');
                $seo->video = $request->getParam('video');

                if ($seo->save()) {
                    $this->flash('success', 'SEO settings have been saved successfully for ' . $route_info['pattern']);
                    return $this->redirect($response, 'admin-seo');
                }else{
                    $this->flashNow('danger', 'There was an error saving the settings for ' . $route_info['pattern'] . '.  Please try again.');
                }
            }
            

        }

        return $this->view->render($response, 'seo-edit.twig', array("seo" => $seo, "route_info" => $route_info));

    }

    private function getAllRoutes($available = true){

    	$routes = $this->container->router->getRoutes();
        $all_routes = array();

        if ($available) {
        	$existing = Seo::select('page')->get()->pluck('page')->toArray();
        }else{
        	$existing = [];
        }
        

        foreach ($routes as $route) {
        	// Do not include pages that are pre optimized or unnecessary/non-GET
        	if (strpos($route->getPattern(), '/dashboard') !== false || 
        		$route->getName() == "blog-post" || 
        		$route->getName() == "deploy" || 
        		$route->getName() == "asset" ||
        		$route->getName() == "csrf" ||
        		$route->getName() == "logout" ||
        		in_array($route->getName(), $existing) ||
        		!in_array("GET", $route->getMethods())){
        		continue;
        	}else{
        		$all_routes[] = array("name" => $route->getName(), "pattern" => $route->getPattern());
        	}
            
        }
        usort($all_routes, function($a, $b){ return strcmp($a["name"], $b["name"]); });

        return $all_routes;
    }

}