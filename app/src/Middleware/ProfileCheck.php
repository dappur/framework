<?php

namespace Dappur\Middleware;

class ProfileCheck extends Middleware
{
    public function __invoke($request, $response, $next)
    {
        if ($this->auth->check()) {
            $user = $this->auth->check();
            $err = 0;
            if (!$user->first_name || $user->first_name == "") {
                $err++;
            }
            if (!$user->last_name || $user->last_name == "") {
                $err++;
            }
            if (!$user->email || $user->email == "") {
                $err++;
            }

            if ($err) {
                $this->flash->addMessage(
                    'warning',
                    'Oops!  It appears that your profile is missing some information.'.
                        '  Please review/correct your profile below in order to continue.'
                );
                return $response->withRedirect($this->router->pathFor('profile-incomplete'));
            }
        }
        
        return $next($request, $response);
    }
}
