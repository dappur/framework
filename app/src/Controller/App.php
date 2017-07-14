<?php

namespace Dappur\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Cartalyst\Sentinel\Users\EloquentUser as EloquentUser;

class App extends Controller{

    public function home(Request $request, Response $response){

        return $this->view->render($response, 'App/home.twig');

    }

    public function csrf(Request $request, Response $response){


    	$csrf = array(
    		"name_key" => $this->csrf->getTokenNameKey(),
    		"name" => $this->csrf->getTokenName(),
    		"value_key" => $this->csrf->getTokenValueKey(),
    		"value" => $this->csrf->getTokenValue());

    	return json_encode($csrf);

    }


}