<?php

use \Dappur\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AdminLteConfig extends Migration
{
    public function up()
    {
        $config = new Dappur\Model\Config;
        $config->group_id = 2;
        $config->name = "adminlte-skin";
        $config->description = "AdminLTE Skin";
        $config->type_id = 2;
        $config->value = "skin-yellow";
        $config->save();
    }

    public function down()
    {
        \Dappur\Model\Config::where('name', "adminlte-skin")->first()->delete();
    }
}
