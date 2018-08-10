<?php

use \Dappur\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddSideBarToCustomRoutes extends Migration
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
        $this->schema->table('routes', function (Blueprint $table) {
            $table->boolean('sidebar')->after('js')->default(0);
            $table->boolean('header')->after('sidebar')->default(0);
            $table->text('header_text')->after('header')->nullable();
            $table->string('header_image')->after('header_text')->nullable();
        });
    }

    public function down()
    {
        $this->schema->table('routes', function (Blueprint $table) {
            $table->dropColumn('sidebar');
            $table->dropColumn('header');
            $table->dropColumn('header_text');
            $table->dropColumn('header_image');
        });
    }
}
