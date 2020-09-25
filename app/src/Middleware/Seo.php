<?php

namespace Dappur\Middleware;

class Seo extends Middleware
{
    public function __invoke($request, $response, $next)
    {
        $page = $request->getAttribute('route')->getName();

        $seoConfig = \Dappur\Model\Seo::where('page', '=', $page)->first();

        if (!$seoConfig) {
            $seoConfig = \Dappur\Model\Seo::where('default', 1)->first();
        }

        $this->view->getEnvironment()->addGlobal('seo', $seoConfig);

        return $next($request, $response);
    }
}
