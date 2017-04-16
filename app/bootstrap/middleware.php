<?php

$app->add(new \Dappur\Middleware\CsrfMiddleware($container));
$app->add(new \Slim\Csrf\Guard());
