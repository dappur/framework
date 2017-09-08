<?php
$app->group('/', function () {
	$this->map(['GET', 'POST'], '', 'App:home')
		->setName('home');

	$this->map(['GET'], 'privacy', 'App:privacy')
		->setName('privacy');

	$this->map(['GET'], 'terms', 'App:terms')
		->setName('terms');

	$this->map(['GET'], 'csrf', 'App:csrf')
		->setName('csrf');

})
->add($container->get('csrf'))
->add(new Dappur\Middleware\Maintenance($container));

$app->map(['GET'], '/maintenance', 'App:maintenance')
		->setName('maintenance-mode');