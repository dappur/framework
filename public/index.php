<?php
require __DIR__ . '/../vendor/autoload.php';

// Load Settings
$settingsFile = file_get_contents(__DIR__ . '/../settings.json');
$settings = json_decode($settingsFile, true);

// Check for installed themes
if (empty(glob(__DIR__ . '/../app/views/*', GLOB_ONLYDIR))) {
    if ($settings['displayErrorDetails']) {
        die('No themes installed.  Please install the default themes via 
            <a href="https://github.com/dappur/dapp">dApp</a> 
            or following the 
            <a href="https://github.com/dappur/framework/blob/master/README.md">README</a>.'
        );
    }
    die('No Theme');
}

$app = new Slim\App(array('settings' => $settings));

// Load Dependancies
require __DIR__ . '/../app/bootstrap/dependencies.php';

// Set PHP Timezone
date_default_timezone_set($container->get('config')['timezone']);

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
