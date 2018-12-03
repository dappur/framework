<?php
// Requires Authentication
$app->group('/', function () use ($app) {
    // User Profile
    $app->group('profile', function () use ($app) {
        //Profile
        $this->map(['GET', 'POST'], '', 'Profile:profile')
            ->setName('profile');
        // Check Password
        $this->map(['POST'], '/password-check', 'Profile:checkPassword')
            ->setName('password-check');
        // Change Password
        $this->map(['POST'], '/change-password', 'Profile:changePassword')
            ->setName('change-password');

         $app->group('/2fa', function () use ($app) {
            $this->post('[/{validate}]', 'Profile:twoFactor')
                ->setName('2fa');
         });
    });
})
->add($container->get('csrf'))
->add(new Dappur\Middleware\Auth($container))
->add(new Dappur\Middleware\Maintenance($container))
->add(new Dappur\Middleware\PageConfig($container))
->add(new Dappur\Middleware\Seo($container))
->add(new Dappur\Middleware\ProfileCheck($container))
->add(new Dappur\Middleware\TwoFactorAuth($container))
->add(new Dappur\Middleware\RouteName($container));

// Incomplete Profile Page
$app->map(['GET','POST'], '/profile/incomplete', 'Profile:profileIncomplete')
    ->setName('profile-incomplete')
    ->add($container->get('csrf'))
    ->add(new Dappur\Middleware\Auth($container))
    ->add(new Dappur\Middleware\Maintenance($container))
    ->add(new Dappur\Middleware\PageConfig($container))
    ->add(new Dappur\Middleware\Seo($container))
    ->add(new Dappur\Middleware\TwoFactorAuth($container))
    ->add(new Dappur\Middleware\RouteName($container));

// 2 Factor Authentication
$app->map(['GET', 'POST'], '/2fa/confirm', 'Profile:twoFactorConfirm')
    ->setName('2fa-confirm')
    ->add($container->get('csrf'))
    ->add(new Dappur\Middleware\Auth($container))
    ->add(new Dappur\Middleware\Maintenance($container))
    ->add(new Dappur\Middleware\PageConfig($container))
    ->add(new Dappur\Middleware\Seo($container))
    ->add(new Dappur\Middleware\RouteName($container));
