<?php

namespace Dappur\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class App extends Controller{

    public function home(Request $request, Response $response){

        return $this->view->render($response, 'home.twig');

    }

    public function privacy(Request $request, Response $response){

        return $this->view->render($response, 'privacy.twig');

    }

    public function terms(Request $request, Response $response){

        return $this->view->render($response, 'terms.twig');

    }

    public function maintenance(Request $request, Response $response){

        return $this->view->render($response, 'maintenance.twig');

    }

    public function csrf(Request $request, Response $response){

    	$csrf = array(
    		"name_key" => $this->csrf->getTokenNameKey(),
    		"name" => $this->csrf->getTokenName(),
    		"value_key" => $this->csrf->getTokenValueKey(),
    		"value" => $this->csrf->getTokenValue());

    	echo json_encode($csrf);

    }

}