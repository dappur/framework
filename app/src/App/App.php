<?php

namespace Dappur\App;

use Interop\Container\ContainerInterface;

/**
 * @property Twig view
 * @property Router router
 * @property Messages flash
 * @property Validator validator
 * @property Sentinel auth
 */
class App
{
    /**
     * Slim application container
     *
     * @var ContainerInterface
     */
    protected $container;
    protected $sentinel;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __get($property)
    {
        return $this->container->get($property);
    }
}
