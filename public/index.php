<?php
session_set_cookie_params(null, null, null, null, true);
session_start();

// Load Vendor Autoload
require __DIR__ . '/../vendor/autoload.php';

// Load Settings
$settings_file = file_get_contents(__DIR__ . '/../settings.json');
$settings = json_decode($settings_file, true);

$app = new Slim\App(array('settings' => $settings));

// Load Dependancies
require __DIR__ . '/../app/bootstrap/dependencies.php';

// Set PHP Timezone
date_default_timezone_set($container['config']['timezone']);

// Load Controllers
require __DIR__ . '/../app/bootstrap/controllers.php';

// Load Routes
foreach (glob(__DIR__ . '/../app/routes/*.php') as $filename) {
    require $filename;
}

// Load Global Middleware
require __DIR__ . '/../app/bootstrap/middleware.php';

//Load Error Handlers
require __DIR__ . '/../app/bootstrap/errors.php';

// Run App
$app->run();
