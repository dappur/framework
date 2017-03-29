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
        $app->get('/delete[/{user_id}]', 'AdminController:usersDelete')
            ->setName('admin-users-delete');

        //User Roles
        $app->group('/roles', function() use ($app) {
            $app->get('/delete[/{role}]', 'AdminController:rolesDelete')
            ->setName('admin-roles-delete');
            $app->map(['GET', 'POST'], '/edit[/{role}]', 'AdminController:rolesEdit')
            ->setName('admin-roles-edit');
            $app->post('/add', 'AdminController:rolesAdd')
            ->setName('admin-roles-add');
        });
    });

    // Account Settings
    $app->get('/settings', 'AdminController:settings')
    	->setName('admin-settings');

    $app->map(['GET', 'POST'], '/settings/global', 'AdminController:settingsGlobal')->setName('settings-global');

    $app->post('/settings/global/add', 'AdminController:settingsGlobalAdd')
        ->setName('settings-global-add');
})
->add(new App\Middleware\AdminMiddleware($container))
->add(new App\Middleware\AuthMiddleware($container));

