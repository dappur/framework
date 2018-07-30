<?php

namespace Dappur\TwigExtension;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;

class Menus extends \Twig_Extension {

    protected $auth;
    protected $request;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getName() {
        return 'menus';
    }

    public function getFunctions() {
        return [
            new \Twig_SimpleFunction('getMenu', [$this, 'getMenu'])
        ];
    }

    public function getMenu($menuId) {
        $menu = \Dappur\Model\Menus::find($menuId);
        $menu = json_decode($menu->json, true);

        $user = $this->container->auth->check();

        foreach ($menu as $key => $value) {
            if ($value['auth'] == "true" && !$user) {
                unset($menu[$key]);
                continue;
            }

            if ($value['guest'] == "true" && $user) {
                unset($menu[$key]);
                continue;
            }

            if (!empty($value['permission']) && !$user->hasAccess($value['permission'])) {
                unset($menu[$key]);
                continue;
            }

            if ($value['roles'] && !empty($value['roles']) && $user) {
                $hasRole = false;
                foreach ($value['roles'] as $role) {
                    if ($user->inRole($role)) {
                        $hasRole = true;
                    }
                }
                if (!$hasRole) {
                    unset($menu[$key]);
                    continue;
                }
            }

            $htmlTemp = new \Twig_Environment(new \Twig_Loader_Array([$value['text'] . '_html' => $value['text']]));
            $htmlTemp = $htmlTemp->render($value['text'] . '_html', array("user" => $user));

            $menu[$key]['text'] = $htmlTemp;

        }
        return $menu;
    }
}