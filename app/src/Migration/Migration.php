<?php

namespace App\Migration;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;
use Phinx\Migration\AbstractMigration;
use Cartalyst\Sentinel\Native\Facades\Sentinel;
use Cartalyst\Sentinel\Native\SentinelBootstrapper;

class Migration extends AbstractMigration {
    
    public $capsule;
    public $schema;
    public $sentinel;

    public function init()
    {
        $settings = require __DIR__ . '/../../bootstrap/settings.php';
        $dbconf = $settings['settings']['database'];

        $this->capsule = new Capsule;
        $this->capsule->addConnection([
          'driver'    => 'mysql',
          'host'      => $dbconf['host'],
          'port'      => $dbconf['port'],
          'database'  => $dbconf['database'],
          'username'  => $dbconf['username'],
          'password'  => $dbconf['password'],
          'charset'   => 'utf8',
          'collation' => 'utf8_unicode_ci',
        ]);

        $this->capsule->bootEloquent();
        $this->capsule->setAsGlobal();
        $this->schema = $this->capsule->schema();

        $this->sentinel = (new Sentinel(new SentinelBootstrapper(__DIR__ . '/../../bootstrap/sentinel.php')))->getSentinel();

    }
}