<?php

namespace Dappur\Middleware;

class BlogCheck extends Middleware
{
    public function __invoke($request, $response, $next)
    {
        if (!$this->config['blog-enabled']) {
            $this->flash->addMessage('danger', 'The blog is not enabled for this site!');

            if (strpos($request->getUri()->getPath(), 'dashboard') !== false) {
                return $response->withRedirect($this->router->pathFor('dashboard'));
            }
            
            return $response->withRedirect($this->router->pathFor('home'));
        }

        return $next($request, $response);
    }
}
