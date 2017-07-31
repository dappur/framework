<?php

use \Dappur\Migration\Migration;
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
            $table->foreign('user_id')->references('id')->on('users')onDelete('cascade');
        });

        // Create Persistences Table
        $this->schema->create('persistences', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('code')->unique();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')onDelete('cascade');
        });

        // Create Reminders Table
        $this->schema->create('reminders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('code');
            $table->boolean('completed')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')onDelete('cascade');
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
            $table->foreign('user_id')->references('id')->on('users')onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('roles')onDelete('cascade');
        });

        // Create Throttle Table
        $this->schema->create('throttle', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->nullable();
            $table->string('type');
            $table->string('ip')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')onDelete('cascade');
        });

        // Create Config Table
        $this->schema->create('config_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Create Config Table
        $this->schema->create('config', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('group_id')->unsigned()->nullable();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->string('type')->nullable();
            $table->text('value')->nullable();
            $table->timestamps();
            $table->foreign('group_id')->references('id')->on('config_groups')onDelete('cascade');
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
                'media.*' => true,
                'blog.*' => true,
                'developer.*' => true,
                'dashboard.*' => true
            )
        ));

        // Create Manager Role
        $this->sentinel->getRoleRepository()->createModel()->create(array(
            'name' => 'Manager',
            'slug' => 'manager',
            'permissions' => array(
                'user.*' => true,
                'user.delete' => true,
                'config.*' => false,
                'role.*' => true,
                'permission.*' => true,
                'media.*' => true,
                'blog.*' => true,
                'developer.*' => false,
                'dashboard.*' => true
            )
        ));

        //Create User Role
        $this->sentinel->getRoleRepository()->createModel()->create(array(
            'name' => 'User',
            'slug' => 'user',
            'permissions' => array(
                'user.create' => false
                )
        ));

        // Create Auditor Role
        $this->sentinel->getRoleRepository()->createModel()->create(array(
            'name' => 'Auditor',
            'slug' => 'auditor',
            'permissions' => array(
                'user.view' => true,
                'config.view' => true,
                'role.view' => true,
                'permission.view' => true,
                'media.view' => true,
                'blog.view' => true,
                'developer.view' => true,
                'dashboard.view' => true
            )
        ));

        //Create Admin User
        $role = $this->sentinel->findRoleByName('Admin');
        $admin = $this->sentinel->registerAndActivate([
            'first_name' => "Admin",
            'last_name' => "User",
            'username' => 'admin',
            'email' => "admin@example.com",
            'password' => "admin123",
            'permissions' => array()
        ]);
        $role->users()->attach($admin);

        //Initial Config Groups
        $init_config_groups = array(
            array(1, "Site Settings"),
            array(2, "Theme Settings"),
            array(3, "Site Settings")
        );

        // Seed Config Table
        foreach ($init_config_groups as $key => $value) {
            $config = new Dappur\Model\ConfigGroups;
            $config->id = $value[0];
            $config->name = $value[1];
            $config->type = $value[2];
            $config->value = $value[3];
            $config->save();
        }

        //Initial Config Table Options
        $init_config = array(
            array('timezone', 'PHP Timezone', 'timezone', 'America/Los_Angeles'),
            array('site-name', 'Site Name', 'string', 'Dappur'),
            array('domain', 'Site Domain', 'string', 'dappur.dev'),
            array('replyto-email', 'Reply To Email', 'string', 'noreply@dappur.dev'),
            array('theme', 'Site Theme', 'theme', 'dappur'),
            array('dashboard-theme', 'Dashboard Theme', 'theme', 'dashboard'),
            array('bootswatch-dashboard', 'Dashboard Bootswatch', 'bootswatch', 'cyborg'),
            array('ga', 'Google Analytics UA', 'string', '')
        );

        // Seed Config Table
        foreach ($init_config as $key => $value) {
            $config = new Dappur\Model\Config;
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
