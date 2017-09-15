<?php

use \Dappur\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddContact extends Migration
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

        $this->schema->create('contact_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
        });
        
        $config = new \Dappur\Model\Config;
        $config->group_id = 3;
        $config->type_id = 2;
        $config->name = 'contact-map-url';
        $config->description = 'Map Iframe Url';
        $config->value = 'https://goo.gl/oDcRix';
        $config->save();

        $config = new \Dappur\Model\Config;
        $config->group_id = 3;
        $config->type_id = 6;
        $config->name = 'contact-map-show';
        $config->description = 'Show Map';
        $config->value = 1;
        $config->save();

        $config = new \Dappur\Model\Config;
        $config->group_id = 3;
        $config->type_id = 6;
        $config->name = 'contact-send-email';
        $config->description = 'Send Confirmation Email';
        $config->value = 1;
        $config->save();

        

    }

    public function down()
    {
        $this->schema->dropIfExists('contact_requests');
    }
}
