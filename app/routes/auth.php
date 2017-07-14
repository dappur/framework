<?php

$app->group('/', function () {
    $this->map(['GET', 'POST'], 'login', 'Auth:login')->setName('login');
    $this->map(['GET', 'POST'], 'register', 'Auth:register')->setName('register');
    $this->map(['GET', 'POST'], 'forgot-password', 'Auth:forgotPassword')->setName('forgot-password');
})
->add(new Dappur\Middleware\Guest($container))
->add($container->get('csrf'));

$app->group('', function () {
    $this->get('/logout', 'Auth:logout')->setName('logout');
});
