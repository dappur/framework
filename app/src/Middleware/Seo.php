<?php

namespace Dappur\Middleware;
use Dappur\Model\Seo as SeoModel;

class Seo extends Middleware{

    public function __invoke($request, $response, $next){

    	$page = $request->getAttribute('route')->getName();

    	$seo_config = SeoModel::where('page', '=', $page)->first();

    	if (!$seo_config) {
    		$seo_config = SeoModel::where('default', 1)->first();
    	}

        $this->view->getEnvironment()->addGlobal('seo', $seo_config);

        return $next($request, $response);

    }
}