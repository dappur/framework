<?php

namespace Dappur\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Auth extends Controller
{
    public function activate(Request $request, Response $response)
    {
        $credentials = [
            'email' => $request->getParam('email')
        ];

        $user = $this->auth->findByCredentials($credentials);

        if ($user) {
            $activations = $this->auth->getActivationRepository();

            $activation = $activations->complete($user, $request->getParam('code'));

            if ($activation) {
                $this->auth->login($user);

                $sendEmail = new \Dappur\Dappurware\Email($this->container);
                $sendEmail = $sendEmail->sendTemplate(array($user->id), 'registration');

                $this->flash('success', 'Your account was successfully activated and you have been logged in.');
                return $this->redirect($response, 'home');
            }
            
            $this->flash('danger', 'Sorry, your activation credentials were incorrect.');
            return $this->redirect($response, 'home');
        }
        
        $this->flash('danger', 'That account does not exist.');
        return $this->redirect($response, 'home');
    }

    public function forgotPassword(Request $request, Response $response)
    {
        if ($request->isPost()) {
            // Validate Data
            $validateData = array(
                'email' => array(
                    'rules' => \Respect\Validation\Validator::email(),
                    'messages' => array(
                        'email' => 'Must be a valid email address.'
                        )
                )
            );
            $this->validator->validate($request, $validateData);

            if ($this->config['recaptcha-enabled']) {
                // Validate Recaptcha
                $recaptcha = new \Dappur\Dappurware\Recaptcha($this->container);
                $recaptcha = $recaptcha->validate($request->getParam('g-recaptcha-response'));
                if (!$recaptcha) {
                    $this->validator->addError('recaptcha', 'Recaptcha was invalid.');
                }
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

                $reminder = $reminders->create($user);
                $reminder = $reminder->code;

                $resetUrl = "https://" . $this->config['domain'] .
                    "/reset-password?reminder=" . $reminder . "&email=" . $request->getParam('email');

                $sendEmail = new \Dappur\Dappurware\Email($this->container);
                $sendEmail = $sendEmail->sendTemplate(
                    array($user->id),
                    'password-reset',
                    array('reset_url' => $resetUrl)
                );
                if ($sendEmail['status'] == "error") {
                    $this->flash('danger', 'There was an error sending your reminder email.');
                    return $this->redirect($response, 'forgot-password');
                }

                $this->flash(
                    'success',
                    'Password reset instructions have been sent to: ' . $request->getParam('email')
                );
                return $this->redirect($response, 'login');
            }
        }

        return $this->view->render($response, 'forgot-password.twig');
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity) At threshold
     */
    public function login(Request $request, Response $response)
    {
        if ($request->isPost()) {
            $credentials = [
                'username' => $request->getParam('login'),
                'password' => $request->getParam('password')
            ];
            if (filter_var($request->getParam('login'), FILTER_VALIDATE_EMAIL)) {
                $credentials = [
                    'email' => $request->getParam('login'),
                    'password' => $request->getParam('password')
                ];
            }
            
            $remember = $request->getParam('remember') ? true : false;

            if ($this->config['recaptcha-enabled']) {
                // Validate Recaptcha
                $recaptcha = new \Dappur\Dappurware\Recaptcha($this->container);
                $recaptcha = $recaptcha->validate($request->getParam('g-recaptcha-response'));
                if (!$recaptcha) {
                    $this->validator->addError('recaptcha', 'Recaptcha was invalid.');
                }
            }

            if ($this->validator->isValid()) {
                if ($this->processLogin($credentials, $remember)) {
                    $this->flashNow('success', 'You have been logged in.');
                    if ($request->getParam('redirect')) {
                        return $response->withRedirect($request->getParam('redirect'));
                    }
                    if ($this->auth->inRole("admin")) {
                        return $this->redirect($response, 'dashboard');
                    }
                    return $this->redirect($response, 'home');
                }
            }
        }

        // Prepare Oauth2 Providers
        $oauth2Providers = \Dappur\Model\Oauth2Providers::where('status', 1)->where('login', 1)->get();
        $clientIds = array();
        foreach ($oauth2Providers as $ovalue) {
            $clientIds[$ovalue->id] = $this->settings['oauth2'][$ovalue->slug]['client_id'];
        }

        // Generate Oauth2 State
        $this->session->set('oauth2-state', (string) microtime(true));

        return $this->view->render(
            $response,
            'login.twig',
            array("oauth2_providers" => $oauth2Providers, "client_ids" => $clientIds)
        );
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function logout(Request $request, Response $response)
    {
        $this->auth->logout();

        $this->session->clear();

        $this->flash('success', 'You have been logged out.');
        return $this->redirect($response, 'home');
    }

    public function register(Request $request, Response $response)
    {
        if ($request->isPost()) {
            // Validate Data
            $this->validateNewUser();

            if ($this->validator->isValid()) {
                $role = $this->auth->findRoleByName('User');
                $userDetails = [
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
                    $user = $this->auth->register($userDetails);
                    $role->users()->attach($user);

                    $activations = $this->auth->getActivationRepository();

                    $activations = $activations->create($user);
                    $code = $activations->code;

                    $confirmUrl = "https://" . $this->config['domain'] . "/activate?code=" .
                        $code . "&email=" . $user->email;

                    $sendEmail = new \Dappur\Dappurware\Email($this->container);
                    $sendEmail = $sendEmail->sendTemplate(
                        array($user->id),
                        'activation',
                        array('confirm_url' => $confirmUrl)
                    );

                    if ($sendEmail['status'] == "error") {
                        $this->flash(
                            'danger',
                            'An error ocurred sending your activation email.  Please contact support.'
                        );
                        return $this->redirect($response, 'forgot-password');
                    }

                    $this->flash(
                        'success',
                        'You must activate your account before you can log in. Instructions have been sent to: ' .
                            $request->getParam('email')
                    );
                    return $this->redirect($response, 'home');
                }

                $user = $this->auth->registerAndActivate($userDetails);
                $role->users()->attach($user);

                $sendEmail = new \Dappur\Dappurware\Email($this->container);
                $sendEmail = $sendEmail->sendTemplate(array($user->id), 'registration');

                $this->flash('success', 'Your account has been created.');

                $this->auth->login($user);

                return $this->redirect($response, 'home');

                $this->logger->addInfo("New user registration.", array("user" => $user));
            }
        }

        // Prepare Oauth2 Providers
        $oauth2Providers = \Dappur\Model\Oauth2Providers::where('status', 1)->where('login', 1)->get();
        $clientIds = array();
        foreach ($oauth2Providers as $ovalue) {
            $clientIds[$ovalue->id] = $this->settings['oauth2'][$ovalue->slug]['client_id'];
        }

        // Generate Oauth2 State
        $this->session->set('oauth2-state', (string) microtime(true));

        return $this->view->render(
            $response,
            'register.twig',
            array("oauth2_providers" => $oauth2Providers, "client_ids" => $clientIds)
        );
    }

    public function resetPassword(Request $request, Response $response)
    {
        if ($request->isPost()) {
            if (filter_var($request->getParam('email'), FILTER_VALIDATE_EMAIL)) {
                $credentials = [
                    'email' => $request->getParam('email')
                ];
            }

            $user = $this->auth->findByCredentials($credentials);

            if ($user) {
                // Validate Data
                $validateData = array(
                    'password' => array(
                        'rules' => \Respect\Validation\Validator::length(6, 64),
                        'messages' => array(
                            'length' => "Must be between 6 and 64 characters."
                            )
                    ),
                    'password_confirm' => array(
                        'rules' => \Respect\Validation\Validator::equals($request->getParam('password')),
                        'messages' => array(
                            'equals' => 'Passwords must match.'
                            )
                    )
                );
                $this->validator->validate($request, $validateData);

                if ($this->validator->isValid()) {
                    $reminders = $this->auth->getReminderRepository();

                    if ($reminders->complete($user, $request->getParam('reminder'), $request->getParam('password'))) {
                        $this->auth->login($user);

                        $this->flash(
                            'success',
                            'Your password has successfully been changed and you have been logged in.'
                        );
                        return $this->redirect($response, 'home');
                    }

                    $this->flash('danger', 'There was an error validating your information.  Please try again.');
                    return $this->redirect($response, 'forgot-password');
                }
            }

            $this->flash('danger', 'That account does not exist.');
            return $this->redirect($response, 'forgot-password');
        }

        return $this->view->render($response, 'reset-password.twig');
    }

    private function validateNewUser()
    {
        $validateData = array(
            'first_name' => array(
                'rules' => \Respect\Validation\Validator::alnum('\'-')->length(2, 25),
                'messages' => array(
                    'alnum' => 'May contain letters, numbers, \' and hyphens.',
                    'length' => "Must be between 2 and 25 characters."
                    )
            ),
            'last_name' => array(
                'rules' => \Respect\Validation\Validator::alnum('\'-')->length(2, 25),
                'messages' => array(
                    'alnum' => 'May contain letters, numbers, \' and hyphens.',
                    'length' => "Must be between 2 and 25 characters."
                    )
            ),
            'email' => array(
                'rules' => \Respect\Validation\Validator::noWhitespace()->email(),
                'messages' => array(
                    'noWhitespace' => 'Must not contain spaces.',
                    'email' => 'Must be a valid email address.'
                    )
            ),
            'username' => array(
                'rules' => \Respect\Validation\Validator::noWhitespace()->alnum()->length(2, 25),
                'messages' => array(
                    'noWhitespace' => 'Must not contain spaces.',
                    'alnum' => 'Must be letters and numbers only.',
                    'length' => "Must be between 2 and 25 characters."
                    )
            ),
            'password' => array(
                'rules' => \Respect\Validation\Validator::length(6, 64),
                'messages' => array(
                    'noWhitespace' => 'Must not contain spaces.',
                    'length' => "Must be between 6 and 64 characters."
                    )
            ),
            'password-confirm' => array(
                'rules' => \Respect\Validation\Validator::equals($this->request->getParam('password')),
                'messages' => array(
                    'equals' => 'Passwords must match.'
                    )
            )
        );
        $this->validator->validate($this->request, $validateData);

        if ($this->config['recaptcha-enabled']) {
            // Validate Recaptcha
            $recaptcha = new \Dappur\Dappurware\Recaptcha($this->container);
            $recaptcha = $recaptcha->validate($this->request->getParam('g-recaptcha-response'));
            if (!$recaptcha) {
                $this->validator->addError('recaptcha', 'Recaptcha was invalid.');
            }
        }

        // Validate Username
        if ($this->auth->findByCredentials(['login' => $this->request->getParam('username')])) {
            $this->validator->addError('username', 'User already exists with this username.');
        }

        // Validate Email
        if ($this->auth->findByCredentials(['login' => $this->request->getParam('email')])) {
            $this->validator->addError('email', 'User already exists with this email.');
        }
    }

    private function processLogin($credentials, $remember)
    {
        try {
            if ($this->auth->authenticate($credentials, $remember)) {
                return true;
            }
            
            $this->flashNow('danger', 'Invalid username or password.');
        } catch (\Cartalyst\Sentinel\Checkpoints\ThrottlingException $e) {
            $this->flashNow(
                'danger',
                'Too many invalid attempts on your ' . $e->getType() . '!  '.
                    'Please wait ' . $e->getDelay() . ' seconds before trying again.'
            );
        } catch (\Cartalyst\Sentinel\Checkpoints\NotActivatedException $e) {
            $this->flashNow('danger', 'Please check your email for instructions on activating your account.');
        }

        return false;
    }
}
