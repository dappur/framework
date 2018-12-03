<?php

$app->group('/', function () {
    // Registration
    $this->map(['GET', 'POST'], 'register', 'Auth:register')
        ->setName('register');
    // Forgot Password
    $this->map(['GET', 'POST'], 'forgot-password', 'Auth:forgotPassword')
        ->setName('forgot-password');
    // Password Reset
    $this->map(['GET', 'POST'], 'reset-password', 'Auth:resetPassword')
        ->setName('reset-password');
    // Activation Account
    $this->map(['GET', 'POST'], 'activate', 'Auth:activate')
        ->setName('activate');
})
->add(new Dappur\Middleware\Guest($container))
->add($container->get('csrf'))
->add(new Dappur\Middleware\Maintenance($container))
->add(new Dappur\Middleware\Seo($container))
->add(new Dappur\Middleware\RouteName($container))
->add(new Dappur\Middleware\PageConfig($container));

// Login
$app->map(['GET', 'POST'], '/login', 'Auth:login')
    ->setName('login')
    ->add(new Dappur\Middleware\Guest($container))
    ->add($container->get('csrf'))
    ->add(new Dappur\Middleware\Seo($container))
    ->add(new Dappur\Middleware\RouteName($container))
    ->add(new Dappur\Middleware\PageConfig($container));

// Logout    
$app->get('/logout', 'Auth:logout')->setName('logout');
