<?php
namespace Dappur\Middleware;
class Admin extends Middleware
{
    public function __invoke($request, $response, $next)
    {
        if (!$this->auth->check()) {
            $currentRoute = $this->request->getUri()->getPath();
            return $response->withRedirect($this->router->pathFor('login') . "?redirect=" . $currentRoute);
        }
        if (!$this->auth->hasAccess('dashboard.*')) {
            return $response->withRedirect($this->router->pathFor('home'));
        }
        return $next($request, $response);
    }
}