<?php

namespace Dappur\Middleware;

use Dappur\Model\Seo as SeoModel;

class Seo extends Middleware
{
    public function __invoke($request, $response, $next)
    {
        $page = $request->getAttribute('route')->getName();

        $seoConfig = SeoModel::where('page', '=', $page)->first();

        if (!$seoConfig) {
            $seoConfig = SeoModel::where('default', 1)->first();
        }

        $this->view->getEnvironment()->addGlobal('seo', $seoConfig);

        return $next($request, $response);
    }
}
