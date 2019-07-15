<?php

namespace Dappur\Middleware;

class PageConfig extends Middleware
{
    public function __invoke($request, $response, $next)
    {
        $pageName = $request->getAttribute('route')->getName();
        
        $pageConfig = \Dappur\Model\ConfigGroups::where('page_name', '=', $pageName)->with('config')->get();

        if ($pageConfig) {
            $cfg = array();
            foreach ($pageConfig as $pc) {
                foreach ($pc->config as $value) {
                    $cfg[$value->name] = $value->value;
                }
            }
            
            $this->view->getEnvironment()->addGlobal('pageConfig', $cfg);
            if (!empty($cfg)) {
                $request->container->pageConfig = $cfg;
            }
            return $next($request, $response);
        }
        
        return $next($request, $response);
    }
}
