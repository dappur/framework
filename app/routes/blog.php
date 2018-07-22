<?php

// Blog Front End
$app->group('/blog', function () use ($app) {
    $this->get('[/{page}]', 'Blog:blog')
        ->setName('blog');

    $this->map(['GET', 'POST'], '/{year}/{month}/{day}/{slug}', 'Blog:blogPost')
        ->setName('blog-post');

    $this->get('/author/{username}[/{page}]', 'Blog:blogAuthor')
        ->setName('blog-author');

    $this->get('/tag/{slug}[/{page}]', 'Blog:blogTag')
        ->setName('blog-tag');

    $this->get('/category/{slug}[/{page}]', 'Blog:blogCategory')
        ->setName('blog-category');
})
->add($container->get('csrf'))
->add(new Dappur\Middleware\Maintenance($container))
->add(new Dappur\Middleware\BlogCheck($container))
->add(new Dappur\Middleware\PageConfig($container))
->add(new Dappur\Middleware\Seo($container))
->add(new Dappur\Middleware\ProfileCheck($container))
->add(new Dappur\Middleware\TwoFactorAuth($container));
