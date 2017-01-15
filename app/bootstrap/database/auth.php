<?php

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;
use Cartalyst\Sentinel\Native\Facades\Sentinel;
use Cartalyst\Sentinel\Native\SentinelBootstrapper;

$sentinel = (new Sentinel(new SentinelBootstrapper(__DIR__ . '/../sentinel.php')))->getSentinel();

// Drop Existing Tables
Manager::schema()->dropIfExists('activations');
Manager::schema()->dropIfExists('persistences');
Manager::schema()->dropIfExists('reminders');
Manager::schema()->dropIfExists('role_users');
Manager::schema()->dropIfExists('throttle');
Manager::schema()->dropIfExists('roles');
Manager::schema()->dropIfExists('users');

// Create Users Table
Manager::schema()->create('users', function (Blueprint $table) {
    $table->increments('id');
    $table->string('email')->unique();
    $table->string('password');
    $table->string('last_name')->nullable();
    $table->string('first_name')->nullable();
    $table->text('permissions');
    $table->timestamp('last_login');
    $table->timestamps();
});

// Create Activations Table
Manager::schema()->create('activations', function (Blueprint $table) {
    $table->increments('id');
    $table->integer('user_id')->unsigned();
    $table->string('code');
    $table->boolean('completed')->default(0);
    $table->timestamp('completed_at')->nullable();
    $table->timestamps();
    $table->foreign('user_id')->references('id')->on('users');
});

// Create Persistences Table
Manager::schema()->create('persistences', function (Blueprint $table) {
    $table->increments('id');
    $table->integer('user_id')->unsigned();
    $table->string('code')->unique();
    $table->timestamps();
    $table->foreign('user_id')->references('id')->on('users');
});

// Create Reminders Table
Manager::schema()->create('reminders', function (Blueprint $table) {
    $table->increments('id');
    $table->integer('user_id')->unsigned();
    $table->string('code');
    $table->boolean('completed')->default(0);
    $table->timestamp('completed_at')->nullable();
    $table->timestamps();
    $table->foreign('user_id')->references('id')->on('users');
});

// Create Roles Table
Manager::schema()->create('roles', function (Blueprint $table) {
    $table->increments('id');
    $table->string('slug')->unique();
    $table->string('name');
    $table->text('permissions');
    $table->timestamps();
});

// Create Roles_Users Table
Manager::schema()->create('role_users', function (Blueprint $table) {
    $table->integer('user_id')->unsigned();
    $table->integer('role_id')->unsigned();
    $table->timestamps();
    $table->primary(['user_id', 'role_id']);
    $table->foreign('user_id')->references('id')->on('users');
    $table->foreign('role_id')->references('id')->on('roles');
});

// Create Throttle Table
Manager::schema()->create('throttle', function (Blueprint $table) {
    $table->increments('id');
    $table->integer('user_id')->unsigned()->nullable();
    $table->string('type');
    $table->string('ip')->nullable();
    $table->timestamps();
    $table->foreign('user_id')->references('id')->on('users');
});


// Create Admin Role
$sentinel->getRoleRepository()->createModel()->create(array(
    'name' => 'Admin',
    'slug' => 'admin',
    'permissions' => array(
        'user.create' => true,
        'user.update' => true,
        'user.delete' => true,
        'page.dashboard' => true,
        'config.main' => true
    )
));

//Create User Role
$sentinel->getRoleRepository()->createModel()->create(array(
    'name' => 'User',
    'slug' => 'user',
    'permissions' => array(
        'user.update' => true,
        'page.dashboard' => true,
    )
));

//Create Admin User
$role = $sentinel->findRoleByName('Admin');

$user = $sentinel->registerAndActivate([
    'first_name' => "Admin",
    'last_name' => "User",
    'email' => "admin@example.com",
    'password' => "admin123",
    'permissions' => [
        
    ]
]);

$role->users()->attach($user);
