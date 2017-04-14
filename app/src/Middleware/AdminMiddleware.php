<?php

namespace App\Middleware;

class AdminMiddleware extends Middleware {
    public function __invoke($request, $response, $next) {
        if (!$this->auth->inRole('admin')) {
            $this->flash->addMessage('danger', 'You do not have sufficient privileges to access this page!');
            return $response->withRedirect($this->router->pathFor('home'));
        }

        return $next($request, $response);
    }
}