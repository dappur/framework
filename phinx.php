<?php
require __DIR__ . '/vendor/autoload.php';
$settings = file_get_contents( __DIR__ . '/app/bootstrap/settings.json');
$settings = json_decode($settings, TRUE);

$database = $settings['db']['use'];

$dbconf = $settings['db']['databases'][$database];


return [
  	'paths' => [
    	'migrations' => 'database/migrations'
  	],
  	'templates' => [
        'file' => '%%PHINX_CONFIG_DIR%%/database/templates/create-template.php'
  	],
  	'migration_base_class' => '\Dappur\Migration\Migration',
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
