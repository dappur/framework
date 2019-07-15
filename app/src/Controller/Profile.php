<?php

namespace Dappur\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Profile extends Controller
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function changePassword(Request $request, Response $response)
    {
        $user = $this->auth->check();
        $reminders = $this->auth->getReminderRepository();

        $reminder = $reminders->create($user);
        $reminder = $reminder->code;

        if ($request->getParam('password') != $request->getParam('confirm')) {
            return json_encode(
                array(
                    "result" => "error",
                    "message" => "The passwords you entered do not match."
                )
            );
        }

        if ($reminders->complete($user, $reminder, $request->getParam('password'))) {
            return json_encode(array("result" => "success"));
        }

        return json_encode(
            array(
                "result" => "error",
                "message" => "There was an error updating your password.."
            )
        );
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function checkPassword(Request $request, Response $response)
    {
        $credentials = [
            'email'    => $this->auth->check()->email,
            'password' => $request->getParam('password'),
        ];

        if ($this->auth->stateless($credentials)) {
            return json_encode(array("result" => "success"));
        }
        
        return json_encode(array("result" => "error"));
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function twoFactor(Request $request, Response $response)
    {
        $user = $this->auth->check();
        $credentials = [
            'email'    => $user->email,
            'password' => $request->getParam('password'),
        ];

        $tfa = new \RobThree\Auth\TwoFactorAuth($this->config['site-name']);

        if ($request->getAttribute('route')->getArgument('validate') &&
            $request->getAttribute('route')->getArgument('validate') == "validate") {
            if ($tfa->verifyCode($this->session->get('2fa-secret'), $request->getParam('code'))) {
                $user['2fa'] = $this->session->get('2fa-secret');
                $user->save();
                $this->session->delete('2fa-secret');
                $this->session->set('2fa-confirmed', true);
                return json_encode(array("result" => "success"));
            }
            return json_encode(array("result" => "error"));
        }

        if ($this->auth->stateless($credentials)) {
            if ($request->getParam('status2fa') == "true") {
                $secret = $tfa->createSecret();
                $qrImage = $tfa->getQRCodeImageAsDataUri($this->config['site-name'] . " - " . $user->username, $secret);
                $this->session->set('2fa-secret', $secret);
                return json_encode(array("result" => "success", "secret" => $secret, "qr" => $qrImage));
            }
            if ($request->getParam('status2fa') == "false") {
                $user['2fa'] = null;
                $user->save();
                return json_encode(array("result" => "disabled"));
            }
        }
        
        return json_encode(array("result" => "error"));
    }

    public function twoFactorConfirm(Request $request, Response $response)
    {
        $user = $this->auth->check();

        if (!$user['2fa']) {
            return $this->redirect($response, 'profile');
        }

        if ($request->isPost()) {
            $tfa = new \RobThree\Auth\TwoFactorAuth($this->config['site-name']);
            if ($tfa->verifyCode($user['2fa'], $request->getParam('code'))) {
                $this->session->set('2fa-confirmed', true);
                $this->flash('success', 'You have been successfully logged in.');
                return $this->redirect($response, 'profile');
            }
            if (!$tfa->verifyCode($user['2fa'], $request->getParam('code'))) {
                $this->validator->addError('code', 'Code was invalid.');
            }
        }

        return $this->view->render($response, '2fa-confirm.twig', array("user" => $user));
    }

    public function profile(Request $request, Response $response)
    {
        $user = $this->auth->check();

        if ($request->isPost()) {
            if ($request->getParam('save_profile') !== null) {
                $this->validateProfile();

                if ($this->validator->isValid()) {
                    $newInformation = [
                        'first_name' => $request->getParam('first_name'),
                        'last_name' => $request->getParam('last_name'),
                        'email' => $request->getParam('email'),
                        'username' => $request->getParam('username')
                    ];

                    $updateUser = $this->auth->update($user, $newInformation);

                    $updateProfile = \Dappur\Model\UsersProfile::where('user_id', $user->id)->first();

                    if (!$updateProfile) {
                        $updateProfile = new \Dappur\Model\UsersProfile;
                        $updateProfile->user_id = $user->id;
                        $updateProfile->save();
                    }

                    $updateProfile->about = strip_tags($request->getParam('about'));
                    $updateProfile->save();
                    

                    if ($updateUser) {
                        $this->flashNow('success', 'Your profile has been updated successfully.');
                    }

                    if (!$updateUser) {
                        $this->flashNow('danger', 'There was an error updating your account information.');
                    }
                }
            }
        }

        $userProviders = \Dappur\Model\Oauth2Users::where('user_id', $user->id)->get()->pluck('provider_id');

        // Prepare Oauth2 Providers
        $providers = \Dappur\Model\Oauth2Providers::where('status', 1)->get();
        $clientIds = array();
        foreach ($providers as $ovalue) {
            $clientIds[$ovalue->id] = $this->settings['oauth2'][$ovalue->slug]['client_id'];
        }

        // Generate Oauth2 State
        $this->session->set('oauth2-state', (string) microtime(true));

        // Set Oauth 2 Redirect
        $this->session->set('oauth2-redirect', "profile");

        return $this->view->render(
            $response,
            'profile.twig',
            array(
                "user" => $user,
                "providers" => $providers,
                "user_providers" => $userProviders,
                "client_ids" => $clientIds
            )
        );
    }

    public function profileIncomplete(Request $request, Response $response)
    {
        $user = $this->auth->check();

        if ($request->isPost()) {
            if ($request->getParam('save_profile') !== null) {
                $this->validateProfile();

                if ($this->validator->isValid()) {
                    $newInformation = [
                        'first_name' => $request->getParam('first_name'),
                        'last_name' => $request->getParam('last_name'),
                        'email' => $request->getParam('email'),
                        'username' => $request->getParam('username')
                    ];

                    $updateUser = $this->auth->update($user, $newInformation);

                    $updateProfile = \Dappur\Model\UsersProfile::where('user_id', $user->id)->first();

                    if (!$updateProfile) {
                        $updateProfile = new \Dappur\Model\UsersProfile;
                        $updateProfile->user_id = $user->id;
                        $updateProfile->about = strip_tags($request->getParam('about'));
                        $updateProfile->save();
                    }
                    
                    $updateProfile->about = strip_tags($request->getParam('about'));
                    $updateProfile->save();

                    if ($updateUser) {
                        $this->flash('success', 'Your profile has been updated successfully.');
                        return $this->redirect($response, 'profile');
                    }
                    
                    $this->flashNow('danger', 'There was an error updating your account information.');
                }
            }
        }

        return $this->view->render($response, 'profile-incomplete.twig', array("user" => $user));
    }

    private function validateProfile()
    {
        $user = $this->auth->check();

        $validateData = array(
            'first_name' => array(
                'rules' => \Respect\Validation\Validatorlength(2, 25)->alnum('\'?!@#,."'),
                'messages' => array(
                    'length' => 'Must be between 2 and 25 characters.',
                    'alpha' => 'Contains an invalid character.'
                    )
            ),
            'last_name' => array(
                'rules' => \Respect\Validation\Validatorlength(2, 25)->alnum('\'?!@#,."'),
                'messages' => array(
                    'length' => 'Must be between 2 and 25 characters.',
                    'alpha' => 'Contains an invalid character.'
                    )
            ),
            'email' => array(
                'rules' => \Respect\Validation\ValidatornoWhitespace()->email(),
                'messages' => array(
                    'email' => 'Enter a valid email address.',
                    'noWhitespace' => 'Must not contain any spaces.'
                    )
            ),
            'username' => array(
                'rules' => \Respect\Validation\ValidatornoWhitespace()->alnum(),
                'messages' => array(
                    'slug' => 'Must be alpha numeric with no spaces.',
                    'noWhitespace' => 'Must not contain any spaces.'
                    )
            )
        );

        //Check username
        if ($user->username != $this->request->getParam('username')) {
            $checkUsername = \Dappur\Model\Users::where('id', '!=', $user->id)
                ->where('username', '=', $this->request->getParam('username'))
                ->first();
            if ($checkUsername) {
                $this->validator->addError('username', 'Username is already in use.');
            }
        }
        

        //Check Email
        if ($user->email != $this->request->getParam('email')) {
            $checkEmail = \Dappur\Model\Users::where('id', '!=', $user->id)
                ->where('email', '=', $this->request->getParam('email'))
                ->first();
            if ($checkEmail) {
                $this->validator->addError('email', 'Email address is already in use.');
            }
        }

        $this->validator->validate($this->request, $validateData);
    }
}
