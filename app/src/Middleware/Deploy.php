<?php

namespace Dappur\Middleware;

class Deploy extends Middleware
{
    public function __invoke($request, $response, $next)
    {
        $deployToken = $request->getParam('token');
        if ($deployToken) {
            if ($deployToken != $this->container->settings['deployment']['deploy_token']) {
                $this->logger->addError("Deployment", array("message" => "Deployment token is invalid."));
                throw new \Exception("Deployment token is invalid.");
            }
            if (!$this->container->settings['deployment']['manual']) {
                $this->logger->addError("Deployment", array("message" => "Manual deployment is not enabled."));
                throw new \Exception("Manual deployment is not enabled.");
            }
        }

        if (!$deployToken) {
            // Check Github
            $this->deployGithub($request, $response);
        }

        return $next($request, $response);
    }

    private function deployGithub($request, $response)
    {
        if (!isset($request->getHeader('HTTP_X_HUB_SIGNATURE')[0])) {
            $this->logger->addError("Deployment", array("message" => "HTTP header 'X-Hub-Signature' is missing."));
            throw new \Exception("HTTP header 'X-Hub-Signature' is missing.");
        }

        if (!extension_loaded('hash')) {
            $this->logger->addError(
                "Deployment",
                array("message" => "Missing 'hash' extension to check the secret code validity.")
            );
            throw new \Exception("Missing 'hash' extension to check the secret code validity.");
        }

        list($algo, $hash) = explode('=', $request->getHeader('HTTP_X_HUB_SIGNATURE')[0], 2) + array('', '');
        
        if (!in_array($algo, hash_algos(), true)) {
            $this->logger->addError("Deployment", array("message" => "Hash algorithm '$algo' is not supported."));
            throw new \Exception("Hash algorithm '$algo' is not supported.");
        }
        
        $rawPost = file_get_contents('php://input');
        if ($hash !== hash_hmac($algo, $rawPost, $this->container->settings['deployment']['deploy_token'])) {
            $this->logger->addError("Deployment", array("message" => "Hook secret does not match."));
            throw new \Exception('Hook secret does not match.');
        }
     
        if ($request->getParam('payload')) {
            $payload = json_decode($request->getParam('payload'));
            if ($payload->ref == 'refs/heads/' . $this->container->settings['deployment']['repo_branch']) {
                return true;
            }

            if ($payload->release->target_commitish == $this->container->settings['deployment']['repo_branch']) {
                return true;
            }

            return $response->write(
                "The " + $this->container->settings['deployment']['repo_branch'] + " branch was not deployed."
            );
        }

        $this->logger->addError("Deployment", array("message" => "An unknown error occured deploying from Github."));
        throw new \Exception('An unknown error occured deploying from Github.');
    }
}
