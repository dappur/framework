<?php

$app->map(['GET', 'POST'], '/' . $settings['deployment']['deploy_url'], 'DeployController:deploy')
	->setName('deploy')->add(new Dappur\Middleware\DeployMiddleware($container));