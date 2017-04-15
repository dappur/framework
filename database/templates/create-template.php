<?php

use $useClassName;
use Illuminate\Database\Schema\Blueprint;

class $className extends $baseClassName
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
        

    }

    public function down()
    {
        
    }
}
