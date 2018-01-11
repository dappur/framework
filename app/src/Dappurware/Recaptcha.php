<?php

namespace Dappur\Dappurware;
use Dappur\Dappurware\Settings;

class Recaptcha extends Dappurware
{

	public function validate($recaptcha_response){

        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = array(
            'secret' => $this->container->settings['recaptcha']['secret_key'],
            'response' => $recaptcha_response
        );
        $options = array(
            'http' => array (
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            )
        );
        $context  = stream_context_create($options);
        $verify = file_get_contents($url, false, $context);
        $captcha_success=json_decode($verify);

        if ($captcha_success->success==false) {
            return false;
        }else{
            return true;
        }
    }

}