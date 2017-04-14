<?php

use \App\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class InitDatabase extends Migration
{
    /**
    *
    * Write your reversible migrations using this method.
    *
    * More information on writing eloquent migrations is available here:
    * https://laravel.com/docs/5.4/migrations
    *
    * Remember to use both the up() and down() functions in order to be able to roll back. 
    */
   
    public function up()
    {
        //Initial Config Table Options
        $init_config = array(
            array('timezone', 'PHP Timezone', 'timezone', 'America/Los_Angeles'),
            array('site-name', 'Site Name', 'string', 'Skeleton-PHP'),
            array('domain', 'Site Domain', 'string', 'skeleton.dev'),
            array('replyto-email', 'Reply To Email', 'string', 'noreply@skeleton.dev'),
            array('theme', 'Theme', 'theme', 'default'),
            array('ga', 'Google Analytics UA', 'string', ''));

        // Create Users Table
        $this->schema->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->unique();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('last_name')->nullable();
            $table->string('first_name')->nullable();
            $table->text('permissions');
            $table->timestamp('last_login');
            $table->timestamps();
        });

        // Create Activations Table
        $this->schema->create('activations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('code');
            $table->boolean('completed')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users');
        });

        // Create Persistences Table
        $this->schema->create('persistences', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('code')->unique();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users');
        });

        // Create Reminders Table
        $this->schema->create('reminders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('code');
            $table->boolean('completed')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users');
        });

        // Create Roles Table
        $this->schema->create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('permissions');
            $table->timestamps();
        });

        // Create Roles_Users Table
        $this->schema->create('role_users', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->integer('role_id')->unsigned();
            $table->timestamps();
            $table->primary(['user_id', 'role_id']);
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('role_id')->references('id')->on('roles');
        });

        // Create Throttle Table
        $this->schema->create('throttle', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->nullable();
            $table->string('type');
            $table->string('ip')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users');
        });

        // Create Config Table
        $this->schema->create('config', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->string('type')->nullable();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Create Admin Role
        $this->sentinel->getRoleRepository()->createModel()->create(array(
            'name' => 'Admin',
            'slug' => 'admin',
            'permissions' => array(
                'user.*' => true,
                'config.*' => true,
                'role.*' => true,
                'permission.*' => true,
                'media.*' => true
            )
        ));

        //Create User Role
        $this->sentinel->getRoleRepository()->createModel()->create(array(
            'name' => 'User',
            'slug' => 'user',
            'permissions' => array()
        ));

        //Create Admin User
        $role = $this->sentinel->findRoleByName('Admin');
        $admin = $this->sentinel->registerAndActivate([
            'first_name' => "Admin",
            'last_name' => "User",
            'username' => 'admin',
            'email' => "admin@example.com",
            'password' => "admin123"
        ]);
        $role->users()->attach($admin);

        // Seed Config Table
        foreach ($init_config as $key => $value) {
            $config = new App\Model\Config;
            $config->name = $value[0];
            $config->description = $value[1];
            $config->type = $value[2];
            $config->value = $value[3];
            $config->save();
        }

    }

    public function down()
    {
        $this->schema->dropIfExists('activations');
        $this->schema->dropIfExists('persistences');
        $this->schema->dropIfExists('reminders');
        $this->schema->dropIfExists('role_users');
        $this->schema->dropIfExists('throttle');
        $this->schema->dropIfExists('roles');
        $this->schema->dropIfExists('users');
        $this->schema->dropIfExists('config');
    }
}
