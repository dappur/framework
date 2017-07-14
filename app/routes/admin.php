<?php

$app->group('/dashboard', function () use($app) {

    // Dashboard Home
    $app->get('', 'Admin:dashboard')
        ->setName('dashboard');

    // Users Routes
    $app->group('/users', function() use ($app) {
        // User List
        $app->get('', 'Admin:users')
            ->setName('admin-users');
        // Add New User
        $app->map(['GET', 'POST'], '/add', 'Admin:usersAdd')
            ->setName('admin-users-add');
        // Edit User
        $app->map(['GET', 'POST'], '/edit[/{user_id}]', 'Admin:usersEdit')
            ->setName('admin-users-edit');
        // Delete User
        $app->post('/delete', 'Admin:usersDelete')
            ->setName('admin-users-delete');

        //User Roles
        $app->group('/roles', function() use ($app) {
            $app->post('/delete', 'Admin:rolesDelete')
                ->setName('admin-roles-delete');
            $app->map(['GET', 'POST'], '/edit[/{role}]', 'Admin:rolesEdit')
                ->setName('admin-roles-edit');
            $app->post('/add', 'Admin:rolesAdd')
                ->setName('admin-roles-add');
        });
    });

    // Global Settings
    $app->map(['GET', 'POST'], '/settings', 'Settings:settingsGlobal')->setName('settings-global');
    $app->post('/settings/add', 'Settings:settingsGlobalAdd')
        ->setName('settings-global-add');
    
    // Edit Settings.php
    $app->map(['GET', 'POST'], '/developer/settings', 'Settings:settingsDeveloper')->setName('settings-developer');

    // My Account
    $app->map(['GET', 'POST'], '/my-account', 'Admin:myAccount')->setName('my-account');

    // Media Manager
    $app->get('/media', 'Admin:media')->setName('admin-media');
})
->add(new Dappur\Middleware\Auth($container))
->add(new Dappur\Middleware\Admin($container))
->add($container->get('csrf'));

