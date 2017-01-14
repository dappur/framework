<?php

$container['HomeController'] = function ($container) {
    return new App\Controller\HomeController($container);
};

$container['AuthController'] = function ($container) {
    return new App\Controller\AuthController($container);
};
