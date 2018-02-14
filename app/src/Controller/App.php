<?php

namespace Dappur\Controller;

use Dappur\Dappurware\Email as E;
use Dappur\Dappurware\FileResponse;
use Dappur\Dappurware\Recaptcha;
use Dappur\Model\ContactRequests;
use Dappur\Model\UsersProfile;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;
use Slim\Exception\NotFoundException;

class App extends Controller{

    public function asset(Request $request, Response $response){

        $asset_path = str_replace("\0", "", $request->getParam('path'));
        $asset_path = __DIR__ . "/../../views/" . str_replace("../", "", $asset_path);

        if (!is_file($asset_path)) {
            throw new NotFoundException($request, $response);
        }else{
            return FileResponse::getResponse($response, $asset_path);
        }
    }

    public function changePassword(Request $request, Response $response){

        $user = $this->auth->check();
        $reminders = $this->auth->getReminderRepository();

        if ($exists = $reminders->exists($user)) {
            $reminder = $exists->code;
        }else{
            $reminder = $reminders->create($user);
            $reminder = $reminder->code;
        }

        if ($request->getParam('password') != $request->getParam('confirm')) {
            return json_encode(
                array(
                    "result" => "error",
                    "message" => "The passwords you entered do not match."
                )
            );
        }else{
            if ($reminders->complete($user, $reminder, $request->getParam('password'))) {
                return json_encode(
                    array(
                        "result" => "success"
                    )
                );
            }else{
                return json_encode(
                    array(
                        "result" => "error",
                        "message" => "There was an error updating your password.  Please contact us if this problem persists."
                    )
                );
            }
        }

    }

    public function checkPassword(Request $request, Response $response){

        $credentials = [
            'email'    => $this->auth->check()->email,
            'password' => $request->getParam('password'),
        ];

        if ($user = $this->auth->stateless($credentials)){
            return json_encode(
                array(
                    "result" => "success"
                )
            );
        }else{
            return json_encode(
                array(
                    "result" => "error"
                )
            );
        }

    }

    public function contact(Request $request, Response $response){

        if ($request->isPost()) {

            // Validate Form Data
            $validate_data = array(
                'name' => array(
                    'rules' => V::length(2, 64)->alnum('\''), 
                    'messages' => array(
                        'length' => 'Must be between 2 and 64 characters.',
                        'alnum' => 'Alphanumeric and can contain \''
                        )
                ),
                'email' => array(
                    'rules' => V::email(), 
                    'messages' => array(
                        'email' => 'Enter a valid email.',
                        )
                ),
                'phone' => array(
                    'rules' => V::phone(), 
                    'messages' => array(
                        'phone' => 'Enter a valid phone number.'
                        )
                ),
                'comment' => array(
                    'rules' => V::alnum('\'!@#$%^&:",.?/'), 
                    'messages' => array(
                        'alnum' => 'Text and punctuation only.',
                        )
                )
            );
            $this->validator->validate($request, $validate_data);

            // Validate Recaptcha
            $recaptcha = new Recaptcha($this->container);
            $recaptcha = $recaptcha->validate($request->getParam('g-recaptcha-response'));
            if (!$recaptcha) {
                $this->validator->addError('recaptcha', 'Recaptcha was invalid.');
            }

            if ($this->validator->isValid()) {
                $add = new ContactRequests;
                $add->name = $request->getParam("name");
                $add->email = $request->getParam("email");
                $add->phone = $request->getParam("phone");
                $add->comment = $request->getParam("comment");

                if ($add->save()) {

                    if ($this->config['contact-send-email']) {
                        
                        $send_email = new E($this->container);
                        $send_email = $send_email->sendTemplate(array($request->getParam("email")), 'contact-confirmation', array('name' => $request->getParam('name'), 'phone' => $request->getParam('phone'), 'comment' => $request->getParam('comment')));
                    }

                    $this->flash('success', 'Your contact request has been submitted successfully.');
                    return $this->redirect($response, 'contact');
                }else{
                    $this->flash('danger', 'An unknown error occured.  Please try again or email us at: ' . $this->config['contact-email']);
                    return $this->redirect($response, 'contact');
                }
            }
        }

        return $this->view->render($response, 'contact.twig', array("requestParams" => $request->getParams()));

    }

    public function csrf(Request $request, Response $response){

        $csrf = array(
            "name_key" => $this->csrf->getTokenNameKey(),
            "name" => $this->csrf->getTokenName(),
            "value_key" => $this->csrf->getTokenValueKey(),
            "value" => $this->csrf->getTokenValue());

        echo json_encode($csrf);

    }

    public function home(Request $request, Response $response){

        return $this->view->render($response, 'home.twig');
        
    }

    public function maintenance(Request $request, Response $response){

        return $this->view->render($response, 'maintenance.twig');

    }

    public function profile(Request $request, Response $response){

        $user = $this->auth->check();

        if ($request->isPost()) {

            if ($request->getParam('save_profile') !== null) {

                // Validate Data
                $validate_data = array(
                    'first_name' => array(
                        'rules' => V::length(2, 25)->alnum('\'?!@#,."'), 
                        'messages' => array(
                            'length' => 'Must be between 2 and 25 characters.',
                            'alpha' => 'Contains an invalid character.'
                            )
                    ),
                    'last_name' => array(
                        'rules' => V::length(2, 25)->alnum('\'?!@#,."'), 
                        'messages' => array(
                            'length' => 'Must be between 2 and 25 characters.',
                            'alpha' => 'Contains an invalid character.'
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

                    $update_profile = UsersProfile::where('user_id', $user->id)->first();

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
                        $this->flashNow('success', 'Your profile has been updated successfully.');
                    }else{
                        $this->flashNow('danger', 'There was an error updating your account information.');
                    }
                }
            }

            if ($request->getParam('change_password') !== null) {
                // Validate Data
                $validate_data = array(
                    'password' => array(
                    'rules' => V::noWhitespace()->length(6), 
                    'messages' => array(
                        'length' => 'Must be greater than 6 characters.'
                        )
                    ),
                    'confirm' => array(
                        'rules' => V::equals($request->getParam('password')),
                        'messages' => array(
                            'equals' => 'Passwords do not match.'
                            )
                    )
                );

                $this->validator->validate($request, $validate_data);

                if ($this->validator->isValid()) {

                    $new_information = [
                        'password' => $request->getParam('password')
                    ];

                    $update_user = $this->auth->update($user, $new_information);

                    if ($update_user) {
                        $this->flashNow('success', 'Your password has been updated successfully.');
                    }else{
                        $this->flashNow('danger', 'There was an error changing your password.');
                    }
                }else{
                    $this->flashNow('danger', 'There was an error changing your password.');
                }
            }
        }

        return $this->view->render($response, 'profile.twig', array("user" => $user));

    }

    public function privacy(Request $request, Response $response){

        return $this->view->render($response, 'privacy.twig');

    }

    public function terms(Request $request, Response $response){

        return $this->view->render($response, 'terms.twig');

    }

}