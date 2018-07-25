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

// Load Databased Routes

$customRoutes = \Dappur\Model\Routes::select('name', 'pattern')->where('status', 1)->get();
if ($customRoutes->count()) {
    $app->group('/', function () use ($app, $customRoutes) {
        foreach ($customRoutes as $cRoute) {
            $app->get($cRoute->pattern, 'App:customRoute')->setName($cRoute->name);
        }
    })
    ->add($container->get('csrf'))
    ->add(new Dappur\Middleware\Maintenance($container))
    ->add(new Dappur\Middleware\PageConfig($container))
    ->add(new Dappur\Middleware\Seo($container))
    ->add(new Dappur\Middleware\ProfileCheck($container))
    ->add(new Dappur\Middleware\TwoFactorAuth($container));
}

// Load Global Middleware
require __DIR__ . '/../app/bootstrap/middleware.php';

//Load Error Handlers
require __DIR__ . '/../app/bootstrap/errors.php';

// Run App
$app->run();
