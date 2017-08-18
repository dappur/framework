<?php

namespace Dappur\Middleware;

class Deploy extends Middleware {
    public function __invoke($request, $response, $next) {

    	$hook_secret = $this->container->settings['deployment']['deploy_token'];
    	$deploy_token = $request->getParam('token');
    	if (isset($deploy_token)) {
    		if ($deploy_token != $hook_secret) {
    			$this->logger->addError("Deployment", array("message" => "Deployment token is invalid."));
    			throw new \Exception("Deployment token is invalid.");
    		}
    		if (!$this->container->settings['deployment']['manual']) {
    			$this->logger->addError("Deployment", array("message" => "Manual deployment is not enabled."));
    			throw new \Exception("Manual deployment is not enabled.");
    		}
    		
    	}else{
    		if (!isset($request->getHeader('HTTP_X_HUB_SIGNATURE')[0])) {
    			$this->logger->addError("Deployment", array("message" => "HTTP header 'X-Hub-Signature' is missing."));
				throw new \Exception("HTTP header 'X-Hub-Signature' is missing.");
			} elseif (!extension_loaded('hash')) {
				$this->logger->addError("Deployment", array("message" => "Missing 'hash' extension to check the secret code validity."));
				throw new \Exception("Missing 'hash' extension to check the secret code validity.");
			}

			list($algo, $hash) = explode('=', $request->getHeader('HTTP_X_HUB_SIGNATURE')[0], 2) + array('', '');
			
			if (!in_array($algo, hash_algos(), TRUE)) {
				$this->logger->addError("Deployment", array("message" => "Hash algorithm '$algo' is not supported."));
				throw new \Exception("Hash algorithm '$algo' is not supported.");
			}
			
			$raw_post = file_get_contents('php://input');
			if ($hash !== hash_hmac($algo, $raw_post, $hook_secret)) {
				$this->logger->addError("Deployment", array("message" => "Hook secret does not match."));
				throw new \Exception('Hook secret does not match.');
			}

			if ($_REQUEST['payload']) {
    			$payload = json_decode($_REQUEST['payload']);
	    		if ($payload->ref != 'refs/heads/' . $this->container->settings['deployment']['repo_branch']){
	    			return $response->write('This branch was not deployed.');
	    		}
			}
    	}



		return $next($request, $response);
    }
}