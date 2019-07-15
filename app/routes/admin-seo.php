<?php

$app->group('/dashboard', function () use ($app, $container) {

    // SEO Manager
    $app->group('/seo', function () use ($app) {
        $app->map(['GET'], '', 'AdminSeo:seo')
            ->setName('admin-seo');
        $app->map(['GET','POST'], '/add', 'AdminSeo:seoAdd')
            ->setName('admin-seo-add');

        $app->map(['GET','POST'], '/edit/{seo_id}', 'AdminSeo:seoEdit')
            ->setName('admin-seo-edit');

        $app->map(['POST'], '/delete', 'AdminSeo:seoDelete')
            ->setName('admin-seo-delete');

        $app->map(['POST'], '/default', 'AdminSeo:seoDefault')
            ->setName('admin-seo-default');
    });
})
->add(new Dappur\Middleware\Auth($container))
->add(new Dappur\Middleware\Admin($container))
->add($container->get('csrf'))
->add(new Dappur\Middleware\TwoFactorAuth($container))
->add(new Dappur\Middleware\RouteName($container));
