<?php

$container['AppController'] = function ($container) {
    return new App\Controller\AppController($container);
};

$container['AuthController'] = function ($container) {
    return new App\Controller\AuthController($container);
};

$container['AdminController'] = function ($container) {
    return new App\Controller\AdminController($container);
};