<?php

// Global Middlewares go here
$app->add(new \Slim\Middleware\Session([
    'name' => $container->settings['framework'],
    'autorefresh' => true,
    'lifetime' => '1 hour'
]));
