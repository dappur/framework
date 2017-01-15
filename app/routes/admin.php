<?php

$app->group('/dashboard', function () {
    $this->get('', 'AdminController:dashboard')->setName('dashboard');
})->add(new App\Middleware\AdminMiddleware($container))->add(new App\Middleware\AuthMiddleware($container));
