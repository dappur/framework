<?php
$app->group('/', function () {
	$this->map(['GET'], '', 'App:home')
		->setName('home');

	$this->map(['GET'], 'csrf', 'App:csrf')
		->setName('csrf');
})
->add($container->get('csrf'));

