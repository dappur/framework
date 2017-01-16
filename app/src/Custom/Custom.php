<?php

namespace App\Custom;

use Interop\Container\ContainerInterface;

/**
 * @property Twig view
 * @property Router router
 * @property Messages flash
 * @property Validator validator
 * @property Sentinel auth
 */
class Custom
{
    /**
     * Slim application container
     *
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __get($property)
    {
        return $this->container->get($property);
    }
}