<?php
$app->group('/dashboard', function () use ($app, $container) {
    // Email Manager
    $app->group('/email', function () use ($app) {
        $app->map(['GET'], '', 'AdminEmail:email')
            ->setName('admin-email');

        $app->map(['GET'], '/details/{email}', 'AdminEmail:emailDetails')
            ->setName('admin-email-details');

        $app->map(['GET','POST'], '/new', 'AdminEmail:emailNew')
            ->setName('admin-email-new');

        $app->map(['GET'], '/templates', 'AdminEmail:templates')
            ->setName('admin-email-template');

        $app->map(['GET','POST'], '/templates/add', 'AdminEmail:templatesAdd')
            ->setName('admin-email-template-add');

        $app->map(['GET','POST'], '/templates/edit/{template_id}', 'AdminEmail:templatesEdit')
            ->setName('admin-email-template-edit');

        $app->map(['POST'], '/templates/delete', 'AdminEmail:templatesDelete')
            ->setName('admin-email-template-delete');

        $app->map(['POST'], '/test', 'AdminEmail:testEmail')
            ->setName('admin-email-test');

        // Email Ajax
        $app->get('/datatables', 'AdminEmail:dataTables')
            ->setName('admin-email-datatables');

        // Email Ajax
        $app->get('/search-users', 'AdminEmail:searchUsers')
            ->setName('admin-email-search-users');
    });
})
->add(new Dappur\Middleware\Auth($container))
->add(new Dappur\Middleware\Admin($container))
->add($container->get('csrf'))
->add(new Dappur\Middleware\TwoFactorAuth($container));
