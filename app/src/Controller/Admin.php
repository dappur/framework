<?php

namespace Dappur\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;
use Dappur\Model\Users;
use Dappur\Model\ContactRequests;
use Dappur\Dappurware\Sentinel as S;

class Admin extends Controller{

    public function dashboard(Request $request, Response $response){

        $sentinel = new S($this->container);
        if(!$sentinel->hasPerm('dashboard.view')){
            return $this->redirect($response, 'home');
        }

        return $this->view->render($response, 'dashboard.twig');

    }


    public function contact(Request $request, Response $response){

        $sentinel = new S($this->container);
        if(!$sentinel->hasPerm('email.view')){
            return $this->redirect($response, 'dashboard');
        }

        return $this->view->render($response, 'contact.twig', array("contact_requests" => ContactRequests::orderBy('created_at', 'desc')->get()));

    }

    

    public function myAccount(Request $request, Response $response){

        $sentinel = new S($this->container);
        if(!$sentinel->hasPerm('user.account')){
            return $this->redirect($response, 'my-account');
        }

        $requestParams = $request->getParams();

        $user = $this->auth->check();
        if ($request->isPost()) {

            $first_name = $requestParams['first_name'];
            $last_name = $requestParams['last_name'];
            $email = $requestParams['email'];
            $username = $requestParams['username'];
            $password = $requestParams['password'];
            $password_confirm = $requestParams['password_confirm'];

            if (null !== $request->getParam('update_account')) {
                 // Validate Data
                $validate_data = array(
                    'first_name' => array(
                        'rules' => V::length(2, 25)->alpha('\''), 
                        'messages' => array(
                            'length' => 'Must be between 2 and 25 characters.',
                            'alpha' => 'Letters only and can contain \''
                            )
                    ),
                    'last_name' => array(
                        'rules' => V::length(2, 25)->alpha('\''), 
                        'messages' => array(
                            'length' => 'Must be between 2 and 25 characters.',
                            'alpha' => 'Letters only and can contain \''
                            )
                    ),
                    'email' => array(
                        'rules' => V::noWhitespace()->email(), 
                        'messages' => array(
                            'email' => 'Enter a valid email address.',
                            'noWhitespace' => 'Must not contain any spaces.'
                            )
                    ),
                    'username' => array(
                        'rules' => V::noWhitespace()->alnum(), 
                        'messages' => array(
                            'slug' => 'Must be alpha numeric with no spaces.',
                            'noWhitespace' => 'Must not contain any spaces.'
                            )
                    )
                );
                //Check username
                if ($user->username != $username) {
                    $check_username = Users::where('id', '!=', $user->id)->where('username', '=', $username)->get()->count();
                    if ($check_username > 0) {
                        $this->validator->addError('username', 'Username is already in use.');
                    }
                }
                

                //Check Email
                if ($user->email != $email) {
                    $check_email = Users::where('id', '!=', $user->id)->where('email', '=', $email)->get()->count();
                    if ($check_email > 0) {
                        $this->validator->addError('email', 'Email address is already in use.');
                    }
                }

                $this->validator->validate($request, $validate_data);

                if ($this->validator->isValid()) {

                    $new_information = [
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'email' => $email,
                        'username' => $username
                    ];

                    $update_user = $this->auth->update($user, $new_information);

                    if ($update_user) {
                        $this->flash('success', 'Your account has been updated successfully.');
                        $this->logger->addInfo("My Account: User successfully updated.", array("first_name" => $first_name, "last_name" => $last_name, "email" => $email, "username" => $username, "user_id" => $user->id));
                        return $this->redirect($response, 'my-account');
                    }else{
                        $this->flash('danger', 'There was an error updating your account information.');
                        $this->logger->addInfo("My Account: An unknown error occured updating user.", array("first_name" => $first_name, "last_name" => $last_name, "email" => $email, "username" => $username, "user_id" => $user->id));
                    }
                }
            }

            if (null !== $request->getParam('change_password')) {
                // Validate Data
                $validate_data = array(
                    'password' => array(
                    'rules' => V::noWhitespace()->length(6, 25), 
                    'messages' => array(
                        'length' => 'Must be between 6 and 25 characters.',
                        'noWhitespace' => 'Must not contain any spaces.'
                        )
                    ),
                    'password_confirm' => array(
                        'rules' => V::equals($password),
                        'messages' => array(
                            'equals' => 'Passwords do not match.'
                            )
                    )
                );

                $this->validator->validate($request, $validate_data);

                if ($this->validator->isValid()) {

                    $new_information = [
                        'password' => $password,
                    ];

                    $update_user = $this->auth->update($user, $new_information);

                    if ($update_user) {
                        $this->flash('success', 'Your password has been updated successfully.');
                        $this->logger->addInfo("My Account: Password successfully changed", array("user_id" => $user->id));
                        return $this->redirect($response, 'my-account');
                    }else{
                        $this->flash('danger', 'There was an error changing your password.');
                        $this->logger->addInfo("My Account: An unknown error occured changing a password.", array("user_id" => $user->id));
                    }
                }
            }

        }

        return $this->view->render($response, 'my-account.twig', array("requestParams" => $requestParams));
    }

    public function getCloudinaryCMS($container){

        $sentinel = new S($container);
        $sentinel->hasPerm('media.cloudinary');

        // Generate Timestamp
        $date = new \DateTime();
        $timestamp = $date->getTimestamp();
        
        // Prepare Cloudinary CMS Params
        $params = array("timestamp" => $timestamp, "mode" => "tinymce");

        // Prepare Cloudinary Options
        $options = array("cloud_name" => $container->settings['cloudinary']['cloud_name'],
            "api_key" => $container->settings['cloudinary']['api_key'],
            "api_secret" => $container->settings['cloudinary']['api_secret']);

        // Sign Request With Cloudinary
        $output = \Cloudinary::sign_request($params, $options);

        if ($output) {
            // Build the http query
            $api_params_cl = http_build_query($output);

            // Complete the Cloudinary URL
            $cloudinary_cms_url = "https://cloudinary.com/console/media_library/cms?$api_params_cl";

            return $cloudinary_cms_url;
        }else{
            return false;
        }
        
    }

}