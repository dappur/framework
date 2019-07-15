<?php
// Initialize Container
$container = $app->getContainer();

// Configure Database
$db = $container['settings']['db'][$container['settings']['environment']];
$capsule = new \Illuminate\Database\Capsule\Manager();
$capsule->addConnection($db);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$container['db'] = function () use ($capsule) {
    return $capsule;
};

$container['session'] = function () use ($container) {
    return new \SlimSession\Helper;
};

$container['projectDir'] = function ($container) {
    $directory = __DIR__ . "/../../";
    return realpath($directory);
};

$container['publicDir'] = function ($container) {
    $directory = __DIR__ . "/../../public/";
    return realpath($directory);
};

$container['uploadDir'] = function ($container) {
    $directory = __DIR__ . "/../../public/uploads/";
    return realpath($directory);
};

// Bind config table from database
$container['config'] = function () use ($container) {
    $config = new \Dappur\Dappurware\SiteConfig;
    $config = $config->getGlobalConfig();

    return $config;
};

// Bind Sentinel Authorization plugin
$container['auth'] = function () {
    $sentinel = new \Cartalyst\Sentinel\Native\Facades\Sentinel(
        new \Cartalyst\Sentinel\Native\SentinelBootstrapper(
            __DIR__ . '/sentinel.php'
        )
    );

    return $sentinel->getSentinel();
};

// Bind User Permissions
$container['userAccess'] = function ($container) {
    return (new \Dappur\Dappurware\Sentinel($container))->userAccess();
};

// Bind Flash Messages
$container['flash'] = function () {
    return new \Slim\Flash\Messages();
};

// Bind Respect Validation
$container['validator'] = function () {
    return new \Awurth\SlimValidation\Validator();
};

// CSRF
$container['csrf'] = function ($container) {
    $guard = new \Slim\Csrf\Guard(
        $container->settings['csrf']['prefix'],
        $storage,
        null,
        $container->settings['csrf']['storage_limit'],
        $container->settings['csrf']['strength'],
        $container->settings['csrf']['persist_tokens']
    );

    $guard->setFailureCallable(
        function ($request, $response, $next) use ($container) {
            return $container['view']
                ->render($response, 'errors/csrf.twig')
                ->withHeader('Content-type', 'text/html')
                ->withStatus(401);
        }
    );

    return $guard;
};

// Bind Twig View
$container['view'] = function ($container) {
    $template_path = __DIR__ . '/../views/' . $container->config['theme'];
    if (strpos($container['request']->getUri()->getPath(), '/dashboard') !== false) {
        $template_path = __DIR__ . '/../views/' . $container->config['dashboard-theme'];
    }

    $view = new \Slim\Views\Twig(
        $template_path,
        $container['settings']['view']['twig']
    );

    // Add Twig Extensions
    $view->addExtension(
        new \Slim\Views\TwigExtension(
            $container['router'],
            $container['request']->getUri()
        )
    );
    $view->addExtension(new \Twig_Extension_Debug());
    $view->addExtension(new \Dappur\TwigExtension\Asset($container['request']));
    $view->addExtension(new \Dappur\TwigExtension\JsonDecode($container['request']));
    $view->addExtension(new \Dappur\TwigExtension\Oauth2($container));
    $view->addExtension(new \Dappur\TwigExtension\Recaptcha($container['settings']['recaptcha']));
    $view->addExtension(new \Dappur\TwigExtension\Csrf($container['csrf']));
    $view->addExtension(new \Awurth\SlimValidation\ValidatorExtension($container['validator']));
    $view->addExtension(new \Dappur\TwigExtension\Md5($container['request']));
    $view->addExtension(new \Dappur\TwigExtension\Gravatar($container['request']));
    $view->addExtension(new \Dappur\TwigExtension\Menus($container));
    $view->addExtension(new \Twig_Extension_StringLoader());

    // Globla Variables
    $view->getEnvironment()->addGlobal('flash', $container['flash']);
    $view->getEnvironment()->addGlobal('auth', $container['auth']);
    $view->getEnvironment()->addGlobal('config', $container['config']);
    $view->getEnvironment()->addGlobal('displayErrorDetails', $container['settings']['displayErrorDetails']);
    $view->getEnvironment()->addGlobal('currentRoute', $container['request']->getUri()->getPath());
    $view->getEnvironment()->addGlobal('requestParams', $container['request']->getParams());

    // Cloudinary View Settings
    $view->addExtension(new \Dappur\TwigExtension\Cloudinary());
    $view->getEnvironment()->addGlobal('hasCloudinary', 0);
    if ($container['cloudinary']) {
        $view->getEnvironment()->addGlobal('hasCloudinary', 1);
        if ($container->auth->check() && $container->auth->hasAccess('media.cloudinary')) {
            $view->getEnvironment()->addGlobal(
                'cloudinaryCmsUrl',
                \Dappur\Controller\Admin\Media::getCloudinaryCMS($container)
            );
            $view->getEnvironment()->addGlobal(
                'cloudinarySignature',
                \Dappur\Controller\Admin\Media::getCloudinaryCMS($container, true)
            );
            $view->getEnvironment()->addGLobal(
                'cloudinaryApiKey',
                $container['settings']['cloudinary']['api_key']
            );
            $view->getEnvironment()->addGLobal(
                'cloudinaryCloudName',
                $container['settings']['cloudinary']['cloud_name']
            );
        }
    }

    // Blog View Settings
    if ($container['config']['blog-enabled']) {
        // Get Categories With Count
        $blog_categories = new \Dappur\Model\BlogCategories;
        $blog_categories = $blog_categories->withCount(
            ['posts' => function ($query) {
                $query->where('blog_posts.status', 1);
            }]
        )
        ->whereHas(
            'posts',
            function ($query) {
                $query->where('blog_posts.status', 1);
            }
        )
        ->get();

        // Get Tags With Count
        $blog_tags = new \Dappur\Model\BlogTags;
        $blog_tags = $blog_tags->withCount([
            'posts' => function ($query) {
                $query->where('blog_posts.status', 1);
            }
        ])
            ->whereHas('posts', function ($query) {
                $query->where('blog_posts.status', 1);
            })
            ->get();

        // Get Recent Posts
        $blogPosts = \Dappur\Model\BlogPosts::orderBy('publish_at', 'DESC')
            ->where('publish_at', '<=', \Carbon\Carbon::now())
            ->where('status', 1)
            ->whereNotNull('featured_image')
            ->skip(0)
            ->take(4)
            ->get();

        // Get Recent Comments
        $blogComments = \Dappur\Model\BlogPostsComments::orderBy('created_at', 'DESC')
            ->where('status', 1)
            ->skip(0)
            ->take(4)
            ->get();

        $view->getEnvironment()->addGlobal('blogCategories', $blog_categories);
        $view->getEnvironment()->addGlobal('blogTags', $blog_tags);
        $view->getEnvironment()->addGlobal('blogRecent', $blogPosts);
        $view->getEnvironment()->addGlobal('blogComments', $blogComments);
    }
    
    
    
    $page_name = $container['request']->getAttribute('name');
    if (strpos($container['request']->getUri()->getPath(), '/dashboard') !== false) {
        $page_settings = new \Dappur\Model\ConfigGroups;
        $page_settings = $page_settings
            ->select('page_name')
            ->whereNotNull('page_name')
            ->groupBy('page_name')
            ->orderBy('page_name')
            ->get();
        $view->getEnvironment()->addGlobal('userAccess', $container['userAccess']);
        $view->getEnvironment()->addGlobal('pageSettings', $page_settings);
    }
    return $view;
};

// Bind Found Handler
$container['foundHandler'] = function () {
    return new \Slim\Handlers\Strategies\RequestResponseArgs();
};

// Bind Monolog Logging System
$container['logger'] = function ($container) {

    // Stream Log output to file
    $logger = new Monolog\Logger($container['settings']['logger']['log_name']);
    $logPath = __DIR__ . "/../../storage/log/monolog/";
    $logFile =  date("Y-m-d-") . $container['settings']['logger']['log_file_name'];
    $fileStream = new \Monolog\Handler\StreamHandler($logPath . $logFile);
    $logger->pushHandler($fileStream);
    
    //Stream log output to Logentries
    if ($container['settings']['logger']['le_token'] != '') {
        $le_stream = new Logentries\Handler\LogentriesHandler($container['settings']['logger']['le_token']);
        $logger->pushHandler($le_stream);
    }
    
    return $logger;
};

// Cloudinary PHP API
$container['cloudinary'] = function ($container) {
    if ($container['settings']['cloudinary']['enabled']) {
        \Cloudinary::config(
            array( "cloud_name" => $container['settings']['cloudinary']['cloud_name'],
                "api_key" => $container['settings']['cloudinary']['api_key'],
                "api_secret" => $container['settings']['cloudinary']['api_secret']
            )
        );

        return new \Cloudinary;
    } else {
        return false;
    }
};

// Mail Relay
$container['mail'] = function ($container) {
    $mailSettings = $container['settings']['mail'];

    $mail = new \PHPMailer\PHPMailer\PHPMailer;

    switch ($mailSettings['relay']) {
        case 'phpmail':
            break;
        
        case 'smtp':
            $mail->isSMTP();
            $mail->Host = $mailSettings['smtp']['host'];
            $mail->Port = $mailSettings['smtp']['port'];
            if ($mailSettings['smtp']['smtp_auth']) {
                $mail->SMTPAuth = true;
                $mail->Username = $mailSettings['smtp']['username'];
                $mail->Password = $mailSettings['smtp']['password'];
            }
            $mail->SMTPSecure = $mailSettings['smtp']['smtp_secure'];
            break;
        
        default:
            break;
    }
    return $mail;
};
