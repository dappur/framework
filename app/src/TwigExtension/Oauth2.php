<?php

/**
    Twitter Authentication functions modified from: 
    http://collaboradev.com/2011/04/01/twitter-oauth-php-tutorial/
 */

namespace Dappur\TwigExtension;

use Dappur\Dappurware\Oauth2Utils;
use Dappur\Model\Oauth2Providers;
use Psr\Http\Message\RequestInterface;
use Slim\Interfaces\RouterInterface;

class Oauth2 extends \Twig_Extension {

    protected $request;

    public function __construct($container) {
        $this->container = $container;
    }

    public function getName() {
        return 'oauth2';
    }

    public function getFunctions() {
        return array(
            new \Twig_SimpleFunction('authorize_url', array($this, 'authorizeUrl')),
        );
    }

    public function authorizeUrl($provider_id, $client_id, $login = 0) {

        $check = Oauth2Providers::find($provider_id);
        $oauth_utils = new Oauth2Utils($this->container);

        if ($check) {

            if (isset($_SESSION['oauth2-state'])) {
                $state = $_SESSION['oauth2-state'];
            }else{
                $state = "";
            }

            $redirect_uri = $this->container->request->getUri()->getBaseUrl() . $this->container->router->pathFor('oauth', array('slug' => $check->slug));

            switch ($check->slug) {

                case 'twitter':
                    $baseURI = 'https://api.twitter.com/oauth/request_token';
                    $nonce = time();
                    $timestamp = time();
                    $oauth = array('oauth_callback' => $redirect_uri,
                                  'oauth_consumer_key' => $this->container->settings['oauth2']['twitter']['client_id'],
                                  'oauth_nonce' => $nonce,
                                  'oauth_signature_method' => 'HMAC-SHA1',
                                  'oauth_timestamp' => $timestamp,
                                  'oauth_version' => '1.0');
                    
                    $baseString = $oauth_utils->buildBaseString($baseURI, $oauth); //build the base string
                    $compositeKey = $oauth_utils->getCompositeKey($this->container->settings['oauth2']['twitter']['client_secret'], null); //first request, no request token yet
                    $oauth_signature = base64_encode(hash_hmac('sha1', $baseString, $compositeKey, true)); //sign the base string
                    $oauth['oauth_signature'] = $oauth_signature; //add the signature to our oauth array
                    $response = $oauth_utils->sendRequest($oauth, $baseURI); //make the call
                    $responseArray = array();
                    $parts = explode('&', $response);
                    foreach($parts as $p){
                        $p = explode('=', $p);
                        $responseArray[$p[0]] = $p[1];    
                    }
                    $oauth_token = $responseArray['oauth_token'];
                    $authorize_url = "https://api.twitter.com/oauth/authorize?oauth_token=$oauth_token";

                    break;
                
                default:
                    $query_params = urldecode(http_build_query(array(
                        "client_id" => $client_id,
                        "redirect_uri" => $redirect_uri,
                        "scope" => $check->scopes,
                        "state" => $state,
                        "response_type" => "code",
                        "access_type" => "offline",
                        "prompt" => "consent"
                    )));

                    $authorize_url = $check->authorize_url . "?" . $query_params;

                    
                    break;
            }

            return $authorize_url;

            
        }else{

            return false;
        }

        
    }

}