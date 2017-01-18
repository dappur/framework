<?php
require __DIR__ . '/vendor/autoload.php';
$settings = require __DIR__ . '/app/bootstrap/settings.php';
$dbconf = $settings['settings']['database'];


return [
  'paths' => [
    'migrations' => 'database/migrations'
  ],
  'migration_base_class' => '\App\Migration\Migration',
  'environments' => [
    'default_migration_table' => 'phinxlog',
    'default_database' => 'dev',
    'dev' => [
      'adapter' => $dbconf['driver'],
      'host' => $dbconf['host'],
      'name' => $dbconf['database'],
      'user' => $dbconf['username'],
      'pass' => $dbconf['password'],
      'port' => $dbconf['port']
    ]
  ]
];
