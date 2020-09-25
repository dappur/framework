<?php

namespace Dappur\Middleware;

use Psr\Container\ContainerInterface as Container;

class Middleware
{
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function __get($property)
    {
        return $this->container->get($property);
    }
}
