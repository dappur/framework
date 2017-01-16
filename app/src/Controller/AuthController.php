<?php

namespace App\Controller;

use Cartalyst\Sentinel\Checkpoints\ThrottlingException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

class AuthController extends Controller{
    
    //Login Controller
    public function login(Request $request, Response $response){
        if ($request->isPost()) {
            if(filter_var($request->getParam('login'), FILTER_VALIDATE_EMAIL)) {
                $credentials = [
                    'email' => $request->getParam('login'),
                    'password' => $request->getParam('password')
                ];
            } else {
                $credentials = [
                    'username' => $request->getParam('login'),
                    'password' => $request->getParam('password')
                ];
            }
            
            $remember = $request->getParam('remember') ? true : false;

            try {
                if ($this->auth->authenticate($credentials, $remember)) {
                    $this->flash('success', 'You have been logged in.');
                    $this->logger->addInfo("user login success", array("login" => $request->getParam('login')));

                    if ($this->auth->inRole("admin")) {
                        return $this->redirect($response, 'dashboard');
                    }else{
                        return $this->redirect($response, 'home');
                    }

                    
                } else {
                    $this->flash('danger', 'Invalid username or password.');
                    $this->logger->addNotice("invalid login info", array("login" => $request->getParam('login')));
                }
            } catch (ThrottlingException $e) {
                $this->flash('danger', 'Too many attempts!  Please wait 5 minutes before trying again.');
                $this->logger->addError("login throttling exception", array("login" => $request->getParam('login')));
            }

            return $this->redirect($response, 'login');
        }

        return $this->view->render($response, 'Auth/login.twig');
    }

    // Register Controller
    public function register(Request $request, Response $response){

        if ($request->isPost()) {
            $first_name = $request->getParam('first_name');
            $last_name = $request->getParam('last_name');
            $email = $request->getParam('email');
            $username = $request->getParam('username');
            $password = $request->getParam('password');

            $this->validator->validate($request, [
                'first_name' => V::length(6, 25)->alpha('\''),
                'last_name' => V::length(6, 25)->alpha('\''),
                'email' => V::noWhitespace()->email(),
                'username' => V::noWhitespace()->alnum(),
                'password' => V::noWhitespace()->length(6, 25),
                'password-confirm' => V::equals($password)
            ]);

            if ($this->auth->findByCredentials(['login' => $username])) {
                $this->validator->addError('username', 'User already exists with this username.');
            }

            if ($this->validator->isValid()) {
                $role = $this->auth->findRoleByName('User');

                $user = $this->auth->registerAndActivate([
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'password' => $password,
                    'permissions' => [
                        'user.delete' => 0
                    ]
                ]);

                $role->users()->attach($user);

                $this->flash('success', 'Your account has been created.');
                $this->logger->addInfo("user registration success", array("first_name" => $first_name, "last_name" => $last_name, "email" => $email));
                return $this->redirect($response, 'login');
            }else{
                $this->logger->addError("registration data validation failed", array("first_name" => $first_name, "last_name" => $last_name, "email" => $email));
            }
        }

        return $this->view->render($response, 'Auth/register.twig');
    }

    // Logout Controller
    public function logout(Request $request, Response $response){

        $this->auth->logout();

        $this->flash('success', 'You have been logged out.');
        return $this->redirect($response, 'home');
    }
}