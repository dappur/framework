<?php

namespace Dappur\Controller\Admin;

use Dappur\Controller\Controller as Controller;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class Email extends Controller
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $email = new \Dappur\Dappurware\Email($this->container);
        $this->email = $email;
    }

    public function dataTables(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('email.view', 'dashboard')) {
            return $check;
        }

  
        $totalData = new \Dappur\Model\Emails;
        $totalData = $totalData->count();
            
        $totalFiltered = $totalData;

        $limit = $request->getParam('length');
        $start = $request->getParam('start');
        $order = $request->getParam('columns')[$request->getParam('order')[0]['column']]['data'];
        $dir = $request->getParam('order')[0]['dir'];

        $emails = \Dappur\Model\Emails::select('secure_id', 'id', 'send_to', 'subject', 'created_at')
            ->with('recentStatus')
            ->skip($start)
            ->take($limit)
            ->orderBy($order, $dir);
            
        if (!empty($request->getParam('search')['value'])) {
            $search = $request->getParam('search')['value'];

            $emails =  $emails->where('send_to', 'LIKE', "%{$search}%")
                    ->orWhere('subject', 'LIKE', "%{$search}%");

            $totalFiltered = \Dappur\Model\Emails::where('send_to', 'LIKE', "%{$search}%")
                    ->orWhere('subject', 'LIKE', "%{$search}%")
                    ->count();
        }
          
        $jsonData = array(
            "draw"            => intval($request->getParam('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $emails->get()->toArray()
            );

        return $response->withJSON(
            $jsonData,
            200
        );
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function email(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('email.view', 'dashboard')) {
            return $check;
        }

        $emails = \Dappur\Model\Emails::take(200)->get();

        return $this->view->render($response, 'emails.twig', array("emails" => $emails));
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function searchUsers(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('email.create', 'dashboard')) {
            return $check;
        }

        $return = new \stdClass();
        $return->status = "error";

        if (!$request->getParam('search')) {
            $return->message = "Search term not defined.";
            return $response->withJSON($return, 200, JSON_UNESCAPED_UNICODE);
        }

        $users = new \Dappur\Model\Users;
        $users = $users->where(function ($query) use ($request) {
                $query->where('first_name', 'LIKE', "%{$request->getParam('search')}%")
                    ->orWhere('last_name', 'LIKE', "%{$request->getParam('search')}%")
                    ->orWhere('username', 'LIKE', "%{$request->getParam('search')}%")
                    ->orWhere('email', 'LIKE', "%{$request->getParam('search')}%");
        });

        if ($users->count() == 0) {
            $return->message = "No results.";
            return $response->withJSON($return, 200, JSON_UNESCAPED_UNICODE);
        }

        $return->status = "success";
        $return->results = $users->get();

        return $response->withJSON($return, 200, JSON_UNESCAPED_UNICODE);
    }

    public function emailDetails(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('email.details', 'dashboard')) {
            return $check;
        }

        $routeArgs =  $request->getAttribute('route')->getArguments();

        $email = \Dappur\Model\Emails::with('status')->find($routeArgs['email']);

        if (!$email) {
            $this->flash('danger', 'There was a problem finding that email in the database.');
            return $this->redirect($response, 'admin-email');
        }

        $user = \Dappur\Model\Users::where('email', $email->send_to)->first();

        return $this->view->render($response, 'emails-details.twig', array("email" => $email, "user" => $user));
    }

    public function testEmail(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('email.test', 'dashboard')) {
            return $check;
        }

        $user = $this->auth->check();

        $email = new \Dappur\Dappurware\Email($this->container);
        $email = $email->sendEmail(
            array($user->id),
            $request->getParam('subject'),
            $request->getParam('html')
        );

        return $response->write(json_encode($email), 201);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function templates(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('email.templates', 'dashboard')) {
            return $check;
        }

        $templates = \Dappur\Model\EmailsTemplates::take(200)->get();

        return $this->view->render($response, 'emails-templates.twig', array("templates" => $templates));
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function templatesDelete(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('email.templates', 'dashboard')) {
            return $check;
        }

        $check = new \Dappur\Model\EmailsTemplates;
        $check = $check->find($request->getParam('template_id'));

        if ($check) {
            $check->delete();
            $this->flash('success', 'Template has been successfully deleted.');
            return $this->redirect($response, 'admin-email-template');
        }

        $this->flash('danger', 'There was an error deleting the template.');
        return $this->redirect($response, 'admin-email-template');
    }

    public function templatesAdd(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('email.templates', 'dashboard')) {
            return $check;
        }

        $placeholders = $this->email->getPlaceholders();

        if ($request->isPost()) {
            if ($this->email->addTemplate()) {
                $this->flash('success', 'Template has been successfully added.');
                return $this->redirect($response, 'admin-email-template');
            }
            $this->flashNow('danger', 'There was an error adding your template.');
        }

        return $this->view->render($response, 'emails-templates-add.twig', array("placeholders" => $placeholders));
    }

    public function templatesEdit(Request $request, Response $response, $templateId)
    {
        if ($check = $this->sentinel->hasPerm('email.templates', 'dashboard')) {
            return $check;
        }

        $placeholders = $this->email->getPlaceholders();

        $template = new \Dappur\Model\EmailsTemplates;
        $template = $template->find($templateId);

        if (!$template) {
            $this->flash('danger', 'Template not found.');
            return $this->redirect($response, 'admin-email-template');
        }

        if ($request->isPost()) {
            if ($this->email->updateTemplate($templateId)) {
                $this->flash('success', 'Template has been successfully updated.');
                return $this->redirect($response, 'admin-email-template');
            }
            $this->flashNow('danger', 'There was an error updating your template.');
        }

        return $this->view->render(
            $response,
            'emails-templates-edit.twig',
            array(
                "template" => $template,
                "placeholders" => $placeholders
            )
        );
    }

    /** @SuppressWarnings(PHPMD.StaticAccess)  */
    public function emailNew(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('email.create', 'dashboard')) {
            return $check;
        }

        $placeholders = $this->email->getPlaceholders();

        if ($request->isPost()) {
            // Validate Text Fields
            $this->validator->validate(
                $request,
                array(
                    'subject' => array(
                        'rules' => \Respect\Validation\Validator::notEmpty(),
                        'messages' => array(
                            'notEmpty' => 'Cannot be empty.'
                        )
                    ),
                    'html' => array(
                        'rules' => \Respect\Validation\Validator::notEmpty(),
                        'messages' => array(
                            'notEmpty' => 'Cannot be empty.'
                        )
                    )
                )
            );
            
            // Check send_to
            if (empty($request->getParam('send_to'))) {
                $this->validator->addError('send_to', 'Please enter an email address.');
            }

            if ($this->validator->isValid()) {
                $email = new \Dappur\Dappurware\Email($this->container);
                $email = $email->sendEmail(
                    $request->getParam('send_to'),
                    $request->getParam('subject'),
                    $request->getParam('html')
                );

                if ($email['status'] == "success") {
                    $this->flash('success', 'Email has been successfully sent.');
                    return $this->redirect($response, 'admin-email');
                }
                
                $this->flashNow('danger', 'There was a problem sending your email.');
            }
        }

        return $this->view->render(
            $response,
            'emails-new.twig',
            array(
                "placeholders" => $placeholders
            )
        );
    }
}
