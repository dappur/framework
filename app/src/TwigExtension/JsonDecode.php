<?php

namespace Dappur\TwigExtension;

use Psr\Http\Message\RequestInterface;

class JsonDecode extends \Twig_Extension {

    protected $request;

    public function __construct(RequestInterface $request) {
        $this->request = $request;
    }

    public function getName() {
        return 'json_decode';
    }

    public function getFilters() {
        return array(
            new \Twig_SimpleFilter('json_decode', array($this, 'jsonDecode')),
        );
    }

    public function jsonDecode($str) {

        $current_url = $this->request->getUri()->getBaseUrl() . $this->request->getUri()->getPath();

        return $current_url;
    }
}