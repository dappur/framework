<?php

namespace Dappur\Middleware;

class DeployMiddleware extends Middleware {
    public function __invoke($request, $response, $next) {

    	$deploy_token = $request->getParam('token');

    	if ($deploy_token != $this->settings['deployment']['deploy_token']) {
	        return $response->withRedirect($this->router->pathFor('home'));
    	}
    	
        return $next($request, $response);
    }
}