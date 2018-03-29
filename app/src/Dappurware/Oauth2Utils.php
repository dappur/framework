<?php

namespace Dappur\Dappurware;

use Abraham\TwitterOAuth\TwitterOAuth;
use Interop\Container\ContainerInterface;

class Oauth2Utils extends Dappurware
{
    public function apiRequest($url, $post = null, $headers = array(), $jsonDecode = null)
    {
        $channel = curl_init($url);
        curl_setopt($channel, CURLOPT_RETURNTRANSFER, true);
        if ($post) {
            curl_setopt($channel, CURLOPT_POSTFIELDS, http_build_query($post));
        }
        curl_setopt($channel, CURLOPT_HTTPHEADER, array('Accept: application/json'));
        curl_setopt($channel, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($channel);
        if ($jsonDecode) {
            return json_decode($response);
        }

        return $response;
    }

    public function buildBaseString($baseURI, $params)
    {
        $output = array();
        ksort($params);
        foreach ($params as $key => $value) {
            $output[] = "$key=" . rawurlencode($value);
        }

        return "POST&" . rawurlencode($baseURI) . '&' . rawurlencode(implode('&', $output));
    }

    public function getCompositeKey($consumerSecret, $requestToken)
    {
        return rawurlencode($consumerSecret) . '&' . rawurlencode($requestToken);
    }

    public function buildAuthorizationHeader($oauth)
    {
        $output = 'Authorization: OAuth ';
        $values = array();
        foreach ($oauth as $key => $value) {
            $values[] = "$key=\"" . rawurlencode($value) . "\"";
        }

        $output .= implode(', ', $values);
        return $output;
    }

    public function sendRequest($oauth, $baseURI)
    {
        $header = array( $this->buildAuthorizationHeader($oauth), 'Expect:');

        $options = array(CURLOPT_HTTPHEADER => $header,
                               CURLOPT_HEADER => false,
                               CURLOPT_URL => $baseURI,
                               CURLOPT_POST => true,
                               CURLOPT_RETURNTRANSFER => true,
                               CURLOPT_SSL_VERIFYPEER => false);

        $channel = curl_init();
        curl_setopt_array($channel, $options);
        $response = curl_exec($channel);
        curl_close($channel);

        return $response;
    }

    public function getUserInfo($token, $provider)
    {
        switch ($provider->slug) {
            // Twitter
            case 'twitter':
                $userInfo = $this->getTwitterUser($token, $provider->resource_url);
                break;
            // Google
            case 'google':
                $userInfo = $this->getGoogleUser($token, $provider->resource_url);
                break;
            // Facebook
            case 'facebook':
                $userInfo = $this->getFacebookUser($token, $provider->resource_url);
                break;
            // LinkedIn
            case 'linkedin':
                $userInfo = $this->getLinkedinUser($token, $provider->resource_url);
                break;
            // Github
            case 'github':
                $userInfo = $this->getGithubUser($token, $provider->resource_url);
                break;
            // Instagram
            case 'instagram':
                $userInfo = $this->getInstagramUser($token);
                break;
            // Oauth2 Default
            default:
                $userInfo = $this->getDefaultUser($token, $provider->resource_url);
                break;
        }

        $userInfo['access_token'] = $token->access_token;
        if (isset($token->token_secret)) {
            $userInfo['token_secret'] = $token->token_secret;
        }
        if (isset($token->refresh_token)) {
            $userInfo['refresh_token'] = $token->refresh_token;
        }
        $userInfo['expires_in'] = $token->expires_in;

        return $userInfo;
    }

    private function getInstagramUser($token)
    {

        $userInfo['uid'] = $token->user->id;
        $fullName = explode(' ', $token->user->full_name, 2);
        $userInfo['first_name'] = $fullName[0];
        $userInfo['last_name'] = $fullName[1];

        return $userInfo;
    }

    private function getTwitterUser($token, $resourceUrl)
    {
        $connection = new TwitterOAuth(
            $this->settings['oauth2']['twitter']['client_id'],
            $this->settings['oauth2']['twitter']['client_secret'],
            $token->access_token,
            $token->token_secret
        );
        $returnedInfo = $connection->get($resourceUrl, array("include_email" => "true"));

        $userInfo['uid'] = $token->uid;
        $fullName = explode(' ', $returnedInfo->name, 2);
        $userInfo['first_name'] = $fullName[0];
        $userInfo['last_name'] = $fullName[1];

        if (isset($returnedInfo->email) && $returnedInfo->email != "") {
            $userInfo['email'] = $returnedInfo->email;
        }

        return $userInfo;
    }

    private function getGoogleUser($token, $resourceUrl)
    {
        $returnedInfo = $this->apiRequest(
            $resourceUrl,
            false,
            array('Authorization: Bearer '. $token->access_token),
            true
        );
        $userInfo['uid'] = $returnedInfo->id;
        $userInfo['first_name'] = $returnedInfo->given_name;
        $userInfo['last_name'] = $returnedInfo->family_name;

        if (isset($returnedInfo->email) && $returnedInfo->email != "") {
            $userInfo['email'] = $returnedInfo->email;
        }
        return $userInfo;
    }

    private function getFacebookUser($token, $resourceUrl)
    {
        $returnedInfo = $this->apiRequest(
            $resourceUrl,
            false,
            array('Authorization: Bearer '. $token->access_token),
            true
        );

        $userInfo['uid'] = $returnedInfo->id;
        $userInfo['first_name'] = $returnedInfo->first_name;
        $userInfo['last_name'] = $returnedInfo->last_name;

        if (isset($returnedInfo->email) && $returnedInfo->email != "") {
            $userInfo['email'] = $returnedInfo->email;
        }
        return $userInfo;
    }

    private function getLinkedinUser($token, $resourceUrl)
    {
        $returnedInfo = $this->apiRequest(
            $resourceUrl,
            false,
            array('Authorization: Bearer '. $token->access_token),
            true
        );

        $userInfo['uid'] = $returnedInfo->id;
        $userInfo['first_name'] = $returnedInfo->firstName;
        $userInfo['last_name'] = $returnedInfo->lastName;

        if (isset($returnedInfo->emailAddress) && $returnedInfo->emailAddress != "") {
            $userInfo['email'] = $returnedInfo->emailAddress;
        }


        return $userInfo;
    }

    private function getGithubUser($token, $resourceUrl)
    {
        $returnedInfo = $this->apiRequest(
            $resourceUrl,
            false,
            array('Authorization: Bearer '. $token->access_token,
                'User-Agent:' . $this->container->request->getHeader('HTTP_USER_AGENT')),
            true
        );
    
        $userInfo['uid'] = $returnedInfo->id;
        $fullName = explode(' ', $returnedInfo->name, 2);
        $userInfo['first_name'] = $fullName[0];
        $userInfo['last_name'] = $fullName[1];

        if (isset($returnedInfo->email) && $returnedInfo->email != "") {
            $userInfo['email'] = $returnedInfo->email;
        }
        return $userInfo;
    }

    private function getDefaultUser($token, $resourceUrl)
    {
        $returnedInfo = $this->apiRequest(
            $resourceUrl,
            false,
            array('Authorization: Bearer '. $token->access_token),
            true
        );
        if (isset($returnedInfo->id) && $returnedInfo->id != "") {
            $userInfo['uid'] = $returnedInfo->id;
        }
        return $userInfo;
    }

    public function getAccessToken($provider)
    {
        // Get Access Token
        switch ($provider->slug) {
            case 'twitter':
                $token = $this->getTwitterAccessToken($provider);
                break;
            case 'facebook':
                $token = $this->getFacebookAccessToken($provider);
                break;
            case 'github':
                $token = $this->getGithubAccessToken($provider);
                break;
            default:
                // Verify the state matches our stored state
                $oauth2State = $this->container->session->get('oauth2-state');
                $returnedState = $this->container->request->getParam('state');

                if ((string) $oauth2State !== (string) $returnedState) {
                    (object) $token['error'] = (object) array(
                        "message" => (string) "Oauth2 Error: Session state did not match. Please try again."
                    );
                    break;
                }

                $currentUrl = $this->container->request->getUri()->getBaseUrl() .
                    $this->container->request->getUri()->getPath();

                // Get Access Token
                $token = $this->apiRequest(
                    $provider->token_url,
                    array(
                        'client_id' => $this->container->settings['oauth2'][$provider->slug]['client_id'],
                        'client_secret' => $this->container->settings['oauth2'][$provider->slug]['client_secret'],
                        'redirect_uri' => $currentUrl,
                        'code' => $this->container->request->getParam('code'),
                        'grant_type' => 'authorization_code'
                    ),
                    array(),
                    true
                );
                break;
        }

        return $token;
    }

    private function getTwitterAccessToken()
    {
        $twConnection = new TwitterOAuth(
            $this->container->settings['oauth2']['twitter']['client_id'],
            $this->container->settings['oauth2']['twitter']['client_secret']
        );

        $twArray = array();
        try {
            $accessToken = $twConnection->oauth(
                "oauth/access_token",
                [
                    "oauth_token" => $this->container->request->getParam('oauth_token'),
                    "oauth_verifier" => $this->container->request->getParam('oauth_verifier')
                ]
            );
            $twArray['access_token'] = $accessToken['oauth_token'];
            $twArray['token_secret'] = $accessToken['oauth_token_secret'];
            $twArray['uid'] = $accessToken['user_id'];
            $twArray['screen_name'] = $accessToken['screen_name'];
            $twArray['expires_in'] = $accessToken['x_auth_expires'];
        } catch (\Abraham\TwitterOAuth\TwitterOAuthException $e) {
            $twArray['error'] = (object) array(
                "message" => (string) "An error occured.  Please try again.");
        }

        $token = (object) $twArray;

        return $token;
    }

    private function getFacebookAccessToken($provider)
    {
        $currentUrl = $this->container->request->getUri()->getBaseUrl() .
            $this->container->request->getUri()->getPath();
        // Verify the state matches our stored state
        $oauth2State = $this->container->session->get('oauth2-state');
        $returnedState = $this->container->request->getParam('state');
        if ((string) $oauth2State !== (string) $returnedState) {
            $output['error'] = (object) array(
                "message" => (string) "Oauth2 Error: Session state did not match. Please try again.");
            return (object) $output;
        }
        // Get Access Token
        $token = $this->apiRequest(
            $provider->token_url,
            array(
                'client_id' => $this->container->settings['oauth2'][$provider->slug]['client_id'],
                'client_secret' => $this->container->settings['oauth2'][$provider->slug]['client_secret'],
                'redirect_uri' => $currentUrl,
                'code' => $this->container->request->getParam('code'),
                'grant_type' => 'authorization_code'
            ),
            array(),
            true
        );

        if (isset($token->access_token)) {
            // Get Long Lived Access Token
            $token = $this->apiRequest(
                $provider->token_url,
                array(
                    'client_id' => $this->container->settings['oauth2'][$provider->slug]['client_id'],
                    'client_secret' => $this->container->settings['oauth2'][$provider->slug]['client_secret'],
                    'fb_exchange_token' => $token->access_token,
                    'grant_type' => 'fb_exchange_token'
                ),
                array(),
                true
            );
            return $token;
        }

        $output['error'] = (object) array(
                "message" => (string) "An error occured.  Please try again.");
        return (object) $output;
    }

    private function getGithubAccessToken($provider)
    {
        // Verify the state matches our stored state
        $oauth2State = $this->container->session->get('oauth2-state');
        $returnedState = $this->container->request->getParam('state');

        if ((string) $oauth2State !== (string) $returnedState) {
            $output['error'] = (object) array(
                "message" => (string) "Oauth2 Error: Session state did not match. Please try again.");
            return (object) $output;
        }

        // Get Github access token
        $token = $this->apiRequest(
            $provider->token_url,
            array(
                'client_id' => $this->container->settings['oauth2'][$provider->slug]['client_id'],
                'client_secret' => $this->container->settings['oauth2'][$provider->slug]['client_secret'],
                'code' => $this->container->request->getParam('code'),
            ),
            array(
                'User-Agent: ' . $this->container->settings['oauth2'][$provider->slug]['app_name'],
                'Content-type: application/x-www-form-urlencoded'
            ),
            false
        );

        parse_str($token, $output);
        $output['expires_in'] = 0;
        $token = (object) $output;

        return $token;
    }
}
