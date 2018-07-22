<?php

$app->group('/dashboard', function () use ($app, $container) {

    // Global Settings
    $app->map(['GET', 'POST'], '/settings/global', 'AdminSettings:settingsGlobal')
        ->setName('settings-global');
    $app->post('/settings/add', 'AdminSettings:settingsGlobalAdd')
        ->setName('settings-global-add');
    $app->post('/settings/group/add', 'AdminSettings:settingsGlobalAddGroup')
        ->setName('settings-global-group-add');
    $app->post('/settings/group/delete', 'AdminSettings:settingsGlobalDeleteGroup')
        ->setName('settings-global-group-delete');

    $app->map(['GET', 'POST'], '/settings/page-settings/{page_name}', 'AdminSettings:settingsPage')
        ->setName('settings-page');

    // View Logs
    $app->map(['GET'], '/developer/logs', 'AdminDeveloper:logs')
        ->setName('developer-logs');

    // Get Logs
    $app->map(['GET'], '/developer/logs/get', 'AdminDeveloper:get')
        ->setName('developer-logs-get');

    // Export Settings
    $app->get('/settings/export', 'AdminSettings:export')
        ->setName('settings-export');

    // Import Settings
    $app->post('/settings/import', 'AdminSettings:import')
        ->setName('settings-import');

    // Import Settings
    $app->post('/settings/save', 'AdminSettings:save')
        ->setName('settings-save');
})
->add(new Dappur\Middleware\Auth($container))
->add(new Dappur\Middleware\Admin($container))
->add($container->get('csrf'))
->add(new Dappur\Middleware\TwoFactorAuth($container));
