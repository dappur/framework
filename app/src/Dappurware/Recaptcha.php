<?php

namespace Dappur\Dappurware;

use Dappur\Dappurware\Settings;

class Recaptcha extends Dappurware
{
    public function validate($recaptchaResponse)
    {
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = array(
            'secret' => $this->container->settings['recaptcha']['secret_key'],
            'response' => $recaptchaResponse
        );
        $options = array(
            'http' => array(
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            ),
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false
            )
        );
        $context  = stream_context_create($options);
        $verify = file_get_contents($url, false, $context);
        $captchaSuccess=json_decode($verify);

        if ($captchaSuccess->success==true) {
            return true;
        }

        return false;
    }
}
