<?php

namespace Dappur\TwigExtension;

use Psr\Http\Message\RequestInterface as Request;

class Csrf extends \Twig_Extension
{

    /**
     * @var \Slim\Csrf\Guard
     */
    protected $csrf;
    
    public function __construct($csrf)
    {
        $this->csrf = $csrf;
    }

    public function getGlobals()
    {
        // CSRF token name and value
        $csrfNameKey = $this->csrf->getTokenNameKey();
        $csrfValueKey = $this->csrf->getTokenValueKey();
        $csrfName = $this->csrf->getTokenName();
        $csrfValue = $this->csrf->getTokenValue();
        
        return [
            'csrf'   => [
                'keys' => [
                    'name'  => $csrfNameKey,
                    'value' => $csrfValueKey
                ],
                'name'  => $csrfName,
                'value' => $csrfValue
            ]
        ];
    }

    public function getName()
    {
        return 'csrf';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('csrf', array($this, 'csrf'), array('is_safe' => array('html')))
        ];
    }

    public function csrf()
    {
        return '
            <input type="hidden" name="' . $this->csrf->getTokenNameKey() .
            '" value="' . $this->csrf->getTokenName() . '">
            <input type="hidden" name="' . $this->csrf->getTokenValueKey() .
            '" value="' . $this->csrf->getTokenValue() . '">
        ';
    }
}
