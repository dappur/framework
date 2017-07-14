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

$container['Deploy'] = function ($container) { 
	return new Dappur\Controller\Deploy($container); 
};

$container['Settings'] = function ($container) { 
	return new Dappur\Controller\Settings($container); 
};