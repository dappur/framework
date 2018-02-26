<?php
$container['Admin'] = function ($container) {
    return new Dappur\Controller\Admin($container);
};

$container['AdminBlog'] = function ($container) {
    return new Dappur\Controller\AdminBlog($container);
};

$container['AdminEmail'] = function ($container) { 
    return new Dappur\Controller\AdminEmail($container); 
};

$container['AdminMedia'] = function ($container) {
    return new Dappur\Controller\AdminMedia($container);
};

$container['AdminRoles'] = function ($container) {
    return new Dappur\Controller\AdminRoles($container);
};

$container['AdminSeo'] = function ($container) {
    return new Dappur\Controller\AdminSeo($container);
};

$container['AdminSettings'] = function ($container) { 
    return new Dappur\Controller\AdminSettings($container); 
};

$container['AdminUsers'] = function ($container) {
    return new Dappur\Controller\AdminUsers($container);
};

$container['App'] = function ($container) {
    return new Dappur\Controller\App($container);
};

$container['Auth'] = function ($container) {
    return new Dappur\Controller\Auth($container);
};

$container['Deploy'] = function ($container) { 
	return new Dappur\Controller\Deploy($container); 
};

$container['Blog'] = function ($container) {
    return new Dappur\Controller\Blog($container);
};

$container['Oauth2'] = function ($container) {
    return new Dappur\Controller\Oauth2($container);
};