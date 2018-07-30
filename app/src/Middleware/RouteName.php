<?php

namespace Dappur\Middleware;

class RouteName extends Middleware
{
    public function __invoke($request, $response, $next)
    {
        if ($request->getAttribute('route')) {
            $pageName = $request->getAttribute('route')->getName();
            $this->view->getEnvironment()->addGlobal('pageName', $pageName);
        }

        return $next($request, $response);
    }
}
