<?php

namespace Dappur\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;
use Dappur\Model\Emails;
use Dappur\Model\EmailsTemplates;
use Dappur\Dappurware\Sentinel as S;
use Dappur\Dappurware\Email as E;

class AdminEmail extends Controller{

	public function email(Request $request, Response $response){

		$sentinel = new S($this->container);
        $sentinel->hasPerm('email.view');

        $emails = Emails::take(200)->get();

        return $this->view->render($response, 'emails.twig', array("emails" => $emails));
	}

	public function templates(Request $request, Response $response){

		$sentinel = new S($this->container);
        $sentinel->hasPerm('email.template.view');

        $templates = EmailsTemplates::take(200)->get();

        return $this->view->render($response, 'emails-templates.twig', array("templates" => $templates));
	}

	public function templatesAdd(Request $request, Response $response){

		$sentinel = new S($this->container);
        $sentinel->hasPerm('email.template.view');

        $placeholders = E::getPlaceholders();

        $templates = EmailsTemplates::get();

        return $this->view->render($response, 'emails-templates-add.twig', array("templates" => $templates, "placeholders" => $placeholders));
	}
}