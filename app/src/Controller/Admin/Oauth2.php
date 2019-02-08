<?php

namespace Dappur\Controller\Admin;

use Dappur\Controller\Controller as Controller;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Oauth2 extends Controller
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function providers(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('oauth2.view', 'dashboard', $this->config['oauth2-enabled'])) {
            return $check;
        }

        $providers = new \Dappur\Model\Oauth2Providers;

        $active = $providers->where('status', 1)->get();
        foreach ($active as $value) {
            $clientId = $this->settings['oauth2'][$value->slug]['client_id'];
            $slientSecret = $this->settings['oauth2'][$value->slug]['client_id'];
            if ((!isset($clientId) || $clientId == "") || (!isset($slientSecret) || $slientSecret == "")) {
                $this->flash->addMessageNow(
                    'warning',
                    "Client ID and/or Client Secret not found.  " .
                    "{$value->name} might not work until these are added to the settings.json file."
                );
            }
        }

        return $this->view->render($response, 'oauth2-providers.twig', array("providers" => $providers->get()));
    }

    public function oauth2Add(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('oauth2.create', 'dashboard', $this->config['oauth2-enabled'])) {
            return $check;
        }

        if ($request->isPost()) {
            // Validate Data
            $validateData = array(
                'name' => array(
                    'rules' => \Respect\Validation\Validator::alnum(''),
                    'messages' => array(
                        'alnum' => 'Must be alphanumeric.'
                        )
                ),
                'slug' => array(
                    'rules' => \Respect\Validation\Validator::slug(),
                    'messages' => array(
                        'slug' => 'Must be slug format.'
                        )
                ),
                'scopes' => array(
                    'rules' => \Respect\Validation\Validator::alnum(',_-.'),
                    'messages' => array(
                        'alnum' => 'Does not fit scope pattern.'
                        )
                ),
                'authorize_url' => array(
                    'rules' => \Respect\Validation\Validator::url(),
                    'messages' => array(
                        'url' => 'Enter a valid URL.'
                        )
                ),
                'token_url' => array(
                    'rules' => \Respect\Validation\Validator::url(),
                    'messages' => array(
                        'url' => 'Enter a valid URL.'
                        )
                ),
                'resource_url' => array(
                    'rules' => \Respect\Validation\Validator::url(),
                    'messages' => array(
                        'url' => 'Enter a valid URL.'
                        )
                )
            );

            $login = 0;
            if ($request->getParam('login')) {
                $login = 1;
            }

            $status = 0;
            if ($request->getParam('status')) {
                $status = 1;
            }

            //Check name
            $checkSlug = \Dappur\Model\Oauth2Providers::where('slug', $request->getParam('slug'))->first();
            if ($checkSlug) {
                $this->validator->addError('slug', 'Slug already in use.');
            }

            //Check slug
            $checkName = \Dappur\Model\Oauth2Providers::where('name', $request->getParam('name'))->first();
            if ($checkName) {
                $this->validator->addError('name', 'Name already in use.');
            }

            $this->validator->validate($request, $validateData);

            if ($this->validator->isValid()) {
                $add = new \Dappur\Model\Oauth2Providers;
                $add->name = $request->getParam('name');
                $add->slug = $request->getParam('slug');
                $add->button = $request->getParam('button');
                $add->scopes = $request->getParam('scopes');
                $add->login = $login;
                $add->status = $status;
                $add->authorize_url = $request->getParam('authorize_url');
                $add->token_url = $request->getParam('token_url');
                $add->resource_url = $request->getParam('resource_url');
                
                if ($add->save()) {
                    $this->flash('success', $add->name . " was successfully added to the Ouath2 providers.");
                    return $this->redirect($response, 'admin-oauth2');
                }
                $this->flashNow('warning', " There was a problem saving your Oauth2 provider to the database.");
            }
        }

        $bsSocial = array(
            'adn','bitbucket','dropbox','facebook','flickr','foursquare','github',
            'google','instagram','linkedin','microsoft','odnoklassniki','openid',
            'pinterest','reddit','soundcloud','tumblr','twitter','vimeo','vk','yahoo'
        );

        return $this->view->render($response, 'oauth2-add.twig', array("bs_social" => $bsSocial));
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function oauth2Disable(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('oauth2.update', 'dashboard', $this->config['oauth2-enabled'])) {
            return $check;
        }

        $path = explode('/', $request->getUri()->getPath());

        $provider = \Dappur\Model\Oauth2Providers::find($request->getParam('provider_id'));

        if (!$provider) {
            $output = array("status" => false, "message" => "Provider Not Found");
        }

        if (end($path) == "login") {
            $provider->login = 0;
            $output = array(
                "status" => false,
                "message" => "An error occured enabling oauth login for {$provider->name}."
            );
            if ($provider->save()) {
                $output = array(
                    "status" => true,
                    "message" => "Login for {$provider->name} has been successfully disabled."
                );
            }
            return json_encode($output);
        }

        $provider->status = 0;
        $output = array("status" => false, "message" => "An error occured enabling {$provider->name}.");
        if ($provider->save()) {
            $output = array("status" => true, "message" => "{$provider->name} has been successfully disabled.");
        }

        return json_encode($output);
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity) At threshold
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength) 9 Lines Over
     */
    public function oauth2Edit(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('oauth2.update', 'dashboard', $this->config['oauth2-enabled'])) {
            return $check;
        }

        $provider = \Dappur\Model\Oauth2Providers::find($request->getAttribute('route')->getArgument('provider_id'));
        if (!$provider) {
            $this->flash('danger', "Oauth2 provider not found.");
            return $this->redirect($response, 'admin-oauth2');
        }

        if ($request->isPost()) {
            // Validate Data
            $validateData = array(
                'name' => array(
                    'rules' => \Respect\Validation\Validator::alnum(''),
                    'messages' => array(
                        'alnum' => 'Must be alphanumeric.'
                        )
                ),
                'slug' => array(
                    'rules' => \Respect\Validation\Validator::slug(),
                    'messages' => array(
                        'slug' => 'Must be slug format.'
                        )
                ),
                'scopes' => array(
                    'rules' => \Respect\Validation\Validator::alnum(',_-.'),
                    'messages' => array(
                        'alnum' => 'Does not fit scope pattern.'
                        )
                ),
                'authorize_url' => array(
                    'rules' => \Respect\Validation\Validator::url(),
                    'messages' => array(
                        'url' => 'Enter a valid URL.'
                        )
                ),
                'token_url' => array(
                    'rules' => \Respect\Validation\Validator::url(),
                    'messages' => array(
                        'url' => 'Enter a valid URL.'
                        )
                ),
                'resource_url' => array(
                    'rules' => \Respect\Validation\Validator::url(),
                    'messages' => array(
                        'url' => 'Enter a valid URL.'
                        )
                )
            );

            $login = 0;
            $status = 0;

            if ($request->getParam('login')) {
                $login = 1;
            }

            if ($request->getParam('status')) {
                $status = 1;
            }

            //Check name
            $checkSlug = \Dappur\Model\Oauth2Providers::where('slug', $request->getParam('slug'))
                ->where('id', '!=', $provider->id)
                ->first();
            if ($checkSlug) {
                $this->validator->addError('slug', 'Slug already in use.');
            }

            //Check slug
            $checkName = \Dappur\Model\Oauth2Providers::where('name', $request->getParam('name'))
                ->where('id', '!=', $provider->id)
                ->first();
            if ($checkName) {
                $this->validator->addError('name', 'Name already in use.');
            }

            $this->validator->validate($request, $validateData);
            if ($this->validator->isValid()) {
                $provider->name = $request->getParam('name');
                $provider->slug = $request->getParam('slug');
                $provider->button = $request->getParam('button');
                $provider->scopes = $request->getParam('scopes');
                $provider->login = $login;
                $provider->status = $status;
                $provider->authorize_url = $request->getParam('authorize_url');
                $provider->token_url = $request->getParam('token_url');
                $provider->resource_url = $request->getParam('resource_url');
                if ($provider->save()) {
                    $this->flash('success', $provider->name . " was successfully modified.");
                    return $this->redirect($response, 'admin-oauth2');
                }
                $this->flashNow('warning', "There was a problem modifying " . $provider->name  . ".");
            }
        }

        $bsSocial = array('adn', 'bitbucket', 'dropbox', 'facebook', 'flickr', 'foursquare', 'github',
            'google', 'instagram', 'linkedin', 'microsoft', 'odnoklassniki', 'openid',
            'pinterest', 'reddit', 'soundcloud', 'tumblr', 'twitter', 'vimeo', 'vk', 'yahoo');

        return $this->view->render(
            $response,
            'oauth2-edit.twig',
            array("bs_social" => $bsSocial, "provider" => $provider)
        );
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function oauth2Enable(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('oauth2.update', 'dashboard', $this->config['oauth2-enabled'])) {
            return $check;
        }

        $path = explode('/', $request->getUri()->getPath());

        $provider = \Dappur\Model\Oauth2Providers::find($request->getParam('provider_id'));

        if (!$provider) {
            return json_encode(array("status" => false, "message" => "Provider Not Found"));
        }
        if (end($path) == "login") {
            $provider->login = 1;
            $output = array(
                "status" => false,
                "message" => "An error occured enabling oauth login for {$provider->name}."
            );
            if ($provider->save()) {
                $output = array(
                    "status" => true,
                    "message" => "Login for {$provider->name} has been successfully enabled."
                );
            }
            return json_encode($output);
        }

        $provider->status = 1;
        $output = array("status" => false, "message" => "An error occured enabling {$provider->name}.");
        if ($provider->save()) {
            $output = array("status" => true, "message" => "{$provider->name} has been successfully enabled.");
        }

        return json_encode($output);
    }

    public function oauth2Delete(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('oauth2.update', 'dashboard', $this->config['oauth2-enabled'])) {
            return $check;
        }

        $provider = \Dappur\Model\Oauth2Providers::find($request->getParam('provider_id'));

        if (!$provider) {
            $this->flash('danger', 'Provider not found.');
            return $this->redirect($response, 'admin-oauth2');
        }

        if ($provider->delete()) {
            $this->flash('success', $provider->name . " was successfully deleted.");
            return $this->redirect($response, 'admin-oauth2');
        }

        $this->flash(
            'danger',
            "An error occured while trying to delete " .
            $provider->name .
            ".  Please try again."
        );
        return $this->redirect($response, 'admin-oauth2');
    }
}
