<?php
require __DIR__ . '/vendor/autoload.php';

$settings = file_get_contents(__DIR__ . '/settings.json');
$settings = json_decode($settings, true);

$dbconf = $settings['db'];

$output = [
    'paths' => [
        'migrations' => 'database/migrations'
    ],
    'templates' => [
        'file' => '%%PHINX_CONFIG_DIR%%/database/templates/create-template.php'
    ],
    'migration_base_class' => '\Dappur\Migration\Migration',
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_database' => $settings['environment']
    ]
];

foreach ($dbconf as $key => $value) {
    $output['environments'][$key] = [
        'adapter' => $value['driver'],
        'host' => $value['host'],
        'name' => $value['database'],
        'user' => $value['username'],
        'pass' => $value['password'],
        'port' => $value['port'],
        'charset'   => $value['charset'],
        'collation' => $value['collation'],
        'prefix' => $value['prefix'],
        'timezone' => $value['timezone']
    ];
}

return $output;
