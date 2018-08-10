<?php

$app->group('/dashboard', function () use ($app, $container) {

    // Page Manager
    $app->group('/pages', function () use ($app) {
        $app->map(['GET'], '', 'AdminPages:view')
            ->setName('admin-pages');

        $app->map(['GET','POST'], '/add', 'AdminPages:add')
            ->setName('admin-pages-add');

        $app->map(['GET','POST'], '/edit/{page_id}', 'AdminPages:edit')
            ->setName('admin-pages-edit');

        $app->map(['POST'], '/delete', 'AdminPages:delete')
            ->setName('admin-pages-delete');

        $app->map(['GET'], '/export', 'AdminPages:export')
            ->setName('admin-pages-export');

        $app->map(['POST'], '/import', 'AdminPages:import')
            ->setName('admin-pages-import');

        $app->map(['GET'], '/datatables', 'AdminPages:datatables')
            ->setName('admin-pages-datatables');
    });
})
->add(new Dappur\Middleware\Auth($container))
->add(new Dappur\Middleware\Admin($container))
->add($container->get('csrf'))
->add(new Dappur\Middleware\TwoFactorAuth($container))
->add(new Dappur\Middleware\RouteName($container));
