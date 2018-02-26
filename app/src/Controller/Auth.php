<?php

namespace Dappur\Controller;

use Dappur\Dappurware\Email as E;
use Dappur\Dappurware\Recaptcha;
use Dappur\Model\Oauth2Providers;
use Cartalyst\Sentinel\Checkpoints\ThrottlingException;
use Cartalyst\Sentinel\Checkpoints\NotActivatedException;
use Cartalyst\Sentinel\Reminders\Reminder;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

class Auth extends Controller{

    public function activate(Request $request, Response $response){

        $credentials = [
            'email' => $request->getParam('email')
        ];

        $user = $this->auth->findByCredentials($credentials);

        if ($user) {

            $activations = $this->auth->getActivationRepository();

            $activation = $activations->complete($user, $request->getParam('code'));

            if ($activation) {

                $this->auth->login($user);

                $send_email = new E($this->container);
                $send_email = $send_email->sendTemplate(array($user->id), 'registration');

                $this->flash('success', 'Your account was successfully activated and you have been logged in.');
            }else{
                $this->flash('danger', 'Sorry, your activation credentials were incorrect.');
            }
        }else{
            $this->flash('danger', 'That account does not exist.');
        }
        return $this->redirect($response, 'home');
    }

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
                    return $this->redirect($response, 'forgot-password');
                }else{
                    $this->flash('success', 'Password reset instructions have been sent to: ' . $request->getParam('email'));
                    return $this->redirect($response, 'login');
                }
            }

        }

        return $this->view->render($response, 'forgot-password.twig');
    }
    
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

            // Validate Recaptcha
            $recaptcha = new Recaptcha($this->container);
            $recaptcha = $recaptcha->validate($request->getParam('g-recaptcha-response'));
            if (!$recaptcha) {
                $this->validator->addError('recaptcha', 'Recaptcha was invalid.');
            }

            if ($this->validator->isValid()) {

                try {
                    if ($this->auth->authenticate($credentials, $remember)) {

                        $this->flashNow('success', 'You have been logged in.');
                        if ($request->getParam('redirect') !== null) {
                            return $response->withRedirect($request->getParam('redirect'));
                        }else{
                            if ($this->auth->inRole("admin")) {
                                return $this->redirect($response, 'dashboard');
                            }else{
                                return $this->redirect($response, 'home');
                            }
                        }
                        
                    } else {
                        $this->flashNow('danger', 'Invalid username or password.');
                    }
                } catch (ThrottlingException $e) {
                    $this->flashNow('danger', 'Too many invalid attempts on your ' . $e->getType() . '!  Please wait ' . $e->getDelay() . ' seconds before trying again.');
                } catch (NotActivatedException $e) {
                    $this->flashNow('danger', 'Please check your email for instructions on activating your account.');
                }

            }

        }

        // Prepare Oauth2 Providers
        $oauth2_providers = Oauth2Providers::where('status', 1)->where('login', 1)->get();
        $client_ids = array();
        foreach ($oauth2_providers as $okey => $ovalue) {
            $client_ids[$ovalue->id] = $this->settings['oauth2'][$ovalue->slug]['client_id'];
        }

        // Generate Oauth2 State
        $_SESSION["oauth2-state"] = (string) microtime(true);

        return $this->view->render($response, 'login.twig', array("oauth2_providers" => $oauth2_providers, "client_ids" => $client_ids));
    }

    public function logout(Request $request, Response $response){

        $this->auth->logout();

        unset($_SESSION['oauth2-redirect']);
        unset($_SESSION['oauth2-state']);

        $this->flash('success', 'You have been logged out.');
        return $this->redirect($response, 'home');
    }

    public function register(Request $request, Response $response){

        if ($request->isPost()) {

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
                    'rules' => V::equals($request->getParam('password')), 
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
            if ($this->auth->findByCredentials(['login' => $request->getParam('username')])) {
                $this->validator->addError('username', 'User already exists with this username.');
            }

            // Validate Email
            if ($this->auth->findByCredentials(['login' => $request->getParam('email')])) {
                $this->validator->addError('email', 'User already exists with this email.');
            }

            if ($this->validator->isValid()) {

                $role = $this->auth->findRoleByName('User');
                $user_details = [
                    'first_name' => $request->getParam('first_name'),
                    'last_name' => $request->getParam('last_name'),
                    'email' => $request->getParam('email'),
                    'username' => $request->getParam('username'),
                    'password' => $request->getParam('password'),
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
                        return $this->redirect($response, 'forgot-password');
                    }else{
                        $this->flash('success', 'You must activate your account before you can log in. Instructions have been sent to: ' . $request->getParam('email'));
                        return $this->redirect($response, 'home');
                    }
                    
                }else{

                    $user = $this->auth->registerAndActivate($user_details);
                    $role->users()->attach($user);

                    $send_email = new E($this->container);
                    $send_email = $send_email->sendTemplate(array($user->id), 'registration');
                    if ($send_email['status'] == "error") {
                        $this->flash('danger', 'An error ocurred sending your welcome email.');
                    }else{
                        $this->flash('success', 'Your account has been created.');
                    }

                    $this->auth->login($user);

                    return $this->redirect($response, 'home');
                }

                $this->logger->addInfo("New user registration.", array("user" => $user));

                
            }
        }

        // Prepare Oauth2 Providers
        $oauth2_providers = Oauth2Providers::where('status', 1)->where('login', 1)->get();
        $client_ids = array();
        foreach ($oauth2_providers as $okey => $ovalue) {
            $client_ids[$ovalue->id] = $this->settings['oauth2'][$ovalue->slug]['client_id'];
        }

        // Generate Oauth2 State
        $_SESSION["oauth2-state"] = (string) microtime(true);

        return $this->view->render($response, 'register.twig', array("oauth2_providers" => $oauth2_providers, "client_ids" => $client_ids));
    }

    public function resetPassword(Request $request, Response $response){

        if ($request->isPost()) {

            if(filter_var($request->getParam('email'), FILTER_VALIDATE_EMAIL)) {
                $credentials = [
                    'email' => $request->getParam('email')
                ];
            }

            $user = $this->auth->findByCredentials($credentials);

            if ($user) {

                // Validate Data
                $validate_data = array(
                    'password' => array(
                        'rules' => V::length(6, 64), 
                        'messages' => array(
                            'length' => "Must be between 6 and 64 characters."
                            )
                    ),
                    'password_confirm' => array(
                        'rules' => V::equals($request->getParam('password')), 
                        'messages' => array(
                            'equals' => 'Passwords must match.'
                            )
                    )
                );
                $this->validator->validate($request, $validate_data);

                if ($this->validator->isValid()) {

                    $reminders = $this->auth->getReminderRepository();

                    if ($reminders->complete($user, $request->getParam('reminder'), $request->getParam('password'))) {

                        $this->auth->login($user);

                        $this->flash('success', 'Your password has successfully been changed and you have been logged in.');
                        return $this->redirect($response, 'home');

                    }else{
                        $this->flash('danger', 'There was an error validating your information.  Please try again.');
                        return $this->redirect($response, 'forgot-password');
                    }
                }

            }else{
                $this->flash('danger', 'That account does not exist.');
                return $this->redirect($response, 'forgot-password');
            }

        }

        return $this->view->render($response, 'reset-password.twig');
    }
}