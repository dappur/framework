<?php

namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class AdminController extends Controller{

    public function dashboard(Request $request, Response $response){

        return $this->view->render($response, 'Admin/dashboard.twig');

    }

    public function users(Request $request, Response $response){


        $users = new \App\Model\Users;

        return $this->view->render($response, 'Admin/users.twig', ["users" => $users->get()]);

    }

    public function usersEdit(Request $request, Response $response, $username){
        
        $users = new \App\Model\Users;
        $user = $users->where('username', '=', $username)->first();

        $roles = new \App\Model\Roles;

        if ($user) {
            return $this->view->render($response, 'Admin/users-edit.twig', ['user' => $user, 'roles' => $roles]);
        }else{
            $this->flash('danger', 'Sorry, that user was not found.');
            return $response->withRedirect($this->router->pathFor('admin-users'));
        }
        
    }

    public function settings(Request $request, Response $response){

        return $this->view->render($response, 'Admin/settings.twig');

    }

    public function settingsGlobal(Request $request, Response $response){

        return $this->view->render($response, 'Admin/global-settings.twig');

    }


}