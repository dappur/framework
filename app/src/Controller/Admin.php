<?php

namespace Dappur\Controller;

use Dappur\Model\ContactRequests;
use Dappur\Model\Users;
use Dappur\Model\UsersProfile;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;
use Slim\Views\PhpRenderer;

class Admin extends Controller{

    public function contact(Request $request, Response $response){

        if($check = $this->sentinel->hasPerm('contact.view')){
            return $check;
        }

        return $this->view->render($response, 'contact.twig', array("contactRequests" => ContactRequests::orderBy('created_at', 'desc')->get()));

    }

    public function dashboard(Request $request, Response $response){

        if($check = $this->sentinel->hasPerm('dashboard.view')){
            return $check;
        }

        return $this->view->render($response, 'dashboard.twig');

    }

    public function myAccount(Request $request, Response $response){

        if($check = $this->sentinel->hasPerm('user.account')){
            return $check;
        }

        $requestParams = $request->getParams();

        $user = Users::with('profile')->find($this->auth->check()->id);

        if ($request->isPost()) {

            if (null !== $request->getParam('update_account')) {
                 // Validate Data
                $validate_data = array(
                    'first_name' => array(
                        'rules' => V::length(2, 25)->alnum('\'?!@#,."'), 
                        'messages' => array(
                            'length' => 'Must be between 2 and 25 characters.',
                            'alnum' => 'Contains an invalid character.'
                            )
                    ),
                    'last_name' => array(
                        'rules' => V::length(2, 25)->alnum('\'?!@#,."'), 
                        'messages' => array(
                            'length' => 'Must be between 2 and 25 characters.',
                            'alnum' => 'Letters only and can contain \''
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
                if ($user->username != $request->getParam('username')) {
                    $check_username = Users::where('id', '!=', $user->id)->where('username', '=', $request->getParam('username'))->get()->count();
                    if ($check_username > 0) {
                        $this->validator->addError('username', 'Username is already in use.');
                    }
                }
                

                //Check Email
                if ($user->email != $request->getParam('email')) {
                    $check_email = Users::where('id', '!=', $user->id)->where('email', '=', $request->getParam('email'))->get()->count();
                    if ($check_email > 0) {
                        $this->validator->addError('email', 'Email address is already in use.');
                    }
                }

                $this->validator->validate($request, $validate_data);

                if ($this->validator->isValid()) {

                    $new_information = [
                        'first_name' => $request->getParam('first_name'),
                        'last_name' => $request->getParam('last_name'),
                        'email' => $request->getParam('email'),
                        'username' => $request->getParam('username')
                    ];

                    $update_user = $this->auth->update($user, $new_information);

                    $update_profile = UsersProfile::find($user->id);
                    if ($update_profile) {
                        $update_profile->about = strip_tags($request->getParam('about'));
                        $update_profile->save();
                    }else{
                        $add_profile = new UsersProfile;
                        $add_profile->user_id = $user->id;
                        $add_profile->about = strip_tags($request->getParam('about'));
                        $add_profile->save();
                    }

                    if ($update_user) {
                        $this->flash('success', 'Your account has been updated successfully.');
                        return $this->redirect($response, 'my-account');
                    }else{
                        $this->flashNow('danger', 'There was an error updating your account information.');
                    }
                }
            }

        }

        return $this->view->render($response, 'my-account.twig');
    }
}