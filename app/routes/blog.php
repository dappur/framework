<?php

// Blog
$app->group('/dashboard/blog', function() use ($app) {
    // Main Blog Admin
    $app->get('', 'AdminBlog:blog')
    ->setName('admin-blog');

    // Blog Post Actions
    $app->group('/posts', function() use ($app) {
        // Unpublish Blog
        $app->post('/unpublish', 'AdminBlog:blogUnpublish')
            ->setName('admin-blog-unpublish');
        // Publish Blog
        $app->post('/publish', 'AdminBlog:blogPublish')
            ->setName('admin-blog-publish');
        // Edit Blog Post
        $app->map(['GET', 'POST'], '/edit[/{post_id}]', 'AdminBlog:blogEdit')
            ->setName('admin-blog-edit');
        // Add Blog Post
        $app->map(['GET', 'POST'], '/add', 'AdminBlog:blogAdd')
            ->setName('admin-blog-add');
        // Delete Blog Post
        $app->post('/delete', 'AdminBlog:blogDelete')
            ->setName('admin-blog-delete');
    });

    // Blog Categories Actions
    $app->group('/categories', function() use ($app) {
        // Delete Category
        $app->post('/delete', 'AdminBlog:categoriesDelete')
            ->setName('admin-blog-categories-delete');
        // Edit Category
        $app->map(['GET', 'POST'], '/edit[/{category}]', 'AdminBlog:categoriesEdit')
            ->setName('admin-blog-categories-edit');
        // Add Category
        $app->post('/add', 'AdminBlog:categoriesAdd')
            ->setName('admin-blog-categories-add');
    });

    // Blog Tag Actions
    $app->group('/tags', function() use ($app) {
        // Delete Tag
        $app->post('/delete', 'AdminBlog:tagsDelete')
            ->setName('admin-blog-tags-delete');
        // Edit Tag
        $app->map(['GET', 'POST'], '/edit[/{tag_id}]', 'AdminBlog:tagsEdit')
            ->setName('admin-blog-tags-edit');
        // Delete Tag
        $app->post('/add', 'AdminBlog:tagsAdd')
            ->setName('admin-blog-tags-add');
    });

    // Preview Blog Post
    $app->get('/preview[/{slug}]', 'AdminBlog:blogPreview')
        ->setName('admin-blog-preview');
})
->add(new Dappur\Middleware\Auth($container))
->add(new Dappur\Middleware\Admin($container))
->add($container->get('csrf'));

// Blog
$app->group('/blog', function() use ($app) {
    $app->get('', 'Blog:blog')
    ->setName('blog');

    $app->get('/{year}/{month}/{day}/{slug}', 'Blog:blogPost')
    ->setName('blog-post');
})
->add($container->get('csrf'))
->add(new Dappur\Middleware\Maintenance($container))
->add(new Dappur\Middleware\PageConfig($container));