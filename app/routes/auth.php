<?php

$app->group('/', function () {
    $this->map(['GET', 'POST'], 'register', 'Auth:register')->setName('register');
    $this->map(['GET', 'POST'], 'forgot-password', 'Auth:forgotPassword')->setName('forgot-password');
    $this->map(['GET', 'POST'], 'reset-password', 'Auth:resetPassword')->setName('reset-password');
    $this->map(['GET', 'POST'], 'activate', 'Auth:activate')->setName('activate');
})
->add(new Dappur\Middleware\Guest($container))
->add($container->get('csrf'))
->add(new Dappur\Middleware\Maintenance($container))
->add(new Dappur\Middleware\Seo($container));


$app->map(['GET', 'POST'], '/login', 'Auth:login')
    ->setName('login')
    ->add(new Dappur\Middleware\Guest($container))
    ->add($container->get('csrf'))
    ->add(new Dappur\Middleware\Seo($container));
    
$app->get('/logout', 'Auth:logout')->setName('logout');
