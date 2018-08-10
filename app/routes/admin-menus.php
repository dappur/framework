<?php

$app->group('/dashboard', function () use ($app, $container) {

    // Menu Manager
    $app->group('/menus', function () use ($app) {
        $app->map(['GET'], '', 'AdminMenus:menus')
            ->setName('admin-menus');

        $app->map(['GET','POST'], '/add', 'AdminMenus:add')
            ->setName('admin-menus-add');

        $app->map(['GET'], '/get', 'AdminMenus:get')
            ->setName('admin-menus-get');

        $app->map(['POST'], '/update', 'AdminMenus:update')
            ->setName('admin-menus-update');

        $app->map(['POST'], '/delete', 'AdminMenus:delete')
            ->setName('admin-menus-delete');

        $app->map(['GET'], '/export', 'AdminMenus:export')
            ->setName('admin-menus-export');

        $app->map(['POST'], '/import', 'AdminMenus:import')
            ->setName('admin-menus-import');
    });
})
->add(new Dappur\Middleware\Auth($container))
->add(new Dappur\Middleware\Admin($container))
->add($container->get('csrf'))
->add(new Dappur\Middleware\TwoFactorAuth($container))
->add(new Dappur\Middleware\RouteName($container));
