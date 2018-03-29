<?php

namespace Dappur\TwigExtension;

use Psr\Http\Message\RequestInterface;

class Md5 extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('md5', 'md5')
        );
    }
    
    public function getName()
    {
        return "md5_hash";
    }
}
