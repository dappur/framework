<?php

namespace Dappur\Migration;

use Phinx\Migration\AbstractMigration;

class Migration extends AbstractMigration
{
    public $capsule;
    public $schema;
    public $sentinel;

    public function init()
    {
        $settings = file_get_contents(__DIR__ . '/../../../settings.json');
        $settings = json_decode($settings, true);
        $dbconf = $settings['db'][$settings['environment']];

        $this->capsule = new \Illuminate\Database\Capsule\Manager;
        $this->capsule->addConnection([
          'driver'    => 'mysql',
          'host'      => $dbconf['host'],
          'port'      => $dbconf['port'],
          'database'  => $dbconf['database'],
          'username'  => $dbconf['username'],
          'password'  => $dbconf['password'],
          'charset'   => $dbconf['charset'],
          'collation' => $dbconf['collation'],
          'timezone' => $dbconf['timezone']
        ]);

        $this->capsule->bootEloquent();
        $this->capsule->setAsGlobal();
        $this->schema = $this->capsule->schema();

        $this->sentinel = (
            new \Cartalyst\Sentinel\Native\Facades\Sentinel(
                new \Cartalyst\Sentinel\Native\SentinelBootstrapper(__DIR__ . '/../../bootstrap/sentinel.php')
            )
        )->getSentinel();
    }
}
