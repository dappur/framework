<?php
// Non Logged Users
$app->group('/webhooks', function () use ($app, $container, $settings) {
    // Contact
    $this->map(['POST'], '/mailgun', 'WebhooksMailgun:hook')
        ->setName('contact');
})
->add(new Dappur\Middleware\Maintenance($container));
