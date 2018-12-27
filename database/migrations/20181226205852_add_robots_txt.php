<?php

use \Dappur\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddRobotsTxt extends Migration
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
        $this->schema->create('robots', function (Blueprint $table) {
            $table->increments('id');
            $table->string('host')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('comment')->nullable();
            $table->timestamps();
        });

        $robot = new \Dappur\Model\Robots;
        $robot->user_agent = "*";
        $robot->comment = "Default Robots";
        $robot->save();

        $this->schema->create('robots_allow', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('robot_id')->unsigned();
            $table->string('route')->nullable();
            $table->timestamps();
            $table->foreign('robot_id')->references('id')->on('robots')->onDelete('cascade');
        });

        $allow = new \Dappur\Model\RobotsAllow;
        $allow->robot_id = $robot->id;
        $allow->route = "/";
        $allow->save();

        $this->schema->create('robots_disallow', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('robot_id')->unsigned();
            $table->string('route')->nullable();
            $table->timestamps();
            $table->foreign('robot_id')->references('id')->on('robots')->onDelete('cascade');
        });

        $disallow = new \Dappur\Model\RobotsDisallow;
        $disallow->robot_id = $robot->id;
        $disallow->route = "/dashboard";
        $disallow->save();
    }

    public function down()
    {
        $this->schema->dropIfExists('robots_disallow');
        $this->schema->dropIfExists('robots_allow');
        $this->schema->dropIfExists('robots');
    }
}
