<?php

namespace Dappur\Controller;

use Carbon\Carbon;
use Dappur\Model\Oauth2Providers;
use Dappur\Model\Oauth2Users;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

class AdminOauth2 extends Controller{

    public function providers(Request $request, Response $response){
        
        if($check = $this->sentinel->hasPerm('oauth2.view', 'dashboard', $this->config['oauth2-enabled'])){
            return $check;
        }

        $providers = new Oauth2Providers;

        $active = $providers->where('status', 1)->get();
        foreach ($active as $key => $value) {
            $client_id = $this->settings['oauth2'][$value->slug]['client_id'];
            $client_secret = $this->settings['oauth2'][$value->slug]['client_id'];
            if ((isset($client_id) || $client_id == "") || (isset($client_secret) || $client_secret == "")) {
                $this->flash->addMessageNow('warning', "Client ID and/or Client Secret not found.  {$value->name} might not work until these are added to the settings.json file.");
            }
        }

        return $this->view->render($response, 'oauth2-providers.twig', array("providers" => $providers->get()));
    }

    public function users(Request $request, Response $response){
        if($check = $this->sentinel->hasPerm('oauth2.view', 'dashboard', $this->config['oauth2-enabled'])){
            return $check;
        }

        $oauth2_users = Oauth2Users::get();

        return $this->view->render($response, 'oauth2-users.twig', array("oauth2_users" => $oauth2_users));
    }

    public function oauth2Add(Request $request, Response $response){

        if($check = $this->sentinel->hasPerm('oauth2.create', 'dashboard', $this->config['oauth2-enabled'])){
            return $check;
        }

        if ($request->isPost()) {
            // Validate Data
            $validate_data = array(
                'name' => array(
                    'rules' => V::alnum(''), 
                    'messages' => array(
                        'alnum' => 'Must be alphanumeric.'
                        )
                ),
                'slug' => array(
                    'rules' => V::slug(), 
                    'messages' => array(
                        'slug' => 'Must be slug format.'
                        )
                ),
                'scopes' => array(
                    'rules' => V::alnum(), 
                    'messages' => array(
                        'alnum' => 'Must be alphanumeric.'
                        )
                ),
                'login' => array(
                    'rules' => V::boolType(), 
                    'messages' => array(
                        'boolType' => 'Not a valid value.'
                        )
                ),
                'status' => array(
                    'rules' => V::boolType(), 
                    'messages' => array(
                        'boolType' => 'Not a valid value..'
                        )
                ),
                'authorize_url' => array(
                    'rules' => V::url(), 
                    'messages' => array(
                        'url' => 'Enter a valid URL.'
                        )
                ),
                'token_url' => array(
                    'rules' => V::url(), 
                    'messages' => array(
                        'url' => 'Enter a valid URL.'
                        )
                ),
                'resource_url' => array(
                    'rules' => V::url(), 
                    'messages' => array(
                        'url' => 'Enter a valid URL.'
                        )
                )
            );
        }

        $bs_social = array('adn', 'bitbucket', 'dropbox', 'facebook', 'flickr', 'foursquare', 'github', 'google', 'instagram', 'linkedin', 'microsoft', 'odnoklassniki', 'openid', 'pinterest', 'reddit', 'soundcloud', 'tumblr', 'twitter', 'vimeo', 'vk', 'yahoo');

        return $this->view->render($response, 'oauth2-add.twig', array("bs_social" => $bs_social));
    }

    public function oauth2Disable(Request $request, Response $response){

        if($check = $this->sentinel->hasPerm('oauth2.update', 'dashboard', $this->config['oauth2-enabled'])){
            return $check;
        }

        $path = explode('/', $request->getUri()->getPath());

        $provider = Oauth2Providers::find($request->getParam('provider_id'));

        if (!$provider) {
            return json_encode(array("status" => false, "message" => "Provider Not Found"));
        }
        if (end($path) == "login") {
            $provider->login = 0;
            if ($provider->save()) {
                return json_encode(array("status" => true, "message" => "Login for {$provider->name} has been successfully disabled."));
            }else{
                return json_encode(array("status" => false, "message" => "An error occured enabling oauth login for {$provider->name}."));
            }
        }else{
            $provider->status = 0;
            if ($provider->save()) {
                return json_encode(array("status" => true, "message" => "{$provider->name} has been successfully disabled."));
            }else{
                return json_encode(array("status" => false, "message" => "An error occured enabling {$provider->name}."));
            }
        }
    }

    public function oauth2Enable(Request $request, Response $response){

        if($check = $this->sentinel->hasPerm('oauth2.update', 'dashboard', $this->config['oauth2-enabled'])){
            return $check;
        }

        $path = explode('/', $request->getUri()->getPath());

        $provider = Oauth2Providers::find($request->getParam('provider_id'));

        if (!$provider) {
            return json_encode(array("status" => false, "message" => "Provider Not Found"));
        }
        if (end($path) == "login") {
            $provider->login = 1;
            if ($provider->save()) {
                return json_encode(array("status" => true, "message" => "Login for {$provider->name} has been successfully enabled."));
            }else{
                return json_encode(array("status" => false, "message" => "An error occured enabling oauth login for {$provider->name}."));
            }
        }else{
            $provider->status = 1;
            if ($provider->save()) {
                return json_encode(array("status" => true, "message" => "{$provider->name} has been successfully enabled."));
            }else{
                return json_encode(array("status" => false, "message" => "An error occured enabling {$provider->name}."));
            }
        }
    }

    public function oauth2Delete(Request $request, Response $response){

        if($check = $this->sentinel->hasPerm('oauth2.update', 'dashboard', $this->config['oauth2-enabled'])){
            return $check;
        }

        $path = explode('/', $request->getUri()->getPath());

        $provider = Oauth2Providers::find($request->getParam('provider_id'));

        if (!$provider) {
            $this->flash('danger', 'Provider not found.');
        }else{
            if ($provider->delete()) {
                $this->flash('success', $provider->name . " was successfully deleted.");
            }else{
                $this->flash('success', "An error occured while trying to delete " . $provider->name . ".  Please try again.");
            }
        }
        return $this->redirect($response, 'admin-oauth2');
        
    }
}