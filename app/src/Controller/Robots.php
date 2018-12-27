<?php

namespace Dappur\Controller;

use Dappur\Controller\Controller as Controller;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Steein\Robots\Robots as R;
use Steein\Robots\RobotsInterface;

class Robots extends Controller
{
    public function view(Request $request, Response $response)
    {

    	$allRobots = \Dappur\Model\Robots::with('allow', 'disallow')->get();

    	//die($allRobots);
    	$test = R::getInstance();
    	$count = 1;
    	foreach ($allRobots as $rob) {
			if ($count > 1) {
				$test = $test->spacer();
			}
			if ($rob->comment) {
				$test = $test->comment($rob->comment);
			}
			if ($rob->host) {
				$test = $test->host($rob->host);
			}
			if ($rob->user_agent) {
				$test = $test->userAgent($rob->user_agent);
			}
			if ($rob->allow->count() >= 1) {
				foreach ($rob->allow as $allow) {
					$test = $test->allow($allow->route);
				}
			}
			if ($rob->disallow->count() >= 1) {
				foreach ($rob->disallow as $disallow) {
					$test = $test->disallow($disallow->route);
				}
			}
		    $count++;
    	}

        return $response->getBody()->write($test->render());
    }
}
