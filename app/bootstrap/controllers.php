<?php

$container['App'] = function ($container) {
    return new Dappur\Controller\App($container);
};

$container['Auth'] = function ($container) {
    return new Dappur\Controller\Auth($container);
};

$container['Admin'] = function ($container) {
    return new Dappur\Controller\Admin($container);
};

$container['AdminRoles'] = function ($container) {
    return new Dappur\Controller\AdminRoles($container);
};

$container['AdminUsers'] = function ($container) {
    return new Dappur\Controller\AdminUsers($container);
};

$container['AdminMedia'] = function ($container) {
    return new Dappur\Controller\AdminMedia($container);
};

$container['AdminSettings'] = function ($container) { 
	return new Dappur\Controller\AdminSettings($container); 
};

$container['Deploy'] = function ($container) { 
	return new Dappur\Controller\Deploy($container); 
};

$container['AdminEmail'] = function ($container) { 
	return new Dappur\Controller\AdminEmail($container); 
};

// Blog Controller
$container['AdminBlog'] = function ($container) {
    return new Dappur\Controller\AdminBlog($container);
};

$container['Blog'] = function ($container) {
    return new Dappur\Controller\Blog($container);
};