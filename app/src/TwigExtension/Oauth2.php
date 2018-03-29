<?php

/**
    Twitter Authentication functions modified from:
    http://collaboradev.com/2011/04/01/twitter-oauth-php-tutorial/
 */

namespace Dappur\TwigExtension;

use Dappur\Dappurware\Oauth2Utils;
use Dappur\Model\Oauth2Providers;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Slim\Interfaces\RouterInterface;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class Oauth2 extends \Twig_Extension
{
    protected $request;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getName()
    {
        return 'oauth2';
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('authorize_url', array($this, 'authorizeUrl')),
        );
    }

    public function authorizeUrl($providerId, $clientId)
    {
        $check = Oauth2Providers::find($providerId);
        $oauthUtils = new Oauth2Utils($this->container);

        if ($check) {
            $state = null;
            if ($this->container->session->exists('oauth2-state')) {
                $state = $this->container->session->get('oauth2-state');
            }

            $redirectUri = $this->container->request->getUri()->getBaseUrl() .
                $this->container->router->pathFor('oauth', array('slug' => $check->slug));

            switch ($check->slug) {
                case 'twitter':
                    $baseURI = 'https://api.twitter.com/oauth/request_token';
                    $nonce = time();
                    $timestamp = time();
                    $oauth = array('oauth_callback' => $redirectUri,
                                  'oauth_consumer_key' => $this->container->settings['oauth2']['twitter']['client_id'],
                                  'oauth_nonce' => $nonce,
                                  'oauth_signature_method' => 'HMAC-SHA1',
                                  'oauth_timestamp' => $timestamp,
                                  'oauth_version' => '1.0');
                    
                    $baseString = $oauthUtils->buildBaseString($baseURI, $oauth); //build the base string
                    $compositeKey = $oauthUtils->getCompositeKey(
                        $this->container->settings['oauth2']['twitter']['client_secret'],
                        null
                    );
                    $oauthSignature = base64_encode(hash_hmac('sha1', $baseString, $compositeKey, true));
                    $oauth['oauth_signature'] = $oauthSignature; //add the signature to our oauth array
                    $response = $oauthUtils->sendRequest($oauth, $baseURI); //make the call
                    $responseArray = array();
                    $parts = explode('&', $response);
                    foreach ($parts as $p) {
                        $p = explode('=', $p);
                        $responseArray[$p[0]] = $p[1];
                    }
                    $oauthToken = $responseArray['oauth_token'];
                    $authorizeUrl = "https://api.twitter.com/oauth/authorize?oauth_token=$oauthToken";
                    break;
                
                default:
                    $queryParams = urldecode(http_build_query(array(
                        "client_id" => $clientId,
                        "redirect_uri" => $redirectUri,
                        "scope" => $check->scopes,
                        "state" => $state,
                        "response_type" => "code",
                        "access_type" => "offline",
                        "prompt" => "consent"
                    )));

                    $authorizeUrl = $check->authorize_url . "?" . $queryParams;

                    
                    break;
            }

            return $authorizeUrl;
        }
        
        return false;
    }
}
