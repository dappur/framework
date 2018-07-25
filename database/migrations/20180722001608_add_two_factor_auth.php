<?php

use \Dappur\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddTwoFactorAuth extends Migration
{
    /**
    Write your reversible migrations using this method.

    Dappur Framework uses Laravel Eloquent ORM as it's database connector.

    More information on writing eloquent migrations is available here:
    https://laravel.com/docs/5.4/migrations

    Remember to use both the up() and down() functions in order to be able to roll back.

    Create Table Sample:
    $this->schema->create('sample', function (Blueprint $table) {
        $table->increments('id');
        $table->string('email')->unique();
        $table->string('last_name')->nullable();
        $table->string('first_name')->nullable();
        $table->timestamps();
    });

    Drop Table Sample:
    $this->schema->dropIfExists('sample');
    */
    
    public function up()
    {
        $this->schema->table('users', function (Blueprint $table) {
            $table->string('2fa')->after('password')->nullable();
        });

        // Add 2FA Config Group
        $config = new \Dappur\Model\ConfigGroups;
        $config->name = "2FA";
        $config->description = "2FA Settings";
        $config->save();

        $init_config = array(
            array($config->id, '2fa-enabled', 'Enable 2FA', 6, 0)
        );

        // Seed Config Table
        foreach ($init_config as $value) {
            $config = new Dappur\Model\Config;
            $config->group_id = $value[0];
            $config->name = $value[1];
            $config->description = $value[2];
            $config->type_id = $value[3];
            $config->value = $value[4];
            $config->save();
        }
    }

    public function down()
    {
        $this->schema->table('users', function (Blueprint $table) {
            $table->dropColumn('2fa');
        });

        // Delete Config and Group
        $delGroup = \Dappur\Model\ConfigGroups::where('name', '2FA')->first();
        $delConfig = \Dappur\Model\Config::where('group_id', $delGroup->id)->delete();
        $delGroup->delete();
    }
}
