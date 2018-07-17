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
    
    // View Settings.json
    $app->map(['GET'], '/developer/settings', 'AdminSettings:settingsDeveloper')
        ->setName('settings-developer');

    // View Logs
    $app->map(['GET'], '/developer/logs', 'AdminSettings:developerLogs')
        ->setName('developer-logs');

    // Export Settings
    $app->get('/settings/export[/{group_id}]', 'AdminSettings:export')
        ->setName('settings-export');

    // Import Settings
    $app->post('/settings/import', 'AdminSettings:import')
        ->setName('settings-import');
})
->add(new Dappur\Middleware\Auth($container))
->add(new Dappur\Middleware\Admin($container))
->add($container->get('csrf'));
