<?php

namespace Dappur\TwigExtension;

class Recaptcha extends \Twig_Extension {

    /**
     * @var \Slim\Csrf\Guard
     */
    protected $recaptcha;
    
    public function __construct($recaptcha)
    {
        $this->recaptcha = $recaptcha;
    }

    public function getName() {
        return 'recaptcha';
    }

    public function getFunctions() {
        return [
            new \Twig_SimpleFunction('recaptcha', array($this, 'recaptcha'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('recaptchaJs', array($this, 'recaptchaJs'), array('is_safe' => array('html')))
        ];
    }

    public function recaptcha() {

        return '<div id="recaptcha">
                    <div class="g-recaptcha" data-sitekey="' . $this->recaptcha['site_key'] . '"></div>
                </div>';
    }

    public function recaptchaJs() {

        return '<script src="https://www.google.com/recaptcha/api.js"></script>';
    }
}