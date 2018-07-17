<?php

$app->group('/dashboard', function () use ($app, $container) {

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
})
->add(new Dappur\Middleware\Auth($container))
->add(new Dappur\Middleware\Admin($container))
->add($container->get('csrf'));
