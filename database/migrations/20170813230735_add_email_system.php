<?php

use \Dappur\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddEmailSystem extends Migration
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
        


        $this->schema->create('emails_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('slug')->unique()->nullable();
            $table->string('description')->nullable();
            $table->text('html')->nullable();
            $table->text('plain_text')->nullable();
            $table->text('placeholders')->nullable();
            $table->timestamps();
        });

        $this->schema->create('emails', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('template_id')->unsigned()->nullable();
            $table->text('send_to')->nullable();
            $table->string('subject')->nullable();
            $table->text('html')->nullable();
            $table->text('plain_text')->nullable();
            $table->timestamps();
            $table->foreign('template_id')->references('id')->on('emails_templates')->onDelete('cascade');
        });
    }

    public function down()
    {
        $this->schema->dropIfExists('emails');
        $this->schema->dropIfExists('emails_templates');

    }
}
