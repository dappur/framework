<?php
// Requires Authentication
$app->group('', function () use ($app) {
    // User Profile
    $app->group('/profile', function () use ($app) {
        //Profile
        $this->map(['GET', 'POST'], '/', 'Profile:profile')
            ->setName('profile');
        // Check Password
        $this->map(['POST'], '/password-check', 'Profile:checkPassword')
            ->setName('password-check');
        // Change Password
        $this->map(['POST'], '/change-password', 'Profile:changePassword')
            ->setName('change-password');
    });
})
->add($container->get('csrf'))
->add(new Dappur\Middleware\Auth($container))
->add(new Dappur\Middleware\Maintenance($container))
->add(new Dappur\Middleware\PageConfig($container))
->add(new Dappur\Middleware\Seo($container))
->add(new Dappur\Middleware\ProfileCheck($container));

// Incomplete Profile Page
$app->map(['GET','POST'], '/profile/incomplete', 'Profile:profileIncomplete')
        ->setName('profile-incomplete')
        ->add($container->get('csrf'))
        ->add(new Dappur\Middleware\Auth($container))
        ->add(new Dappur\Middleware\Maintenance($container))
        ->add(new Dappur\Middleware\PageConfig($container))
        ->add(new Dappur\Middleware\Seo($container));
