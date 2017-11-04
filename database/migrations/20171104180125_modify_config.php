<?php

use \Dappur\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class ModifyConfig extends Migration
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
    * 	Create Table Sample
    * 	$this->schema->create('sample', function (Blueprint $table) {
	*     	$table->increments('id');
	*    	$table->string('email')->unique();
	*    	$table->string('last_name')->nullable();
	*    	$table->string('first_name')->nullable();
	*    	$table->timestamps();
    *  	});
    * 
    * 	Drop Table Sample
    * 	$this->schema->dropIfExists('sample');
    */
    
    public function up()
    {
        $this->schema->table('config_groups', function (Blueprint $table) {
            $table->string('description')->after('name')->nullable();
            $table->string('page_name')->after('description')->unique()->nullable();
        });

        $modify = new \Dappur\Model\ConfigGroups;
        $modify = $modify->find(3);
        $modify->description = "Contact Page Config";
        $modify->page_name = 'contact';
        $modify->save();

    }

    public function down()
    {
        $this->schema->table('config_groups', function (Blueprint $table) {
            $table->dropColumn('description');
            $table->dropColumn('page_slug');
        });
    }
}
