<?php

$container = $app->getContainer();

$db = $container['settings']['db'];

$capsule = new \Illuminate\Database\Capsule\Manager();
$capsule->addConnection($db);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$container['db'] = function () use ($capsule) {
    return $capsule;
};

$container['config'] = function () use ($container) {
    //Generate Config Array
    $config = $container->db->table('config')->get();
    $cfg = array();
    foreach($config as $cfgkey => $cfgvalue){
        $cfg[$cfgvalue->name] = $cfgvalue->value;
    }

    $cfg['copyright-year'] = date("Y");

    //Set Default Timezone
    date_default_timezone_set($cfg['timezone']);

    return $cfg;
};

$container['auth'] = function () {
    $sentinel = new \Cartalyst\Sentinel\Native\Facades\Sentinel(
        new \Cartalyst\Sentinel\Native\SentinelBootstrapper(__DIR__ . '/sentinel.php')
    );

    return $sentinel->getSentinel();
};

// Get User Permissions
$container['userAccess'] = function($container) {

    $sentinel = $container->auth;
    $user = $sentinel->check();

    $permissions = [];
    $rolesSlugs = [];

    if ($user) {
        foreach ($user->getRoles() as $key => $value) {
            $rolesSlugs[] = $value->slug;
        }

        $roles = $sentinel->getRoleRepository()->createModel()->whereIn('slug', $rolesSlugs)->get();

        foreach ($roles as $role) {
            foreach ($role->permissions as $key => $value) {
                if ($value == 1) {
                    $permissions[] = $key;
                }
            }
        }

        foreach ($user->permissions as $key => $value) {
            if (!in_array($key, $permissions) && $value == 1) {
                $permissions[] = $key;
            }

            if (in_array($key, $permissions) && $value == 0) {
                $key = array_search($key, $permissions);
                unset($permissions[$key]);
            }
        }

    }
    return array('roles' => $rolesSlugs, 'permissions' => $permissions);
    
};

$container['flash'] = function () {
    return new \Slim\Flash\Messages();
};

$container['validator'] = function () {
    return new \Awurth\Slim\Validation\Validator();
};

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

$container['foundHandler'] = function() {
    return new \Slim\Handlers\Strategies\RequestResponseArgs();
};


$container['logger'] = function($container) {

    $logger = new Monolog\Logger($container['settings']['logger']['name']);
    if($container['settings']['logger']['log_path']){
        $file_stream = new \Monolog\Handler\StreamHandler($container['settings']['logger']['log_path']);
        $logger->pushHandler($file_stream);
    }
    
    if ($container['settings']['logger']['le_token']) {
        $le_stream = new Logentries\Handler\LogentriesHandler($container['settings']['logger']['le_token']);
        $logger->pushHandler($le_stream);
    }

    
    
    
    return $logger;
};