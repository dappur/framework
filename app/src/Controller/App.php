<?php

namespace Dappur\Controller;

use Dappur\Dappurware\Email as E;
use Dappur\Dappurware\FileResponse;
use Dappur\Dappurware\Recaptcha;
use Dappur\Model\ContactRequests;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class App extends Controller
{
    public function asset(Request $request, Response $response)
    {
        $assetPath = str_replace("\0", "", $request->getParam('path'));
        $assetPath = __DIR__ . "/../../views/" . str_replace("../", "", $assetPath);

        if (!is_file($assetPath)) {
            throw new NotFoundException($request, $response);
        }
        
        return FileResponse::getResponse($response, $assetPath);
    }

    public function contact(Request $request, Response $response)
    {
        if ($request->isPost()) {
            // Validate Form Data
            $validateData = array(
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
            $this->validator->validate($request, $validateData);

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
                        $sendEmail = new E($this->container);
                        $sendEmail = $sendEmail->sendTemplate(
                            array(
                                $request->getParam("email")
                            ),
                            'contact-confirmation',
                            array(
                                'name' => $request->getParam('name'),
                                'phone' => $request->getParam('phone'),
                                'comment' => $request->getParam('comment')
                            )
                        );
                    }

                    $this->flash('success', 'Your contact request has been submitted successfully.');
                    return $this->redirect($response, 'contact');
                }
            }
            $this->flash(
                'danger',
                'An unknown error occured.  Please try again or email us at: ' .
                $this->config['contact-email']
            );
            return $this->redirect($response, 'contact');
        }

        return $this->view->render($response, 'contact.twig', array("requestParams" => $request->getParams()));
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function csrf(Request $request, Response $response)
    {
        $csrf = array(
            "name_key" => $this->csrf->getTokenNameKey(),
            "name" => $this->csrf->getTokenName(),
            "value_key" => $this->csrf->getTokenValueKey(),
            "value" => $this->csrf->getTokenValue());

        echo json_encode($csrf);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function home(Request $request, Response $response)
    {
        return $this->view->render($response, 'home.twig');
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function customRoute(Request $request, Response $response)
    {
        $route = \Dappur\Model\Routes::where('name', $request->getAttribute('route')->getName())
            ->with('roles')
            ->where('status', 1)
            ->first();

        if (!$route) {
            throw new NotFoundException($request, $response);
        }

        // Check for If Permissions are set
        if (($route->permission && !$this->auth->check()) ||
            ($route->roles->count() > 0 && !$this->auth->check())
        ) {
            $this->flash('warning', 'You must be logged in to access this page.');
            //return $response->withRedirect($response, 'login', array(), array('redirect' => $route->name));
            return $response->withRedirect($this->router->pathFor('login', array(), array('redirect' => $route->name)));

        }

        // Check For permission if logged in and set
        if ($route->permission && $this->auth->check()) {
            return $this->sentinel->hasPerm($route->permission);
        }

        // Check for role if logged in and set
        if ($route->roles && $this->auth->check()) {
            $userRoles = $this->auth->check()->roles->pluck('id')->toArray();
            $access = false;
            foreach ($route->roles as $rval) {
                if (in_array($rval->id, $userRoles)) {
                    $access = true;
                }
            }

            if (!$access) {
                $this->flash('danger', 'You do not have permission to access that page.');
                return $this->redirect($response, 'home');
            }
        }

        return $this->view->render($response, 'custom-route.twig', array('route' => $route));
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function maintenance(Request $request, Response $response)
    {
        return $this->view->render($response, 'maintenance.twig');
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function privacy(Request $request, Response $response)
    {
        return $this->view->render($response, 'privacy.twig');
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function terms(Request $request, Response $response)
    {
        return $this->view->render($response, 'terms.twig');
    }
}
