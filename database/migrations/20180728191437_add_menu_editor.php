<?php

use \Dappur\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddMenuEditor extends Migration
{
    /**
    Write your reversible migrations using this method.

    Dappur Framework uses Laravel Eloquent ORM as it's database connector.

    More information on writing eloquent migrations is available here:
    https://laravel.com/docs/5.4/migrations

    Remember to use both the up() and down() functions in order to be able
    to roll back.

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
        $this->schema->create('menus', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->json('json')->nullable();
            $table->timestamps();
        });

        $ins = new \Dappur\Model\Menus;
        $ins->id = 1;
        $ins->name = "Dappur Menu";
        $ins->json = '[{"text":"Home","href":"","icon":"fa fa-home","target":"_self","title":"","auth":"false","page":"home","guest":"false","roles":[],"active":["home"],"permission":""},{"text":"Blog","href":"","icon":"fa fa-eye","target":"_self","title":"","auth":"false","page":"blog","guest":"false","roles":[],"active":["blog","blog-author","blog-category","blog-post","blog-tag"],"permission":""},{"text":"Contact","href":"","icon":"fa fa-phone","target":"_self","title":"","auth":"false","page":"contact","guest":"false","roles":[],"active":["contact"],"permission":""},{"text":"Login","href":"","icon":"fa fa-lock","target":"_self","title":"","auth":"false","page":"login","guest":"true","roles":[],"active":["activate","forgot-password","login"],"permission":""},{"text":"Register","href":"","icon":"fa fa-user-circle-o","target":"_self","title":"","auth":"false","page":"register","guest":"true","roles":[],"active":["register"],"permission":""},{"text":"{{auth.username}}","href":"","icon":"fa fa-user-circle-o","target":"_self","title":"","auth":"true","page":"","guest":"false","roles":[],"active":[],"permission":"","children":[{"text":"Admin Dashboard","href":"","icon":"fa fa-dashboard","target":"_self","title":"","auth":"true","page":"dashboard","guest":"false","roles":[],"active":["dashboard"],"permission":"dashboard.*"},{"text":"Profile","href":"","icon":"fa fa-clipboard","target":"_self","title":"","auth":"true","page":"profile","guest":"false","roles":[],"active":["profile","profile-incomplete"],"permission":""},{"text":"Logout","href":"","icon":"fa fa-lock","target":"_self","title":"","auth":"true","page":"logout","guest":"false","roles":[],"active":[],"permission":""}]}]';
        $ins->save();
        
        $ins = new \Dappur\Model\Menus;
        $ins->id = 2;
        $ins->name = "AdminLTE Sidebar";
        $ins->json = '[{"text":"Dashboard","icon":"fa fa-dashboard","page":"dashboard","active":["dashboard"],"roles":[],"auth":"true","guest":"false","permission":"dashbaord.view","target":"_self","title":""},{"text":"Users","icon":"fa fa-users","page":"","active":["admin-roles-add","admin-roles-edit","admin-users","admin-users-add","admin-users-edit"],"roles":[],"auth":"true","guest":"false","permission":"user.view","target":"_self","title":"","children":[{"text":"All Users","icon":"fa fa-users","page":"admin-users","active":["admin-roles-add","admin-roles-edit","admin-users","admin-users-edit"],"roles":[],"auth":"true","guest":"false","permission":"user.view","target":"_self","title":""},{"text":"New User","icon":"fa fa-plus","page":"admin-users-add","active":["admin-users-add"],"roles":[],"auth":"false","guest":"false","permission":"user.update","target":"_self","title":""}]}]';
        $ins->save();

        // Update Admin Role
        $admin_role = $this->sentinel->findRoleByName('Admin');
        $admin_role->addPermission('menus.*');
        $admin_role->save();

        
    }

    public function down()
    {
        $this->schema->dropIfExists('menus');
    }
}
