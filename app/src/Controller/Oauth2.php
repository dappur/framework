<?php

namespace Dappur\Controller;

use Abraham\TwitterOAuth\TwitterOAuth;
use Carbon\Carbon;
use Dappur\Dappurware\Email as E;
use Dappur\Dappurware\Oauth2Utils;
use Dappur\Model\Oauth2Providers;
use Dappur\Model\Oauth2Users;
use Dappur\Model\Users;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\ContainerException;

class Oauth2 extends Controller
{
    public function oauth2(Request $request, Response $response)
    {
        $oauth_utils = new Oauth2Utils($this->container);

        $slug = $request->getAttribute('route')->getArgument('slug');

        $tw_connection = new TwitterOAuth(
            $this->settings['oauth2'][$slug]['client_id'],
            $this->settings['oauth2'][$slug]['client_secret']
        );

        $provider = Oauth2Providers::where('slug', $slug)->where('status', 1)->first();

        if (!$provider) {
            $this->flash->addMessage('danger', 'Oauth2 provider not found.');
            return $this->oauthRedirect();
        }

        $current_url = $request->getUri()->getBaseUrl() . $request->getUri()->getPath();

        // When Github redirects the user back here, there will be a "code" and "state" parameter in the query string
        if ($request->getParam('code') || $request->getParam('oauth_token')) {
            // Get Access Token
            switch ($provider->slug) {
                // Twitter
                case 'twitter':
                    $tw_array = array();
                    try {
                        $access_token = $tw_connection->oauth(
                            "oauth/access_token",
                            [
                                "oauth_token" => $request->getParam('oauth_token'),
                                "oauth_verifier" => $request->getParam('oauth_verifier')
                            ]
                        );
                        $tw_array['access_token'] = $access_token['oauth_token'];
                        $tw_array['token_secret'] = $access_token['oauth_token_secret'];
                        $tw_array['uid'] = $access_token['user_id'];
                        $tw_array['screen_name'] = $access_token['screen_name'];
                        $tw_array['expires_in'] = $access_token['x_auth_expires'];
                    } catch (\Abraham\TwitterOAuth\TwitterOAuthException $e) {
                        $tw_array['error'] = (object) array(
                            "message" => (string) "An error occured.  Please try again.");
                    }

                    $token = (object) $tw_array;
                    break;

                // seperate because of need to exchange for long lived token
                case 'facebook':
                    // Verify the state matches our stored state
                    $oauth2_state = $_SESSION['oauth2-state'];
                    $returned_state = $request->getParam('state');
                    if ((string) $oauth2_state !== (string) $returned_state) {
                        $this->flash->addMessage(
                            'danger',
                            'Oauth2 Error: Session state did not match. Please try again.'
                        );
                        return $this->oauthRedirect();
                    }
                    // Get Access Token
                    $token = $oauth_utils->apiRequest($provider->token_url, array(
                        'client_id' => $this->settings['oauth2'][$slug]['client_id'],
                        'client_secret' => $this->settings['oauth2'][$slug]['client_secret'],
                        'redirect_uri' => $current_url,
                        'code' => $request->getParam('code'),
                        'grant_type' => 'authorization_code'
                    ));

                    if (isset($token->access_token)) {
                        // Get Long Lived Access Token
                        $token = $oauth_utils->apiRequest($provider->token_url, array(
                            'client_id' => $this->settings['oauth2'][$slug]['client_id'],
                            'client_secret' => $this->settings['oauth2'][$slug]['client_secret'],
                            'fb_exchange_token' => $token->access_token,
                            'grant_type' => 'fb_exchange_token'
                        ));
                    }
                      
                    break;

                case 'github':
                    // Verify the state matches our stored state
                    $oauth2_state = $_SESSION['oauth2-state'];
                    $returned_state = $request->getParam('state');

                    if ((string) $oauth2_state !== (string) $returned_state) {
                        $this->flash->addMessage(
                            'danger',
                            'Oauth2 Error: Session state did not match. Please try again.'
                        );
                        return $this->oauthRedirect();
                    }

                    // Get Github access token
                    $token = $oauth_utils->apiRequest(
                        $provider->token_url,
                        array(
                            'client_id' => $this->settings['oauth2'][$slug]['client_id'],
                            'client_secret' => $this->settings['oauth2'][$slug]['client_secret'],
                            'code' => $request->getParam('code'),
                        ),
                        array(
                            'User-Agent: Dappur Demo',
                            'Content-type: application/x-www-form-urlencoded'
                        ),
                        false
                    );

                    $gh_response = parse_str($token, $output);
                    $output['expires_in'] = 0;
                    $token = (object) $output;
                    break;

                default:
                    // Verify the state matches our stored state
                    $oauth2_state = $_SESSION['oauth2-state'];
                    $returned_state = $request->getParam('state');

                    if ((string) $oauth2_state !== (string) $returned_state) {
                        $this->flash->addMessage('danger', 'Oauth2 Error: Session state did not match. Please try again.');
                        return $this->oauthRedirect();
                    }
                    // Get Access Token
                    $token = $oauth_utils->apiRequest($provider->token_url, array(
                        'client_id' => $this->settings['oauth2'][$slug]['client_id'],
                        'client_secret' => $this->settings['oauth2'][$slug]['client_secret'],
                        'redirect_uri' => $current_url,
                        'code' => $request->getParam('code'),
                        'grant_type' => 'authorization_code'
                    ));
                    break;
            }
              
            // Redirect to login if oauth error
            if (isset($token->error)) {
                $this->flash->addMessage('danger', 'Oauth2 Error: ' . $token->error->message);
                return $this->oauthRedirect();
            }
             
            // Get and Process User Data
            if (isset($token->access_token)) {
                $user_array = array();
                $user_array['access_token'] = $token->access_token;
                if (isset($token->token_secret)) {
                    $user_array['token_secret'] = $token->token_secret;
                }
                if (isset($token->refresh_token)) {
                    $user_array['refresh_token'] = $token->refresh_token;
                }
                $user_array['expires_in'] = $token->expires_in;
                 

                switch ($slug) {
                    // Twitter
                    case 'twitter':
                        $connection = new TwitterOAuth(
                            $this->settings['oauth2'][$slug]['client_id'],
                            $this->settings['oauth2'][$slug]['client_secret'],
                            $token->access_token,
                            $token->token_secret
                        );
                        $returned_info = $connection->get($provider->resource_url, array("include_email" => "true"));

                        $user_array['uid'] = $token->uid;
                        $full_name = explode(' ', $returned_info->name, 2);
                        $user_array['first_name'] = $full_name[0];
                        $user_array['last_name'] = $full_name[1];

                        if (isset($returned_info->email) && $returned_info->email != "") {
                            $user_array['email'] = $returned_info->email;
                        }
                        break;
                    // Google
                    case 'google':
                        $returned_info = $oauth_utils->apiRequest(
                            $provider->resource_url,
                            false,
                            array('Authorization: Bearer '. $token->access_token)
                        );
                        $user_array['uid'] = $returned_info->id;
                        $user_array['first_name'] = $returned_info->given_name;
                        $user_array['last_name'] = $returned_info->family_name;

                        if (isset($returned_info->email) && $returned_info->email != "") {
                            $user_array['email'] = $returned_info->email;
                        }

                        break;
                    // Facebook
                    case 'facebook':
                         $returned_info = $oauth_utils->apiRequest($provider->resource_url, false, array('Authorization: Bearer '. $token->access_token));

                         $user_array['uid'] = $returned_info->id;
                        $user_array['first_name'] = $returned_info->first_name;
                        $user_array['last_name'] = $returned_info->last_name;

                        if (isset($returned_info->email) && $returned_info->email != "") {
                            $user_array['email'] = $returned_info->email;
                        }

                        break;
                    // LinkedIn
                    case 'linkedin':
                         $returned_info = $oauth_utils->apiRequest($provider->resource_url, false, array('Authorization: Bearer '. $token->access_token));

                         $user_array['uid'] = $returned_info->id;
                        $user_array['first_name'] = $returned_info->firstName;
                        $user_array['last_name'] = $returned_info->lastName;

                        if (isset($returned_info->emailAddress) && $returned_info->emailAddress != "") {
                            $user_array['email'] = $returned_info->emailAddress;
                        }

                        break;
                    // Github
                    case 'github':
                         $returned_info = $oauth_utils->apiRequest($provider->resource_url, false, array('Authorization: Bearer '. $token->access_token, 'User-Agent:' . $_SERVER['HTTP_USER_AGENT']));
            
                        $user_array['uid'] = $returned_info->id;
                        $full_name = explode(' ', $returned_info->name, 2);
                        $user_array['first_name'] = $full_name[0];
                        $user_array['last_name'] = $full_name[1];

                        if (isset($returned_info->email) && $returned_info->email != "") {
                            $user_array['email'] = $returned_info->email;
                        }

                        break;

                    // Oauth2 Default
                    default:
                        $returned_info = $oauth_utils->apiRequest(
                            $provider->resource_url,
                            false,
                            array('Authorization: Bearer '. $token->access_token)
                        );
                        if (isset($returned_info->id) && $returned_info->id != "") {
                            $user_array['uid'] = $returned_info->id;
                        }

                        break;
                }

                if ($user_array['uid']) {
                    $oauth_user = Oauth2Users::where('uid', $user_array['uid'])
                         ->where('provider_id', $provider->id)
                         ->first();

                    // if exists log in and update record
                    if ($oauth_user) {
                        return $this->updateOauth2User($user_array, $provider);
                    }



                    if ($this->auth->check()) {
                        // Handle if logged in
                        return $this->createOauth2User($this->auth->check(), $user_array, $provider);
                    } else {
                        // Handle if not logged in
                        // Check user email if exists in array
                        if (isset($user_array['email']) && $user_array['email'] != "") {
                            $email_check = Users::where('email', $user_array['email'])->first();
                        } else {
                            $email_check = false;
                        }



                        if (!$email_check) {
                            // Create account if email doesnt exist
                            return $this->createUser($user_array, $provider);
                        } else {
                            // Create Oauth2 entry for existing user
                            return $this->createOauth2User($email_check, $user_array, $provider);
                        }
                    }
                } else {
                    $this->flash->addMessage('danger', 'Oauth2 Error: ' . "An unknown error occured logging you in with " . $provider->name);
                    return $this->oauthRedirect();
                }
            } else {
                $this->flash->addMessage('danger', 'Oauth2 Error: ' . "An unknown error occured logging you in with " . $provider->name);
                return $this->oauthRedirect();
            }
        }
    }

    private function oauthRedirect($page = 'login')
    {
        if (isset($_SESSION['oauth2-redirect'])) {
            return $this->response->withRedirect($this->router->pathFor($_SESSION['oauth2-redirect']));
        } else {
            return $this->response->withRedirect($this->router->pathFor($page));
        }
    }

    private function updateOauth2User(array $user_array, $provider)
    {
        $oauth_user = Oauth2Users::where('uid', $user_array['uid'])
            ->where('provider_id', $provider->id)
            ->first();

        $oauth_user->access_token = $user_array['access_token'];

        if (isset($user_array['token_secret'])) {
            $oauth_user->token_secret = $user_array['token_secret'];
        }
        if (isset($user_array['refresh_token'])) {
            $oauth_user->refresh_token = $user_array['refresh_token'];
        }
        if ($user_array['expires_in'] == 0) {
            $oauth_user->expires = null;
        } else {
            $oauth_user->expires = Carbon::now()->addSeconds($user_array['expires_in']);
        }

        $oauth_user->save();

        $user = $this->auth->findById($oauth_user->user_id);
        $this->auth->login($user);

        $this->flash->addMessage('success', "You have been logged in using your {$provider->name} account.");
        return $this->oauthRedirect('home');
    }

    private function createUser(array $user_array, $provider)
    {
        // Create user account and log in user
        $role = $this->auth->findRoleByName('User');

        $user_details = array();

        if (isset($user_array['first_name'])) {
            $user_details['first_name'] = $user_array['first_name'];
        }
        if (isset($user_array['last_name'])) {
            $user_details['last_name'] = $user_array['last_name'];
        }
        if (isset($user_array['email'])) {
            $user_details['email'] = $user_array['email'];
        }

        // Generate a username based on first & last with db check
        $original_username = preg_replace('/[^ \w]+/', '', strtolower($user_array['first_name'] . $user_array['last_name']));
        $username = $original_username;
        $username_check = Users::where('username', $username)->first();
        $username_count = 0;
        while ($username_check) {
            $username_count++;
            $username = $original_username . $username_count;
            $username_check = Users::where('username', $username)->first();
        }
        $user_details['username'] = $username;

        // Generate random password
        $bytes = openssl_random_pseudo_bytes(8);
        $user_details['password'] = bin2hex($bytes);

        // Add user permissions
        $user_details['permissions'] = ['user.delete' => 0];

        // Create user account
        $user = $this->auth->registerAndActivate($user_details);
        $role->users()->attach($user);

        // Send Welcome email
        $send_email = new E($this->container);
        $send_email = $send_email->sendTemplate(array($user->id), 'registration');
        $this->flash('success', 'Your account has been created.');
        $this->auth->login($user);

        // Add Oauth record
        $oauth_user = new Oauth2Users;
        $oauth_user->user_id = $user->id;
        $oauth_user->provider_id = $provider->id;
        $oauth_user->uid = $user_array['uid'];
        $oauth_user->access_token = $user_array['access_token'];

        if (isset($user_array['token_secret'])) {
            $oauth_user->token_secret = $user_array['token_secret'];
        }
        if (isset($user_array['refresh_token'])) {
            $oauth_user->refresh_token = $user_array['refresh_token'];
        }
        if ($user_array['expires_in'] == 0) {
            $oauth_user->expires = null;
        } else {
            $oauth_user->expires = Carbon::now()->addSeconds($user_array['expires_in']);
        }
        $oauth_user->save();

        return $this->oauthRedirect('home');
    }

    private function createOauth2User($user, array $user_array, $provider)
    {
        $this->auth->login($user);

        // Add Oauth record
        $oauth_user = new Oauth2Users;
        $oauth_user->user_id = $user->id;
        $oauth_user->provider_id = $provider->id;
        $oauth_user->uid = $user_array['uid'];
        $oauth_user->access_token = $user_array['access_token'];

        if (isset($user_array['token_secret'])) {
            $oauth_user->token_secret = $user_array['token_secret'];
        }
        if (isset($user_array['refresh_token'])) {
            $oauth_user->refresh_token = $user_array['refresh_token'];
        }
        if ($user_array['expires_in'] == 0) {
            $oauth_user->expires = null;
        } else {
            $oauth_user->expires = Carbon::now()->addSeconds($user_array['expires_in']);
        }
        $oauth_user->save();

        $this->flash->addMessage('success', "Your {$provider->name} account has been successfully linked.");
        
        return $this->oauthRedirect('home');
    }
}
