<?php
// Non Logged Users
$app->group('/', function () {
	// Home Page
	$this->map(['GET'], '', 'App:home')
		->setName('home');
	// Privacy Policy
	$this->map(['GET'], 'privacy', 'App:privacy')
		->setName('privacy');
	// Contact 
	$this->map(['GET', 'POST'], 'contact', 'App:contact')
		->setName('contact');
	// Terms and Conditions
	$this->map(['GET'], 'terms', 'App:terms')
		->setName('terms');
	// CSRF
	$this->map(['GET'], 'csrf', 'App:csrf')
		->setName('csrf');
})
->add($container->get('csrf'))
->add(new Dappur\Middleware\Maintenance($container))
->add(new Dappur\Middleware\PageConfig($container))
->add(new Dappur\Middleware\Seo($container));

// Requires Authentication
$app->group('/', function () use($app) {
	// User Profile
	$app->group('profile', function() use ($app) {
		//Profile
		$this->map(['GET', 'POST'], '', 'App:profile')
			->setName('profile');
		// Check Password
		$this->map(['POST'], '/password-check', 'App:checkPassword')
			->setName('password-check');
		// Change Password
		$this->map(['POST'], '/change-password', 'App:changePassword')
			->setName('change-password');
	});
})
->add($container->get('csrf'))
->add(new Dappur\Middleware\Auth($container))
->add(new Dappur\Middleware\Maintenance($container))
->add(new Dappur\Middleware\PageConfig($container))
->add(new Dappur\Middleware\Seo($container));

// Maintenance Mode Bypasses All Middleware
$app->map(['GET'], '/maintenance', 'App:maintenance')
		->setName('maintenance-mode');

// Assets Bypass All Middleware
$app->map(['GET'], '/asset', 'App:asset')
		->setName('asset');