<?php

namespace Dappur\Middleware;

class Amp extends Middleware {
    public function __invoke($request, $response, $next) {
        
        if ($request->getparam('amp_page')) {
        	# code...
        }

        return $next($request, $response);
    }
}