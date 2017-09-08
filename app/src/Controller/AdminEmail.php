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

	public function testEmail(Request $request, Response $response){

		$sentinel = new S($this->container);
        $sentinel->hasPerm('email.test');

        $user = $this->auth->check();

		$email = new E($this->container);
		$email = $email->sendEmail(array($user->id), $request->getParam('subject'), $request->getParam('html'), $request->getParam('plain_text'));

		return $response->write(json_encode($email), 201);

	}

	public function templates(Request $request, Response $response){

		$sentinel = new S($this->container);
        $sentinel->hasPerm('email.template.view');

        $templates = EmailsTemplates::take(200)->get();

        return $this->view->render($response, 'emails-templates.twig', array("templates" => $templates));
	}

	public function templatesAdd(Request $request, Response $response){

    	$sentinel = new S($this->container);
        $sentinel->hasPerm('email.template.create');

        $placeholders = E::getPlaceholders();

        $requestParams = $request->getParams();

        if ($request->isPost()) {
        	
        	// Validate Text Fields
        	$this->validator->validate($request, 
	            array(
	                'name' => array(
	                    'rules' => V::alnum('\'-".,?!@#$%^&')->length(4, 32), 
	                    'messages' => array(
	                        'alnum' => 'May only contain letters and numbers.', 
	                        'length' => 'Must be between 4 and 32 characters.'
	                    )
	                ),
	                'slug' => array(
	                	'rules' => V::slug()->length(4, 32), 
	                    'messages' => array(
	                        'slug' => 'Must be in slug format.',  
	                        'length' => 'Must be between 4 and 32 characters.'
	                    )
	                ),
	                'description' => array(
	                    'rules' => V::alnum('\'-".,?!@#$%^&')->length(4, 255), 
	                    'messages' => array(
	                        'alnum' => 'May only contain letters and numbers.', 
	                        'length' => 'Must be between 4 and 255 characters.'
	                    )
	                )
	            )
	        );

	        // Check for Dupe Slug
	        $slug_check = EmailsTemplates::where('slug', $requestParams['slug'])->first();
	        if ($slug_check) {
	        	$this->validator->addError('slug', 'Slug is already in use.');
	        }
	        
	        // Check Plain Text for HTML
	        if (strip_tags($requestParams['plain_text']) != $requestParams['plain_text']) {
	        	$this->validator->addError('plain_text', 'Plain Text cannot contain HTML.');
	        }

	        // Process Placeholders
	        if ($requestParams['placeholders']) {
	        	foreach ($requestParams['placeholders'] as $plvalue) {
	        		if(!V::alnum('_')->lowercase()->length(4, 32)->validate($plvalue)){
		        		$this->validator->addError('placeholders', 'All custom data fields must be in slug format.');
		        	}
	        	}
	        }

	        if ($this->validator->isValid()) {
	        	$add_template = new EmailsTemplates;
	        	$add_template->name = $requestParams['name'];
	        	$add_template->slug = $requestParams['slug'];
	        	$add_template->description = $requestParams['description'];
	        	$add_template->subject = $requestParams['subject'];
	        	$add_template->html = $requestParams['html'];
	        	$add_template->plain_text = $requestParams['plain_text'];
	        	if ($requestParams['placeholders']) {
		        	$add_template->placeholders = json_encode($requestParams['placeholders']);
	        	}

	        	if ($add_template->save()) {
	        		$this->flash('success', 'Template has been successfully added.');
                    $this->logger->addInfo("Admin Add Email Template", array("message" => "Template has been successfully added.", "template_id" => $add_template->id, "user_id" => $user->id));
                    return $this->redirect($response, 'admin-email-template');
	        	}else{
	        		$this->flash('danger', 'There was a problem adding the template to the database.');
                    $this->logger->addError("Admin Add Email Template", array("message" => "There was an error adding your template to the database.", "template_id" => $add_template->id, "user_id" => $user->id));
                    return $this->redirect($response, 'admin-email-template-add', array("placeholders" => $placeholders, "requestParams" => $requestParams));
	        	}
	        	
	        }

        }

        return $this->view->render($response, 'emails-templates-add.twig', array("placeholders" => $placeholders, "requestParams" => $requestParams));
	}

	public function templatesEdit(Request $request, Response $response){

    	$sentinel = new S($this->container);
        $sentinel->hasPerm('email.template.update');

        $placeholders = E::getPlaceholders();

        $requestParams = $request->getParams();
        $args = $request->getAttribute('routeInfo')[2];

        $template = EmailsTemplates::find($args['template_id']);

        if (!$template) {
        	$this->flash('danger', 'Template has been successfully added.');
            $this->logger->addWarning("Admin Edit Email Template", array("message" => "Template not found.", "template_id" => $args['template_id'], "user_id" => $user->id));
            return $this->redirect($response, 'admin-email-template');
        }
        
        if ($request->isPost()) {
        	
        	// Validate Text Fields
        	$this->validator->validate($request, 
	            array(
	                'name' => array(
	                    'rules' => V::alnum('\'-".,?!@#$%^&')->length(4, 32), 
	                    'messages' => array(
	                        'alnum' => 'May only contain letters and numbers.', 
	                        'length' => 'Must be between 4 and 32 characters.'
	                    )
	                ),
	                'slug' => array(
	                	'rules' => V::slug()->length(4, 32), 
	                    'messages' => array(
	                        'slug' => 'Must be in slug format.',  
	                        'length' => 'Must be between 4 and 32 characters.'
	                    )
	                ),
	                'description' => array(
	                    'rules' => V::alnum('\'-".,?!@#$%^&')->length(4, 255), 
	                    'messages' => array(
	                        'alnum' => 'May only contain letters and numbers.', 
	                        'length' => 'Must be between 4 and 255 characters.'
	                    )
	                )
	            )
	        );

	        // Check for Dupe Slug
	        $slug_check = EmailsTemplates::where('slug', $requestParams['slug'])->first();
	        if ($slug_check && $requestParams['slug'] != $template->slug) {
	        	$this->validator->addError('slug', 'Slug is already in use.');
	        }
	        
	        // Check Plain Text for HTML
	        if (strip_tags($requestParams['plain_text']) != $requestParams['plain_text']) {
	        	$this->validator->addError('plain_text', 'Plain Text cannot contain HTML.');
	        }

	        // Process Placeholders
	        if ($requestParams['placeholders']) {
	        	foreach ($requestParams['placeholders'] as $plvalue) {
	        		if(!V::alnum('_')->lowercase()->length(4, 32)->validate($plvalue)){
		        		$this->validator->addError('placeholders', 'All custom data fields must be in slug format.');
		        	}
	        	}
	        }

	        if ($this->validator->isValid()) {
	        	$template->name = $requestParams['name'];
	        	$template->slug = $requestParams['slug'];
	        	$template->description = $requestParams['description'];
	        	$template->subject = $requestParams['subject'];
	        	$template->html = $requestParams['html'];
	        	$template->plain_text = $requestParams['plain_text'];
	        	if ($requestParams['placeholders']) {
		        	$template->placeholders = json_encode($requestParams['placeholders']);
	        	}

	        	if ($template->save()) {
	        		$this->flash('success', 'Template has been successfully added.');
                    $this->logger->addInfo("Admin Edit Email Template", array("message" => "Template has been updated added.", "template_id" => $template->id, "user_id" => $user->id));
                    return $this->redirect($response, 'admin-email-template');
	        	}else{
	        		$this->flash('danger', 'There was an error updating the template in the database.');
                    $this->logger->addError("Admin Edit Email Template", array("message" => "There was an error updating the template in the database.", "template_id" => $template->id, "user_id" => $user->id));
                    return $this->redirect($response, 'admin-email-template-add', array("template" => $template, "placeholders" => $placeholders, "requestParams" => $requestParams));
	        	}
	        	
	        }

        }

        return $this->view->render($response, 'emails-templates-edit.twig', array("template" => $template, "placeholders" => $placeholders, "requestParams" => $requestParams));

	}
}