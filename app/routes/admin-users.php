<?php

$app->group('/dashboard', function () use ($app, $container) {
    // Users Routes
    $app->group('/users', function () use ($app) {
        // User List
        $app->get('', 'AdminUsers:users')
            ->setName('admin-users');
        // Add New User
        $app->map(['GET', 'POST'], '/add', 'AdminUsers:usersAdd')
            ->setName('admin-users-add');
        // Edit User
        $app->map(['GET', 'POST'], '/edit/{user_id}', 'AdminUsers:usersEdit')
            ->setName('admin-users-edit');
        // Delete User
        $app->post('/delete', 'AdminUsers:usersDelete')
            ->setName('admin-users-delete');
        // User Ajax
        $app->get('/datatables', 'AdminUsers:dataTables')
            ->setName('admin-users-datatables');

        //User Roles
        $app->group('/roles', function () use ($app) {
            $app->post('/delete', 'AdminRoles:rolesDelete')
                ->setName('admin-roles-delete');
            $app->map(['GET', 'POST'], '/edit/{role}', 'AdminRoles:rolesEdit')
                ->setName('admin-roles-edit');
            $app->post('/add', 'AdminRoles:rolesAdd')
                ->setName('admin-roles-add');
        });

        // Change User Password
        $app->map(['POST'], '/change-password', 'AdminUsers:changePassword')
            ->setName('admin-user-change-password');

        // Change User Password
        $app->map(['POST'], '/2fa/disable', 'AdminUsers:disable2fa')
            ->setName('admin-users-disable-2fa');
    });
})
->add(new Dappur\Middleware\Auth($container))
->add(new Dappur\Middleware\Admin($container))
->add($container->get('csrf'))
->add(new Dappur\Middleware\TwoFactorAuth($container));
