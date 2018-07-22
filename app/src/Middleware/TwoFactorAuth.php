<?php

namespace Dappur\Middleware;

use Dappur\Model\Seo as SeoModel;

class TwoFactorAuth extends Middleware
{
    public function __invoke($request, $response, $next)
    {
        $user = $this->auth->check();
        if ($user && $user['2fa'] && !$this->session->exists('2fa-confirmed') && $this->config['2fa-enabled']) {
            return $response->withRedirect($this->router->pathFor('2fa-confirm'));
        }
        return $next($request, $response);
    }
}
