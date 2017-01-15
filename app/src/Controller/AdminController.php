<?php

namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class AdminController extends Controller{

    public function dashboard(Request $request, Response $response){

        return $this->view->render($response, 'App/home.twig');

    }


}