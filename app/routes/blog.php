<?php

// Blog
$app->group('/dashboard/blog', function() use ($app) {
    // Main Blog Admin
    $app->get('', 'AdminBlog:blog')
    ->setName('admin-blog');

    // Blog Post Actions
    $app->group('', function() use ($app) {
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

    // Blog Comments
    $app->group('/comments', function() use ($app) {
        // View Comments
        $app->get('', 'AdminBlog:comments')
            ->setName('admin-blog-comments');
        $app->get('/{comment_id}', 'AdminBlog:commentDetails')
            ->setName('admin-blog-comment-details');
        // Unpublish Comment
        $app->post('/publish', 'AdminBlog:commentUnpublish')
            ->setName('admin-blog-comment-unpublish');
        // Publish Comment
        $app->post('/unpublish', 'AdminBlog:commentPublish')
            ->setName('admin-blog-comment-publish');
        // Delte Comment
        $app->post('/delete', 'AdminBlog:commentDelete')
            ->setName('admin-blog-comment-delete');
    });

    // Blog Replies
    $app->group('/replies', function() use ($app) {

        // Unpublish Comment
        $app->post('/publish', 'AdminBlog:replyUnpublish')
            ->setName('admin-blog-reply-unpublish');
        // Publish Comment
        $app->post('/unpublish', 'AdminBlog:replyPublish')
            ->setName('admin-blog-reply-publish');
        // Delte Comment
        $app->post('/delete', 'AdminBlog:replyDelete')
            ->setName('admin-blog-reply-delete');
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
->add(new Dappur\Middleware\BlogCheck($container))
->add($container->get('csrf'));

// Blog
$app->group('/blog', function() use ($app) {
    $app->get('[/{page}]', 'Blog:blog')
        ->setName('blog');

    $app->get('/{year}/{month}/{day}/{slug}', 'Blog:blogPost')
        ->setName('blog-post');

    $app->get('/author/{username}[/{page}]', 'Blog:blogAuthor')
        ->setName('blog-author');

    $app->get('/tag/{slug}[/{page}]', 'Blog:blogTag')
        ->setName('blog-tag');

    $app->get('/category/{slug}[/{page}]', 'Blog:blogCategory')
        ->setName('blog-category');
})
->add($container->get('csrf'))
->add(new Dappur\Middleware\Maintenance($container))
->add(new Dappur\Middleware\BlogCheck($container))
->add(new Dappur\Middleware\PageConfig($container));