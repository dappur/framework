<?php

namespace Dappur\Controller\Webhooks;

use Dappur\Controller\Controller as Controller;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Mailgun extends Controller
{
    public function hook(Request $request, Response $response)
    {
    	// Get Payload
        $payload = json_decode($request->getBody());

        $apiKey = $this->settings['webhooks']['mailgun']['api_key'];

        // Check For Setting
        if (empty($apiKey)) {
        	throw new \Slim\Exception\NotFoundException($request, $response);
        }

        // Check For payload
        if (!isset($payload->signature->timestamp, $payload->signature->token, $payload->signature->signature)) {
        	throw new \Slim\Exception\NotFoundException($request, $response);
        }

        $hash = hash_hmac(
    		'sha256',
    		$payload->signature->timestamp . $payload->signature->token,
    		$apiKey
    	);

    	if ($hash != $payload->signature->signature) {
    		throw new \Slim\Exception\NotFoundException($request, $response);
    	}

    	if (isset($payload->{'event-data'}->message->headers->{'message-id'})) {
			$messageId = $payload->{'event-data'}->message->headers->{'message-id'};
			$email = \Dappur\Model\Emails::where('secure_id', $messageId)->first();

			if ($email) {

                $addStatus = new \Dappur\Model\EmailsStatus;
                $addStatus->status = $payload->{'event-data'}->event;

                switch ($payload->{'event-data'}->event) {
                    case 'clicked':
                        $addStatus->details = $payload->{'event-data'}->url;
                        break;
                }
				$addStatus->save();
				return $response->withJSON(
		            json_encode(array('status' => "success")),
		            200
		        );
			}

			return $response->withJSON(
	            json_encode(array("status" => "error")),
	            200
	        );
		}

		throw new \Slim\Exception\NotFoundException($request, $response);

    }
}
