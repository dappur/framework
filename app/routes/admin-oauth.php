<?php

$app->group('/dashboard', function () use ($app, $container) {

    // Oauth Manager
    $app->group('/oauth2', function () use ($app) {
        $app->map(['GET'], '', 'AdminOauth2:providers')
            ->setName('admin-oauth2');

        $app->map(['GET','POST'], '/add', 'AdminOauth2:oauth2Add')
            ->setName('admin-oauth2-add');

        $app->map(['GET','POST'], '/edit/{provider_id}', 'AdminOauth2:oauth2Edit')
            ->setName('admin-oauth2-edit');

        $app->map(['POST'], '/enable[/login]', 'AdminOauth2:oauth2Enable')
            ->setName('admin-oauth2-enable');

        $app->map(['POST'], '/disable[/login]', 'AdminOauth2:oauth2Disable')
            ->setName('admin-oauth2-disable');

        $app->map(['POST'], '/delete', 'AdminOauth2:oauth2Delete')
            ->setName('admin-oauth2-delete');
    });
})
->add(new Dappur\Middleware\Auth($container))
->add(new Dappur\Middleware\Admin($container))
->add($container->get('csrf'))
->add(new Dappur\Middleware\TwoFactorAuth($container));
