<?php

$app->group('/dashboard', function () use ($app, $container) {

    // Dashboard Home
    $app->get('', 'Admin:dashboard')
        ->setName('dashboard');

    // Users Routes
    $app->group('/users', function () use ($app) {
        // User List
        $app->get('', 'AdminUsers:users')
            ->setName('admin-users');
        // Add New User
        $app->map(['GET', 'POST'], '/add', 'AdminUsers:usersAdd')
            ->setName('admin-users-add');
        // Edit User
        $app->map(['GET', 'POST'], '/edit/{user_id}', 'AdminUsers:usersEdit')
            ->setName('admin-users-edit');
        // Delete User
        $app->post('/delete', 'AdminUsers:usersDelete')
            ->setName('admin-users-delete');
        // User Ajax
        $app->get('/datatables', 'AdminUsers:dataTables')
            ->setName('admin-users-datatables');

        //User Roles
        $app->group('/roles', function () use ($app) {
            $app->post('/delete', 'AdminRoles:rolesDelete')
                ->setName('admin-roles-delete');
            $app->map(['GET', 'POST'], '/edit/{role}', 'AdminRoles:rolesEdit')
                ->setName('admin-roles-edit');
            $app->post('/add', 'AdminRoles:rolesAdd')
                ->setName('admin-roles-add');
        });
    });

    // Global Settings
    $app->map(['GET', 'POST'], '/settings/global', 'AdminSettings:settingsGlobal')
        ->setName('settings-global');
    $app->post('/settings/add', 'AdminSettings:settingsGlobalAdd')
        ->setName('settings-global-add');
    $app->post('/settings/group/add', 'AdminSettings:settingsGlobalAddGroup')
        ->setName('settings-global-group-add');
    $app->post('/settings/group/delete', 'AdminSettings:settingsGlobalDeleteGroup')
        ->setName('settings-global-group-delete');

    $app->map(['GET', 'POST'], '/settings/page-settings/{page_name}', 'AdminSettings:settingsPage')
        ->setName('settings-page');
    
    // View Settings.json
    $app->map(['GET'], '/developer/settings', 'AdminSettings:settingsDeveloper')
        ->setName('settings-developer');

    // View Logs
    $app->map(['GET'], '/developer/logs', 'AdminSettings:developerLogs')
        ->setName('developer-logs');

    // My Account
    $app->map(['GET', 'POST'], '/my-account', 'Admin:myAccount')
        ->setName('my-account');

    // Media Manager
    $app->group('/media', function () use ($app) {
        // Media
        $app->map(['GET'], '', 'AdminMedia:media')
            ->setName('admin-media');
        // Media
        $app->map(['POST'], '/folder', 'AdminMedia:mediaFolder')
            ->setName('admin-media-folder');

        $app->map(['POST'], '/folder/new', 'AdminMedia:mediaFolderNew')
            ->setName('admin-media-folder-new');

        $app->map(['POST'], '/upload', 'AdminMedia:mediaUpload')
            ->setName('admin-media-upload');

        $app->map(['POST'], '/delete', 'AdminMedia:mediaDelete')
            ->setName('admin-media-delete');

        $app->map(['GET'], '/cloudinary-sign', 'AdminMedia:cloudinarySign')
            ->setName('cloudinary-sign');
    });

    // Email Manager
    $app->group('/email', function () use ($app) {
        $app->map(['GET'], '', 'AdminEmail:email')
            ->setName('admin-email');

        $app->map(['GET'], '/details/{email}', 'AdminEmail:emailDetails')
            ->setName('admin-email-details');

        $app->map(['GET','POST'], '/new', 'AdminEmail:emailNew')
            ->setName('admin-email-new');

        $app->map(['GET'], '/templates', 'AdminEmail:templates')
            ->setName('admin-email-template');

        $app->map(['GET','POST'], '/templates/add', 'AdminEmail:templatesAdd')
            ->setName('admin-email-template-add');

        $app->map(['GET','POST'], '/templates/edit/{template_id}', 'AdminEmail:templatesEdit')
            ->setName('admin-email-template-edit');

        $app->map(['POST'], '/templates/delete', 'AdminEmail:templatesDelete')
            ->setName('admin-email-template-delete');

        $app->map(['POST'], '/test', 'AdminEmail:testEmail')
            ->setName('admin-email-test');

        // Email Ajax
        $app->get('/datatables', 'AdminEmail:dataTables')
            ->setName('admin-email-datatables');
    });

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

    // Oauth Manager
    $app->group('/oauth2', function () use ($app) {
        $app->map(['GET'], '', 'AdminOauth2:providers')
            ->setName('admin-oauth2');

        $app->map(['GET','POST'], '/add', 'AdminOauth2:oauth2Add')
            ->setName('admin-oauth2-add');

        $app->map(['GET','POST'], '/edit/{provider_id}', 'AdminOauth2:oauth2Edit')
            ->setName('admin-oauth2-edit');

        $app->map(['POST'], '/enable[/login]', 'AdminOauth2:oauth2Enable')
            ->setName('admin-oauth2-enable');

        $app->map(['POST'], '/disable[/login]', 'AdminOauth2:oauth2Disable')
            ->setName('admin-oauth2-disable');

        $app->map(['POST'], '/delete', 'AdminOauth2:oauth2Delete')
            ->setName('admin-oauth2-delete');
    });

    // Contact Requests
    $app->map(['GET'], '/contact', 'Admin:contact')
        ->setName('admin-contact');

    // Blog Admin
    $app->group('/blog', function () use ($app) {
        // Main Blog Admin
        $app->get('', 'AdminBlog:blog')
        ->setName('admin-blog');

        $app->get('/datatables', 'AdminBlog:datatables')
        ->setName('admin-blog-datatables');

        // Blog Post Actions
        $app->group('', function () use ($app) {
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
        $app->group('/comments', function () use ($app) {
            // View Comments
            $app->get('', 'AdminBlogComments:comments')
                ->setName('admin-blog-comments');
            $app->get('/{comment_id}', 'AdminBlogComments:commentDetails')
                ->setName('admin-blog-comment-details');
            // Unpublish Comment
            $app->post('/publish', 'AdminBlogComments:commentUnpublish')
                ->setName('admin-blog-comment-unpublish');
            // Publish Comment
            $app->post('/unpublish', 'AdminBlogComments:commentPublish')
                ->setName('admin-blog-comment-publish');
            // Delte Comment
            $app->post('/delete', 'AdminBlogComments:commentDelete')
                ->setName('admin-blog-comment-delete');
        });

        // Blog Replies
        $app->group('/replies', function () use ($app) {

            // Unpublish Comment
            $app->post('/publish', 'AdminBlogComments:replyUnpublish')
                ->setName('admin-blog-reply-unpublish');
            // Publish Comment
            $app->post('/unpublish', 'AdminBlogComments:replyPublish')
                ->setName('admin-blog-reply-publish');
            // Delte Comment
            $app->post('/delete', 'AdminBlogComments:replyDelete')
                ->setName('admin-blog-reply-delete');
        });

        // Blog Categories Actions
        $app->group('/categories', function () use ($app) {
            // Delete Category
            $app->post('/delete', 'AdminBlogCategories:categoriesDelete')
                ->setName('admin-blog-categories-delete');
            // Edit Category
            $app->map(['GET', 'POST'], '/edit[/{category}]', 'AdminBlogCategories:categoriesEdit')
                ->setName('admin-blog-categories-edit');
            // Add Category
            $app->post('/add', 'AdminBlogCategories:categoriesAdd')
                ->setName('admin-blog-categories-add');
        });

        // Blog Tag Actions
        $app->group('/tags', function () use ($app) {
            // Delete Tag
            $app->post('/delete', 'AdminBlogTags:tagsDelete')
                ->setName('admin-blog-tags-delete');
            // Edit Tag
            $app->map(['GET', 'POST'], '/edit[/{tag_id}]', 'AdminBlogTags:tagsEdit')
                ->setName('admin-blog-tags-edit');
            // Delete Tag
            $app->post('/add', 'AdminBlogTags:tagsAdd')
                ->setName('admin-blog-tags-add');
        });

        // Preview Blog Post
        $app->get('/preview[/{slug}]', 'AdminBlog:blogPreview')
            ->setName('admin-blog-preview');
    })
    ->add(new Dappur\Middleware\BlogCheck($container));
})
->add(new Dappur\Middleware\Auth($container))
->add(new Dappur\Middleware\Admin($container))
->add($container->get('csrf'));
