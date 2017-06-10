<?php

namespace Dappur\Middleware;

class DeployMiddleware extends Middleware {
    public function __invoke($request, $response, $next) {

    	$hook_secret = $this->container->settings['deployment']['deploy_token'];
    	$deploy_token = $request->getParam('token');
    	if (isset($deploy_token)) {
    		if ($deploy_token != $hook_secret) {
    			throw new \Exception("Deployment token is invalid.");
    		}
    		if (!$this->container->settings['deployment']['manual']) {
    			throw new \Exception("Manual deployment is not enabled.");
    		}
    		
    	}else{
    		if (!isset($request->getHeader('HTTP_X_HUB_SIGNATURE')[0])) {
				throw new \Exception("HTTP header 'X-Hub-Signature' is missing.");
			} elseif (!extension_loaded('hash')) {
				throw new \Exception("Missing 'hash' extension to check the secret code validity.");
			}

			list($algo, $hash) = explode('=', $request->getHeader('HTTP_X_HUB_SIGNATURE')[0], 2) + array('', '');
			
			if (!in_array($algo, hash_algos(), TRUE)) {
				throw new \Exception("Hash algorithm '$algo' is not supported.");
			}
			
			$raw_post = file_get_contents('php://input');
			if ($hash !== hash_hmac($algo, $raw_post, $hook_secret)) {
				throw new \Exception('Hook secret does not match.');
			}
    	}

		return $next($request, $response);
    }
}