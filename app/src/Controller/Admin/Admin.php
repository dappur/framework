<?php

namespace Dappur\Controller\Admin;

use Carbon\Carbon;
use Dappur\Model\ContactRequests;
use Dappur\Model\Oauth2Providers;
use Dappur\Model\Users;
use Dappur\Model\UsersProfile;
use Illuminate\Database\Capsule\Manager as DB;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;
use Slim\Views\PhpRenderer;

/** @SuppressWarnings(PHPMD.StaticAccess) */
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

        // Get Users By Month
        $users = Users::select(DB::raw('count(id) as total'), 'created_at')
        ->where('created_at', '>', Carbon::now()->subYear())
        ->get()
        ->groupBy(function ($date) {
            //return Carbon::parse($date->created_at)->format('Y'); // grouping by years
            return Carbon::parse($date->created_at)->format('Y-m'); // grouping by months
        });
        $usersByMonth = [];
        foreach ($users as $key => $value) {
            $usersByMonth[] = array("month" => $key, "total" => $value[0]->total);
        }
        $usersByMonth = json_encode($usersByMonth);

        // Get Users Last 90 Days
        $users2 = Users::select(DB::raw('count(id) as total'), 'created_at')
        ->where('created_at', '>', Carbon::now()->subDays(90))
        ->get()
        ->groupBy(function ($date) {
            //return Carbon::parse($date->created_at)->format('Y'); // grouping by years
            return Carbon::parse($date->created_at)->format('Y-m-d'); // grouping by months
        });
        $usersByDay = [];
        foreach ($users2 as $key => $value) {
            $usersByDay[] = array("date" => $key, "total" => $value[0]->total);
        }
        $usersByDay = json_encode($usersByDay);

        // Get Oauth2 Providers
        $providers = Oauth2Providers::withCount(['users'])->get();

        return $this->view->render(
            $response,
            'dashboard.twig',
            array(
                "usersByMonth" => $usersByMonth,
                "usersByDay" => $usersByDay,
                "providers" => $providers
            )
        );
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
