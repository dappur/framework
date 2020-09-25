<?php
// Non Logged Users
$app->group('/', function () use ($app, $container, $settings) {
    // Contact
    $this->map(['GET', 'POST'], 'contact', 'App:contact')
        ->setName('contact');
    // Cron Jobs
    $this->map(['GET'], 'cron', 'Cron:run')
        ->setName('cron');
    // Oauth
    $this->map(['GET'], 'oauth/{slug}', 'Oauth2:oauth2')
        ->setName('oauth');
})
->add($container->get('csrf'))
->add(new Dappur\Middleware\Maintenance($container))
->add(new Dappur\Middleware\PageConfig($container))
->add(new Dappur\Middleware\Seo($container))
->add(new Dappur\Middleware\ProfileCheck($container))
->add(new Dappur\Middleware\TwoFactorAuth($container))
->add(new Dappur\Middleware\RouteName($container));

// Maintenance Mode Bypasses All Middleware
$app->map(['GET'], '/maintenance', 'App:maintenance')
        ->setName('maintenance-mode')
        ->add(new Dappur\Middleware\PageConfig($container))
        ->add(new Dappur\Middleware\Seo($container))
        ->add(new Dappur\Middleware\RouteName($container));

// Assets Bypass All Middleware
$app->map(['GET'], '/asset', 'App:asset')
        ->setName('asset');

// CSRF Bypasses Middleware
$app->map(['GET'], '/csrf', 'App:csrf')
    ->setName('csrf')
    ->add($container->get('csrf'));

//Deployment
$app->map(['GET', 'POST'], '/' . $settings['deployment']['deploy_url'], 'Deploy:deploy')
    ->setName('deploy')
    ->add(new Dappur\Middleware\Deploy($container));

// Robots.txt
$app->map(['GET'], '/robots.txt', 'Robots:view')
        ->setName('robots');
