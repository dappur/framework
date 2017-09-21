<?php

namespace Dappur\Controller;

use Cartalyst\Sentinel\Checkpoints\ThrottlingException;
use Cartalyst\Sentinel\Checkpoints\NotActivatedException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;
use Dappur\Dappurware\Recaptcha;
use Cartalyst\Sentinel\Reminders\Reminder;
use Dappur\Dappurware\Email as E;

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
                    $this->logger->addNotice("Login: Invalid login info.", array("login" => $request->getParam('login')));
                    return $this->redirect($response, 'login');
                }
            } catch (ThrottlingException $e) {

                $this->flash('danger', 'Too many invalid attempts on your ' . $e->getType() . '!  Please wait ' . $e->getDelay() . ' seconds before trying again.');
                $this->logger->addError("Login: Throttling Exception", array("login" => $request->getParam('login')));
                return $this->redirect($response, 'login');

            } catch (NotActivatedException $e) {

                $this->flash('danger', 'Please check your email for instructions on activating your account.');
                $this->logger->addError("Login:  Account Not Activated", array("exception" => $request->getParam('login')));
                return $this->redirect($response, 'login');

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
                    'rules' => V::length(6, 64), 
                    'messages' => array(
                        'noWhitespace' => 'Must not contain spaces.',
                        'length' => "Must be between 6 and 64 characters."
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
                $user_details = [
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'username' => $username,
                    'password' => $password,
                    'permissions' => [
                        'user.delete' => 0
                    ]
                ];

                if ($this->config['activation']) {

                    $user = $this->auth->register($user_details);
                    $role->users()->attach($user);

                    $activations = $this->auth->getActivationRepository();

                    $activations = $activations->create($user);
                    $code = $activations->code;

                    $confirm_url = "https://" . $this->config['domain'] . "/activate?code=" . $code . "&email=" . $email;

                    $send_email = new E($this->container);
                    $send_email = $send_email->sendTemplate(array($user->id), 'activation', array('confirm_url' => $confirm_url));
                    if ($send_email['status'] == "error") {
                        $this->flash('danger', 'An error ocurred sending your activation email.  Please contact support.');
                        $this->logger->addError("Registration: Send Activation Email Error.", array("result" => $send_email));
                        return $this->redirect($response, 'forgot-password');
                    }else{
                        $this->flash('success', 'You must activate your account before you can log in. Instructions have been sent to: ' . $request->getParam('email'));
                        $this->logger->addInfo("Registration: Activation email sent.", array("result" => $send_email));
                        return $this->redirect($response, 'home');
                    }
                    
                }else{

                    $user = $this->auth->registerAndActivate($user_details);
                    $role->users()->attach($user);

                    $send_email = new E($this->container);
                    $send_email = $send_email->sendTemplate(array($user->id), 'registration');
                    if ($send_email['status'] == "error") {
                        $this->flash('danger', 'An error ocurred sending your activation email.  Please contact support.');
                        $this->logger->addError("Registration: Send Activation Email Error.", array("result" => $send_email));
                    }else{
                        $this->flash('success', 'Your account has been created.');
                        $this->logger->addInfo("Registration: Success", array("request_params" => $request->getParams()));
                    }

                    $this->auth->login($user);

                    return $this->redirect($response, 'home');
                }

                
            }else{
                $this->logger->addError("registration data validation failed", array("first_name" => $first_name, "last_name" => $last_name, "email" => $email));
            }
        }

        return $this->view->render($response, 'register.twig', array("requestParams" => $request->getParams()));
    }

    // Forgot Password
    public function forgotPassword(Request $request, Response $response){

        if ($request->isPost()) {

            // Validate Data
            $validate_data = array(
                'email' => array(
                    'rules' => V::email(), 
                    'messages' => array(
                        'email' => 'Must be a valid email address.'
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

            $credentials = [
                'email' => $request->getParam('email')
            ];

            $user = $this->auth->findByCredentials($credentials);

            if (!$user) {
                $this->validator->addError('email', 'There is no valid account with that email.');
            }

            if ($this->validator->isValid()) {
                $reminders = $this->auth->getReminderRepository();
                

                if ($exists = $reminders->exists($user)) {
                    $reminder = $exists->code;
                }else{
                    $reminder = $reminders->create($user);
                    $reminder = $reminder->code;
                }

                $reset_url = "https://" . $this->config['domain'] . "/reset-password?reminder=" . $reminder . "&email=" . $request->getParam('email');

                $send_email = new E($this->container);
                $send_email = $send_email->sendTemplate(array($user->id), 'password-reset', array('reset_url' => $reset_url));
                if ($send_email['status'] == "error") {
                    $this->flash('danger', 'There was an error sending your email.  Please try again or contact support.');
                    $this->logger->addError("Forgot Password: Send Email Error.", array("email" => $request->getParam('email')));
                    return $this->redirect($response, 'forgot-password');
                }else{
                    $this->flash('success', 'Password reset instructions have been sent to: ' . $request->getParam('email'));
                    $this->logger->addInfo("Forgot Password: Password successfully reset.", array("email" => $request->getParam('email')));
                    return $this->redirect($response, 'login');
                }
            }

        }

        return $this->view->render($response, 'forgot-password.twig', array("requestParams" => $request->getParams()));
    }

    // Forgot Password
    public function resetPassword(Request $request, Response $response){

        if ($request->isPost()) {

            if(filter_var($request->getParam('email'), FILTER_VALIDATE_EMAIL)) {
                $credentials = [
                    'email' => $request->getParam('email')
                ];
            }

            $user = $this->auth->findByCredentials($credentials);

            if ($user) {

                $reminder = $request->getParam('reminder');
                $email = $request->getParam('email');
                $password = $request->getParam('password');

                // Validate Data
                $validate_data = array(
                    'password' => array(
                        'rules' => V::length(6, 64), 
                        'messages' => array(
                            'length' => "Must be between 6 and 64 characters."
                            )
                    ),
                    'password_confirm' => array(
                        'rules' => V::equals($password), 
                        'messages' => array(
                            'equals' => 'Passwords must match.'
                            )
                    )
                );
                $this->validator->validate($request, $validate_data);

                if ($this->validator->isValid()) {

                    $reminders = $this->auth->getReminderRepository();

                    if ($reminders->complete($user, $reminder, $password)) {

                        $this->auth->login($user);

                        $this->flash('success', 'Your password has successfully been changed and you have been logged in.');
                        $this->logger->addInfo("Reset Password: Password successfully reset.", array("user" => $user));
                        return $this->redirect($response, 'home');

                    }else{
                        $this->flash('danger', 'There was an error validating your information.  Please try again.');
                        $this->logger->addError("Reset Password: Error validating info.", array("request_params" => $request->getParams()));
                        return $this->redirect($response, 'forgot-password');
                    }
                }

            }else{
                $this->flash('danger', 'That account does not exist.');
                $this->logger->addError("Reset Password: Account doesn't exist.", array("email" => $request->getParam('email')));
                return $this->redirect($response, 'forgot-password');
            }

        }

        return $this->view->render($response, 'reset-password.twig', array("requestParams" => $request->getParams()));
    }

    // Forgot Password
    public function activate(Request $request, Response $response){

        $code = $request->getParam('code');
        $email = $request->getParam('email');

        $credentials = [
            'email' => $email
        ];

        $user = $this->auth->findByCredentials($credentials);

        if ($user) {

            $activations = $this->auth->getActivationRepository();

            $activation = $activations->complete($user, $code);

            if ($activation) {

                $this->auth->login($user);

                $send_email = new E($this->container);
                $send_email = $send_email->sendTemplate(array($user->id), 'registration');
                if ($send_email['status'] == "error") {
                    $this->logger->addError("Activation: Send Registration Email Error.", array("result" => $send_email));
                }

                $this->flash('success', 'Your account was successfully activated and you have been logged in.');
                $this->logger->addError("Activation: Account successfully activated.", array("user" => $user));
                return $this->redirect($response, 'home');
            }else{
                $this->flash('danger', 'Sorry, your activation credentials were incorrect.');
                $this->logger->addError("Activation: Credentials incorrect.", array("request_params" => $request->getParams()));
                return $this->redirect($response, 'home');
            }

        }else{
            $this->flash('danger', 'That account does not exist.');
            $this->logger->addError("Activation: Account doesn't exist.", array("email" => $request->getParam('email')));
            return $this->redirect($response, 'home');
        }
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