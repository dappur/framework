<?php
// Initialize Container
$container = $app->getContainer();

// Configure Database
$db = $container['settings']['db'];
$capsule = new \Illuminate\Database\Capsule\Manager();
$capsule->addConnection($db);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$container['db'] = function () use ($capsule) {
    return $capsule;
};

// Bind config table from database
$container['config'] = function () use ($container) {
    $config = new \App\Dappurware\SiteConfig($container);
    return $config->getConfig();
};

// Bind Sentinel Authorization plugin
$container['auth'] = function () {
    $sentinel = new \Cartalyst\Sentinel\Native\Facades\Sentinel(
        new \Cartalyst\Sentinel\Native\SentinelBootstrapper(__DIR__ . '/sentinel.php')
    );

    return $sentinel->getSentinel();
};

// Bind User Permissions
$container['userAccess'] = function($container) {
    return (new \App\Dappurware\Sentinel($container))->userAccess();
};

// Bind Flash Messages
$container['flash'] = function () {
    return new \Slim\Flash\Messages();
};

// Bind Respect Validation
$container['validator'] = function () {
    return new \Awurth\Slim\Validation\Validator();
};

// Bind Cookies
$container['cookies'] = function ($container){
    return new \App\Dappurware\Cookies($container);
};

// Bind Twig View
$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig(
        $container['settings']['view']['template_path'] . $container->config['theme'],
        $container['settings']['view']['twig']
    );

    $view->addExtension(new \Slim\Views\TwigExtension(
        $container['router'],
        $container['request']->getUri()
    ));
    $view->addExtension(new \Twig_Extension_Debug());
    $view->addExtension(new \App\TwigExtension\Asset($container['request']));
    $view->addExtension(new \App\TwigExtension\JsonDecode($container['request']));
    $view->addExtension(new \Awurth\Slim\Validation\ValidatorExtension($container['validator']));
    if ($container['cloudinary']) {
        $view->addExtension(new \App\TwigExtension\Cloudinary());
    }
    

    $view->getEnvironment()->addGlobal('flash', $container['flash']);
    $view->getEnvironment()->addGlobal('auth', $container['auth']);
    $view->getEnvironment()->addGlobal('config', $container['config']);
    $view->getEnvironment()->addGlobal('userAccess', $container['userAccess']);
    $view->getEnvironment()->addGlobal('currentRoute', $container['request']->getUri()->getPath());

    return $view;
};

// Bind Found Handler
$container['foundHandler'] = function() {
    return new \Slim\Handlers\Strategies\RequestResponseArgs();
};

// Bind Monolog Logging System if Enables
$container['logger'] = function($container) {

    // Stream Log output to file
    $logger = new Monolog\Logger($container['settings']['logger']['name']);
    $file_stream = new \Monolog\Handler\StreamHandler($container['settings']['logger']['log_path']);
    $logger->pushHandler($file_stream);
    
    //Stream log output to Logentries
    if ($container['settings']['logger']['le_token']) {
        $le_stream = new Logentries\Handler\LogentriesHandler($container['settings']['logger']['le_token']);
        $logger->pushHandler($le_stream);
    }
    
    return $logger;
};

// Bind Cloudinary
// Cloudinary PHP API
$container['cloudinary'] = function($container) {
    
    if ($container['settings']['cloudinary']['enabled']) {
        \Cloudinary::config(
        array( "cloud_name" => $container['settings']['cloudinary']['cloud_name'], 
            "api_key" => $container['settings']['cloudinary']['api_key'], 
            "api_secret" => $container['settings']['cloudinary']['api_secret'])
        );

        return new \Cloudinary;
    }else{
        return false;
    }
    

    


};