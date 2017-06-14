<?php

$container['AppController'] = function ($container) {
    return new Dappur\Controller\AppController($container);
};

$container['AuthController'] = function ($container) {
    return new Dappur\Controller\AuthController($container);
};

$container['AdminController'] = function ($container) {
    return new Dappur\Controller\AdminController($container);
};

$container['DeployController'] = function ($container) { 
	return new Dappur\Controller\DeployController($container); 
};
$container['SettingsController'] = function ($container) { 
	return new Dappur\Controller\SettingsController($container); 
};