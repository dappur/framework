<?php

namespace Dappur\Controller;

use Cartalyst\Sentinel\Checkpoints\ThrottlingException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;
use Dappur\Dappurware\Recaptcha;

class Auth extends Controller{
    
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

        return $this->view->render($response, 'login.twig');
    }

    // Register Controller
    public function register(Request $request, Response $response){

        if ($request->isPost()) {
            $first_name = $request->getParam('first_name');
            $last_name = $request->getParam('last_name');
            $email = $request->getParam('email');
            $username = $request->getParam('username');
            $password = $request->getParam('password');

            // Validate Data
            $validate_data = array(
                'first_name' => array(
                    'rules' => V::alnum('\'-')->length(2, 25), 
                    'messages' => array(
                        'alnum' => 'May contain letters, numbers, \' and hyphens.',
                        'length' => "Must be between 2 and 25 characters."
                        )
                ),
                'last_name' => array(
                    'rules' => V::alnum('\'-')->length(2, 25), 
                    'messages' => array(
                        'alnum' => 'May contain letters, numbers, \' and hyphens.',
                        'length' => "Must be between 2 and 25 characters."
                        )
                ),
                'email' => array(
                    'rules' => V::noWhitespace()->email(), 
                    'messages' => array(
                        'noWhitespace' => 'Must not contain spaces.',
                        'email' => 'Must be a valid email address.'
                        )
                ),
                'username' => array(
                    'rules' => V::noWhitespace()->alnum()->length(2, 25), 
                    'messages' => array(
                        'noWhitespace' => 'Must not contain spaces.',
                        'alnum' => 'Must be letters and numbers only.',
                        'length' => "Must be between 2 and 25 characters."
                        )
                ),
                'password' => array(
                    'rules' => V::noWhitespace()->length(6, 25), 
                    'messages' => array(
                        'noWhitespace' => 'Must not contain spaces.',
                        'length' => "Must be between 2 and 25 characters."
                        )
                ),
                'password-confirm' => array(
                    'rules' => V::equals($password), 
                    'messages' => array(
                        'equals' => 'Passwords must match.'
                        )
                )
            );
            $this->validator->validate($request, $validate_data);

            // Validate Recaptcha
            $recaptcha = new Recaptcha($this->container);
            $recaptcha = $recaptcha->validate($request->getParam('g-recaptcha-response'));
            if (!$recaptcha) {
                $this->validator->addError('recaptcha', 'Recaptcha was invalid.');
            }

            // Validate Username
            if ($this->auth->findByCredentials(['login' => $username])) {
                $this->validator->addError('username', 'User already exists with this username.');
            }

            // Validate Email
            if ($this->auth->findByCredentials(['login' => $email])) {
                $this->validator->addError('email', 'User already exists with this email.');
            }

            if ($this->validator->isValid()) {
                $role = $this->auth->findRoleByName('User');

                $user = $this->auth->registerAndActivate([
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'username' => $username,
                    'password' => $password,
                    'permissions' => [
                        'user.delete' => 0
                    ]
                ]);

                $role->users()->attach($user);

                if($send_email->send()){
                    $this->flash('success', 'Your new password has been emailed to: ' . $request->getParam('email'));
                    $this->logger->addInfo("Forgot Password: Password successfully reset.", array("email" => $request->getParam('email')));
                    return $this->redirect($response, 'login');
                }else{
                    $this->flash('danger', 'An error occured sending your email, please try again.');
                    $this->logger->addError("Forgot Password: An unknown error occured sending the email.", array("response" => $send_email, "mail_error" => $send_email->ErrorInfo));
                    return $this->redirect($response, 'forgot-password');
                }

                $this->flash('success', 'Your account has been created.');
                $this->logger->addInfo("user registration success", array("first_name" => $first_name, "last_name" => $last_name, "email" => $email));
                return $this->redirect($response, 'login');
            }else{
                $this->logger->addError("registration data validation failed", array("first_name" => $first_name, "last_name" => $last_name, "email" => $email));
            }
        }

        return $this->view->render($response, 'register.twig');
    }

    // Forgot Password
    public function forgotPassword(Request $request, Response $response){

        if ($request->isPost()) {

            if(filter_var($request->getParam('email'), FILTER_VALIDATE_EMAIL)) {
                $credentials = [
                    'email' => $request->getParam('email')
                ];
            }

             $user = $this->auth->findByCredentials($credentials);

             if ($user) {
                $password = $this->randomAlnum();
                 $new_password = [
                    'password' => $password
                ];

                $update_password = $this->auth->update($user, $new_password);

                if ($update_password) {

                    $message = "Here is your new password for " . $this->config['site-name'] . ":" . "\n\n";
                    $message .= $password . "\n\n";
                    $message .= "To log on, please visit: https://" . $this->config['domain'] . "/login to sign into your account.";

                    $send_email = $this->mail;
                    $send_email->setFrom($this->config['from-email']);
                    $send_email->addAddress($request->getParam('email'));
                    $send_email->Subject = $this->config['site-name'] . " - Password Reset";
                    $send_email->Body    = $message;

                    if($send_email->send()){
                        $this->flash('success', 'Your new password has been emailed to: ' . $request->getParam('email'));
                        $this->logger->addInfo("Forgot Password: Password successfully reset.", array("email" => $request->getParam('email')));
                        return $this->redirect($response, 'login');
                    }else{
                        $this->flash('danger', 'An error occured sending your email, please try again.');
                        $this->logger->addError("Forgot Password: An unknown error occured sending the email.", array("response" => $send_email, "mail_error" => $send_email->ErrorInfo));
                        return $this->redirect($response, 'forgot-password');
                    }

                }else{
                    $this->flash('danger', 'An unknown error occured, please try again.');
                    $this->logger->addError("Forgot Password: An unknown error occured updating the password.", array("response" => $update_password));
                }


             }else{
                $this->flash('danger', 'That account does not exist.');
                $this->logger->addError("Forgot Password: Account doesn't exist.", array("email" => $request->getParam('email')));
                return $this->redirect($response, 'forgot-password');
             }

        }

        return $this->view->render($response, 'forgot-password.twig', array("requestParams" => $request->getParams()));
    }

    // Logout Controller
    public function logout(Request $request, Response $response){

        $this->auth->logout();

        $this->flash('success', 'You have been logged out.');
        return $this->redirect($response, 'home');
    }


    // Generate Random Password
    private function randomAlnum($length = 10) {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < $length; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }
}