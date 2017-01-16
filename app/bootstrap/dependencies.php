<?php
// Initialize Container
$container = $app->getContainer();

// Configure Database
$db = $container['settings']['database'];
$capsule = new \Illuminate\Database\Capsule\Manager();
$capsule->addConnection($db);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$container['db'] = function () use ($capsule) {
    return $capsule;
};

// Get config table from database
$container['config'] = function () use ($container) {
    $config = new \App\Dappurware\SiteConfig($container);
    return $config->getConfig();
};

// Load Sentinel Authorization plugin
$container['auth'] = function () {
    $sentinel = new \Cartalyst\Sentinel\Native\Facades\Sentinel(
        new \Cartalyst\Sentinel\Native\SentinelBootstrapper(__DIR__ . '/sentinel.php')
    );

    return $sentinel->getSentinel();
};

// Get User Permissions
$container['userAccess'] = function($container) {
    return (new \App\Dappurware\Sentinel($container))->userAccess();
};

// Load Flash Messages
$container['flash'] = function () {
    return new \Slim\Flash\Messages();
};

// Load Respect Validation
$container['validator'] = function () {
    return new \Awurth\Slim\Validation\Validator();
};

// Load View
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
    $view->addExtension(new \Awurth\Slim\Validation\ValidatorExtension($container['validator']));

    $view->getEnvironment()->addGlobal('flash', $container['flash']);
    $view->getEnvironment()->addGlobal('auth', $container['auth']);
    $view->getEnvironment()->addGlobal('config', $container['config']);
    $view->getEnvironment()->addGlobal('userAccess', $container['userAccess']);
    $view->getEnvironment()->addGlobal('currentRoute', $container['request']->getUri()->getPath());

    return $view;
};

//Load Found Handler
$container['foundHandler'] = function() {
    return new \Slim\Handlers\Strategies\RequestResponseArgs();
};

//Initialize FigCookies
$container['cookies'] = function() {
    return new \Dflydev\FigCookies\FigRequestCookies();
};

//Initialize Monolog Logging System
$container['logger'] = function($container) {

    // Stream Log output to file
    $logger = new Monolog\Logger($container['settings']['logger']['name']);
    $file_stream = new \Monolog\Handler\StreamHandler($container['settings']['logger']['log_path'] . $container['settings']['logger']['log_file_name']);
    $logger->pushHandler($file_stream);
    
    //Stream log output to Logentries
    if ($container['settings']['logger']['le_token']) {
        $le_stream = new Logentries\Handler\LogentriesHandler($container['settings']['logger']['le_token']);
        $logger->pushHandler($le_stream);
    }
    
    return $logger;
};