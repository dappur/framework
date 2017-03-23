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
        $app->map(['GET', 'POST'], '/edit[/{username}]', 'AdminController:usersEdit')
            ->setName('admin-users-edit');

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

