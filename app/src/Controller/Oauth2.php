<?php

namespace Dappur\Controller;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Oauth2 extends Controller
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->oauthUtils = new \Dappur\Dappurware\Oauth2($container);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function oauth2(Request $request, Response $response)
    {
        $slug = $request->getAttribute('route')->getArgument('slug');

        $provider = \Dappur\Model\Oauth2Providers::where('slug', $slug)->where('status', 1)->first();

        if (!$provider) {
            $this->flash->addMessage('danger', 'Oauth2 provider not found.');
            return $this->oauthRedirect();
        }
        if ($request->getParam('error')) {
            $this->flash->addMessage('danger', 'Oauth2 Error: ' . $request->getParam('error_description'));
            return $this->oauthRedirect();
        }

        // When Github redirects the user back here, there will be a "code" and "state" parameter in the query string
        if ($request->getParam('code') || $request->getParam('oauth_token')) {
            $token = $this->oauthUtils->getAccessToken($provider);
            // Redirect to login if oauth error
            if (isset($token->error)) {
                $this->flash->addMessage('danger', 'Oauth2 Error: ' . $token->error->message);
                return $this->oauthRedirect();
            }
             
            // Get and Process User Data
            if (isset($token->access_token)) {
                $userInfo = $this->oauthUtils->getUserInfo($token, $provider);

                if ($userInfo['uid']) {
                    return $this->processOauthUser($userInfo, $provider);
                }

                $this->flash->addMessage(
                    'danger',
                    'Oauth2 Error: ' . "An unknown error occured logging you in with " . $provider->name
                );
                return $this->oauthRedirect();
            }

            $this->flash->addMessage(
                'danger',
                'Oauth2 Error: ' . "An unknown error occured logging you in with " . $provider->name
            );
            return $this->oauthRedirect();
        }
    }

    private function processOauthUser($userInfo, $provider)
    {

        $oauthUser = \Dappur\Model\Oauth2Users::where('uid', $userInfo['uid'])
             ->where('provider_id', $provider->id)
             ->first();

        // if exists log in and update record
        if ($oauthUser) {
            return $this->updateOauth2User($userInfo, $provider);
        }

        if ($this->auth->check()) {
            // Handle if logged in
            return $this->createOauth2User($this->auth->check(), $userInfo, $provider);
        }

        // Handle if not logged in
        // Check user email if exists in array
        $emailCheck = false;
        if (isset($userInfo['email']) && $userInfo['email'] != "") {
            $emailCheck = \Dappur\Model\Users::where('email', $userInfo['email'])->first();
        }


        // Create account if email doesnt exist
        if (!$emailCheck) {
            return $this->createUser($userInfo, $provider);
        }



        // Create Oauth2 entry for existing user
        return $this->createOauth2User($emailCheck, $userInfo, $provider);
    }

    private function oauthRedirect($page = 'login')
    {
        if ($this->session->exists('oauth2-redirect')) {
            return $this->response->withRedirect($this->router->pathFor($this->session->get('oauth2-redirect')));
        }

        return $this->response->withRedirect($this->router->pathFor($page));
    }

    private function updateOauth2User(array $userInfo, $provider)
    {
        $oauthUser = \Dappur\Model\Oauth2Users::where('uid', $userInfo['uid'])
            ->where('provider_id', $provider->id)
            ->first();

        $oauthUser->access_token = $userInfo['access_token'];

        if (isset($userInfo['token_secret'])) {
            $oauthUser->token_secret = $userInfo['token_secret'];
        }
        if (isset($userInfo['refresh_token'])) {
            $oauthUser->refresh_token = $userInfo['refresh_token'];
        }
        $oauthUser->expires = null;
        if ($userInfo['expires_in'] != 0) {
            $oauthUser->expires = \Carbon\Carbon::now()->addSeconds($userInfo['expires_in']);
        }

        $oauthUser->save();

        $user = $this->auth->findById($oauthUser->user_id);
        $this->auth->login($user);

        $this->flash->addMessage('success', "You have been logged in using your {$provider->name} account.");

        if ($this->auth->inRole('admin')) {
            return $this->oauthRedirect('dashboard');
        }

        return $this->oauthRedirect('home');
    }

    private function createUser(array $userInfo, $provider)
    {
        // Create user account and log in user
        $role = $this->auth->findRoleByName('User');

        $userDetails = array();

        if (isset($userInfo['first_name'])) {
            $userDetails['first_name'] = $userInfo['first_name'];
        }
        if (isset($userInfo['last_name'])) {
            $userDetails['last_name'] = $userInfo['last_name'];
        }
        if (isset($userInfo['email'])) {
            $userDetails['email'] = $userInfo['email'];
        }

        // Generate a username based on first & last with db check
        $originalUsername = preg_replace(
            '/[^ \w]+/',
            '',
            strtolower($userInfo['first_name'] . $userInfo['last_name'])
        );
        $username = $originalUsername;
        $usernameCheck = \Dappur\Model\Users::where('username', $username)->first();
        $usernameCount = 0;
        while ($usernameCheck) {
            $usernameCount++;
            $username = $originalUsername . $usernameCount;
            $usernameCheck = \Dappur\Model\Users::where('username', $username)->first();
        }
        $userDetails['username'] = $username;

        // Generate random password
        $bytes = openssl_random_pseudo_bytes(8);
        $userDetails['password'] = bin2hex($bytes);

        // Add user permissions
        $userDetails['permissions'] = ['user.delete' => 0];



        // Create user account
        $user = $this->auth->registerAndActivate($userDetails);
        $role->users()->attach($user);

        // Send Welcome email
        $sendEmail = new \Dappur\Dappurware\Email($this->container);
        $sendEmail = $sendEmail->sendTemplate(array($user->id), 'registration');
        $this->flash('success', 'Your account has been created.');
        $this->auth->login($user);

        // Add Oauth record
        $oauthUser = new \Dappur\Model\Oauth2Users;
        $oauthUser->user_id = $user->id;
        $oauthUser->provider_id = $provider->id;
        $oauthUser->uid = $userInfo['uid'];
        $oauthUser->access_token = $userInfo['access_token'];

        if (isset($userInfo['token_secret'])) {
            $oauthUser->token_secret = $userInfo['token_secret'];
        }
        if (isset($userInfo['refresh_token'])) {
            $oauthUser->refresh_token = $userInfo['refresh_token'];
        }
        $oauthUser->expires = null;
        if ($userInfo['expires_in'] != 0) {
            $oauthUser->expires = \Carbon\Carbon::now()->addSeconds($userInfo['expires_in']);
        }
        $oauthUser->save();

        return $this->oauthRedirect('home');
    }

    private function createOauth2User($user, array $userInfo, $provider)
    {
        $this->auth->login($user);

        // Add Oauth record
        $oauthUser = new \Dappur\Model\Oauth2Users;
        $oauthUser->user_id = $user->id;
        $oauthUser->provider_id = $provider->id;
        $oauthUser->uid = $userInfo['uid'];
        $oauthUser->access_token = $userInfo['access_token'];

        if (isset($userInfo['token_secret'])) {
            $oauthUser->token_secret = $userInfo['token_secret'];
        }
        if (isset($userInfo['refresh_token'])) {
            $oauthUser->refresh_token = $userInfo['refresh_token'];
        }
        $oauthUser->expires = null;
        if ($userInfo['expires_in'] != 0) {
            $oauthUser->expires = \Carbon\Carbon::now()->addSeconds($userInfo['expires_in']);
        }

        $oauthUser->save();

        $this->flash->addMessage('success', "Your {$provider->name} account has been successfully linked.");
        
        return $this->oauthRedirect('home');
    }
}
