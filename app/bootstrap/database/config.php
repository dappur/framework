<?php

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;

Manager::schema()->create('config', function (Blueprint $table) {
    $table->increments('id');
    $table->string('name')->unique();
    $table->string('type')->nullable();
    $table->text('value')->nullable();
    $table->timestamps();
});

$init_config = array(
	array('timezone', 'timezone', 'America/Los_Angeles'),
	array('site-name', 'string', 'Dappur Skeleton PHP|Slim|Twig|Sentinel'),
	array('domain', 'string', 'skeleton.dev'),
	array('replyto-email', 'string', 'noreply@skeleton.dev'),
	array('theme', 'string', 'default'));

foreach ($init_config as $key => $value) {
	$config = new App\Model\Config;
	$config->name = $value[0];
	$config->type = $value[1];
	$config->value = $value[2];
	$config->save();
}
