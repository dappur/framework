<?php 

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
    ->add(new Dappur\Middleware\TwoFactorAuth($container))
    ->add(new Dappur\Middleware\RouteName($container));
}