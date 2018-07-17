<?php

$app->group('/dashboard', function () use ($app, $container) {

    // Dashboard Home
    $app->get('', 'Admin:dashboard')
        ->setName('dashboard');

    // My Account
    $app->map(['GET', 'POST'], '/my-account', 'Admin:myAccount')
        ->setName('my-account');

    // Contact Requests
    $app->map(['GET'], '/contact', 'Admin:contact')
        ->setName('admin-contact');

    // Contact Requests
    $app->map(['GET'], '/contact/datatables', 'Admin:contactDatatables')
        ->setName('admin-contact-datatables');
})
->add(new Dappur\Middleware\Auth($container))
->add(new Dappur\Middleware\Admin($container))
->add($container->get('csrf'));
