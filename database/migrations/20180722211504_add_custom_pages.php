<?php

use \Dappur\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddCustomPages extends Migration
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
        $this->schema->create('routes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('pattern')->unique();
            $table->text('content')->nullable();
            $table->text('css')->nullable();
            $table->text('js')->nullable();
            $table->string('permission')->nullable();
            $table->boolean('status')->default(0);
            $table->timestamps();
        });

        $this->schema->create('role_routes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('role_id')->unsigned();
            $table->integer('route_id')->unsigned();
            $table->timestamps();
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('route_id')->references('id')->on('routes')->onDelete('cascade');
        });

        $ins = new \Dappur\Model\Routes;
        $ins->name = "custom-demo";
        $ins->pattern = "custom/demo";
        $ins->content = '<div class="row"><div class="col-md-12 col-sm-12 col-xs-12 column"><div class="ge-content ge-content-type-tinymce" data-ge-content-type="tinymce"><p style="text-align: center;" data-mce-style="text-align: center;">This is a custom route. Custom routes can be set and edited from within the dashboard. Custom CSS and Javascript can be added as well.</p></div></div></div>';
        $ins->status = 1;
        $ins->save();

        // Update Admin Role
        $admin_role = $this->sentinel->findRoleByName('Admin');
        $admin_role->addPermission('pages.*');
        $admin_role->save();
    }

    public function down()
    {
        $this->schema->dropIfExists('role_routes');
        $this->schema->dropIfExists('routes');

        // Update Admin Role
        $admin_role = $this->sentinel->findRoleByName('Admin');
        $admin_role->removePermission('pages.*');
        $admin_role->save();
    }
}
