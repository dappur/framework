<?php
session_start();

// Load Vendor Autoload
require __DIR__ . '/../vendor/autoload.php';

// Load Settings
$settings = require __DIR__ . '/../app/bootstrap/settings.php';
$app = new Slim\App(array('settings' => $settings));

// Load Dependancies
require __DIR__ . '/../app/bootstrap/dependencies.php';

// Load Middleware
require __DIR__ . '/../app/bootstrap/middleware.php';

// Load Controllers
require __DIR__ . '/../app/bootstrap/controllers.php';

// Load Routes
foreach (glob(__DIR__ . '/../app/routes/*.php') as $filename)
{
    require $filename;
}

// Run App
$app->run();
