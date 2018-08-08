<?php

namespace Dappur\Controller;

use Dappur\Dappurware\Deployment;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class Deploy extends Controller
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function deploy(Request $request, Response $response)
    {
        if (!$this->settings['deployment']['enabled']) {
            $this->flash->addMessage(
                'danger',
                'Deployment is not enabled in the config.'
            );
            $this->logger->addError(
                "Deployment",
                array("message" => "Deployment is not enabled in the config.")
            );
            return $response->withRedirect($this->router->pathFor('home'));
        }

        if ($this->settings['deployment']['repo_url'] == "") {
            $this->flash->addMessage(
                'danger',
                'Please specify a repo url in your deployment config.'
            );
            $this->logger->addError(
                "Deployment",
                array("message" => "Please specify a repo url in your deployment config.")
            );
            return $response->withRedirect($this->router->pathFor('home'));
        }

        $deploy = new Deployment();

        echo $deploy->execute();
        echo $deploy->migrate();
    }
}
