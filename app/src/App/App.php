<?php

namespace Dappur\App;

use Psr\Container\ContainerInterface as Container;

class App
{
    /**
     * Slim application container
     *
     * @var ContainerInterface
     */
    protected $container;
    protected $sentinel;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function __get($property)
    {
        return $this->container->get($property);
    }
}
