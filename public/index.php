<?php
session_start();

require __DIR__ . '/../vendor/autoload.php';

$settings = require __DIR__ . '/../app/bootstrap/settings.php';
$app = new Slim\App($settings);

require __DIR__ . '/../app/bootstrap/dependencies.php';

require __DIR__ . '/../app/bootstrap/middleware.php';

require __DIR__ . '/../app/bootstrap/controllers.php';

require __DIR__ . '/../app/bootstrap/routes.php';

$app->run();
