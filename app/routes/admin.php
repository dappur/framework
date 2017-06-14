<?php

$app->group('/dashboard', function () use($app) {

    // Dashboard Home
    $app->get('', 'AdminController:dashboard')
        ->setName('dashboard');

    // Users Routes
    $app->group('/users', function() use ($app) {
        // User List
        $app->get('', 'AdminController:users')
            ->setName('admin-users');
        // Add New User
        $app->map(['GET', 'POST'], '/add', 'AdminController:usersAdd')
            ->setName('admin-users-add');
        // Edit User
        $app->map(['GET', 'POST'], '/edit[/{user_id}]', 'AdminController:usersEdit')
            ->setName('admin-users-edit');
        // Delete User
        $app->post('/delete', 'AdminController:usersDelete')
            ->setName('admin-users-delete');

        //User Roles
        $app->group('/roles', function() use ($app) {
            $app->post('/delete', 'AdminController:rolesDelete')
                ->setName('admin-roles-delete');
            $app->map(['GET', 'POST'], '/edit[/{role}]', 'AdminController:rolesEdit')
                ->setName('admin-roles-edit');
            $app->post('/add', 'AdminController:rolesAdd')
                ->setName('admin-roles-add');
        });
    });

    // Global Settings
    $app->map(['GET', 'POST'], '/settings', 'SettingsController:settingsGlobal')->setName('settings-global');
    $app->post('/settings/add', 'SettingsController:settingsGlobalAdd')
        ->setName('settings-global-add');
    
    // Edit Settings.php
    $app->map(['GET', 'POST'], '/developer/settings', 'SettingsController:settingsDeveloper')->setName('settings-developer');

    // My Account
    $app->map(['GET', 'POST'], '/my-account', 'AdminController:myAccount')->setName('my-account');

    // Media Manager
    $app->get('/media', 'AdminController:media')->setName('admin-media');
})
->add(new Dappur\Middleware\AdminMiddleware($container))
->add(new Dappur\Middleware\AuthMiddleware($container))
->add(new \Dappur\Middleware\CsrfMiddleware($container))
->add($container->get('csrf'));

