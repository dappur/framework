<?php
   
$app->post('/' . $settings['deployment']['page_name'], 'DeployController:deploy')
	->setName('deploy')
	->add(new Dappur\Middleware\DeployMiddleware($container));