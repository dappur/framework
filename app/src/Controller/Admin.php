<?php

namespace Dappur\Controller;

use Dappur\Model\ContactRequests;
use Dappur\Model\Users;
use Dappur\Model\UsersProfile;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;
use Slim\Views\PhpRenderer;

class Admin extends Controller
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function contact(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('contact.view')) {
            return $check;
        }

        return $this->view->render(
            $response,
            'contact.twig',
            array("contactRequests" => ContactRequests::orderBy('created_at', 'desc')->get())
        );
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function dashboard(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('dashboard.view')) {
            return $check;
        }

        return $this->view->render($response, 'dashboard.twig');
    }

    public function myAccount(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('user.account')) {
            return $check;
        }

        $user = Users::with('profile')->find($this->auth->check()->id);

        if ($request->isPost()) {
            // Validate Data
            $validateData = array(
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
            $checkUsername = Users::where('id', '!=', $user->id)
                ->where('username', '=', $request->getParam('username'))
                ->first();
            if ($checkUsername) {
                $this->validator->addError('username', 'Username is already in use.');
            }

            //Check Email
            $checkEmail = Users::where('id', '!=', $user->id)
                ->where('email', '=', $request->getParam('email'))
                ->first();
            if ($checkEmail) {
                $this->validator->addError('email', 'Email address is already in use.');
            }

            $this->validator->validate($request, $validateData);

            if ($this->validator->isValid()) {
                if ($this->updateProfile($request->getParams())) {
                    $this->flash('success', 'Your account has been updated successfully.');
                    return $this->redirect($response, 'my-account');
                }
                $this->flashNow('danger', 'There was an error updating your account information.');
            }
        }

        return $this->view->render($response, 'my-account.twig');
    }

    private function updateProfile($requestParams)
    {
        $user = Users::with('profile')->find($this->auth->check()->id);

        $newInformation = [
            'first_name' => $requestParams['first_name'],
            'last_name' => $requestParams['last_name'],
            'email' => $requestParams['email'],
            'username' => $requestParams['username']
        ];

        $updateUser = $this->auth->update($user, $newInformation);

        $updateProfile = new UsersProfile;
        $updateProfile = $updateProfile->find($user->id);
        if ($updateProfile) {
            $updateProfile->about = strip_tags($requestParams['about']);
            $updateProfile->save();
        }
        if (!$updateProfile) {
            $addProfile = new UsersProfile;
            $addProfile->user_id = $user->id;
            $addProfile->about = strip_tags($requestParams['about']);
            $addProfile->save();
        }

        if ($updateUser && ($addProfile || $updateProfile)) {
            return true;
        }

        return false;
    }
}
