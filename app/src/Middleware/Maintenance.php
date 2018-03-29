<?php

namespace Dappur\Middleware;

class Maintenance extends Middleware
{
    public function __invoke($request, $response, $next)
    {
        if ($this->config['maintenance-mode']) {
            return $response->withRedirect($this->router->pathFor('maintenance-mode'));
        }

        return $next($request, $response);
    }
}
