<?php

require __DIR__ . '/../../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager;

$settings = require __DIR__ . '/settings.php';

$config = $settings['db'];

$capsule = new Manager();
$capsule->addConnection($config);
$capsule->setAsGlobal();
$capsule->bootEloquent();

require __DIR__ . '/database/auth.php';

require __DIR__ . '/database/config.php';
