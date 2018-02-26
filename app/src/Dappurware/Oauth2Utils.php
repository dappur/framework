<?php

namespace Dappur\Dappurware;

class Oauth2Utils extends Dappurware
{
    public function apiRequest($url, $post=FALSE, $headers=array(), $json_decode = true) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        if($post){
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        if ($json_decode) {
            return json_decode($response);
        }else{
            return $response;
        }
        
    }

    public function buildBaseString($baseURI, $params){

    $r = array();
        ksort($params);
        foreach($params as $key=>$value){
            $r[] = "$key=" . rawurlencode($value); 
        }             

        return "POST&" . rawurlencode($baseURI) . '&' . rawurlencode(implode('&', $r));
    }

    public function getCompositeKey($consumerSecret, $requestToken){
        return rawurlencode($consumerSecret) . '&' . rawurlencode($requestToken);
    }

    public function buildAuthorizationHeader($oauth){
        $r = 'Authorization: OAuth ';
        $values = array();
        foreach($oauth as $key=>$value)
            $values[] = "$key=\"" . rawurlencode($value) . "\"";

        $r .= implode(', ', $values);
        return $r;
    }

    public function sendRequest($oauth, $baseURI){
        $header = array( $this->buildAuthorizationHeader($oauth), 'Expect:');

        $options = array(CURLOPT_HTTPHEADER => $header,
                               CURLOPT_HEADER => false,
                               CURLOPT_URL => $baseURI, 
                               CURLOPT_POST => true,
                               CURLOPT_RETURNTRANSFER => true,
                               CURLOPT_SSL_VERIFYPEER => false);

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}