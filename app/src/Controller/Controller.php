<?php

namespace Dappur\Controller;

use Psr\Container\ContainerInterface as Container;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Controller
{
    protected $container;
    protected $sentinel;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->sentinel = new \Dappur\Dappurware\Sentinel($this->container);
    }

    public function redirect(Response $response, $route, array $params = array())
    {
        return $response->withRedirect($this->router->pathFor($route, $params));
    }

    public function redirectTo(Response $response, $url)
    {
        return $response->withRedirect($url);
    }

    public function json(Response $response, $data, $status = 200)
    {
        return $response->withJson($data, $status);
    }

    public function write(Response $response, $data, $status = 200)
    {
        return $response->withStatus($status)->getBody()->write($data);
    }

    public function flash($name, $message)
    {
        $this->flash->addMessage($name, $message);
    }

    public function flashNow($name, $message)
    {
        $this->flash->addMessageNow($name, $message);
    }

    public function notFoundException(Request $request, Response $response)
    {
        return new \Slim\Exception\NotFoundException($request, $response);
    }

    public function __get($property)
    {
        return $this->container->get($property);
    }
}
