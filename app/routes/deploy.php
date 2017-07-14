<?php

$app->map(['GET', 'POST'], '/' . $settings['deployment']['deploy_url'], 'Deploy:deploy')
	->setName('deploy')
	->add(new Dappur\Middleware\Deploy($container));