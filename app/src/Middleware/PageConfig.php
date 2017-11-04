<?php

namespace Dappur\Middleware;
use Dappur\Model\ConfigGroups;
use Dappur\Dappurware\SiteConfig;

class PageConfig extends Middleware{

    public function __invoke($request, $response, $next){

    	$page_name = $request->getAttribute('route')->getName();
        
    	$page_config = ConfigGroups::where('page_name', '=', $page_name)->with('config')->first();

    	if ($page_config) {
    		$cfg = array();
    		foreach ($page_config->config as $key => $value) {
		        $cfg[$value->name] = $value->value;
    		}
    		$this->view->getEnvironment()->addGlobal('page_config', $cfg);
    		return $next($request, $response);
    	}else{
    		return $next($request, $response);
    	}

    }
}