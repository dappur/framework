<?php

namespace Dappur\Middleware;

use Dappur\Model\ConfigGroups;
use Dappur\Dappurware\SiteConfig;

class PageConfig extends Middleware
{
    public function __invoke($request, $response, $next)
    {
        $pageName = $request->getAttribute('route')->getName();
        
        $pageConfig = ConfigGroups::where('page_name', '=', $pageName)->with('config')->first();

        if ($pageConfig) {
            $cfg = array();
            foreach ($pageConfig->config as $value) {
                $cfg[$value->name] = $value->value;
            }
            $this->view->getEnvironment()->addGlobal('page_config', $cfg);
            return $next($request, $response);
        }
        
        return $next($request, $response);
    }
}
