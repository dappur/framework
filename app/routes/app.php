<?php
$app->group('/', function () {
	$this->map(['GET'], '', 'AppController:home')
		->setName('home');
})
->add(new \Dappur\Middleware\CsrfMiddleware($container))
->add($container->get('csrf'));

