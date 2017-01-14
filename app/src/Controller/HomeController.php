<?php

namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class HomeController extends Controller{

    public function home(Request $request, Response $response){

        return $this->view->render($response, 'App/home.twig');

    }


}