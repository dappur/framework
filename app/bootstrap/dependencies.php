<?php
// Initialize Container
$container = $app->getContainer();

// Configure Database
$database = $container['settings']['db']['use'];
$db = $container['settings']['db']['databases'][$database];
$capsule = new \Illuminate\Database\Capsule\Manager();
$capsule->addConnection($db);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$container['db'] = function () use ($capsule) {
    return $capsule;
};

// Bind config table from database
$container['config'] = function () use ($container) {
    $config = new \Dappur\Dappurware\SiteConfig($container);
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
    return (new \Dappur\Dappurware\Sentinel($container))->userAccess();
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
    return new \Dappur\Dappurware\Cookies($container);
};

// CSRF
$container['csrf'] = function ($container) {
    return new \Slim\Csrf\Guard;
};

// Bind Twig View
$container['view'] = function ($container) {
    if (substr($container['request']->getUri()->getPath(), 0, 10 ) === "/dashboard") {
        $template_path = $container['settings']['view']['template_path'] . $container->config['admin-theme'];
    }else{
        $template_path = $container['settings']['view']['template_path'] . $container->config['theme'];
    }

    $view = new \Slim\Views\Twig(
        
        $template_path,
        $container['settings']['view']['twig']
    );

    $view->addExtension(new \Slim\Views\TwigExtension(
        $container['router'],
        $container['request']->getUri()
    ));
    $view->addExtension(new \Twig_Extension_Debug());
    $view->addExtension(new \Dappur\TwigExtension\Asset($container['request']));
    $view->addExtension(new \Dappur\TwigExtension\JsonDecode($container['request']));
    $view->addExtension(new \Awurth\Slim\Validation\ValidatorExtension($container['validator']));
    $view->addExtension(new \Dappur\TwigExtension\Csrf($container['csrf']));
    if ($container['cloudinary']) {
        $view->addExtension(new \Dappur\TwigExtension\Cloudinary());
        $view->getEnvironment()->addGlobal('hasCloudinary', 1);
        $view->getEnvironment()->addGlobal('cloudinaryCmsUrl', \Dappur\Controller\Admin::getCloudinaryCMS($container));
    }else{
        $view->getEnvironment()->addGlobal('hasCloudinary', 0);
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
    $logger = new Monolog\Logger($container['settings']['logger']['log_name']);
    $file_stream = new \Monolog\Handler\StreamHandler($container['settings']['logger']['log_path'] . date("Y-m-d-") . $container['settings']['logger']['log_file_name']);
    $logger->pushHandler($file_stream);
    
    //Stream log output to Logentries
    if ($container['settings']['logger']['le_token'] != '') {
        $le_stream = new Logentries\Handler\LogentriesHandler($container['settings']['logger']['le_token']);
        $logger->pushHandler($le_stream);
    }
    
    return $logger;
};

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

// Mail Relay
$container['mail'] = function($container) {
    
    $mail_settings = $container['settings']['mail'];

    $mail = new \PHPMailer;

    switch ($mail_settings['relay']) {
        case 'phpmail':
            break;
        
        case 'mailgun':
            $mail->isSMTP();                                            // Set mailer to use SMTP
            $mail->Host = 'smtp.mailgun.org';                           // Specify main and backup server
            $mail->Port = 587;                                          // Set the SMTP port
            $mail->SMTPAuth = true;                                     // SMTP username
            $mail->Username = $mail_settings['mailgun']['username'];    // SMTP username from https://mailgun.com/cp/domains
            $mail->Password = $mail_settings['mailgun']['password'];    // SMTP password from https://mailgun.com/cp/domains
            $mail->SMTPSecure = 'tls';                                  // Enable encryption, 'ssl'
            break;
        
        case 'mandrill':
            $mail->IsSMTP();                                            // Set mailer to use SMTP
            $mail->Host = 'smtp.mandrillapp.com';                       // Specify main and backup server
            $mail->Port = 587;                                          // Set the SMTP port
            $mail->SMTPAuth = true;                                     // Enable SMTP authentication
            $mail->Username = $mail_settings['mandrill']['username'];   // SMTP username
            $mail->Password = $mail_settings['mandrill']['password'];   // SMTP password
            $mail->SMTPSecure = 'tls';                                  // Enable encryption, 'ssl' also accepted
            break;
        
        default:
            break;
    }
    
    return $mail; 
};