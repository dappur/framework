<?php

use \Dappur\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AdminLteConfig extends Migration
{
    /**
    *
    * Write your reversible migrations using this method.
    *
    * Dappur Framework uses Laravel Eloquent ORM as it's database connector.
    *
    * More information on writing eloquent migrations is available here:
    * https://laravel.com/docs/5.4/migrations
    *
    * Remember to use both the up() and down() functions in order to be able to roll back.
    *
    *   Create Table Sample
    *   $this->schema->create('sample', function (Blueprint $table) {
    *       $table->increments('id');
    *       $table->string('email')->unique();
    *       $table->string('last_name')->nullable();
    *       $table->string('first_name')->nullable();
    *       $table->timestamps();
    *   });
    *
    *   Drop Table Sample
    *   $this->schema->dropIfExists('sample');
    */
    
    public function up()
    {
        $config = new Dappur\Model\Config;
        $config->group_id = 2;
        $config->name = "adminlte-skin";
        $config->description = "AdminLTE Skin";
        $config->type_id = 2;
        $config->value = "skin-black";
        $config->save();
    }

    public function down()
    {
        \Dappur\Model\Config::where('name', "adminlte-skin")->first()->delete();
    }
}
