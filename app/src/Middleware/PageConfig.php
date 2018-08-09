<?php

namespace Dappur\Middleware;

use Dappur\Model\ConfigGroups;
use Dappur\Dappurware\SiteConfig;

class PageConfig extends Middleware
{
    public function __invoke($request, $response, $next)
    {
        $pageName = $request->getAttribute('route')->getName();
        
        $pageConfig = ConfigGroups::where('page_name', '=', $pageName)->with('config')->get();

        if ($pageConfig) {
            $cfg = array();
            foreach ($pageConfig as $pc) {
                foreach ($pc->config as $value) {
                    $cfg[$value->name] = $value->value;
                }
            }
            
            $this->view->getEnvironment()->addGlobal('page_config', $cfg);
            return $next($request, $response);
        }
        
        return $next($request, $response);
    }
}
